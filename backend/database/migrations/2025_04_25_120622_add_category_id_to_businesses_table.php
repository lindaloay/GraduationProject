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
        Schema::table('businesses', function (Blueprint $table) {
            // Add new category_id column
            $table->foreignId('category_id')->nullable()->after('type')
                  ->constrained('business_categories')
                  ->onDelete('set null');
        });
        
        // Migrate existing data: map 'type' values to the new category_id
        // Note: This assumes the business categories are already seeded with IDs 1-4
        $this->migrateExistingData();
    }

    /**
     * Migrate the existing data from type to category_id
     */
    private function migrateExistingData(): void
    {
        // Get type-to-category mapping data from database
        $categories = DB::table('business_categories')->get(['id', 'name'])->keyBy('name');
        
        // Map old types to category IDs
        $typeMapping = [
            'شركة' => $categories->get('Company')->id ?? null,
            'مطعم' => $categories->get('Restaurant')->id ?? null,
            'فندق' => $categories->get('Hotel')->id ?? null,
            'صالة رياضة' => $categories->get('Gym')->id ?? null,
        ];
        
        // Update each business based on its current type
        foreach ($typeMapping as $type => $categoryId) {
            if ($categoryId) {
                DB::table('businesses')
                  ->where('type', $type)
                  ->update(['category_id' => $categoryId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};
