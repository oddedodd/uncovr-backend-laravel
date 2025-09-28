<?php

namespace App\Filament\Resources\Labels\Pages;

use App\Filament\Resources\Labels\LabelResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EditLabel extends EditRecord
{
    protected static string $resource = LabelResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $label = $this->getRecord();
        $user  = $label->owner;

        // Oppdater tilknyttet bruker ved behov
        if ($user) {
            if (!empty($data['owner_name'] ?? '')) {
                $user->name = (string)$data['owner_name'];
            }
            if (!empty($data['owner_email'] ?? '')) {
                $user->email = (string)$data['owner_email'];
            }
            if (!empty($data['owner_password'] ?? '')) {
                $user->password = Hash::make((string)$data['owner_password']);
            }
            $user->save();
        }

        // Rydd bort de midlertidige feltene fra labels-tabellen
        unset($data['owner_name'], $data['owner_email'], $data['owner_password']);

        // Slug fallback om tomt
        if (empty($data['slug'] ?? '') && !empty($data['name'] ?? '')) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $data;
    }
}