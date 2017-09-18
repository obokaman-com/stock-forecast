<?php

namespace Obokaman\StockForecast\Application\Service;

use Obokaman\StockForecast\Domain\Model\Financial\StockStats;

final class PredictStockValueResponse
{
    private $real_stats_array;
    private $forecast_stats_array;

    public function __construct(array $a_real_stats, array $a_forecast_stats_array)
    {
        $this->real_stats_array     = $a_real_stats;
        $this->forecast_stats_array = $a_forecast_stats_array;
    }

    /**
     * @return StockStats[]
     */
    public function realStatsArray(): array
    {
        return $this->real_stats_array;
    }

    /**
     * @return StockStats[]
     */
    public function forecastStatsArray(): array
    {
        return $this->forecast_stats_array;
    }
}
