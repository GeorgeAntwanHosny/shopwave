<?php

namespace App\Actions\Product;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListProductsAction
{
    /**
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation  $query
     *         A base query/relation to filter — lets callers scope to "all active products" (public
     *         catalog) or "this vendor's own products" (vendor dashboard, which should also see
     *         inactive ones) before the shared filters below are applied. Deliberately not
     *         type-hinted to a single class since both Builder and Relation support the same
     *         where()/with()/orderBy()/paginate() calls used here.
     */
    public function execute($query, array $filters): LengthAwarePaginator
    {
        $query->with(['vendor', 'category', 'images']);

        if (! empty($filters['q'])) {
            // Plain ILIKE substring match, not tsquery prefix search.
            // to_tsquery runs terms through the "english" dictionary before
            // matching, which silently drops very short tokens (1–2 chars
            // get treated like stopword noise by the stemmer), so
            // searching a single letter returned nothing. ILIKE has no
            // such minimum — it matches any substring of any length,
            // which is what "search as you type" actually needs. The
            // generated search_vector/GIN index is left in the schema for
            // a future relevance-ranked "smart search" mode if ever added.
            $term = trim($filters['q']);
            $query->where(function ($sub) use ($term) {
                $sub->where('name', 'ILIKE', "%{$term}%")
                    ->orWhere('description', 'ILIKE', "%{$term}%");
            });
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (! empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }
        if (isset($filters['price_min'])) {
            $query->where('price', '>=', $filters['price_min']);
        }
        if (isset($filters['price_max'])) {
            $query->where('price', '<=', $filters['price_max']);
        }
        if (! empty($filters['rating_min'])) {
            $query->where('average_rating', '>=', $filters['rating_min']);
        }
        if (! empty($filters['in_stock'])) {
            $query->where('stock_quantity', '>', 0);
        }
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        match ($filters['sort'] ?? 'newest') {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'rating' => $query->orderBy('average_rating', 'desc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        return $query->paginate(12)->withQueryString();
    }
}
