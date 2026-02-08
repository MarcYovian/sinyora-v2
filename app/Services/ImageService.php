<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Interfaces\ImageInterface;

class ImageService
{
    /**
     * Default options for image optimization
     */
    private array $defaultOptions = [
        'path' => 'images',           // Storage path
        'max_width' => 1200,          // Max width in pixels
        'max_height' => null,         // Max height (null = auto based on aspect ratio)
        'quality' => 80,              // Compression quality (1-100)
        'format' => 'webp',           // Output format (webp, jpg, png)
        'keep_original' => false,     // Keep original file alongside optimized
        'disk' => 'public',           // Storage disk
        'watermark' => null,          // Path file watermark (opsional)
        'filename' => null,           // Custom filename (opsional)
    ];

    /**
     * Optimize and store an uploaded image
     *
     * @param UploadedFile $file The uploaded file
     * @param array $options Optimization options
     * @return string The stored file path
     */
    public function optimize(UploadedFile $file, array $options = []): string
    {
        try {
            $options = array_merge($this->defaultOptions, $options);

            // 1. Read Image
            $image = Image::read($file->getRealPath());

            // 2. Auto Rotate (Penting untuk foto dari HP Samsung/iPhone)
            // Intervention v3 biasanya auto-orient, tapi memastikan tidak ada salahnya
            // $image->orientate(); 

            // 3. Resize Logic
            $image = $this->resize($image, $options['max_width'], $options['max_height']);

            // 4. Watermark Logic (Jika ada opsi watermark)
            if (!empty($options['watermark']) && file_exists($options['watermark'])) {
                // Tempel watermark di pojok kanan bawah, opacity 50%
                $image->place($options['watermark'], 'bottom-right', 10, 10, 50);
            }

            // 5. Generate Filename
            $filename = $this->generateFilename($file, $options['format'], $options['filename']);
            $fullPath = $options['path'] . '/' . $filename;

            // 6. Encode
            $encoded = $this->encode($image, $options['format'], $options['quality']);

            // 7. Store
            Storage::disk($options['disk'])->put($fullPath, $encoded);

            // 8. Keep Original?
            if ($options['keep_original']) {
                $ext = $file->getClientOriginalExtension();
                $originalName = pathinfo($filename, PATHINFO_FILENAME) . '_orig.' . $ext;
                Storage::disk($options['disk'])->put(
                    $options['path'] . '/originals/' . $originalName, 
                    file_get_contents($file->getRealPath())
                );
            }

            return $fullPath;

        } catch (Exception $e) {
            // Log error agar developer tahu, tapi jangan bikin crash user
            Log::error("ImageService Error: " . $e->getMessage());
            return null; // Return null jika gagal
        }
    }

    /**
     * Resize image maintaining aspect ratio
     *
     * @param ImageInterface $image
     * @param int|null $maxWidth
     * @param int|null $maxHeight
     * @return ImageInterface
     */
    public function resize(ImageInterface $image, ?int $maxWidth = null, ?int $maxHeight = null): ImageInterface
    {
        if ($maxWidth || $maxHeight) {
            $image->scaleDown(width: $maxWidth, height: $maxHeight);
        }

        return $image;
    }

    /**
     * Encode image to specified format
     *
     * @param ImageInterface $image
     * @param string $format
     * @param int $quality
     * @return string
     */
    private function encode(ImageInterface $image, string $format, int $quality): string
    {
        return match (strtolower($format)) {
            'webp' => $image->toWebp($quality)->toString(),
            'jpg', 'jpeg' => $image->toJpeg($quality)->toString(),
            'png' => $image->toPng()->toString(),
            'gif' => $image->toGif()->toString(),
            default => $image->toWebp($quality)->toString(),
        };
    }

    /**
     * Generate unique filename with new extension
     *
     * @param UploadedFile $file
     * @param string $format
     * @return string
     */
    private function generateFilename(UploadedFile $file, string $format, ?string $customName = null): string
    {
        if ($customName) {
            $name = Str::slug($customName);
        } else {
            $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $name = Str::slug($name);
        }
        
        $uniqueId = Str::random(6);

        return "{$name}-{$uniqueId}.{$format}";
    }

    /**
     * Generate multiple thumbnail sizes
     *
     * @param UploadedFile $file
     * @param array $sizes Array of [width, height] pairs, e.g. [[300, 200], [600, 400]]
     * @param array $options Base options
     * @return array Array of stored file paths
     */
    public function generateThumbnails(UploadedFile $file, array $sizes, array $options = []): array
    {
        $options = array_merge($this->defaultOptions, $options);
        $paths = [];
        
        // Mode: 'cover' (default, crop gambar) atau 'scale' (gambar utuh mengecil)
        $mode = $options['resize_mode'] ?? 'cover'; 

        foreach ($sizes as $key => $size) {
            [$width, $height] = $size;

            $image = Image::read($file->getRealPath());

            if ($mode === 'cover') {
                $image->cover($width, $height);
            } else {
                $image->scaleDown($width, $height);
            }

            $filename = $this->generateFilename($file, $options['format'], $options['filename']);
            
            // Struktur folder: path/300x200/namafile.webp
            $fullPath = $options['path'] . "/{$width}x{$height}/" . $filename;

            $encoded = $this->encode($image, $options['format'], $options['quality']);
            Storage::disk($options['disk'])->put($fullPath, $encoded);

            // Gunakan key custom jika ada (misal 'mobile' => [480, null])
            $paths[$key] = $fullPath; 
        }

        return $paths;
    }

    /**
     * Delete an image from storage
     *
     * @param string|null $path
     * @param string $disk
     * @return bool
     */
    public function delete(?string $path, string $disk = 'public'): bool
    {
        if ($path && Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }
        return false;
    }

    /**
     * Get image dimensions
     *
     * @param string $path
     * @param string $disk
     * @return array{width: int, height: int}|null
     */
    public function getDimensions(string $path, string $disk = 'public'): ?array
    {
        if (!Storage::disk($disk)->exists($path)) {
            return null;
        }

        $image = Image::read(Storage::disk($disk)->path($path));

        return [
            'width' => $image->width(),
            'height' => $image->height(),
        ];
    }
}
