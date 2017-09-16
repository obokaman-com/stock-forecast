<?php

namespace Obokaman\StockForecast\Domain\Service\Predict\Strategy;

use Obokaman\StockForecast\Domain\Service\Predict\PredictionStrategy;

final class CumulativeMovingAverage implements PredictionStrategy
{
    /** @var array */
    private $data_sequence;

    private $least_square_prediction_strategy;

    public function __construct(LeastSquares $a_least_square_prediction_strategy)
    {
        $this->least_square_prediction_strategy = $a_least_square_prediction_strategy;
    }

    public function train(array $data_sequence): void
    {
        $this->data_sequence = $data_sequence;
    }

    public function resetTraining()
    {
        $this->data_sequence = null;
    }

    public function predictNext(int $quantity = 1)
    {
        $last_average = 0;

        $normalized_by_cumulative_average_samples = [];

        for ($i = 0; $i < sizeof($this->data_sequence); $i++)
        {
            $last_average                               = $this->cumulativeMovingAverage($this->data_sequence[$i], $last_average, $i + 1);
            $normalized_by_cumulative_average_samples[] = $last_average;
        }

        if (1 == $quantity)
        {
            return [$last_average];
        }

        $this->least_square_prediction_strategy->train($normalized_by_cumulative_average_samples);

        $predictions = $this->least_square_prediction_strategy->predictNext($quantity - 1);

        $predictions = array_merge([$last_average], $predictions);

        return $predictions;
    }

    private function cumulativeMovingAverage($sequence_data, $last_average, $sequence_position)
    {
        $result = $last_average + (($sequence_data - $last_average) / $sequence_position);

        return $result;
    }
}
