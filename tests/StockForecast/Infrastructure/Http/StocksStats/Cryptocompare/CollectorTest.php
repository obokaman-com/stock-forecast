<?php

namespace Obokaman\StockForecast\Infrastructure\Http\StocksStats\Cryptocompare;

use Obokaman\StockForecast\Domain\Model\Financial\Currency;
use Obokaman\StockForecast\Domain\Model\Financial\Stock;
use Obokaman\StockForecast\Domain\Model\Financial\StockDateInterval;
use Obokaman\StockForecast\Domain\Model\Financial\StockStats;
use PHPUnit\Framework\TestCase;

class CollectorTest extends TestCase
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
        $this->collector         = new CollectorTestClass();
        $this->stock_stats_array = $this->collector->getStats(
            Currency::fromCode('USD'),
            Stock::fromCode('BTC'),
            StockDateInterval::fromStringDateInterval('days')
        );
    }

    private function thenIShouldHaveAStockStatsArray()
    {
        $this->assertContainsOnlyInstancesOf(StockStats::class, $this->stock_stats_array);
    }
}

class CollectorTestClass extends Collector
{
    protected function collectStockInformationFromRemoteApi(string $api_url): array
    {
        $results = [
            [
                'time'       => 1504656000,
                'close'      => 4616.18,
                'high'       => 4692,
                'low'        => 4431,
                'open'       => 4432.51,
                'volumefrom' => 15975.31,
                'volumeto'   => 73082808.58
            ]
        ];

        return $results;
    }

}
