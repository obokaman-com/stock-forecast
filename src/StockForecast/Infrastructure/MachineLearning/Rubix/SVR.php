<?php

namespace Obokaman\StockForecast\Infrastructure\MachineLearning\Rubix;

use Obokaman\StockForecast\Domain\Service\Predict\Strategy\SupportVectorRegression as SupportVectorRegressionContract;
use Rubix\ML\Regressors\SVR as RubixSVR;
use Rubix\ML\Kernels\SVM\Polynomial;
use Rubix\ML\Estimator;

final class SVR extends PredictionStrategy implements SupportVectorRegressionContract
{
    private const C = 1.0;

    private const EPSILON = 0.03;
    
    protected function buildRegressor() : Estimator
    {
        return new RubixSVR(self::C, self::EPSILON, new Polynomial());
    }
}
