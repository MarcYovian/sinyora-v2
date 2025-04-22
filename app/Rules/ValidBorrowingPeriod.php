<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidBorrowingPeriod implements ValidationRule
{
    public function __construct(
        protected ?string $startDate = null,
        protected ?string $endDate = null
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->startDate || !$this->endDate) {
            $fail('Tanggal mulai dan selesai harus diisi.');
            return;
        }

        if ($attribute === 'start_datetime' && $value >= $this->endDate) {
            $fail('Tanggal mulai harus sebelum tanggal selesai.');
        }

        if ($attribute === 'end_datetime' && $value <= $this->startDate) {
            $fail('Tanggal selesai harus setelah tanggal mulai.');
        }

        $maxEndDate = now()->parse($this->startDate)->addMonths(3);
        if ($attribute === 'end_datetime' && now()->parse($value) > $maxEndDate) {
            $fail('Maksimal periode peminjaman adalah 3 bulan.');
        }
    }
}
