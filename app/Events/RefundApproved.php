<?php

namespace App\Events;

use App\Models\RefundRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RefundApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly RefundRequest $refundRequest) {}
}
