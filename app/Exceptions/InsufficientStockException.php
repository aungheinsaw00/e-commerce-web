<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(int $variantId, int $available, int $requested)
    {
        parent::__construct(
            "Insufficient stock for variant {$variantId}. Available: {$available}, requested: {$requested}."
        );
    }
}
