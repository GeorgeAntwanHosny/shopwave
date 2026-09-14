<?php

namespace App\Http\Controllers;

use App\Actions\Review\CreateReviewAction;
use App\Actions\Review\GetProductReviewEligibilityAction;
use App\Actions\Review\ListProductReviewsAction;
use App\Actions\Review\UpdateReviewAction;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Requests\Review\UpdateReviewRequest;
use App\Http\Responses\ApiResponse;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Product $product, ListProductReviewsAction $action): JsonResponse
    {
        $reviews = $action->execute($product);

        return ApiResponse::success([
            'reviews' => $reviews->items(),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'total' => $reviews->total(),
            ],
        ], 'Reviews retrieved.');
    }

    public function reviewEligibility(Product $product, Request $request, GetProductReviewEligibilityAction $action): JsonResponse
    {
        return ApiResponse::success($action->execute($product, $request->user()), 'Review eligibility retrieved.');
    }

    public function store(StoreReviewRequest $request, OrderItem $orderItem, CreateReviewAction $action): JsonResponse
    {
        $review = $action->execute($orderItem, $request->validated());

        return ApiResponse::success($review, 'Review submitted.', 201);
    }

    public function update(UpdateReviewRequest $request, Review $review, UpdateReviewAction $action): JsonResponse
    {
        $updated = $action->execute($review, $request->validated());

        return ApiResponse::success($updated, 'Review updated.');
    }
}
