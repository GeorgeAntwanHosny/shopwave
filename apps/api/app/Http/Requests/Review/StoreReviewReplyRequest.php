<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $review = $this->route('review');

        return $this->user()->vendor && $this->user()->vendor->id === $review->vendor_id;
    }

    public function rules(): array
    {
        return ['reply' => ['required', 'string', 'max:2000']];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $review = $this->route('review');

            if ($review->reply()->exists()) {
                $validator->errors()->add('reply', 'This review already has a reply.');
            }
        });
    }
}
