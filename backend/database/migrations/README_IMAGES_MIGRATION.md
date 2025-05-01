# Business Images Migration Guide

This guide explains the process of migrating from storing business images directly in the `businesses` table to using a new dedicated `images` table.

## Migration Overview

The migration process involves the following steps:

1. Create a new `images` table with appropriate columns
2. Modify the Business model to add relationships with the Images model
3. Update the BusinessController to use the new Images model
4. Migrate existing image data from the businesses table to the images table
5. Remove the old image columns from the businesses table

## Migration Files

The following migration files handle this process:

1. `2025_04_25_095346_create_images_table.php` - Creates the new images table
2. `2025_04_25_095444_migrate_business_images_to_images_table.php` - Migrates existing data to the new table
3. `2025_04_25_095653_remove_image_columns_from_businesses_table.php` - Removes old columns from businesses table

## Running the Migrations

To apply the migrations, run the following command:

```
php artisan migrate
```

**Important**: The migrations must be run in the correct order to ensure data is properly migrated before removing the old columns. The timestamps on the migration files ensure this order.

## Models

The migration introduces a new `Image` model and updates the `Business` model:

-   The `Image` model has a `belongsTo` relationship with `Business`
-   The `Business` model has `hasMany` relationships with `Images`

## Troubleshooting

### "Column not found" Errors

If you encounter errors like the following after running the migrations:

```
Error fetching businesses: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'businesses.main_picture'
```

This means that the old columns have been removed from the database, but some query code is still trying to select these columns. To fix this:

1. Check all controller methods that query the Business model
2. Remove any references to `businesses.main_picture` and `businesses.gallery_pictures` from the select statements
3. Update any code that processes these columns to use the new Images relationships instead

Example changes to make in controller methods:

```php
// Before:
$business = Business::select([
    // ...
    'businesses.main_picture',
    'businesses.gallery_pictures',
    // ...
])->get();

// After:
$business = Business::select([
    // ... (without the removed columns)
])->get();

// Then add the image URLs after query:
$mainImage = Image::where('business_id', $business->id)
    ->where('type', 'main')
    ->first();

if ($mainImage) {
    $business->main_picture_url = $mainImage->image_url;
}
```

4. Update the `$fillable` and `$casts` arrays in the Business model to remove the old columns
5. Clean up any remaining backward compatibility code in accessor methods

## Final Cleanup

After confirming that the migration works correctly in all environments, you can remove the backward compatibility code from the models and controllers.
