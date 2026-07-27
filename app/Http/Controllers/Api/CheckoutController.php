<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\CheckoutRequest;
use App\Services\CheckoutService;

class CheckoutController extends Controller
{
    public function __construct(protected CheckoutService $checkoutService)
    {
    }

    public function store(CheckoutRequest $request)
    {
        $order = $this->checkoutService->process(
            $request->user(),
            $request->address_id
        );

        return response()->json([
            'message' => 'Checkout berhasil',
            'order' => $order,
        ], 201);
    }
}
