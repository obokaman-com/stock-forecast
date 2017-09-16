<?php

namespace Obokaman\StockForecast\Application\Service;

use Obokaman\StockForecast\Domain\Model\Financial\Currency;
use Obokaman\StockForecast\Domain\Model\Financial\Stock;

final class PredictStockValueRequest
{
    private $currency_code;
    private $stock_code;
    private $days_to_collect;
    private $days_to_forecast;

    public function __construct(
        string $a_currency_code,
        string $a_stock_code,
        int $days_to_collect,
        int $days_to_forecast
    )
    {
        $this->currency_code    = $a_currency_code;
        $this->stock_code       = $a_stock_code;
        $this->days_to_collect  = $days_to_collect;
        $this->days_to_forecast = $days_to_forecast;
    }

    public function currency(): Currency
    {
        return Currency::fromCode($this->currency_code);
    }

    public function stock(): Stock
    {
        return Stock::fromCode($this->stock_code);
    }

    public function daysToCollect(): int
    {
        return (int) $this->days_to_collect;
    }

    public function daysToForecast(): int
    {
        return (int) $this->days_to_forecast;
    }
}
