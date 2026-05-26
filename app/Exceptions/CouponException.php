<?php

namespace App\Exceptions;

use RuntimeException;

class CouponException extends RuntimeException
{
    public static function notFound(string $code): self
    {
        return new self("Coupon '{$code}' not found or is inactive.");
    }

    public static function expired(): self
    {
        return new self('This coupon has expired.');
    }

    public static function usageLimitReached(): self
    {
        return new self('This coupon has reached its usage limit.');
    }

    public static function perUserLimitReached(): self
    {
        return new self('You have already used this coupon the maximum number of times.');
    }

    public static function minimumSpendNotMet(float $minSpend): self
    {
        return new self("A minimum spend of {$minSpend} is required to use this coupon.");
    }
}
