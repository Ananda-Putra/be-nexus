<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function process(User $user, int $addressId): Order
    {
        $cart = $user->cart;

        if (! $cart || $cart->items()->count() === 0) {
            throw ValidationException::withMessages([
                'cart' => 'Keranjang kamu masih kosong.',
            ]);
        }

        // alamat milik user yang checkout
        $address = $user->addresses()->find($addressId);

        if (! $address) {
            throw ValidationException::withMessages([
                'address_id' => 'Alamat tidak ditemukan atau bukan milik kamu.',
            ]);
        }

        return DB::transaction(function () use ($user, $cart, $address) {
            $cartItems = $cart->items()->with('product')->get();

            // Cek stok semua produk dulu sebelum proses apapun
            foreach ($cartItems as $item) {
                if (! $item->product->isInStock($item->quantity)) {
                    throw ValidationException::withMessages([
                        'stock' => "Stok produk \"{$item->product->name}\" tidak mencukupi.",
                    ]);
                }
            }

            $totalPrice = $cartItems->sum(fn ($item) => $item->quantity * $item->product->price);

            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $address->id,
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);

            foreach ($cartItems as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price, 
                ]);

                // Kurangi stok produk
                $item->product->decrement('stock', $item->quantity);
            }

            // Kosongkan cart setelah checkout berhasil
            $cart->items()->delete();

            return $order->load('items.product', 'address');
        });
    }
}
