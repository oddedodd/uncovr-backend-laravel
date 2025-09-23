<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends \Illuminate\Database\Migrations\Migration {
    public function up(): void
    {
        // Postgres: endre JSON -> TEXT
        DB::statement('ALTER TABLE releases ALTER COLUMN content TYPE TEXT USING content::text');
    }

    public function down(): void
    {
        // Reverser til JSON hvis du vil (tom/ugyldig HTML vil feile her)
        DB::statement("ALTER TABLE releases ALTER COLUMN content TYPE JSON USING NULLIF(content, '')::json");
    }
};