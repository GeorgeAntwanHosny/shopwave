<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $electronics = Category::create(['name' => 'Electronics', 'slug' => 'electronics']);
        Category::create(['name' => 'Phones', 'slug' => 'phones', 'parent_id' => $electronics->id]);
        Category::create(['name' => 'Laptops', 'slug' => 'laptops', 'parent_id' => $electronics->id]);

        $home = Category::create(['name' => 'Home & Kitchen', 'slug' => 'home-kitchen']);
        Category::create(['name' => 'Furniture', 'slug' => 'furniture', 'parent_id' => $home->id]);

        Category::create(['name' => 'Fashion', 'slug' => 'fashion']);
    }
}
