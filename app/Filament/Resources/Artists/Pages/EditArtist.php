<?php

namespace App\Filament\Resources\Artists\Pages;

use App\Filament\Resources\Artists\ArtistResource;
use App\Models\Artist;
use App\Models\User;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EditArtist extends EditRecord
{
    protected static string $resource = ArtistResource::class;

    /**
     * Prefyll eier-felter i skjema (kun admin/label ser dem).
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Artist $artist */
        $artist = $this->record;

        if ($artist->user) {
            $data['owner_email'] = $artist->user->email;
            // owner_password prefilles aldri
        }

        return $data;
    }

    /**
     * Rydd og oppdater eier/slug før lagring.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        /** @var Artist $artist */
        $artist = $this->record;

        // 1) Generer unik slug hvis tom
        if (empty($data['slug']) && !empty($data['name'])) {
            $base = Str::slug($data['name']) ?: 'artist';
            $slug = $base;
            $i = 1;
            while (
                Artist::where('slug', $slug)
                    ->whereKeyNot($artist->getKey())
                    ->exists()
            ) {
                $slug = $base . '-' . $i++;
            }
            $data['slug'] = $slug;
        }

        // 2) Eierhåndtering (kun admin/label)
        if (auth()->user()?->hasAnyRole(['admin', 'label'])) {
            $ownerEmail    = $data['owner_email']    ?? null;
            $ownerPassword = $data['owner_password'] ?? null;

            if ($ownerEmail) {
                // Finn eksisterende bruker eller opprett ny
                $user = User::firstOrCreate(
                    ['email' => $ownerEmail],
                    [
                        'name'     => $data['name'] ?? 'Artist Owner',
                        'password' => Hash::make($ownerPassword ?: Str::password(16)),
                    ]
                );

                // Oppdater passord hvis gitt
                if ($ownerPassword) {
                    $user->password = Hash::make($ownerPassword);
                    $user->save();
                }

                if (method_exists($user, 'assignRole') && !$user->hasRole('artist')) {
                    $user->assignRole('artist');
                }

                // Knytt artist til bruker
                $data['user_id'] = $user->id;
            }

            unset($data['owner_email'], $data['owner_password']);
        } else {
            unset($data['owner_email'], $data['owner_password']);
        }

        return $data;
    }
}