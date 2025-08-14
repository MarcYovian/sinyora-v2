<?php

namespace App\Services;

use App\Models\EventCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class EventCategoryClassifier
{
    /**
     * The name of the default category to fall back to.
     */
    const DEFAULT_CATEGORY_NAME = 'Lain-lain';

    /**
     * @var Collection|null
     */
    private $categories;

    /**
     * @var int|null
     */
    private $defaultCategoryId;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->categories = Cache::rememberForever('event_categories_with_keywords', function () {
            return EventCategory::where('is_active', true)->get();
        });
        $this->defaultCategoryId = $this->categories
            ->firstWhere('name', self::DEFAULT_CATEGORY_NAME)
            ?->id;
    }

    /**
     * Classifies an event into a category based on its name and description.
     *
     * @param string $eventName The name of the event.
     * @param string|null $eventDescription The description of the event.
     * @return int|null The ID of the matched category, or null if no match is found.
     */
    public function classify(string $eventName, ?string $eventDescription): ?int
    {
        // 1. Combine name and description into a single, lowercase string for easy searching.
        $searchableText = Str::lower($eventName . ' ' . $eventDescription);

        // 2. Iterate through each category.
        foreach ($this->categories as $category) {
            // Ensure keywords is an array and not empty.
            if (empty($category->keywords) || !is_array($category->keywords)) {
                continue;
            }

            // 3. Iterate through each keyword of the category.
            foreach ($category->keywords as $keyword) {
                // 4. Check if the searchable text contains the keyword.
                // We add spaces around the keyword to match whole words, preventing partial matches
                // e.g., preventing "misa" from matching "komisaris".
                $pattern = '/\b' . preg_quote(Str::lower($keyword), '/') . '\b/';
                // dd($category->keywords, $keyword, $searchableText, preg_match($pattern, $searchableText));
                if (preg_match($pattern, $searchableText)) {
                    // 5. If a match is found, return the category ID immediately.
                    return $category->id;
                }
            }
        }
        // 6. If no keywords match after checking all categories, return null.
        return $this->defaultCategoryId;
    }
}
