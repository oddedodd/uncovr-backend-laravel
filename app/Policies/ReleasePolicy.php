<?php

namespace App\Policies;

use App\Models\Artist;
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
        if ($user->hasRole('admin')) return true;

        if ($user->hasRole('label')) {
            return optional($release->artist->label)->owner_user_id === $user->id;
        }

        if ($user->hasRole('artist')) {
            return $release->artist?->user_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin','label','artist']);
    }

    public function update(User $user, Release $release): bool
    {
        return $this->view($user, $release);
    }

    public function delete(User $user, Release $release): bool
    {
        return $this->view($user, $release);
    }
}