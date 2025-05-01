<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BusinessCategory;

class BusinessCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Company',
                'name_ar' => 'شركة',
                'description' => 'Business companies and corporations',
                'icon' => 'mdi-domain',
                'is_active' => true
            ],
            [
                'name' => 'Restaurant',
                'name_ar' => 'مطعم',
                'description' => 'Food and dining establishments',
                'icon' => 'mdi-silverware-fork-knife',
                'is_active' => true
            ],
            [
                'name' => 'Hotel',
                'name_ar' => 'فندق',
                'description' => 'Accommodation and lodging services',
                'icon' => 'mdi-bed',
                'is_active' => true
            ],
            [
                'name' => 'Gym',
                'name_ar' => 'صالة رياضة',
                'description' => 'Fitness centers and gyms',
                'icon' => 'mdi-dumbbell',
                'is_active' => true
            ],
        ];

        foreach ($categories as $category) {
            BusinessCategory::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
