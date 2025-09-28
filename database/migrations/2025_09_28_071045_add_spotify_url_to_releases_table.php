<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('releases', function (Blueprint $table) {
            // Postgres: bruk text for trygg lengde
            $table->text('spotify_url')->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('releases', function (Blueprint $table) {
            $table->dropColumn('spotify_url');
        });
    }
};