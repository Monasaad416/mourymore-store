<?php

namespace App\Services\Paymob;

use RuntimeException;

class PaymobRefundException extends RuntimeException
{
    public function __construct(string $message, public readonly bool $ambiguous = false)
    {
        parent::__construct($message);
    }
}
