<?php

namespace Obokaman\StockForecast\Infrastructure\MachineLearning\PhpMl;

use Obokaman\StockForecast\Domain\Service\Predict\Strategy\LeastSquares as LeastSquaresContract;
use Phpml\Regression\LeastSquares as LeastSquaresLibrary;

final class LeastSquares extends PredictionStrategy implements LeastSquaresContract
{
    protected function getMlLibrary()
    {
        return new LeastSquaresLibrary();
    }
}
