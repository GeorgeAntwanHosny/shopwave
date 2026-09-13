<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderFulfillmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('updateAsVendor', $this->route('order'));
    }

    public function rules(): array
    {
        return [
            'fulfillment_status' => ['required', 'in:processing,shipped,delivered'],
            'tracking_number' => ['nullable', 'string', 'max:255'],
            'carrier' => ['nullable', 'string', 'max:255'],
        ];
    }
}
