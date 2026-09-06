<?php

namespace App\Http\Controllers;

use App\Actions\Category\ListCategoryTreeAction;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(ListCategoryTreeAction $action): JsonResponse
    {
        return ApiResponse::success($action->execute(), 'Category tree retrieved.');
    }
}
