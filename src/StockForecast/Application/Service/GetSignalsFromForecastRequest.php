<?php

namespace Obokaman\StockForecast\Application\Service;

use Obokaman\StockForecast\Domain\Model\Financial\Stock\MeasurementCollection;

final class GetSignalsFromForecastRequest
{
    private $short_term_predictions;
    private $medium_term_predictions;
    private $long_term_predictions;
    private $stock_measurements;

    public function __construct(
        MeasurementCollection $a_stock_measurements,
        MeasurementCollection $a_short_term_predictions,
        MeasurementCollection $a_medium_term_predictions,
        MeasurementCollection $a_long_term_predictions
    )
    {
        $this->stock_measurements      = $a_stock_measurements;
        $this->short_term_predictions  = $a_short_term_predictions;
        $this->medium_term_predictions = $a_medium_term_predictions;
        $this->long_term_predictions   = $a_long_term_predictions;
    }

    public function measurements(): MeasurementCollection
    {
        return $this->stock_measurements;
    }

    public function shortTermPredictions(): MeasurementCollection
    {
        return $this->short_term_predictions;
    }

    public function mediumTermPredictions(): MeasurementCollection
    {
        return $this->medium_term_predictions;
    }

    public function longTermPredictions(): MeasurementCollection
    {
        return $this->long_term_predictions;
    }
}
