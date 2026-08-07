<?php

namespace App\Services;

use App\Models\Order;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function createSnapToken(Order $order): string
    {
        $params = [
            'transaction_details' => [
                'order_id' => 'ORDER-' . $order->id . '-' . time(),
                'gross_amount' => $order->total_price,
            ],
            'customer_details' => [
                'first_name' => $order->user->name,
                'email' => $order->user->email,
                'phone' => $order->address->phone,
            ],
            'item_details' => $order->items->map(function ($item) {
                return [
                    'id' => $item->product_id,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'name' => $item->product->name,
                ];
            })->toArray(),

            'finish_redirect_url' => env('APP_URL_FRONTEND', 'http://localhost:3000') . '/order-callback',
        ];

        $snapToken = Snap::getSnapToken($params);

        $order->payment()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'method' => 'midtrans',
                'status' => 'pending',
                'snap_token' => $snapToken,
            ]
        );

        return $snapToken;
    }

    public function handleNotification(array $payload): array
    {
        $orderIdRaw = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;
        $transactionId = $payload['transaction_id'] ?? null;

        preg_match('/ORDER-(\d+)-/', $orderIdRaw, $matches);
        $orderId = $matches[1] ?? null;

        if (! $orderId) {
            throw new \Exception('Order ID tidak ditemukan dalam payload notifikasi.');
        }

        $order = Order::findOrFail($orderId);
        $payment = $order->payment;

        $status = match ($transactionStatus) {
            'capture' => $fraudStatus === 'accept' ? 'paid' : 'pending',
            'settlement' => 'paid',
            'pending' => 'pending',
            'deny', 'expire', 'cancel' => 'failed',
            default => 'pending',
        };

        $payment->update([
            'status' => $status,
            'midtrans_transaction_id' => $transactionId,
        ]);

        if ($status === 'paid') {
            $order->update(['status' => 'paid']);
        } elseif ($status === 'failed') {
            $order->update(['status' => 'dibatalkan']);
        }

        return ['order_id' => $order->id, 'status' => $status];
    }
}
