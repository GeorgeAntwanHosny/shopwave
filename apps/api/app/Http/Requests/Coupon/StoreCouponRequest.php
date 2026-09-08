<?php

namespace App\Http\Requests\Coupon;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->vendor?->stripe_onboarding_complete;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required', 'string', 'max:50', 'alpha_dash',
                Rule::unique('coupons')->where('vendor_id', $this->user()->vendor->id),
            ],
            'type' => ['required', 'in:percentage,fixed'],
            'value' => [
                'required', 'numeric', 'min:0.01',
                function ($attribute, $value, $fail) {
                    if ($this->input('type') === 'percentage' && $value > 100) {
                        $fail('Percentage discounts cannot exceed 100.');
                    }
                },
            ],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['boolean'],
        ];
    }
}
