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

    /** @var PredictStockValueRequest */
    private $request;

    /** @var array */
    private $sample_data;

    /** @var array */
    private $targets;

    public function __construct(Collector $a_stock_stats_collector, PredictionStrategy $a_prediction_strategy)
    {
        $this->stock_stats_collector = $a_stock_stats_collector;
        $this->prediction_strategy   = $a_prediction_strategy;
    }

    public function predict(PredictStockValueRequest $a_request): PredictStockValueResponse
    {
        $this->request     = $a_request;
        $this->sample_data = $this->getSampleData();
        $this->targets     = $this->getTargets();

        $date_interval = $a_request->dateInterval()->interval();

        $short_term_forecast  = $this->getForecastForDateInterval(Collector::SHORT_INTERVAL[$date_interval]);
        $medium_term_forecast = $this->getForecastForDateInterval(Collector::MEDIUM_INTERVAL[$date_interval]);
        $long_term_forecast   = $this->getForecastForDateInterval(Collector::LONG_INTERVAL[$date_interval]);

        return new PredictStockValueResponse($this->sample_data, $short_term_forecast, $medium_term_forecast, $long_term_forecast);
    }

    /**
     * @return StockStats[]
     */
    private function getSampleData(): array
    {
        $real_stats_array = $this->stock_stats_collector->getStats(
            $this->request->currency(),
            $this->request->stock(),
            $this->request->dateInterval()
        );

        return $real_stats_array;
    }

    private function getTargets(): array
    {
        $all_targets = [];
        foreach ($this->sample_data as $stats)
        {
            $all_targets['close'][]       = $stats->close();
            $all_targets['high'][]        = $stats->high();
            $all_targets['low'][]         = $stats->low();
            $all_targets['volume_from'][] = $stats->volumeFrom();
            $all_targets['volume_to'][]   = $stats->volumeTo();
        }

        return $all_targets;
    }

    private function getForecastForDateInterval(int $date_interval): StockStats
    {
        $filtered_targets = array_map(
            function ($target) use ($date_interval) {
                return \array_slice($target, -$date_interval, $date_interval);
            },
            $this->targets
        );

        return $this->getForecast($filtered_targets);
    }

    private function getForecast(array $targets): StockStats
    {
        $last_measurement = end($this->sample_data);

        $close_prediction       = $this->predictSelectedTarget($targets['close'], 1);
        $high_prediction        = $this->predictSelectedTarget($targets['high'], 1);
        $low_prediction         = $this->predictSelectedTarget($targets['low'], 1);
        $volume_from_prediction = $this->predictSelectedTarget($targets['volume_from'], 1);
        $volume_to_prediction   = $this->predictSelectedTarget($targets['volume_to'], 1);

        $stock_stats = new StockStats(
            $this->request->currency(),
            $this->request->stock(),
            $last_measurement->timestamp()->add(\DateInterval::createFromDateString('1 ' . $this->request->dateInterval()->interval())),
            $last_measurement->close(),
            $close_prediction[0],
            $high_prediction[0],
            $low_prediction[0],
            $volume_from_prediction[0],
            $volume_to_prediction[0]
        );

        return $stock_stats;
    }

    private function predictSelectedTarget(array $targets, int $forecast_sequences_quantity): array
    {
        $this->prediction_strategy->train($targets);
        $prediction = $this->prediction_strategy->predictNext($forecast_sequences_quantity);
        $this->prediction_strategy->resetTraining();

        return $prediction;
    }
}
