<?php

namespace App\Actions\Admin;

use App\Models\OrderItem;
use App\Models\Product;

class GetProductDetailAction
{
    public function execute(Product $product): array
    {
        $product->load(['vendor', 'category', 'images']);

        $stats = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.product_id', $product->id)
            ->where('orders.status', 'paid')
            ->selectRaw('COUNT(DISTINCT orders.id) as orders_count, COALESCE(SUM(order_items.quantity), 0) as units_sold')
            ->first();

        return array_merge($product->toArray(), [
            'orders_count' => (int) ($stats->orders_count ?? 0),
            'units_sold' => (int) ($stats->units_sold ?? 0),
        ]);
    }
}
