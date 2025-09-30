<?php

namespace App\Services;

use App\Repositories\Contracts\ContentSettingRepositoryInterface;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ContentService
{
    protected ContentSettingRepositoryInterface $contentSettingRepository;
    protected CacheRepository $cache;

    /**
     * The base cache key for content settings.
     */
    protected const CACHE_KEY = 'content_settings';

    /**
     * Create a new class instance.
     *
     * @param ContentSettingRepositoryInterface $contentSettingRepository
     * @param CacheRepository $cache
     */
    public function __construct(ContentSettingRepositoryInterface $contentSettingRepository, CacheRepository $cache)
    {
        $this->contentSettingRepository = $contentSettingRepository;
        $this->cache = $cache;
    }

    /**
     * Get a specific content value for a page, section, and key.
     *
     * @param string $page
     * @param string $section
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $page, string $section, string $key, mixed $default = null): mixed
    {
        $pageContent = $this->getPage($page);

        return data_get($pageContent, "{$section}.{$key}", $default);
    }

    /**
     * Get all formatted content for a specific page.
     *
     * @param string $page
     * @return array<string, array<string, string>>
     */
    public function getPage(string $page): array
    {
        $cacheKey = self::CACHE_KEY . '.' . $page;

        return $this->cache->rememberForever($cacheKey, function () use ($page) {
            $settings = $this->contentSettingRepository->getByPage($page);

            return $this->formatContent($settings);
        });
    }

    /**
     * Clear the cache for a specific page.
     *
     * @param string $page
     * @return bool
     */
    public function clearCache(string $page): bool
    {
        return $this->cache->forget(self::CACHE_KEY . '.' . $page);
    }

    /**
     * Get all content for a page, grouped by section, for admin editing.
     *
     * @param string $page
     * @return array
     */
    public function getGroupedContent(string $page): array
    {
        $settings = $this->contentSettingRepository->getByPage($page);

        return $settings->groupBy('section')
            ->map(function ($sectionSettings) {
                return $sectionSettings->mapWithKeys(function ($setting) {
                    return [$setting->key => [
                        'id' => $setting->id,
                        'value' => $setting->value,
                        'type' => $setting->type,
                    ]];
                });
            })
            ->all();
    }

    /**
     * Update multiple content settings at once within a database transaction.
     *
     * @param string $page The page identifier to clear cache for.
     * @param array<int, array<string, mixed>> $updates Array of updates, each with 'id' and 'value'.
     * @return void
     */
    public function updateContent(string $page, array $updates): void
    {
        if (empty($updates)) {
            return;
        }

        // Ekstrak IDs dan siapkan statement CASE
        $ids = [];
        $cases = [];
        $bindings = [];

        foreach ($updates as $update) {
            $id = (int) $update['id'];
            $ids[] = $id;
            $cases[] = "WHEN {$id} THEN ?";
            $bindings[] = $update['value'];
        }

        $idsString = implode(',', $ids);
        $casesSql = implode(' ', $cases);

        DB::transaction(function () use ($idsString, $casesSql, $bindings, $page, $ids) {
            // Gabungkan bindings untuk WHERE IN clause
            $allBindings = array_merge($bindings, $ids);

            // Lakukan 1 query untuk semua update
            DB::update(
                "UPDATE content_settings SET `value` = CASE `id` {$casesSql} END WHERE `id` IN ({$idsString})",
                $bindings
            );

            // Hapus cache HANYA jika transaksi berhasil
            $this->clearCache($page);
        });
    }

    private function formatContent(Collection $settings): array
    {
        return $settings->groupBy('section')
            ->map(fn($section) => $section->pluck('value', 'key')->all())
            ->all();
    }
}
