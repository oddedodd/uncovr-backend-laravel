<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;

class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin','label','artist']);
    }

    public function view(User $user, Page $page): bool
    {
        if ($user->hasRole('admin') || $user->hasRole('label')) return true;
        return $user->hasRole('artist')
            && $page->release?->artist?->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin','label','artist']);
    }

    public function update(User $user, Page $page): bool
    {
        if ($user->hasRole('admin') || $user->hasRole('label')) return true;
        return $user->hasRole('artist')
            && $page->release?->artist?->user_id === $user->id;
    }

    public function delete(User $user, Page $page): bool
    {
        return $this->update($user, $page);
    }
}