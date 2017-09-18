<?php

namespace Obokaman\StockForecast\Application\Service;

use Obokaman\StockForecast\Domain\Model\Financial\Currency;
use Obokaman\StockForecast\Domain\Model\Financial\Stock;
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

    public function predict(PredictStockValueRequest $a_request)
    {
        $sample_data = $this->getSampleData($a_request);
        $targets     = $this->getTargets($sample_data);

        $last_day_datetime = (end($sample_data))->timestamp();

        $forecast_stats_array = $this->getForecast($a_request->currency(), $a_request->stock(), $last_day_datetime, $targets, $a_request->daysToForecast());

        return new PredictStockValueResponse($sample_data, $forecast_stats_array);
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
            $all_targets['close'][]       = $stats->close();
            $all_targets['high'][]        = $stats->high();
            $all_targets['low'][]         = $stats->low();
            $all_targets['open'][]        = $stats->open();
            $all_targets['volume_from'][] = $stats->volumeFrom();
            $all_targets['volume_to'][]   = $stats->volumeTo();
        }

        return $all_targets;
    }

    private function getSampleData(PredictStockValueRequest $a_request)
    {
        $real_stats_array = $this->stock_stats_collector->getStats(
            $a_request->currency(),
            $a_request->stock(),
            $a_request->daysToCollect()
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

    private function getForecast(
        Currency $a_currency,
        Stock $a_stock,
        \DateTimeImmutable $last_day_datetime,
        array $all_targets,
        int $forecast_days_quantity
    ): array
    {
        $open_prediction        = $this->predictSelectedTarget($all_targets['open'], $forecast_days_quantity);
        $close_prediction       = $this->predictSelectedTarget($all_targets['close'], $forecast_days_quantity);
        $high_prediction        = $this->predictSelectedTarget($all_targets['high'], $forecast_days_quantity);
        $low_prediction         = $this->predictSelectedTarget($all_targets['low'], $forecast_days_quantity);
        $volume_from_prediction = $this->predictSelectedTarget($all_targets['volume_from'], $forecast_days_quantity);
        $volume_to_prediction   = $this->predictSelectedTarget($all_targets['volume_to'], $forecast_days_quantity);

        $stock_stats = [];

        for ($i = 0; $i < $forecast_days_quantity; $i++)
        {
            $stock_stats[] = new StockStats(
                $a_currency,
                $a_stock,
                $last_day_datetime->add(new \DateInterval('P' . $i . 'D')),
                $close_prediction[$i],
                $high_prediction[$i],
                $low_prediction[$i],
                $open_prediction[$i],
                $volume_from_prediction[$i],
                $volume_to_prediction[$i]
            );
        }

        return $stock_stats;
    }
}
