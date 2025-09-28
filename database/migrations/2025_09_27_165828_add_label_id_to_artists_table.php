<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artists', function (Blueprint $table) {
            // Legg til kolonne hvis den ikke finnes fra før
            if (! Schema::hasColumn('artists', 'label_id')) {
                // FK til labels.id, nullable (artists kan eksistere uten label),
                // nullOnDelete for at artist beholder record hvis label slettes.
                $table->foreignId('label_id')
                    ->nullable()
                    ->constrained('labels')
                    ->cascadeOnUpdate()
                    ->nullOnDelete()
                    ->after('user_id'); // flytt gjerne hvis du vil ha annen rekkefølge
            }
        });
    }

    public function down(): void
    {
        Schema::table('artists', function (Blueprint $table) {
            // Må droppe constraint før kolonnen i PostgreSQL
            if (Schema::hasColumn('artists', 'label_id')) {
                // Laravel genererer vanligvis navnet: artists_label_id_foreign
                $table->dropForeign(['label_id']);
                $table->dropColumn('label_id');
            }
        });
    }
};