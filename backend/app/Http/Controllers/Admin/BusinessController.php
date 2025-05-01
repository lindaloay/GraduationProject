<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BusinessController extends Controller
{
    /**
     * Display a listing of businesses.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Business::with(['user', 'mainPicture', 'category']);
        
        // Handle search parameter
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('phone', 'like', "%{$searchTerm}%")
                  ->orWhereHas('user', function($userQuery) use ($searchTerm) {
                      $userQuery->where('city', 'like', "%{$searchTerm}%");
                  });
            });
        }
        
        // Handle category filter
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }
        
        // Handle sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        
        if ($sortBy === 'category') {
            // Join with categories table to sort by category name
            $query->select('businesses.*')
                  ->leftJoin('business_categories', 'businesses.category_id', '=', 'business_categories.id')
                  ->orderBy('business_categories.name_ar', $sortOrder);
        } elseif ($sortBy === 'user') {
            // Join with users table to sort by owner's name
            $query->select('businesses.*')
                  ->leftJoin('users', 'businesses.user_id', '=', 'users.id')
                  ->orderBy('users.name', $sortOrder);
        } elseif ($sortBy === 'rating') {
            // Sort by the business rating
            $query->orderBy('rating', $sortOrder);
        } else {
            // Default sorting for actual columns
            $query->orderBy($sortBy, $sortOrder);
        }
        
        // Pagination
        $perPage = $request->input('per_page', 10);
        $businesses = $query->paginate($perPage);
        
        // Format the business data
        $formattedBusinesses = $businesses->map(function ($business) {
            $business->created_since = $this->getTimeSince($business->created_at);
            
            // Add direct access to main picture URL
            if ($business->mainPicture) {
                $business->main_picture_url = $business->mainPicture->image_url;
            }
            
            return $business;
        });
        
        return response()->json([
            'status' => 'success',
            'businesses' => $formattedBusinesses,
            'pagination' => [
                'total' => $businesses->total(),
                'per_page' => $businesses->perPage(),
                'current_page' => $businesses->currentPage(),
                'last_page' => $businesses->lastPage(),
                'from' => $businesses->firstItem(),
                'to' => $businesses->lastItem()
            ]
        ]);
    }

    /**
     * Display the specified business.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $business = Business::with(['user', 'mainPicture', 'galleryPictures', 'category'])
            ->findOrFail($id);
        
        // Add direct access to picture URLs
        if ($business->mainPicture) {
            $business->main_picture_url = $business->mainPicture->image_url;
        }
        
        // Add gallery picture URLs
        $galleryImages = $business->galleryPictures;
        $business->gallery_pictures_urls = $galleryImages->map(function($image) {
            return $image->image_url;
        })->toArray();
        
        return response()->json([
            'status' => 'success',
            'business' => $business
        ]);
    }

    /**
     * Remove the specified business from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $business = Business::findOrFail($id);
        
        // Delete related images
        if ($business->mainPicture) {
            $path = str_replace('/storage/', '', $business->mainPicture->url);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            $business->mainPicture->delete();
        }
        
        // Delete gallery images
        foreach ($business->galleryPictures as $image) {
            $path = str_replace('/storage/', '', $image->url);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            $image->delete();
        }
        
        // Delete business
        $business->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Business deleted successfully'
        ]);
    }
    
    /**
     * Update the specified business in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validate request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:business_categories,id',
            'description' => 'required|string',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'website' => 'nullable|string|max:255',
            'working_times' => 'nullable',
            'facebook_link' => 'nullable|string|max:255',
            'instagram_link' => 'nullable|string|max:255',
            'twitter_link' => 'nullable|string|max:255',
        ]);
        
        // Find the business
        $business = Business::findOrFail($id);
        
        // Ensure working_times is valid JSON
        if (isset($validated['working_times'])) {
            // Check if it's already a valid JSON string
            try {
                json_decode($validated['working_times']);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    // Not valid JSON, convert it to a JSON string
                    $validated['working_times'] = json_encode($validated['working_times']);
                }
            } catch (\Exception $e) {
                // If there's any error, just encode it
                $validated['working_times'] = json_encode($validated['working_times']);
            }
        }
        
        // Update business
        $business->update($validated);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Business updated successfully',
            'business' => $business
        ]);
    }
    
    /**
     * Update business with image uploads
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateWithImages(Request $request, $id)
    {
        // Validate request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:business_categories,id',
            'description' => 'required|string',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'website' => 'nullable|string|max:255',
            'working_times' => 'nullable',
            'facebook_link' => 'nullable|string|max:255',
            'instagram_link' => 'nullable|string|max:255',
            'twitter_link' => 'nullable|string|max:255',
            'main_image' => 'nullable|image|max:2048', // Max 2MB
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'nullable|image|max:2048',
            'gallery_images_to_remove' => 'nullable|string',
        ]);
        
        // Find the business
        $business = Business::findOrFail($id);
        
        // Process working_times for JSON
        if (isset($validated['working_times'])) {
            try {
                json_decode($validated['working_times']);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $validated['working_times'] = json_encode($validated['working_times']);
                }
            } catch (\Exception $e) {
                $validated['working_times'] = json_encode($validated['working_times']);
            }
        }
        
        // Update basic business data
        $business->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'description' => $validated['description'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'website' => $validated['website'],
            'working_times' => $validated['working_times'],
            'facebook_link' => $validated['facebook_link'],
            'instagram_link' => $validated['instagram_link'],
            'twitter_link' => $validated['twitter_link'],
        ]);
        
        // Handle main image upload if present
        if ($request->hasFile('main_image')) {
            // Delete old main image if exists
            if ($business->mainPicture) {
                // Get the path and delete the physical file
                $path = str_replace('/storage/', '', $business->mainPicture->url);
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
                
                // Update the existing main image record
                $mainImage = $business->mainPicture;
                $fileName = 'business_main_' . time() . '.' . $request->file('main_image')->extension();
                $filePath = $request->file('main_image')->storeAs('businesses/' . $business->id, $fileName, 'public');
                
                $mainImage->update([
                    'file_path' => $filePath,
                    'file_name' => $fileName,
                    'type' => 'main'
                ]);
            } else {
                // Create new main image record
                $fileName = 'business_main_' . time() . '.' . $request->file('main_image')->extension();
                $filePath = $request->file('main_image')->storeAs('businesses/' . $business->id, $fileName, 'public');
                
                $business->images()->create([
                    'file_path' => $filePath,
                    'file_name' => $fileName,
                    'type' => 'main',
                    'order' => 0
                ]);
            }
        }
        
        // Handle gallery images upload if present
        if ($request->hasFile('gallery_images')) {
            $galleryImages = $request->file('gallery_images');
            $maxOrder = $business->galleryPictures()->max('order') ?? 0;
            
            foreach ($galleryImages as $index => $image) {
                $fileName = 'business_gallery_' . time() . '_' . $index . '.' . $image->extension();
                $filePath = $image->storeAs('businesses/' . $business->id . '/gallery', $fileName, 'public');
                
                $business->images()->create([
                    'file_path' => $filePath,
                    'file_name' => $fileName,
                    'type' => 'gallery',
                    'order' => $maxOrder + $index + 1
                ]);
            }
        }
        
        // Handle gallery images to remove
        if ($request->has('gallery_images_to_remove') && !empty($request->gallery_images_to_remove)) {
            try {
                $imageUrlsToRemove = json_decode($request->gallery_images_to_remove, true);
                \Log::info('Gallery images to remove: ' . print_r($imageUrlsToRemove, true));
                
                if (is_array($imageUrlsToRemove) && count($imageUrlsToRemove) > 0) {
                    foreach ($imageUrlsToRemove as $imageUrl) {
                        \Log::info('Trying to remove image: ' . $imageUrl);
                        
                        // Clean up the URL to help with finding the image
                        $cleanUrl = $imageUrl;
                        $baseUrl = url('');
                        
                        // Remove the base URL if present
                        if (strpos($cleanUrl, $baseUrl) === 0) {
                            $cleanUrl = substr($cleanUrl, strlen($baseUrl));
                        }
                        
                        // Remove storage prefix if present
                        if (strpos($cleanUrl, '/storage/') === 0) {
                            $cleanUrl = substr($cleanUrl, strlen('/storage/'));
                        }
                        
                        \Log::info('Cleaned URL: ' . $cleanUrl);
                        
                        // Try multiple ways to find the image
                        $image = null;
                        
                        // Try by full URL
                        $image = $business->images()->where('file_path', $imageUrl)->first();
                        
                        // Try by path with /storage/
                        if (!$image && strpos($imageUrl, '/storage/') === 0) {
                            $pathWithoutStorage = substr($imageUrl, strlen('/storage/'));
                            $image = $business->images()->where('file_path', $pathWithoutStorage)->first();
                        }
                        
                        // Try by cleaned path
                        if (!$image) {
                            $image = $business->images()->where('file_path', $cleanUrl)->first();
                        }
                        
                        // Try by partial match
                        if (!$image) {
                            // Get all gallery images
                            $galleryImages = $business->galleryPictures;
                            
                            // Look for matching image by URL suffix
                            foreach ($galleryImages as $galleryImage) {
                                // Get the filename part of the URL
                                $filename = basename($imageUrl);
                                $galleryFilename = basename($galleryImage->file_path);
                                
                                if ($filename === $galleryFilename) {
                                    $image = $galleryImage;
                                    break;
                                }
                            }
                        }
                        
                        if ($image) {
                            \Log::info('Found image to delete: ' . $image->id . ' - ' . $image->file_path);
                            
                            // Delete the physical file
                            $path = $image->file_path;
                            if (Storage::disk('public')->exists($path)) {
                                Storage::disk('public')->delete($path);
                                \Log::info('Deleted file: ' . $path);
                            } else {
                                \Log::warning('File not found: ' . $path);
                            }
                            
                            // Delete the database record
                            $image->delete();
                            \Log::info('Deleted image record from database');
                        } else {
                            \Log::warning('Image not found for removal: ' . $imageUrl);
                        }
                    }
                }
            } catch (\Exception $e) {
                // Log error but continue
                \Log::error('Error removing gallery images: ' . $e->getMessage());
            }
        }
        
        // Get updated business with images
        $updatedBusiness = Business::with(['user', 'mainPicture', 'galleryPictures', 'category'])
            ->findOrFail($id);
        
        // Add direct access to picture URLs
        if ($updatedBusiness->mainPicture) {
            $updatedBusiness->main_picture_url = $updatedBusiness->mainPicture->image_url;
        }
        
        // Add gallery picture URLs
        $updatedBusiness->gallery_pictures_urls = $updatedBusiness->galleryPictures->pluck('image_url')->toArray();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Business updated successfully with images',
            'business' => $updatedBusiness
        ]);
    }
    
    /**
     * Get time since creation in Arabic format
     *
     * @param  \Carbon\Carbon|string  $date
     * @return string
     */
    private function getTimeSince($date)
    {
        if (!$date) return '';
        
        $carbon = $date instanceof Carbon ? $date : new Carbon($date);
        $now = Carbon::now();
        $diff = $carbon->diffInSeconds($now);
        
        if ($diff < 60) {
            return 'منذ لحظات';
        } else if ($diff < 3600) {
            $minutes = floor($diff / 60);
            return "منذ {$minutes} " . ($minutes == 1 ? 'دقيقة' : 'دقائق');
        } else if ($diff < 86400) {
            $hours = floor($diff / 3600);
            return "منذ {$hours} " . ($hours == 1 ? 'ساعة' : 'ساعات');
        } else if ($diff < 2592000) {
            $days = floor($diff / 86400);
            return "منذ {$days} " . ($days == 1 ? 'يوم' : 'أيام');
        } else if ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return "منذ {$months} " . ($months == 1 ? 'شهر' : 'أشهر');
        } else {
            $years = floor($diff / 31536000);
            return "منذ {$years} " . ($years == 1 ? 'سنة' : 'سنوات');
        }
    }
} 