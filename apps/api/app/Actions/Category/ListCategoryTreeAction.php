<?php

namespace App\Actions\Category;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ListCategoryTreeAction
{
    public function execute(): array
    {
        return Cache::remember('categories:tree', now()->addHour(), function () {
            $categories = Category::orderBy('name')->get(['id', 'parent_id', 'name', 'slug']);

            return $this->buildTree($categories, null);
        });
    }

    protected function buildTree(Collection $categories, ?int $parentId): array
    {
        return $categories->where('parent_id', $parentId)->map(fn ($category) => [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'children' => $this->buildTree($categories, $category->id),
        ])->values()->all();
    }
}
