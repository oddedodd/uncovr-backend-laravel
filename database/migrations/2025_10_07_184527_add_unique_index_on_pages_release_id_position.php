<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            // Fjern eventuell eksisterende enkel-indeks om den finnes (valgfritt/trygt å ignorere)
            // $table->dropIndex(['position']);

            $table->unique(['release_id', 'position'], 'pages_release_id_position_unique');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropUnique('pages_release_id_position_unique');
        });
    }
};