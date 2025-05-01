<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Business;
use App\Models\Image;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing data from Business to Image table
        $businesses = Business::all();
        
        foreach ($businesses as $business) {
            // Migrate main picture
            if ($business->main_picture) {
                Image::create([
                    'business_id' => $business->id,
                    'file_name' => $business->main_picture,
                    'file_path' => 'business/main_pictures/' . $business->main_picture,
                    'type' => 'main',
                    'order' => 0,
                ]);
            }
            
            // Migrate gallery pictures
            if ($business->gallery_pictures && is_array($business->gallery_pictures)) {
                foreach ($business->gallery_pictures as $index => $fileName) {
                    Image::create([
                        'business_id' => $business->id,
                        'file_name' => $fileName,
                        'file_path' => 'business/gallery/' . $fileName,
                        'type' => 'gallery',
                        'order' => $index,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete all images
        Image::truncate();
    }
};
