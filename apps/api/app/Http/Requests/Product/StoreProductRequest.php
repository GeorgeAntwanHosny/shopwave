<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $vendor = $this->user()?->vendor;

        return $vendor && $vendor->stripe_onboarding_complete && ! $vendor->is_suspended;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'is_active' => ['boolean'],
        ];
    }
}
