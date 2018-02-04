<?php

namespace Obokaman\StockForecast\Application\Service;

use Obokaman\StockForecast\Domain\Model\Financial\Stock\MeasurementCollection;

final class PredictStockValueResponse
{
    private $real_measurements;
    private $short_term_predictions;
    private $medium_term_predictions;
    private $long_term_predictions;

    public function __construct(
        MeasurementCollection $a_real_stats,
        MeasurementCollection $a_short_term_predictions,
        MeasurementCollection $a_medium_term_predictions,
        MeasurementCollection $a_long_term_predictions
    )
    {
        $this->real_measurements       = $a_real_stats;
        $this->short_term_predictions  = $a_short_term_predictions;
        $this->medium_term_predictions = $a_medium_term_predictions;
        $this->long_term_predictions   = $a_long_term_predictions;
    }

    public function realMeasurements(): MeasurementCollection
    {
        return $this->real_measurements;
    }

    public function shortTermPredictions(): MeasurementCollection
    {
        return $this->short_term_predictions;
    }

    public function mediumTermPredicitons(): MeasurementCollection
    {
        return $this->medium_term_predictions;
    }

    public function longTermPredictions(): MeasurementCollection
    {
        return $this->long_term_predictions;
    }
}
