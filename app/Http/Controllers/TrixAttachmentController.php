<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TrixAttachmentController extends Controller
{
    /**
     * Allowed MIME types for Trix attachments.
     */
    private const ALLOWED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    /**
     * Blocked file extensions (defense-in-depth against MIME spoofing).
     */
    private const BLOCKED_EXTENSIONS = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phps', 'pht', 'phar',
        'sh', 'bash', 'cgi', 'pl', 'py', 'rb', 'asp', 'aspx', 'jsp',
        'exe', 'bat', 'cmd', 'com', 'scr', 'msi', 'dll', 'vbs', 'js',
        'htaccess', 'htpasswd', 'env', 'ini', 'conf', 'config',
    ];

    /**
     * Maximum file size in bytes (5 MB).
     */
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;

    /**
     * Store a Trix attachment.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'attachment' => [
                'required',
                'file',
                'max:5120', // 5MB in KB
                'mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx',
            ],
        ]);

        $file = $request->file('attachment');

        // Defense-in-depth: Block dangerous extensions (even if MIME is spoofed)
        $extension = strtolower($file->getClientOriginalExtension());
        if (in_array($extension, self::BLOCKED_EXTENSIONS)) {
            Log::warning('Blocked file upload attempt via Trix', [
                'user_id' => Auth::id(),
                'filename' => $file->getClientOriginalName(),
                'extension' => $extension,
                'mime' => $file->getMimeType(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Jenis file ini tidak diizinkan.',
            ], 422);
        }

        // Defense-in-depth: Verify actual MIME type
        $actualMime = $file->getMimeType();
        if (!in_array($actualMime, self::ALLOWED_MIMES)) {
            Log::warning('Blocked file upload: MIME type not allowed via Trix', [
                'user_id' => Auth::id(),
                'filename' => $file->getClientOriginalName(),
                'actual_mime' => $actualMime,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Jenis file ini tidak diizinkan.',
            ], 422);
        }

        // Defense-in-depth: Check file size again (in case validation is bypassed)
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            return response()->json([
                'error' => 'Ukuran file terlalu besar. Maksimal 5MB.',
            ], 422);
        }

        // Generate a safe, randomized filename to prevent path traversal
        $safeName = Str::uuid() . '.' . $extension;
        $path = $file->storeAs('trix-attachments', $safeName, 'public');
        $url = Storage::disk('public')->url($path);

        Log::info('Trix attachment uploaded', [
            'user_id' => Auth::id(),
            'original_name' => $file->getClientOriginalName(),
            'stored_path' => $path,
            'size' => $file->getSize(),
            'mime' => $actualMime,
        ]);

        return response()->json([
            'href' => $url,
            'url' => $url,
        ]);
    }

    /**
     * Delete a Trix attachment.
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'url' => ['required', 'string', 'url'],
        ]);

        $url = $request->input('url');

        // Parse URL to get path
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';

        // Remove '/storage/' prefix as it's a symbolic link to storage/app/public
        $relativePath = str_replace('/storage/', '', $path);

        // Security: Only allow deletion within the trix-attachments directory
        if (!Str::startsWith($relativePath, 'trix-attachments/')) {
            Log::warning('Attempted path traversal on Trix attachment delete', [
                'user_id' => Auth::id(),
                'url' => $url,
                'resolved_path' => $relativePath,
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => 'Invalid attachment path.'], 403);
        }

        // Security: Prevent directory traversal via ../ sequences
        $normalizedPath = realpath(Storage::disk('public')->path($relativePath));
        $allowedDirectory = realpath(Storage::disk('public')->path('trix-attachments'));

        if ($normalizedPath === false || $allowedDirectory === false || !Str::startsWith($normalizedPath, $allowedDirectory)) {
            Log::warning('Blocked directory traversal attempt on Trix attachment delete', [
                'user_id' => Auth::id(),
                'url' => $url,
                'resolved_path' => $relativePath,
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => 'Invalid attachment path.'], 403);
        }

        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);

            Log::info('Trix attachment deleted', [
                'user_id' => Auth::id(),
                'path' => $relativePath,
            ]);

            return response()->json(['message' => 'Attachment deleted successfully.']);
        }

        return response()->json(['message' => 'Attachment not found.'], 404);
    }
}
