<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BusinessCategory;
use App\Models\RatingAspect;
use Carbon\Carbon;

class AddDefaultRatingAspects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rating:add-defaults';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add default rating aspects to all business categories';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Adding default rating aspects to all categories...');
        
        // Get all business categories
        $categories = BusinessCategory::all();
        
        if ($categories->isEmpty()) {
            $this->error('No business categories found. Please seed categories first.');
            return 1;
        }
        
        $now = Carbon::now();
        $defaultAspect = 'جودة الخدمة'; // Quality of Service
        $added = 0;
        $skipped = 0;
        
        foreach ($categories as $category) {
            // Check if this category already has a "جودة الخدمة" aspect
            $exists = RatingAspect::where('name', $defaultAspect)
                ->where('category_id', $category->id)
                ->exists();
            
            if (!$exists) {
                RatingAspect::create([
                    'name' => $defaultAspect,
                    'description' => 'تقييم مستوى جودة الخدمة المقدمة',
                    'category_id' => $category->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                
                $this->info("Added '{$defaultAspect}' rating aspect for category: {$category->name}");
                $added++;
            } else {
                $this->comment("Category {$category->name} already has '{$defaultAspect}' rating aspect.");
                $skipped++;
            }
        }
        
        $this->info("Completed! Added {$added} rating aspects, skipped {$skipped} that already existed.");
        
        return 0;
    }
} 