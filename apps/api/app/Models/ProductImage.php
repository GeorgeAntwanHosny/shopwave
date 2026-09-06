<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'path', 'sort_order'];
    protected $appends = ['url'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Built directly from config('app.url') rather than via
     * Storage::disk('public')->url() — both ultimately read APP_URL, but
     * building it explicitly here makes the resulting URL predictable and
     * easy to debug (e.g. via `php artisan tinker` -> config('app.url'))
     * if images ever fail to load again. Requires APP_URL in .env to
     * include the port the API actually runs on: http://localhost:8000,
     * not just http://localhost.
     */
    protected function url(): Attribute
    {
        return Attribute::get(
            fn () => rtrim(config('app.url'), '/').'/storage/'.ltrim($this->path, '/')
        );
    }
}
