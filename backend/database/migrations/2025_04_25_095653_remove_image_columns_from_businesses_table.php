<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration should be run after all data has been migrated to the new images table.
     */
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('main_picture');
            $table->dropColumn('gallery_pictures');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('main_picture')->nullable();
            $table->text('gallery_pictures')->nullable();
        });
    }
};
