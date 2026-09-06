<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReorderProductImagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ownership is checked via Gate in the controller
    }

    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'image_ids' => ['required', 'array'],
            'image_ids.*' => [
                'integer',
                Rule::exists('product_images', 'id')->where('product_id', $product->id),
            ],
        ];
    }
}
