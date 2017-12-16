<?php

namespace Obokaman\StockForecast\Infrastructure\Http\StocksStats;

final class CollectException extends \RuntimeException
{
    public function __construct(string $message = null)
    {
        parent::__construct($message ?? 'Default message');
    }
}
