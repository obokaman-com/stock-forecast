<?php

namespace Obokaman\StockForecast\Application\Service;

use Obokaman\StockForecast\Domain\Service\Predict\Predict;
use Obokaman\StockForecast\Infrastructure\Http\StockMeasurement\Collector;

final class PredictStockValue
{
    private $stock_measurement_collector;
    private $prediction_service;

    public function __construct(Collector $a_stock_measurement_collector, Predict $a_prediction_service)
    {
        $this->stock_measurement_collector = $a_stock_measurement_collector;
        $this->prediction_service = $a_prediction_service;
    }

    public function predict(PredictStockValueRequest $a_request): PredictStockValueResponse
    {
        $long_term_measurements = $this->stock_measurement_collector->getMeasurements($a_request->currency(), $a_request->stock(), $a_request->dateInterval());

        return new PredictStockValueResponse(
            $long_term_measurements,
            $this->prediction_service->predict($long_term_measurements->filterByPeriod('short')),
            $this->prediction_service->predict($long_term_measurements->filterByPeriod('medium')),
            $this->prediction_service->predict($long_term_measurements)
        );
    }
}
