<?php

namespace Obokaman\StockForecast\Infrastructure\Http\StockMeasurement;

use RuntimeException;

final class CollectException extends RuntimeException
{
    public function __construct(string $message = null)
    {
        parent::__construct($message ?? 'Default message');
    }
}
