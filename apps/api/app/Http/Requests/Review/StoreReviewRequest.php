<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    /**
     * Ownership only here — genuine business-state failures (not delivered
     * yet, already reviewed, product no longer exists) go through
     * withValidator() below so they surface as clear 422 messages instead
     * of an opaque 403.
     */
    public function authorize(): bool
    {
        $orderItem = $this->route('orderItem');

        return $orderItem && $orderItem->order->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $orderItem = $this->route('orderItem');

            if (! $orderItem->product_id) {
                $validator->errors()->add('order_item', 'This product is no longer available to review.');
                return;
            }

            if ($orderItem->order->fulfillment_status !== 'delivered') {
                $validator->errors()->add('order_item', 'You can only review items from delivered orders.');
            }

            if ($orderItem->review()->exists()) {
                $validator->errors()->add('order_item', 'You have already reviewed this item.');
            }
        });
    }
}
