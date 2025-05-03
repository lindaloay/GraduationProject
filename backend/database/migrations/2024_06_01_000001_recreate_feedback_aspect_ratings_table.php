<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing table if it exists
        Schema::dropIfExists('feedback_aspect_ratings');
        
        // Create the table with the correct foreign keys
        Schema::create('feedback_aspect_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_id')->references('id')->on('feedbacks')->onDelete('cascade');
            $table->foreignId('aspect_id')->references('id')->on('rating_aspects')->onDelete('cascade');
            $table->integer('rating')->comment('Rating value from 1 to 5');
            $table->timestamps();
            
            // Each feedback can have only one rating for each aspect
            $table->unique(['feedback_id', 'aspect_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_aspect_ratings');
    }
}; 