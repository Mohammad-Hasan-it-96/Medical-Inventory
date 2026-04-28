<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\BaseController;
use App\Models\Order;
use App\Models\Pharmacy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends BaseController
{
    /**
     * GET /api/v1/rep/dashboard
     *
     * Returns a lightweight summary for the currently-authenticated rep:
     *   - Number of pharmacies assigned to them
     *   - Order counts broken down by status (pending / confirmed / cancelled)
     *   - Total value of confirmed orders
     *
     * Admin/moderator users receive the same format but scoped to ALL orders/pharmacies.
     */
    public function repDashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        // ── Pharmacies ────────────────────────────────────────────────────────
        $pharmaciesQuery = Pharmacy::query();
        if ($user->isRep()) {
            $pharmaciesQuery->where('rep_id', $user->id);
        }
        $pharmacyCount = $pharmaciesQuery->count();

        // ── Orders ────────────────────────────────────────────────────────────
        $ordersQuery = Order::query();
        if ($user->isRep()) {
            $ordersQuery->where('rep_id', $user->id);
        }

        $orderStats = (clone $ordersQuery)
            ->selectRaw('status, COUNT(*) as count, SUM(total) as total_value')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return $this->sendResponse([
            'pharmacy_count' => $pharmacyCount,
            'orders'         => [
                'pending'   => (int)   ($orderStats->get(Order::STATUS_PENDING)?->count       ?? 0),
                'confirmed' => (int)   ($orderStats->get(Order::STATUS_CONFIRMED)?->count     ?? 0),
                'cancelled' => (int)   ($orderStats->get(Order::STATUS_CANCELLED)?->count     ?? 0),
                'draft'     => (int)   ($orderStats->get(Order::STATUS_DRAFT)?->count         ?? 0),
            ],
            'confirmed_total' => round(
                (float) ($orderStats->get(Order::STATUS_CONFIRMED)?->total_value ?? 0),
                2
            ),
        ], 'Dashboard data retrieved successfully.');
    }
}
