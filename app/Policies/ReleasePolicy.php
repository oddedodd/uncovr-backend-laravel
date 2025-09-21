<?php

namespace App\Policies;

use App\Models\Release;
use App\Models\User;

class ReleasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin','label','artist']);
    }

    public function view(User $user, Release $release): bool
    {
        if ($user->hasRole('admin') || $user->hasRole('label')) return true;
        return $user->hasRole('artist') && $release->artist?->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin','label','artist']);
    }

    public function update(User $user, Release $release): bool
    {
        if ($user->hasRole('admin') || $user->hasRole('label')) return true;
        return $user->hasRole('artist') && $release->artist?->user_id === $user->id;
    }

    public function delete(User $user, Release $release): bool
    {
        return $this->update($user, $release);
    }
}