<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(private readonly AnalyticsService $analyticsService) {}

    /**
     * Admin analytics dashboard.
     */
    public function index(Request $request)
    {
        $year = (int) $request->input('year', now()->year);

        $stats             = $this->analyticsService->getDashboardStats();
        $monthlyEarnings   = $this->analyticsService->getYearlyEarningsByMonth($year);
        $userSpending      = $this->analyticsService->getUserSpending();

        return view('admin.analytics.index', compact('stats', 'monthlyEarnings', 'userSpending', 'year'));
    }
}
