<?php

namespace App\Actions\Product;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;

class CreateProductAction
{
    public function execute(User $user, array $data): Product
    {
        return Product::create([
            'vendor_id' => $user->vendor->id,
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name']),
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'stock_quantity' => $data['stock_quantity'],
            'category_id' => $data['category_id'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
