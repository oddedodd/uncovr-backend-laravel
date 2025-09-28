<?php

namespace App\Policies;

use App\Models\Artist;
use App\Models\User;

class ArtistPolicy
{
    /**
     * Bare admin/label kan liste artists i admin.
     * Sett til true for artist hvis du vil tillate listevisning (meny er uansett skjult).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'label']);
    }

    public function view(User $user, Artist $artist): bool
    {
        if ($user->hasAnyRole(['admin', 'label'])) {
            return true;
        }
        // Artist kan se egen Artist-record (hvis du ønsker å tillate det)
        return $user->hasRole('artist') && $artist->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'label']);
    }

    public function update(User $user, Artist $artist): bool
    {
        if ($user->hasAnyRole(['admin', 'label'])) {
            return true;
        }
        // Eventuelt la artist oppdatere sin egen record:
        return $user->hasRole('artist') && $artist->user_id === $user->id;
    }

    public function delete(User $user, Artist $artist): bool
    {
        return $user->hasAnyRole(['admin', 'label']);
    }

    // (andre metoder som restore/forceDelete kan settes tilsvarende)
}