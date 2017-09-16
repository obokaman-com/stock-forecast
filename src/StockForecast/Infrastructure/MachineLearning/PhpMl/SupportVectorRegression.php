<?php

namespace Obokaman\StockForecast\Infrastructure\MachineLearning\PhpMl;

use Obokaman\StockForecast\Domain\Service\Predict\Strategy\SupportVectorRegression as SupportVectorRegressionContract;
use Phpml\Regression\SVR;
use Phpml\SupportVectorMachine\Kernel;

final class SupportVectorRegression implements SupportVectorRegressionContract
{
    private const REGRESSION_STRATEGY = Kernel::LINEAR;

    /** @var SVR */
    private $php_ml;

    /** @var array */
    private $data_sequence;

    public function __construct()
    {
        $this->php_ml = $this->getMlLibrary();
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
        $this->php_ml = $this->getMlLibrary();
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

    private function getMlLibrary()
    {
        return new SVR(self::REGRESSION_STRATEGY);
    }
}
