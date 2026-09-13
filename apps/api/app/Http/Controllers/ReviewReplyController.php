<?php

namespace App\Http\Controllers;

use App\Actions\ReviewReply\CreateReviewReplyAction;
use App\Http\Requests\Review\StoreReviewReplyRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Review;
use Illuminate\Http\JsonResponse;

class ReviewReplyController extends Controller
{
    public function store(StoreReviewReplyRequest $request, Review $review, CreateReviewReplyAction $action): JsonResponse
    {
        $reply = $action->execute($review, $request->user(), $request->validated('reply'));

        return ApiResponse::success($reply, 'Reply posted.', 201);
    }
}
