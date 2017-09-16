<?php

namespace Obokaman\StockForecast\Infrastructure\MachineLearning\PhpMl;

use Obokaman\StockForecast\Domain\Service\Predict\Strategy\LeastSquares as LeastSquaresContract;
use Phpml\Regression\LeastSquares as LeastSquaresLibrary;

final class LeastSquares implements LeastSquaresContract
{
    /** @var LeastSquaresLibrary */
    private $php_ml;

    /** @var array */
    private $data_sequence;

    public function __construct()
    {
        $this->php_ml = new LeastSquaresLibrary();
    }

    public function train(array $data_sequence): void
    {
        $this->data_sequence = $data_sequence;

        $sample_range = array_map(
            function ($sequence)
            {
                return [$sequence];
            },
            range(1, count($this->data_sequence))
        );

        $this->php_ml->train($sample_range, $data_sequence);
    }

    public function resetTraining()
    {
        $this->php_ml = new LeastSquaresLibrary();
    }

    public function predictNext(int $quantity = 1)
    {
        $prediction_sample = array_map(
            function ($sequence)
            {
                return [$sequence];
            },
            range(count($this->data_sequence), count($this->data_sequence) - 1 + $quantity)
        );

        return $this->php_ml->predict($prediction_sample);
    }
}
