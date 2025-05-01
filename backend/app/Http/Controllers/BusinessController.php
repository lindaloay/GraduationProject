<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BusinessController extends Controller
{
    public function storeDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:businesses,email,' . ($request->user()->business ? $request->user()->business->id : 'NULL'),
            'website' => 'nullable|url',
            'phone' => 'required|string|max:20',
            'working_times' => 'required|json',
            'category_id' => 'required|exists:business_categories,id',
            'description' => 'required|string',
            'facebook_link' => 'nullable|url',
            'instagram_link' => 'nullable|url',
            'twitter_link' => 'nullable|url',
            'has_wifi' => 'boolean',
            'has_online_booking' => 'boolean',
            'near_transportation' => 'boolean',
            'has_parking' => 'boolean',
            'main_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gallery_pictures.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'existing_gallery_pictures' => 'nullable|string|json',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = auth()->user();
            $data = $request->except(['main_picture', 'gallery_pictures', 'existing_gallery_pictures']);
            $data['user_id'] = $user->id;

            // Update or create business
            if ($user->business) {
                $user->business->update($data);
                $business = $user->business;
                $message = 'Business details updated successfully';
            } else {
                $business = Business::create($data);
                $message = 'Business details saved successfully';
            }

            // Handle main picture upload
            if ($request->hasFile('main_picture')) {
                // Delete old main picture if exists
                $oldMainPicture = $business->mainPicture;
                if ($oldMainPicture) {
                    // Delete file from storage
                    Storage::disk('public')->delete($oldMainPicture->file_path);
                    // Delete record
                    $oldMainPicture->delete();
                }
                
                // Upload new main picture
                $path = $request->file('main_picture')->store('business/main_pictures', 'public');
                $fileName = basename($path);
                
                // Create record in images table
                Image::create([
                    'business_id' => $business->id,
                    'file_name' => $fileName,
                    'file_path' => $path,
                    'type' => 'main',
                    'order' => 0
                ]);
            }

            // Handle gallery pictures
            if ($request->hasFile('gallery_pictures') || $request->has('existing_gallery_pictures')) {
                // Get existing gallery pictures from database
                $existingDbImages = $business->galleryPictures()->get();
                
                // Parse existing gallery pictures from request
                $keepImageIds = [];
                if ($request->has('existing_gallery_pictures')) {
                    $existingPictureUrls = json_decode($request->input('existing_gallery_pictures'), true);
                    
                    foreach ($existingPictureUrls as $url) {
                        $fileName = basename($url);
                        // Find the image in the database
                        $existingImage = $existingDbImages->first(function($image) use ($fileName) {
                            return $image->file_name === $fileName;
                        });
                        
                        if ($existingImage) {
                            $keepImageIds[] = $existingImage->id;
                        }
                    }
                }
                
                // Delete gallery pictures that are not in the keep list
                foreach ($existingDbImages as $image) {
                    if (!in_array($image->id, $keepImageIds)) {
                        // Delete file from storage
                        Storage::disk('public')->delete($image->file_path);
                        // Delete record
                        $image->delete();
                    }
                }
                
                // Upload new gallery pictures
                if ($request->hasFile('gallery_pictures')) {
                    $order = count($keepImageIds); // Start order after existing images
                    
                    foreach ($request->file('gallery_pictures') as $image) {
                        $path = $image->store('business/gallery', 'public');
                        $fileName = basename($path);
                        
                        // Create record in images table
                        Image::create([
                            'business_id' => $business->id,
                            'file_name' => $fileName,
                            'file_path' => $path,
                            'type' => 'gallery',
                            'order' => $order++
                        ]);
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $business
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error saving business details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDetails()
    {
        try {
            $user = auth()->user();
            $business = $user->business;

            if (!$business) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No business profile found'
                ], 404);
            }

            // Add main picture URL
            $mainImage = Image::where('business_id', $business->id)
                ->where('type', 'main')
                ->first();
            
            if ($mainImage) {
                $business->main_picture_url = $mainImage->image_url;
            }
            
            // Add gallery pictures URLs
            $galleryImages = Image::where('business_id', $business->id)
                ->where('type', 'gallery')
                ->orderBy('order')
                ->get();
                
            if ($galleryImages->count() > 0) {
                $business->gallery_picture_urls = $galleryImages->pluck('image_url')->toArray();
            } else {
                $business->gallery_picture_urls = [];
            }

            return response()->json([
                'status' => 'success',
                'data' => $business
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error fetching business details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getImage($path)
    {
        try {
            // Find the image in the images table
            $image = Image::where('file_name', $path)->first();
            
            if ($image) {
                return response()->file(storage_path('app/public/' . $image->file_path));
            }
            
            return response()->json([
                'status' => 'error',
                'message' => 'Image not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error retrieving image: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteGalleryImage(Request $request)
    {
        try {
            $user = auth()->user();
            $business = $user->business;

            if (!$business) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Business not found'
                ], 404);
            }

            $request->validate([
                'image_path' => 'required|string'
            ]);

            $imagePath = $request->input('image_path');
            $fileName = basename($imagePath);
            
            // Find the image in the database
            $image = Image::where('business_id', $business->id)
                ->where('file_name', $fileName)
                ->where('type', 'gallery')
                ->first();
                
            if ($image) {
                // Delete the file from storage
                Storage::disk('public')->delete($image->file_path);
                
                // Delete the record
                $image->delete();
                
                // Reorder remaining gallery images
                $remainingImages = $business->galleryPictures()->orderBy('order')->get();
                foreach ($remainingImages as $index => $img) {
                    $img->update(['order' => $index]);
                }
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Image deleted successfully'
                ]);
            }
            
            return response()->json([
                'status' => 'error',
                'message' => 'Image not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete image: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAllBusinesses(Request $request)
    {
        try {
            $query = Business::query()
                ->join('users', 'businesses.user_id', '=', 'users.id')
                ->select([
                    'businesses.id',
                    'businesses.name',
                    'businesses.email',
                    'businesses.phone',
                    'users.address',
                    'users.city',
                    'users.country',
                    'businesses.latitude',
                    'businesses.longitude',
                    'businesses.working_times',
                    'businesses.has_wifi',
                    'businesses.has_online_booking',
                    'businesses.near_transportation',
                    'businesses.has_parking',
                    'businesses.facebook_link',
                    'businesses.instagram_link',
                    'businesses.twitter_link',
                    'businesses.website',
                    'businesses.description',
                    'businesses.category_id',
                    'businesses.created_at',
                    'businesses.rating',
                    'businesses.rating_count'
                ]);

            // If search query is provided, filter by name
            if ($request->has('search')) {
                $searchQuery = $request->input('search');
                $query->where('businesses.name', 'like', '%' . $searchQuery . '%');
            }

            // If category filter is provided
            if ($request->has('category_id')) {
                $categoryId = $request->input('category_id');
                $query->where('businesses.category_id', $categoryId);
            }

            $businesses = $query->get();
            
            // Load images and category for each business
            foreach ($businesses as $business) {
                // Add main picture URL
                $mainImage = Image::where('business_id', $business->id)
                    ->where('type', 'main')
                    ->first();
                
                if ($mainImage) {
                    $business->main_picture_url = $mainImage->image_url;
                }
                
                // Add gallery pictures URLs
                $galleryImages = Image::where('business_id', $business->id)
                    ->where('type', 'gallery')
                    ->orderBy('order')
                    ->get();
                    
                if ($galleryImages->count() > 0) {
                    $business->gallery_picture_urls = $galleryImages->pluck('image_url')->toArray();
                } else {
                    $business->gallery_picture_urls = [];
                }

                // Add category information if available
                if ($business->category_id) {
                    $business->category = \App\Models\BusinessCategory::find($business->category_id);
                }
            }

            return response()->json([
                'status' => 'success',
                'businesses' => $businesses
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error fetching businesses: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBusinessById($id)
    {
        try {
            $business = Business::where('businesses.id', $id)
                ->join('users', 'businesses.user_id', '=', 'users.id')
                ->select([
                    'businesses.id',
                    'businesses.name',
                    'businesses.email',
                    'businesses.phone',
                    'users.address',
                    'users.city',
                    'users.country',
                    'businesses.latitude',
                    'businesses.longitude',
                    'businesses.working_times',
                    'businesses.has_wifi',
                    'businesses.has_online_booking',
                    'businesses.near_transportation',
                    'businesses.has_parking',
                    'businesses.facebook_link',
                    'businesses.instagram_link',
                    'businesses.twitter_link',
                    'businesses.website',
                    'businesses.description',
                    'businesses.category_id',
                    'businesses.created_at',
                    'businesses.rating',
                    'businesses.rating_count'
                ])
                ->first();

            if (!$business) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Business not found'
                ], 404);
            }
            
            // Add main picture URL
            $mainImage = Image::where('business_id', $business->id)
                ->where('type', 'main')
                ->first();
            
            if ($mainImage) {
                $business->main_picture_url = $mainImage->image_url;
            }
            
            // Add gallery pictures URLs
            $galleryImages = Image::where('business_id', $business->id)
                ->where('type', 'gallery')
                ->orderBy('order')
                ->get();
                
            if ($galleryImages->count() > 0) {
                $business->gallery_picture_urls = $galleryImages->pluck('image_url')->toArray();
            } else {
                $business->gallery_picture_urls = [];
            }

            // Add category information if available
            if ($business->category_id) {
                $business->category = \App\Models\BusinessCategory::find($business->category_id);
            }

            return response()->json([
                'status' => 'success',
                'business' => $business
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error fetching business: ' . $e->getMessage()
            ], 500);
        }
    }
} 