<?php

namespace Obokaman\StockForecast\Infrastructure\MachineLearning\PhpMl;

use Obokaman\StockForecast\Domain\Service\Predict\Strategy\SupportVectorRegression as SupportVectorRegressionContract;
use Phpml\Regression\SVR;
use Phpml\SupportVectorMachine\Kernel;

final class SupportVectorRegression extends PredictionStrategy implements SupportVectorRegressionContract
{
    private const REGRESSION_STRATEGY = Kernel::LINEAR;

    protected function getMlLibrary()
    {
        return new SVR(self::REGRESSION_STRATEGY);
    }
}
