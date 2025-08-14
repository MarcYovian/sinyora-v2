<?php

namespace App\Repositories\Contracts;

use App\Models\Event;
use App\Models\GuestSubmitter;
use App\Models\User;
use Illuminate\Support\Collection;

interface EventRepositoryInterface
{
    public function all(): Collection;
    public function findById(int $id): ?Event;
    public function create(User|GuestSubmitter $creator, Event $data): Event;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function findByOrganizationId(int $organizationId): Collection;
}
