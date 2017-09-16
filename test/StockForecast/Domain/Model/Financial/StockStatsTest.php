<?php

namespace Obokaman\StockForecast\Domain\Model\Financial;

use PHPUnit\Framework\TestCase;

class StockStatsTest extends TestCase
{
    /** @var StockStats */
    private $stock_stats;

    /**
     * @test
     * @dataProvider statsSamplesProvider
     * @param $stats_sample
     */
    public function shouldBeAbleToCreateStats($stats_sample)
    {
        $this->whenIHaveStatsData($stats_sample);
        $this->thenIShouldHaveAStockStatsEntity();
    }

    /**
     * @test
     * @dataProvider statsSamplesProvider
     * @param $stats_sample
     */
    public function shouldBeAbleToRecoverStatsInfo($stats_sample)
    {
        $this->whenIHaveStatsData($stats_sample);
        $this->thenIShouldBeAbleToRecoverStatsInfo();
    }

    private function whenIHaveStatsData($stats_sample)
    {
        $this->stock_stats = new StockStats(...$stats_sample);
    }

    private function thenIShouldHaveAStockStatsEntity()
    {
        $this->assertInstanceOf(StockStats::class, $this->stock_stats);
    }

    public function statsSamplesProvider()
    {
        return [
            [[Currency::fromCode('EUR'), Stock::fromCode('BTC'), new \DateTimeImmutable('now'), 10, 10, 10, 10, 10, 10]]
        ];
    }

    private function thenIShouldBeAbleToRecoverStatsInfo()
    {
        $this->assertInstanceOf(Currency::class, $this->stock_stats->currency());
        $this->assertInstanceOf(Stock::class, $this->stock_stats->stock());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->stock_stats->timestamp());
        $this->assertInternalType('float', $this->stock_stats->open());
        $this->assertInternalType('float', $this->stock_stats->close());
        $this->assertInternalType('float', $this->stock_stats->high());
        $this->assertInternalType('float', $this->stock_stats->low());
        $this->assertInternalType('float', $this->stock_stats->volumeFrom());
        $this->assertInternalType('float', $this->stock_stats->volumeTo());
    }
}
