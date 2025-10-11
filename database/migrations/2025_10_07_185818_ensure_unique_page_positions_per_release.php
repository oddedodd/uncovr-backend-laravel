<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Backfill posisjoner (null eller duplikater) per release
        DB::transaction(function () {
            // Hent alle release_id
            $releaseIds = DB::table('pages')->distinct()->pluck('release_id');

            foreach ($releaseIds as $rid) {
                // Hent sider for release, sortert slik at rekkefølgen blir forutsigbar
                $pages = DB::table('pages')
                    ->where('release_id', $rid)
                    ->orderBy('position')        // behold eksisterende pos der det gir mening
                    ->orderBy('created_at')      // stabil fallback
                    ->orderBy('id')
                    ->get();

                $next = 1;
                $seen = [];

                foreach ($pages as $p) {
                    // Bruk neste ledige posisjon hvis null eller duplikat
                    if (is_null($p->position) || isset($seen[$p->position])) {
                        DB::table('pages')
                            ->where('id', $p->id)
                            ->update(['position' => $next]);
                        $seen[$next] = true;
                        $next++;
                    } else {
                        // reserver eksisterende pos og hopp videre
                        $seen[$p->position] = true;
                        while (isset($seen[$next])) {
                            $next++;
                        }
                    }
                }
            }
        });

        // 2) Legg på unik indeks per release
        Schema::table('pages', function (Blueprint $table) {
            // Gi indeksen et navn så vi kan droppe den i down()
            $table->unique(['release_id', 'position'], 'pages_release_position_unique');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropUnique('pages_release_position_unique');
        });
    }
};