<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pages', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->foreignId('release_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->string('slug');
            $table->string('page_type')->default('generic');
            $table->unsignedInteger('position')->default(1);
            $table->enum('status', ['draft','published'])->default('draft');
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique(['release_id','slug']);
            $table->index(['release_id','position']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
