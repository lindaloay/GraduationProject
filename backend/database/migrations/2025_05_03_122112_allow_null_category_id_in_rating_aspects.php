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
        Schema::table('rating_aspects', function (Blueprint $table) {
            // Make category_id nullable
            $table->foreignId('category_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rating_aspects', function (Blueprint $table) {
            // Revert to non-nullable
            $table->foreignId('category_id')->nullable(false)->change();
        });
    }
};
