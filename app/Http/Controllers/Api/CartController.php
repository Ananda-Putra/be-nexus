<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Models\CartItem;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $cart = $request->user()->cart()->with('items.product')->first();

        return response()->json([
            'items'=>$cart?->items ?? [],
            'total' =>$cart?->total ?? 0
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AddToCartRequest $request)
    {
        $cart = $request->user()->cart ?? $request->user()->cart()->create();

        $existingItem = $cart->items()->where('product_id', $request->product_id)->first();

        if ($existingItem) {
            $existingItem->update([
                'quantity' => $existingItem->quantity + $request->quantity,
            ]);

            return response()->json($existingItem->load('product'));
    }

    $item = $cart->items()->create([
        'product_id' => $request->product_id,
        'quantity' => $request->quantity,
    ]);

    return response()->json($item->load('product'), 201);

    }

    /**
     * Display the specified resource.
     */
    public function update(Request $request, CartItem $cartItem)
    {
        // Pastikan cart item ini memang milik user yang login
        if ($cartItem->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cartItem->update($validated);

        return response()->json($cartItem->load('product'));
    }

    public function destroy(Request $request, CartItem $cartItem)
    {
        if ($cartItem->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $cartItem->delete();

        return response()->json(['message' => 'Item berhasil dihapus dari keranjang']);
    }

    public function clear(Request $request)
    {
        $cart = $request->user()->cart;
        $cart->items()->delete();

        return response()->json(['message' => 'Keranjang berhasil dikosongkan']);
    }
}
