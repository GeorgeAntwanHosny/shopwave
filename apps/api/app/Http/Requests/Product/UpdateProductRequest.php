<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('product'));
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0.01'],
            'stock_quantity' => ['sometimes', 'integer', 'min:0'],
            'category_id' => ['sometimes', 'nullable', 'exists:categories,id'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
