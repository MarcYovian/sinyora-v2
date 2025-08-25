<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface EventCategoryRepositoryInterface
{
    public function getAllWithKeywords(): Collection;
}
