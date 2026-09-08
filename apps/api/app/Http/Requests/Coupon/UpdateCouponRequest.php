<?php

namespace App\Http\Requests\Coupon;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('coupon'));
    }

    public function rules(): array
    {
        $coupon = $this->route('coupon');

        return [
            'code' => [
                'sometimes', 'string', 'max:50', 'alpha_dash',
                Rule::unique('coupons')->where('vendor_id', $coupon->vendor_id)->ignore($coupon->id),
            ],
            'type' => ['sometimes', 'in:percentage,fixed'],
            'value' => ['sometimes', 'numeric', 'min:0.01'],
            'min_order_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'max_uses' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'starts_at' => ['sometimes', 'nullable', 'date'],
            'expires_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
