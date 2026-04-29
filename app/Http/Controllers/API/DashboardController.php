<?php

namespace App\Http\Controllers\API;

use App\Models\AccountEntry;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseController
{
    public function dashboard(Request $request)
    {
        $now        = now();
        $monthStart = $now->copy()->startOfMonth()->toDateString();
        $monthEnd   = $now->copy()->endOfMonth()->toDateString();

        // ── Aggregate stats — cached 5 minutes ────────────────────────────
        $stats = Cache::remember('dashboard.stats', 300, function () use ($now, $monthStart, $monthEnd) {
            return [
                'total_products'   => Product::count(),
                'active_products'  => Product::where('is_active', true)->count(),
                'total_pharmacies' => Pharmacy::count(),
                'total_reps'       => User::where('role', 'rep')->count(),
                'orders_today'     => Order::whereDate('created_at', today())->count(),
                'pending_orders'   => Order::where('status', Order::STATUS_PENDING)->count(),
                'confirmed_month'  => Order::where('status', Order::STATUS_CONFIRMED)
                                        ->whereMonth('confirmed_at', $now->month)
                                        ->whereYear('confirmed_at', $now->year)
                                        ->count(),
                'sales_month'      => (float) AccountEntry::where('type', AccountEntry::TYPE_DEBIT)
                                        ->whereBetween('entry_date', [$monthStart, $monthEnd])
                                        ->sum('amount'),
                'payments_month'   => (float) Payment::whereMonth('paid_at', $now->month)
                                        ->whereYear('paid_at', $now->year)
                                        ->sum('amount'),
                'low_stock_count'  => DB::table('products')
                                        ->whereNull('deleted_at')
                                        ->where('min_stock', '>', 0)
                                        ->whereRaw(
                                            'COALESCE((SELECT SUM(quantity) FROM stock_movements WHERE product_id = products.id), 0) <= min_stock'
                                        )
                                        ->count(),
            ];
        });

        // ── Recent lists — always fresh ────────────────────────────────────
        $recentOrders = Order::with(['pharmacy', 'rep'])
            ->latest()
            ->limit(10)
            ->get();

        $recentPayments = Payment::with(['pharmacy', 'creator'])
            ->latest('paid_at')
            ->limit(10)
            ->get();

        $stockSub = 'COALESCE((SELECT SUM(quantity) FROM stock_movements WHERE product_id = products.id), 0)';

        $lowStockProducts = Product::select('products.*')
            ->selectRaw("{$stockSub} AS current_stock")
            ->where('min_stock', '>', 0)
            ->whereRaw("{$stockSub} <= products.min_stock")
            ->orderByRaw("({$stockSub} - products.min_stock) ASC")
            ->limit(10)
            ->get();

        $topPharmacies = Pharmacy::withSum('payments', 'amount')
            ->withCount('orders')
            ->orderByDesc('payments_sum_amount')
            ->limit(8)
            ->get(['id', 'name']);

        return view('dashboard', compact('stats', 'recentOrders', 'recentPayments', 'lowStockProducts', 'topPharmacies'));
    }

    public function welcome(Request $request)
    {
        if (auth()->user())
            return $this->dashboard($request);
        else
            return view('auth.login');
    }
}
