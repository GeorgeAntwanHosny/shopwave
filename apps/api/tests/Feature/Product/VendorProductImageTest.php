<?php

namespace Tests\Feature\Product;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Vendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VendorProductImageTest extends TestCase
{
    public function test_owner_can_upload_images(): void
    {
        Storage::fake('public');
        $vendor = Vendor::factory()->create(['stripe_onboarding_complete' => true]);
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);
        Sanctum::actingAs($vendor->user);

        $response = $this->postJson("/api/v1/vendor/products/{$product->id}/images", [
            'images' => [UploadedFile::fake()->image('photo1.jpg'), UploadedFile::fake()->image('photo2.jpg')],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseCount('product_images', 2);
    }

    public function test_non_owner_cannot_upload_images(): void
    {
        Storage::fake('public');
        $owner = Vendor::factory()->create();
        $otherVendor = Vendor::factory()->create();
        $product = Product::factory()->create(['vendor_id' => $owner->id]);
        Sanctum::actingAs($otherVendor->user);

        $response = $this->postJson("/api/v1/vendor/products/{$product->id}/images", [
            'images' => [UploadedFile::fake()->image('photo.jpg')],
        ]);

        $response->assertStatus(403);
    }

    public function test_owner_can_delete_an_image(): void
    {
        Storage::fake('public');
        $vendor = Vendor::factory()->create(['stripe_onboarding_complete' => true]);
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);
        $image = ProductImage::factory()->create(['product_id' => $product->id]);
        Sanctum::actingAs($vendor->user);

        $response = $this->deleteJson("/api/v1/vendor/products/{$product->id}/images/{$image->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('product_images', ['id' => $image->id]);
    }

    public function test_owner_can_reorder_images(): void
    {
        $vendor = Vendor::factory()->create(['stripe_onboarding_complete' => true]);
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);
        $first = ProductImage::factory()->create(['product_id' => $product->id, 'sort_order' => 0]);
        $second = ProductImage::factory()->create(['product_id' => $product->id, 'sort_order' => 1]);
        Sanctum::actingAs($vendor->user);

        $response = $this->putJson("/api/v1/vendor/products/{$product->id}/images/reorder", [
            'image_ids' => [$second->id, $first->id],
        ]);

        $response->assertStatus(200);
        $this->assertEquals(0, $second->fresh()->sort_order);
        $this->assertEquals(1, $first->fresh()->sort_order);
    }

    public function test_reorder_rejects_image_ids_from_another_product(): void
    {
        $vendor = Vendor::factory()->create(['stripe_onboarding_complete' => true]);
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);
        $foreignImage = ProductImage::factory()->create();
        Sanctum::actingAs($vendor->user);

        $response = $this->putJson("/api/v1/vendor/products/{$product->id}/images/reorder", [
            'image_ids' => [$foreignImage->id],
        ]);

        $response->assertStatus(422);
    }
}
