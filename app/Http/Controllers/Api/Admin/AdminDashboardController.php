<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'summary' => $this->summary(),
            'revenue_last_7_days' => $this->revenueLast7Days(),
            'order_status_breakdown' => $this->orderStatusBreakdown(),
            'top_products' => $this->topProducts(),
        ]);
    }

    private function summary(): array
    {
        return [
            'total_orders' => Order::count(),
            'total_revenue' => Order::where('status', '!=', 'dibatalkan')
                ->whereIn('status', ['paid', 'diproses', 'dikirim', 'selesai'])
                ->sum('total_price'),
            'total_customers' => User::where('role', 'customer')->count(),
            'total_products' => Product::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
        ];
    }

    private function revenueLast7Days(): array
    {
        $data = Order::whereIn('status', ['paid', 'diproses', 'dikirim', 'selesai'])
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as date, SUM(total_price) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $result = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $result[] = [
                'date' => $date,
                'total' => (int) ($data[$date]->total ?? 0),
            ];
        }

        return $result;
    }

    private function orderStatusBreakdown(): array
    {
        return Order::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->map(fn ($row) => [
                'status' => $row->status,
                'total' => $row->total,
            ])
            ->toArray();
    }

    private function topProducts(int $limit = 5): array
    {
        return DB::table('order_items')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.status', ['paid', 'diproses', 'dikirim', 'selesai'])
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
