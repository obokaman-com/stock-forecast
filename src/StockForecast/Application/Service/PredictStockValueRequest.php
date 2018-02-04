<?php

namespace Obokaman\StockForecast\Application\Service;

use Obokaman\StockForecast\Domain\Model\Date\Interval;
use Obokaman\StockForecast\Domain\Model\Financial\Currency;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\Stock;

final class PredictStockValueRequest
{
    private $currency_code;
    private $stock_code;
    private $date_interval;

    public function __construct(string $a_currency_code, string $a_stock_code, string $a_date_interval)
    {
        $this->currency_code = $a_currency_code;
        $this->stock_code    = $a_stock_code;
        $this->date_interval = $a_date_interval;
    }

    public function currency(): Currency
    {
        return Currency::fromCode($this->currency_code);
    }

    public function stock(): Stock
    {
        return Stock::fromCode($this->stock_code);
    }

    public function dateInterval(): Interval
    {
        return Interval::fromStringDateInterval($this->date_interval);
    }
}
