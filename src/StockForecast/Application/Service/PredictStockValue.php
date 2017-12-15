<?php

namespace Obokaman\StockForecast\Application\Service;

use Obokaman\StockForecast\Domain\Model\Financial\StockStats;
use Obokaman\StockForecast\Domain\Service\Predict\PredictionStrategy;
use Obokaman\StockForecast\Infrastructure\Http\StocksStats\Collector;

final class PredictStockValue
{
    /** @var Collector */
    private $stock_stats_collector;

    /** @var PredictionStrategy */
    private $prediction_strategy;

    public function __construct(Collector $a_stock_stats_collector, PredictionStrategy $a_prediction_strategy)
    {
        $this->stock_stats_collector = $a_stock_stats_collector;
        $this->prediction_strategy   = $a_prediction_strategy;
    }

    public function predict(PredictStockValueRequest $a_request): PredictStockValueResponse
    {
        $sample_data      = $this->getSampleData($a_request);
        $targets          = $this->getTargets($sample_data);
        $last_measurement = end($sample_data);

        $short_term_targets  = array_map(
            function ($target) {
                return \array_slice($target, -Collector::SHORT_INTERVAL, Collector::SHORT_INTERVAL);
            },
            $targets
        );
        $short_term_forecast = $this->getForecast($a_request, $short_term_targets, $last_measurement);

        $medium_term_targets  = array_map(
            function ($target) {
                return \array_slice($target, -Collector::MEDIUM_INTERVAL, Collector::MEDIUM_INTERVAL);
            },
            $targets
        );
        $medium_term_forecast = $this->getForecast($a_request, $medium_term_targets, $last_measurement);

        $long_term_targets  = array_map(
            function ($target) {
                return \array_slice($target, -Collector::LONG_INTERVAL, Collector::LONG_INTERVAL);
            },
            $targets
        );
        $long_term_forecast = $this->getForecast($a_request, $long_term_targets, $last_measurement);

        return new PredictStockValueResponse($sample_data, $short_term_forecast, $medium_term_forecast, $long_term_forecast);
    }

    /**
     * @param StockStats[] $sample_data
     *
     * @return array
     */
    private function getTargets(array $sample_data): array
    {
        $all_targets = [];
        foreach ($sample_data as $stats)
        {
            $all_targets['change'][]      = $stats->change();
            $all_targets['high'][]        = $stats->high();
            $all_targets['low'][]         = $stats->low();
            $all_targets['volume_from'][] = $stats->volumeFrom();
            $all_targets['volume_to'][]   = $stats->volumeTo();
        }

        return $all_targets;
    }

    /**
     * @param PredictStockValueRequest $a_request
     *
     * @return StockStats[]
     */
    private function getSampleData(PredictStockValueRequest $a_request): array
    {
        $real_stats_array = $this->stock_stats_collector->getStats(
            $a_request->currency(),
            $a_request->stock(),
            $a_request->dateInterval()
        );

        return $real_stats_array;
    }

    private function predictSelectedTarget(array $targets, int $forecast_sequences_quantity): array
    {
        $this->prediction_strategy->train($targets);
        $prediction = $this->prediction_strategy->predictNext($forecast_sequences_quantity);
        $this->prediction_strategy->resetTraining();

        return $prediction;
    }

    private function getForecast(PredictStockValueRequest $a_request, array $targets, StockStats $last_measurement): StockStats
    {
        $change_predition       = $this->predictSelectedTarget($targets['change'], 1);
        $high_prediction        = $this->predictSelectedTarget($targets['high'], 1);
        $low_prediction         = $this->predictSelectedTarget($targets['low'], 1);
        $volume_from_prediction = $this->predictSelectedTarget($targets['volume_from'], 1);
        $volume_to_prediction   = $this->predictSelectedTarget($targets['volume_to'], 1);

        $stock_stats = new StockStats(
            $a_request->currency(),
            $a_request->stock(),
            $last_measurement->timestamp()->add(\DateInterval::createFromDateString('1 ' . $a_request->dateInterval()->interval())),
            $last_measurement->close(),
            $last_measurement->close() + $change_predition[0],
            $high_prediction[0],
            $low_prediction[0],
            $volume_from_prediction[0],
            $volume_to_prediction[0]
        );

        return $stock_stats;
    }
}
