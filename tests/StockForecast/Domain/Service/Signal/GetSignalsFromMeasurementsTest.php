<?php

namespace Obokaman\StockForecast\Domain\Service\Signal;

use Obokaman\StockForecast\Domain\Model\Financial\Signal;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\MeasurementCollection;
use Obokaman\StockForecast\Domain\Service\Predict\Predict;
use Obokaman\StockForecast\Infrastructure\MachineLearning\PhpMl\LeastSquares;
use Obokaman\StocksForecast\Domain\Service\Signal\SignalsDataProvider;
use PHPUnit\Framework\TestCase;

class GetSignalsFromMeasurementsTest extends TestCase
{
    /**
     * @test
     * @dataProvider getMeasurementCollection
     *
     * @param MeasurementCollection $a_measurement_collection
     */
    public function shouldGetSignals(MeasurementCollection $a_measurement_collection)
    {
        $signals = (new GetSignalsFromMeasurements(new Predict(new LeastSquares())))
            ->getSignals($a_measurement_collection);

        $this->assertContainsOnlyInstancesOf(Signal::class, $signals);
    }

    public function getMeasurementCollection()
    {
        $measurement_collection = SignalsDataProvider::getSustainedIncrease();

        return [
            [
                $measurement_collection
            ]
        ];
    }
}
