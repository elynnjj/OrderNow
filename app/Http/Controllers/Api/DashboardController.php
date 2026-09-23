<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $todaySales = (float) Order::today()->where('status', 'confirmed')->sum('total');
        $todayOrderCount = (int) Order::today()->count();
        $todayConfirmedCount = (int) Order::today()->where('status', 'confirmed')->count();
        $todayPendingCount = (int) Order::today()->where('status', 'pending')->count();

        $lowStockIngredients = Ingredient::lowStock()
            ->get(['id', 'name', 'unit', 'current_stock', 'reorder_level'])
            ->map(fn ($i) => [
                'id'            => $i->id,
                'name'          => $i->name,
                'unit'          => $i->unit,
                'current_stock' => (float) $i->current_stock,
                'reorder_level' => (float) $i->reorder_level,
            ]);

        $recentOrders = Order::with('items.menuItem')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($o) => [
                'id'           => $o->id,
                'order_number' => $o->order_number,
                'status'       => $o->status,
                'total'        => (float) $o->total,
                'item_count'   => (int) $o->items->sum('quantity'),
                'created_at'   => $o->created_at->toIso8601String(),
            ]);

        $topSellingToday = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
            ->whereDate('orders.created_at', today())
            ->where('orders.status', 'confirmed')
            ->selectRaw('menu_items.id, menu_items.name, SUM(order_items.quantity) as total_qty')
            ->groupBy('menu_items.id', 'menu_items.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        return response()->json([
            'today' => [
                'sales'           => $todaySales,
                'order_count'     => $todayOrderCount,
                'confirmed_count' => $todayConfirmedCount,
                'pending_count'   => $todayPendingCount,
            ],
            'low_stock_ingredients' => $lowStockIngredients,
            'recent_orders'         => $recentOrders,
            'top_selling_today'     => $topSellingToday,
        ]);
    }
}
