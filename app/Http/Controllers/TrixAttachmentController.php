<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TrixAttachmentController extends Controller
{
    public function store(Request $request)
    {
        $path = $request->attachment->store('trix-attachments', 'public');
        $url = Storage::disk('public')->url($path);

        return [
            'href' => $url,
            'url' => $url,
        ];
    }

    public function destroy(Request $request)
    {
        $url = $request->url;

        // Parse URL untuk mendapatkan path
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'];

        // Hapus prefix '/storage' karena itu adalah symbolic link ke 'storage/app/public'
        $relativePath = str_replace('/storage/', '', $path);

        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
            return response()->json(['message' => 'Attachment deleted successfully.']);
        }

        return response()->json(['message' => 'Attachment not found.'], 404);
    }
}
