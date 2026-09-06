<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Test-only seeder: one demo vendor + 5 products per category, each with
 * one real downloaded photo, for exercising the catalog UI end to end.
 * Not part of the normal DatabaseSeeder chain — run explicitly:
 *   php artisan db:seed --class=TestProductSeeder
 */
class TestProductSeeder extends Seeder
{
    public function run(): void
    {
        $vendor = $this->demoVendor();
        $categories = Category::all();

        if ($categories->isEmpty()) {
            $this->command->warn('No categories found — run CategorySeeder first.');
            return;
        }

        foreach ($categories as $category) {
            for ($i = 1; $i <= 5; $i++) {
                $name = ucfirst(fake()->words(3, true));

                $product = Product::create([
                    'vendor_id' => $vendor->id,
                    'category_id' => $category->id,
                    'name' => $name,
                    'slug' => Str::slug($name).'-'.Str::random(6),
                    'description' => fake()->paragraphs(2, true),
                    'price' => fake()->randomFloat(2, 9, 299),
                    'stock_quantity' => fake()->numberBetween(0, 80),
                    'is_active' => true,
                ]);

                $this->attachRealImage($product, $category->slug);
            }
        }

        $this->command->info("Seeded 5 products for each of {$categories->count()} categories.");
    }

    protected function demoVendor(): Vendor
    {
        $user = User::firstOrCreate(
            ['email' => 'demo-vendor@shopwave.test'],
            ['name' => 'Demo Vendor', 'password' => bcrypt('password')]
        );

        return Vendor::firstOrCreate(
            ['user_id' => $user->id],
            [
                'shop_name' => 'ShopWave Demo Shop',
                'shop_slug' => 'shopwave-demo-shop',
                'stripe_account_id' => 'acct_demo_seed',
                'stripe_onboarding_complete' => true,
            ]
        );
    }

    /**
     * Downloads one real photo per product from picsum.photos and stores
     * it on the public disk. Fails silently (just logs a warning) if the
     * machine running this seeder has no internet access.
     */
    protected function attachRealImage(Product $product, string $seed): void
    {
        try {
            $response = Http::timeout(10)->get("https://picsum.photos/seed/{$seed}-{$product->id}/800/800");

            if (! $response->successful()) {
                return;
            }

            $path = "products/{$product->id}/".Str::random(20).'.jpg';
            Storage::disk('public')->put($path, $response->body());

            $product->images()->create(['path' => $path, 'sort_order' => 0]);
        } catch (\Throwable $e) {
            $this->command->warn("Could not download an image for product #{$product->id}: {$e->getMessage()}");
        }
    }
}
