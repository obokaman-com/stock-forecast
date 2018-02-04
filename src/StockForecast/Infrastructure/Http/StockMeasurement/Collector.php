<?php

namespace Obokaman\StockForecast\Infrastructure\Http\StockMeasurement;

use Obokaman\StockForecast\Domain\Model\Date\Interval;
use Obokaman\StockForecast\Domain\Model\Financial\Currency;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\MeasurementCollection;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\Stock;

interface Collector
{
    public function getMeasurements(
        Currency $a_currency,
        Stock $a_stock_code,
        Interval $a_date_interval
    ): MeasurementCollection;
}
