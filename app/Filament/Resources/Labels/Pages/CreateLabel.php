<?php

namespace App\Filament\Resources\Labels\Pages;

use App\Filament\Resources\Labels\LabelResource;
use App\Models\Label;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateLabel extends CreateRecord
{
    protected static string $resource = LabelResource::class;

    /**
     * Vi forventer at formet gir oss:
     * - name, slug (på Label)
     * - owner_name, owner_email, owner_password (IKKE dehydrert; brukes kun her)
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Hent owner-feltene fra komponentens data (ikke dehydrert i schemaet)
        $ownerName     = $this->data['owner_name']     ?? null;
        $ownerEmail    = $this->data['owner_email']    ?? null;
        $ownerPassword = $this->data['owner_password'] ?? null;

        // Siden feltene er required på "create" i schemaet, bør dette være satt.
        // Men vi sjekker og feiler pent hvis ikke.
        if (! $ownerName || ! $ownerEmail || ! $ownerPassword) {
            throw new \RuntimeException('Owner name, email og password er påkrevd.');
        }

        // Opprett (eller finn) bruker på e-post
        $user = User::firstOrNew(['email' => $ownerEmail]);
        $user->name     = $ownerName;
        $user->password = Hash::make($ownerPassword);
        $user->save();

        // Sørg for rolle
        if (method_exists($user, 'assignRole')) {
            $user->assignRole('label');
        }

        // Knytt til Label
        $data['owner_user_id'] = $user->id;

        // Fjern "skjemafeltene" som ikke hører hjemme på Label-tabellen
        unset($data['owner_name'], $data['owner_email'], $data['owner_password']);

        return $data;
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Label opprettet')
            ->success()
            ->send();
    }
}