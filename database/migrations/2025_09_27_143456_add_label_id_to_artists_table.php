<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('artists', function (Blueprint $table) {
            $table->foreignId('label_id')->nullable()->after('user_id')->constrained('labels')->nullOnDelete();
        });
    }

    public function down(): void {
        Schema::table('artists', function (Blueprint $table) {
            $table->dropConstrainedForeignId('label_id');
        });
    }
};