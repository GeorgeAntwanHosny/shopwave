<?php

namespace App\Http\Controllers;

use App\Actions\Coupon\CreateCouponAction;
use App\Actions\Coupon\DeleteCouponAction;
use App\Actions\Coupon\UpdateCouponAction;
use App\Http\Requests\Coupon\StoreCouponRequest;
use App\Http\Requests\Coupon\UpdateCouponRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VendorCouponController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $coupons = $request->user()->vendor->coupons()->latest()->get();

        return ApiResponse::success($coupons, 'Coupons retrieved.');
    }

    public function store(StoreCouponRequest $request, CreateCouponAction $action): JsonResponse
    {
        $coupon = $action->execute($request->user(), $request->validated());

        return ApiResponse::success($coupon, 'Coupon created.', 201);
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon, UpdateCouponAction $action): JsonResponse
    {
        $updated = $action->execute($coupon, $request->validated());

        return ApiResponse::success($updated, 'Coupon updated.');
    }

    public function destroy(Coupon $coupon, DeleteCouponAction $action): JsonResponse
    {
        Gate::authorize('delete', $coupon);
        $action->execute($coupon);

        return ApiResponse::success(null, 'Coupon deleted.');
    }
}
