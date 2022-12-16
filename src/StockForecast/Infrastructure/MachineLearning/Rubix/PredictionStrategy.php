<?php

namespace Obokaman\StockForecast\Infrastructure\MachineLearning\Rubix;

use Obokaman\StockForecast\Domain\Service\Predict\PredictionStrategy as PredictionStrategyContract;
use Rubix\ML\Estimator;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;

use function count;

abstract class PredictionStrategy implements PredictionStrategyContract
{
    /** @var Regression */
    protected $regressor;
    /** @var array */
    private $data_sequence;

    public function __construct()
    {
        $this->regressor = $this->buildRegressor();
    }

    public function resetTraining(): void
    {
        $this->data_sequence = null;
        $this->regressor = $this->buildRegressor();
    }

    public function train(array $data_sequence): void
    {
        $this->data_sequence = $data_sequence;

        $sample_range = array_map(
            function ($sequence) {
                return [$sequence];
            },
            range(1, count($this->data_sequence))
        );

        $this->regressor->train(new Labeled($sample_range, $data_sequence));
    }

    public function predictNext(int $quantity = 1)
    {
        $len = count($this->data_sequence);
        $prediction_sample = array_map(
            function ($sequence) {
                return [$sequence];
            },
            range($len + 1, $len + $quantity)
        );

        return $this->regressor->predict(new Unlabeled($prediction_sample));
    }

    abstract protected function buildRegressor() : Estimator;
}
