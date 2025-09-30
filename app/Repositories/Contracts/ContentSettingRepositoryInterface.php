<?php

namespace App\Repositories\Contracts;

use App\Models\ContentSetting;
use Illuminate\Support\Collection;

interface ContentSettingRepositoryInterface
{
    /**
     * Temukan ContentSetting berdasarkan ID.
     */
    public function find(int $id): ?ContentSetting;

    /**
     * Ambil semua ContentSetting untuk halaman tertentu.
     */
    public function getByPage(string $page): Collection;

    /**
     * Update ContentSetting berdasarkan ID.
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool;

    /**
     * @param array<string, string> $attributes
     * @param array<string, mixed> $values
     */
    public function updateOrCreate(array $attributes, array $values): ContentSetting;
}
