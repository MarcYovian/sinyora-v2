<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class HorizontalImage implements ValidationRule
{
    /**
     * Minimum aspect ratio (width/height).
     * 16:9 = 1.78, we use 1.5 (3:2) as minimum to be more flexible
     */
    protected float $minRatio = 1.5;

    /**
     * Maximum aspect ratio to prevent extremely wide images.
     * 3:1 = 3.0
     */
    protected float $maxRatio = 3.0;

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            return;
        }

        $imageInfo = @getimagesize($value->getPathname());

        if ($imageInfo === false) {
            $fail('File yang diupload bukan gambar yang valid.');
            return;
        }

        [$width, $height] = $imageInfo;

        // Check if image is horizontal (landscape)
        if ($width <= $height) {
            $fail('Gambar harus dalam orientasi horizontal (landscape). Ukuran saat ini: ' . $width . 'x' . $height . 'px.');
            return;
        }

        // Calculate aspect ratio
        $aspectRatio = $width / $height;

        if ($aspectRatio < $this->minRatio) {
            $fail('Rasio gambar terlalu kotak. Gunakan gambar dengan rasio minimal 3:2 (landscape). Rasio saat ini: ' . number_format($aspectRatio, 2) . ':1.');
            return;
        }

        if ($aspectRatio > $this->maxRatio) {
            $fail('Rasio gambar terlalu lebar. Gunakan gambar dengan rasio maksimal 3:1. Rasio saat ini: ' . number_format($aspectRatio, 2) . ':1.');
            return;
        }
    }
}
