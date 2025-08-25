<?php

namespace App\Repositories\Eloquent;

use App\Models\EventCategory;
use App\Repositories\Contracts\EventCategoryRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentEventCategoryRepository implements EventCategoryRepositoryInterface
{
    //

    /**
     * @inheritDoc
     */
    public function getAllWithKeywords(): Collection {
        return EventCategory::whereNotNull('keywords')->where('keywords', '!=', '')->get();
    }
}
