<?php

namespace App\Providers;

use App\Events\OrderPlaced;
use App\Events\OrderStatusUpdated;
use App\Events\RefundApproved;
use App\Events\RefundRejected;
use App\Events\RefundRequested;
use App\Listeners\NotifyAdminOrderPlaced;
use App\Listeners\NotifyAdminRefundRequested;
use App\Listeners\NotifyUserOrderStatusUpdated;
use App\Listeners\NotifyUserRefundApproved;
use App\Listeners\NotifyUserRefundRejected;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Relying on Laravel 11 auto-discovery for Event Listeners
    }
}
