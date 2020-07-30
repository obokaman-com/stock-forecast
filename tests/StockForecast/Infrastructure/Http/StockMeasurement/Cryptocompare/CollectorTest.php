<?php

namespace Obokaman\StockForecast\Infrastructure\Http\StockMeasurement\Cryptocompare;

use Obokaman\StockForecast\Domain\Model\Date\Interval;
use Obokaman\StockForecast\Domain\Model\Financial\Currency;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\Measurement;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\Stock;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CollectorTest extends WebTestCase
{
    private $collector;
    private $stock_stats_array;

    /** @test */
    public function shouldReturnAStockStatsArray()
    {
        $this->whenICollectStockStats();
        $this->thenIShouldHaveAStockStatsArray();
    }

    private function whenICollectStockStats()
    {
        $this->collector = new CollectorTestClass();
        $this->stock_stats_array = $this->collector->getMeasurements(
            Currency::fromCode('USD'),
            Stock::fromCode('BTC'),
            Interval::fromStringDateInterval('days')
        );
    }

    private function thenIShouldHaveAStockStatsArray()
    {
        $this->assertContainsOnlyInstancesOf(Measurement::class, $this->stock_stats_array);
    }
}

class CollectorTestClass extends Collector
{
    protected function collectStockInformationFromRemoteApi(string $api_url): array
    {
        $results = [
            [
                'time' => 1504656000,
                'close' => 4616.18,
                'high' => 4692,
                'low' => 4431,
                'open' => 4432.51,
                'volumefrom' => 15975.31,
                'volumeto' => 73082808.58
            ]
        ];

        return $results;
    }
}
