<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\BusinessCategory;
use App\Models\RatingAspect;
use Carbon\Carbon;

class DefaultRatingAspectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all business categories
        $categories = BusinessCategory::all();
        
        $now = Carbon::now();
        
        foreach ($categories as $category) {
            // Check if this category already has a "جودة الخدمة" aspect
            $exists = RatingAspect::where('name', 'جودة الخدمة')
                ->where('category_id', $category->id)
                ->exists();
            
            if (!$exists) {
                RatingAspect::create([
                    'name' => 'جودة الخدمة',
                    'description' => 'تقييم مستوى جودة الخدمة المقدمة',
                    'category_id' => $category->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                
                $this->command->info("Added 'جودة الخدمة' rating aspect for category: {$category->name}");
            } else {
                $this->command->info("Category {$category->name} already has 'جودة الخدمة' rating aspect.");
            }
        }
        
        $this->command->info('Default rating aspects have been added successfully!');
    }
} 