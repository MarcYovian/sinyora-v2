<?php

namespace App\Repositories\Contracts;

use App\Models\Activity;
use App\Models\Borrowing;
use App\Models\Event;
use App\Models\GuestSubmitter;
use App\Models\User;
use Illuminate\Support\Collection;

interface BorrowingRepositoryInterface
{
    public function all(): Collection;
    public function findById(int $id): ?Borrowing;
    public function create(User|GuestSubmitter $creator, Event|Activity $event, array $data): Borrowing;
    public function update(int $id, array $data): Borrowing;
    public function delete(int $id): bool;
}
