<?php

namespace App\Http\Controllers;

use App\Actions\System\PingAction;
use Illuminate\Http\JsonResponse;

class PingController extends Controller
{
    public function __invoke(PingAction $action): JsonResponse
    {
        return response()->json($action->execute());
    }
}
