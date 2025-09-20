<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Lag tre roller
        $roles = ['admin', 'label', 'artist'];
        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r]);
        }

        // Lag en adminbruker (om den ikke finnes)
        $admin = User::firstOrCreate(
            ['email' => 'admin@uncovr.local'],
            ['name' => 'Admin', 'password' => Hash::make('secret123')]
        );

        // Knytt adminrollen til brukeren
        $admin->assignRole('admin');
    }
}