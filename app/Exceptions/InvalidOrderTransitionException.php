<?php

namespace App\Exceptions;

use RuntimeException;

class InvalidOrderTransitionException extends RuntimeException
{
    public function __construct(string $fromStatus, string $toStatus)
    {
        parent::__construct(
            "Invalid order status transition from '{$fromStatus}' to '{$toStatus}'."
        );
    }
}
