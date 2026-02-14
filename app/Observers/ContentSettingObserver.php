<?php

namespace App\Observers;

use App\Models\ContentSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ContentSettingObserver
{
    /**
     * The base cache key for content settings.
     */
    protected const CACHE_KEY = 'content_settings';

    /**
     * Handle the ContentSetting "updated" event.
     */
    public function updated(ContentSetting $contentSetting): void
    {
        $this->clearPageCache($contentSetting);
        $this->logChange('updated', $contentSetting);
    }

    /**
     * Handle the ContentSetting "created" event.
     */
    public function created(ContentSetting $contentSetting): void
    {
        $this->clearPageCache($contentSetting);
        $this->logChange('created', $contentSetting);
    }

    /**
     * Handle the ContentSetting "deleted" event.
     */
    public function deleted(ContentSetting $contentSetting): void
    {
        $this->clearPageCache($contentSetting);
        $this->logChange('deleted', $contentSetting);
    }

    /**
     * Clear the cache for the affected page.
     */
    private function clearPageCache(ContentSetting $contentSetting): void
    {
        $page = $contentSetting->page;
        Cache::forget(self::CACHE_KEY . '.' . $page);
    }

    /**
     * Log the content setting change for audit trail.
     */
    private function logChange(string $action, ContentSetting $contentSetting): void
    {
        $original = $contentSetting->getOriginal('value');
        $current = $contentSetting->value;

        // Only log if value actually changed (for updates)
        if ($action === 'updated' && $original === $current) {
            return;
        }

        Log::channel('single')->info("ContentSetting {$action}", [
            'page' => $contentSetting->page,
            'section' => $contentSetting->section,
            'key' => $contentSetting->key,
            'old_value' => $action === 'updated' ? \Illuminate\Support\Str::limit($original, 100) : null,
            'new_value' => \Illuminate\Support\Str::limit($current, 100),
            'user_id' => auth()->id(),
        ]);
    }
}
