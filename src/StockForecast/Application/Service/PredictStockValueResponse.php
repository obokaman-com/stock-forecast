<?php

namespace Obokaman\StockForecast\Application\Service;

use Obokaman\StockForecast\Domain\Model\Financial\StockStats;

final class PredictStockValueResponse
{
    private $last_days_real_stats_array;
    private $forecast_stats_array;

    public function __construct(array $a_last_day_real_stats, array $a_forecast_stats_array)
    {
        $this->last_days_real_stats_array = $a_last_day_real_stats;
        $this->forecast_stats_array       = $a_forecast_stats_array;
    }

    /**
     * @return StockStats[]
     */
    public function lastDaysRealStatsArray(): array
    {
        return $this->last_days_real_stats_array;
    }

    /**
     * @return StockStats[]
     */
    public function forecastStatsArray(): array
    {
        return $this->forecast_stats_array;
    }
}
