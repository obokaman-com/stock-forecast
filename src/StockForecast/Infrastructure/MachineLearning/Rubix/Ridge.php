<?php

namespace Obokaman\StockForecast\Infrastructure\MachineLearning\Rubix;

use Obokaman\StockForecast\Domain\Service\Predict\Strategy\LeastSquares as LeastSquaresContract;
use Rubix\ML\Regressors\Ridge as RubixRidge;
use Rubix\ML\Estimator;

final class Ridge extends PredictionStrategy implements LeastSquaresContract    
{
    private const ALPHA = 2.0;
    
    protected function buildRegressor() : Estimator
    {
        return new RubixRidge(self::ALPHA);
    }
}
