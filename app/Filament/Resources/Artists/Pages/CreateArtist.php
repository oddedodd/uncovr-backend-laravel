<?php

namespace App\Filament\Resources\Artists\Pages;

use App\Filament\Resources\Artists\ArtistResource;
use App\Models\Artist;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateArtist extends CreateRecord
{
    protected static string $resource = ArtistResource::class;

    /**
     * Vi håndterer opprettelsen for å lage/koble bruker først.
     */
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // 1) Generer unik slug hvis tom
        $base = !empty($data['slug'] ?? '')
            ? Str::slug((string) $data['slug'])
            : Str::slug((string) ($data['name'] ?? ''));

        $base = $base !== '' ? $base : 'artist';
        $slug = $base;
        $i = 1;
        while (Artist::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        $data['slug'] = $slug;

        // 2) Opprett/koble bruker (admin/label)
        if (auth()->user()?->hasAnyRole(['admin', 'label'])) {
            $ownerEmail    = $data['owner_email']    ?? null;
            $ownerPassword = $data['owner_password'] ?? null;

            if (!$ownerEmail) {
                throw new \RuntimeException('Owner email is required.');
            }

            $user = User::where('email', $ownerEmail)->first();

            if (!$user) {
                if (!$ownerPassword) {
                    throw new \RuntimeException('Owner password is required when creating a new user.');
                }

                $user = User::create([
                    'name'     => $data['name'] ?? 'Artist Owner',
                    'email'    => $ownerEmail,
                    'password' => Hash::make($ownerPassword),
                ]);
            } else {
                // Hvis passord er oppgitt ved opprettelse med eksisterende bruker, oppdater det
                if ($ownerPassword) {
                    $user->password = Hash::make($ownerPassword);
                    $user->save();
                }
            }

            if (method_exists($user, 'assignRole') && !$user->hasRole('artist')) {
                $user->assignRole('artist');
            }

            $data['user_id'] = $user->id;

            unset($data['owner_email'], $data['owner_password']);
        } else {
            // Artist (om tilgjengelig) – lås til seg selv
            $data['user_id'] = auth()->id();
            unset($data['owner_email'], $data['owner_password']);
        }

        // 3) Opprett artist
        return Artist::create($data);
    }
}