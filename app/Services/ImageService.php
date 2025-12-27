<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
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
        'quality' => 85,              // Compression quality (1-100)
        'format' => 'webp',           // Output format (webp, jpg, png)
        'keep_original' => false,     // Keep original file alongside optimized
        'disk' => 'public',           // Storage disk
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
        $options = array_merge($this->defaultOptions, $options);

        // Read image using Intervention Image
        $image = Image::read($file->getRealPath());

        // Resize if necessary
        $image = $this->resize($image, $options['max_width'], $options['max_height']);

        // Generate unique filename
        $filename = $this->generateFilename($file, $options['format']);
        $fullPath = $options['path'] . '/' . $filename;

        // Encode to desired format with quality
        $encoded = $this->encode($image, $options['format'], $options['quality']);

        // Store the optimized image
        Storage::disk($options['disk'])->put($fullPath, $encoded);

        // Optionally keep original
        if ($options['keep_original']) {
            $originalPath = $options['path'] . '/originals/' . $file->hashName();
            Storage::disk($options['disk'])->put($originalPath, file_get_contents($file->getRealPath()));
        }

        return $fullPath;
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
        $currentWidth = $image->width();
        $currentHeight = $image->height();

        // Calculate new dimensions while maintaining aspect ratio
        if ($maxWidth && $currentWidth > $maxWidth) {
            $image = $image->scale(width: $maxWidth);
        }

        if ($maxHeight && $image->height() > $maxHeight) {
            $image = $image->scale(height: $maxHeight);
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
        return match ($format) {
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
    private function generateFilename(UploadedFile $file, string $format): string
    {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $name = Str::slug($name);
        $uniqueId = Str::random(8);

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

        foreach ($sizes as $size) {
            [$width, $height] = $size;

            $image = Image::read($file->getRealPath());
            $image = $image->cover($width, $height);

            $filename = $this->generateFilename($file, $options['format']);
            $fullPath = $options['path'] . "/{$width}x{$height}/" . $filename;

            $encoded = $this->encode($image, $options['format'], $options['quality']);
            Storage::disk($options['disk'])->put($fullPath, $encoded);

            $paths["{$width}x{$height}"] = $fullPath;
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
