<?php

namespace App\Filament\Resources\Labels\Pages;

use App\Filament\Resources\Labels\LabelResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateLabel extends CreateRecord
{
    protected static string $resource = LabelResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Valider input for ny eier
        $ownerName  = trim((string)($data['owner_name'] ?? ''));
        $ownerEmail = trim((string)($data['owner_email'] ?? ''));
        $password   = (string)($data['owner_password'] ?? '');

        if ($ownerName === '' || $ownerEmail === '' || $password === '') {
            throw new \RuntimeException('Owner name, email og password er påkrevd.');
        }

        // Opprett ny bruker med rolle "label"
        $user = User::create([
            'name'     => $ownerName,
            'email'    => $ownerEmail,
            'password' => Hash::make($password),
        ]);

        // Tildel rolle
        if (method_exists($user, 'assignRole')) {
            $user->assignRole('label');
        }

        // Koble til label
        $data['owner_user_id'] = $user->id;

        // Rydd bort de midlertidige feltene fra labels-tabellen
        unset($data['owner_name'], $data['owner_email'], $data['owner_password']);

        // Slug fallback om tomt
        if (empty($data['slug'] ?? '') && !empty($data['name'] ?? '')) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $data;
    }
}