<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user', 'items.product', 'address', 'payment');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json(
            $query->latest()->paginate(15)
        );
    }

    public function show(Order $order)
    {
        return response()->json(
            $order->load('user', 'items.product', 'address', 'payment')
        );
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:diproses,dikirim,selesai,dibatalkan'],
        ]);

        if ($order->status === 'pending') {
            return response()->json([
                'message' => 'Order belum dibayar, tidak bisa diproses admin.',
            ], 422);
        }

        $order->update($validated);

        return response()->json([
            'message' => 'Status order berhasil diperbarui',
            'order' => $order,
        ]);
    }
}
