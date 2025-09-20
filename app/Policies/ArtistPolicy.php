<?php

namespace App\Policies;

use App\Models\Artist;
use App\Models\User;

class ArtistPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin','label','artist']);
    }

    public function view(User $user, Artist $artist): bool
    {
        if ($user->hasRole('admin')) return true;
        if ($user->hasRole('label')) return true; // evt. snevre inn senere
        if ($user->hasRole('artist') && $artist->user_id === $user->id) return true;
        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin','label','artist']);
    }

    public function update(User $user, Artist $artist): bool
    {
        if ($user->hasRole('admin')) return true;
        if ($user->hasRole('label')) return true; // snevres inn senere
        if ($user->hasRole('artist') && $artist->user_id === $user->id) return true;
        return false;
    }

    public function delete(User $user, Artist $artist): bool
    {
        if ($user->hasRole('admin')) return true;
        if ($user->hasRole('label')) return true; // snevres inn senere
        if ($user->hasRole('artist') && $artist->user_id === $user->id) return true;
        return false;
    }
}