<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(private readonly InventoryService $inventoryService)
    {}

    public function index()
    {
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::whereIn('status', [
                Order::STATUS_PENDING,
                Order::STATUS_PENDING_PAYMENT,
            ])->count(),
            'revenue' => Order::where('status', Order::STATUS_PAID)->sum('total'),
            'pending_payments' => Payment::where('status', Payment::STATUS_PENDING)
                ->whereNotNull('proof_path')
                ->count(),
        ];

        $lowStock = $this->inventoryService->getLowStockVariants();

        $recentOrders = Order::with('user', 'latestPayment')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'lowStock', 'recentOrders'));
    }
}
