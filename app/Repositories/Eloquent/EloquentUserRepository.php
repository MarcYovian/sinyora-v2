<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Collection;
// use App\Models\UserRepository;

class EloquentUserRepository implements UserRepositoryInterface
{
    //

    /**
     * @inheritDoc
     */
    public function all(): Collection
    {
        return User::all();
    }

    /**
     * @inheritDoc
     */
    public function changePassword(int $id, string $currentPassword, string $newPassword): bool
    {
        $user = $this->findById($id);
        if (!$user || !password_verify($currentPassword, $user->password)) {
            return false;
        }

        $user->password = bcrypt($newPassword);
        return $user->save();
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): User
    {
        $user = new User($data);
        $user->password = bcrypt($data['password']);
        $user->save();
        return $user;
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $user = $this->findById($id);
        if (!$user) {
            return false;
        }
        return $user->delete();
    }

    /**
     * @inheritDoc
     */
    public function findByEmail(string $email): User|null
    {
        return User::where('email', $email)->first();
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): User|null
    {
        return User::find($id);
    }

    /**
     * @inheritDoc
     */
    public function findByUsername(string $username): User|null
    {
        return User::where('username', $username)->first();
    }

    /**
     * @inheritDoc
     */
    public function resetPassword(int $id, string $newPassword): bool
    {
        $user = $this->findById($id);
        if (!$user) {
            return false;
        }

        $user->password = bcrypt($newPassword);
        return $user->save();
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): User
    {
        $user = $this->findById($id);
        if (!$user) {
            throw new \Exception('User not found');
        }

        // Update only the fields that are present in the data array
        foreach ($data as $key => $value) {
            if (in_array($key, ['name', 'email'])) {
                $user->$key = $value;
            }
        }

        $user->save();
        return $user;
    }
}
