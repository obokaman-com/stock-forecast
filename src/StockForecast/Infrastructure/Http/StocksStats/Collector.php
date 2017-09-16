<?php

namespace Obokaman\StockForecast\Infrastructure\Http\StocksStats;

use Obokaman\StockForecast\Domain\Model\Financial\Currency;
use Obokaman\StockForecast\Domain\Model\Financial\Stock;
use Obokaman\StockForecast\Domain\Model\Financial\StockStats;

interface Collector
{
    /**
     * @param Currency $a_currency
     * @param Stock    $a_stock_code
     * @param int      $previous_days_to_collect
     *
     * @return StockStats[]
     */
    public function getStats(
        Currency $a_currency,
        Stock $a_stock_code,
        int $previous_days_to_collect
    ): array;
}
