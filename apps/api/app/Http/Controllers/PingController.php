<?php

namespace App\Http\Controllers;

use App\Actions\System\PingAction;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class PingController extends Controller
{
    public function __invoke(PingAction $action): JsonResponse
    {
        return ApiResponse::success($action->execute(), 'Pong.');
    }
}
