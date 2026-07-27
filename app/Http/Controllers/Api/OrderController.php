<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with('items.product', 'address', 'payment')
            ->latest()
            ->get();

        return response()->json($orders);
    }

    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        return response()->json($order->load('items.product', 'address', 'payment'));
    }

    public function cancel(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        if (! $order->isPending()) {
            return response()->json([
                'message' => 'Order ini sudah tidak bisa dibatalkan karena statusnya bukan pending',
            ], 422);
        }

        $order->update(['status' => 'dibatalkan']);

        return response()->json(['message' => 'Order berhasil dibatalkan', 'order' => $order]);
    }
}
