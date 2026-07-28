<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected MidtransService $midtransService)
    {
    }

    public function createSnapToken(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        if (! $order->isPending()) {
            return response()->json(['message' => 'Order ini sudah tidak bisa dibayar'], 422);
        }

        $snapToken = $this->midtransService->createSnapToken($order);

        return response()->json(['snap_token' => $snapToken]);
    }

    public function notification(Request $request)
    {
        $result = $this->midtransService->handleNotification($request->all());

        return response()->json($result);
    }

    public function status(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        return response()->json([
            'order_status' => $order->status,
            'payment_status' => $order->payment?->status,
        ]);
    }
}
