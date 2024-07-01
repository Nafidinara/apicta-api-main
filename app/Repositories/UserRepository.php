<?php

namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class UserRepository implements UserRepositoryInterface
{
    public function getAllUsers()
    {
        return User::all();
    }

    public function getUserById($userId): Model|User|array|Collection
    {
        return User::findOrFail($userId);
    }

    public function deleteUser($userId): int
    {
        return User::destroy($userId);
    }

    public function createUser(array $userData): Model|User
    {
        return User::create($userData);
    }

    public function updateUser($userId, array $userData)
    {
        return User::whereId($userId)->update($userData);
    }
}
