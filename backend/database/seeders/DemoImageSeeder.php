<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\UsedBike;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Copies the demo photos in database/seeders/images into the public disk
 * and attaches them to the seeded products and used bikes.
 *
 * File names are "<slug>-<n>.jpg"; n is the gallery order (1 = main photo).
 * A product or bike that already has images (e.g. uploaded from the admin)
 * is left alone, so re-running the seeder never overwrites real photos.
 *
 * Sources and licenses: IMAGE_CREDITS.md in the project root.
 */
class DemoImageSeeder extends Seeder
{
    public function run(): void
    {
        $this->attach(Product::class, 'products', 'products/demo');
        $this->attach(UsedBike::class, 'bikes', 'bikes/demo');
    }

    /**
     * @param  class-string<Product|UsedBike>  $model
     */
    private function attach(string $model, string $folder, string $targetDir): void
    {
        $files = glob(__DIR__."/images/{$folder}/*.jpg") ?: [];
        $bySlug = [];
        foreach ($files as $file) {
            if (preg_match('/^(.+)-(\d+)\.jpg$/', basename($file), $m)) {
                $bySlug[$m[1]][(int) $m[2]] = $file;
            }
        }

        $disk = Storage::disk('public');

        foreach ($bySlug as $slug => $images) {
            $record = $model::where('slug', $slug)->first();
            if (! $record || $record->images()->exists()) {
                continue;
            }

            ksort($images);
            foreach (array_values($images) as $i => $file) {
                $path = $targetDir.'/'.basename($file);
                if (! $disk->exists($path)) {
                    $disk->put($path, file_get_contents($file));
                }
                $record->images()->create(['path' => $path, 'sort' => $i]);
            }
        }
    }
}
