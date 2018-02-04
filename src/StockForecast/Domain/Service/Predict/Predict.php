<?php

namespace Obokaman\StockForecast\Domain\Service\Predict;

use Obokaman\StockForecast\Domain\Model\Financial\Stock\Measurement;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\MeasurementCollection;

class Predict
{
    private $prediction_strategy;

    public function __construct(PredictionStrategy $a_prediction_strategy)
    {
        $this->prediction_strategy = $a_prediction_strategy;
    }

    public function predict(MeasurementCollection $a_measurement_collection): MeasurementCollection
    {
        /** @var Measurement[] $source_data */
        $source_data                  = $a_measurement_collection->getAllItems();
        $target_data                  = [];
        $last_stock_stats_measurement = end($source_data);

        $measurement_count = 0;

        foreach ($source_data as $stock_stats)
        {
            $target_data['close'][]       = $stock_stats->close();
            $target_data['change'][]      = $stock_stats->change();
            $target_data['high'][]        = $stock_stats->high();
            $target_data['low'][]         = $stock_stats->low();
            $target_data['volume_from'][] = $stock_stats->volumeFrom();
            $target_data['volume_to'][]   = $stock_stats->volumeTo();
            $measurement_count++;
        }

        $predicted_close = $this->predictValues($target_data['close']);
        $predicted_high  = $this->predictValues($target_data['high']);
        $predicted_low   = $this->predictValues($target_data['low']);
        $predicted_from  = $this->predictValues($target_data['volume_from']);
        $predicted_to    = $this->predictValues($target_data['volume_to']);

        $predictions = new MeasurementCollection();
        for ($i = 0; $i < $measurement_count; $i++)
        {
            $current_measurement = new Measurement(
                $last_stock_stats_measurement->currency(),
                $last_stock_stats_measurement->stock(),
                $last_stock_stats_measurement->timestamp()->add($a_measurement_collection->getIntervalBetweenMeasurements()),
                $last_stock_stats_measurement->close(),
                $predicted_close[$i],
                $predicted_high[$i],
                $predicted_low[$i],
                $predicted_from[$i],
                $predicted_to[$i]
            );

            $predictions->addItem($current_measurement);

            $last_stock_stats_measurement = $current_measurement;
        }

        return $predictions;
    }

    private function predictValues(array $samples): array
    {
        $this->prediction_strategy->resetTraining();
        $this->prediction_strategy->train($samples);
        $predictions = $this->prediction_strategy->predictNext(\count($samples));
        $this->prediction_strategy->resetTraining();

        return $predictions;
    }

}
