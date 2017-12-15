<?php

namespace Obokaman\StockForecast\Infrastructure\Http\StocksStats;

use Obokaman\StockForecast\Domain\Model\Financial\Currency;
use Obokaman\StockForecast\Domain\Model\Financial\Stock;
use Obokaman\StockForecast\Domain\Model\Financial\StockDateInterval;
use Obokaman\StockForecast\Domain\Model\Financial\StockStats;

interface Collector
{
    public const SHORT_INTERVAL = 5;
    public const MEDIUM_INTERVAL = 15;
    public const LONG_INTERVAL = 30;

    /**
     * @param Currency          $a_currency
     * @param Stock             $a_stock_code
     * @param StockDateInterval $a_date_interval
     *
     * @return StockStats[]
     */
    public function getStats(
        Currency $a_currency,
        Stock $a_stock_code,
        StockDateInterval $a_date_interval
    ): array;
}
