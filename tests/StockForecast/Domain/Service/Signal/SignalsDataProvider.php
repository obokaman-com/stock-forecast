<?php

namespace Obokaman\StockForecast\Domain\Service\Signal;

use DateTimeImmutable;
use Obokaman\StockForecast\Domain\Model\Financial\Currency;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\Measurement;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\MeasurementCollection;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\Stock;

use function count;

class SignalsDataProvider
{
    public static function getSustainedIncrease(): MeasurementCollection
    {
        $numbers = [];
        for ($i = 200; $i <= 230; $i++) {
            $numbers[] = $i;
        }

        return self::buildMeasurementCollection($numbers);
    }

    private static function buildMeasurementCollection(array $numbers_array): MeasurementCollection
    {
        $measurements_collection = new MeasurementCollection();
        foreach ($numbers_array as $position => $number) {
            $measurements_collection->addItem(self::buildMeasurement($number, $position + 1, count($numbers_array)));
        }

        return $measurements_collection;
    }

    private static function buildMeasurement(float $number, int $position, int $total): Measurement
    {
        $days = $total - $position;

        return new Measurement(
            Currency::fromCode('EUR'),
            Stock::fromCode('BTC'),
            DateTimeImmutable::createFromFormat('Y-m-d', date('Y-m-d', strtotime('-' . $days . ' day'))),
            $number,
            $number + 1,
            $number + 1,
            $number,
            0,
            0
        );
    }
}
