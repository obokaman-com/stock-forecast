<?php

namespace Obokaman\StockForecast\Domain\Model\Financial\Stock;

use DateTimeImmutable;
use Obokaman\StockForecast\Domain\Model\Financial\Currency;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MeasurementTest extends WebTestCase
{
    /** @var Measurement */
    private $measurement;

    /**
     * @test
     * @dataProvider statsSamplesProvider
     * @param $stats_sample
     */
    public function shouldBeAbleToCreateStats($stats_sample)
    {
        $this->whenIHaveAMeasurement($stats_sample);
        $this->thenIShouldHaveAMeasurementEntity();
    }

    /**
     * @test
     * @dataProvider statsSamplesProvider
     * @param $stats_sample
     */
    public function shouldBeAbleToRecoverStatsInfo($stats_sample)
    {
        $this->whenIHaveAMeasurement($stats_sample);
        $this->thenIShouldBeAbleToRecoverStatsInfo();
    }

    private function whenIHaveAMeasurement($stats_sample)
    {
        $this->measurement = new Measurement(...$stats_sample);
    }

    private function thenIShouldHaveAMeasurementEntity()
    {
        $this->assertInstanceOf(Measurement::class, $this->measurement);
    }

    public function statsSamplesProvider()
    {
        return [
            [[Currency::fromCode('EUR'), Stock::fromCode('BTC'), new DateTimeImmutable('now'), 10, 10, 10, 10, 10, 10]]
        ];
    }

    private function thenIShouldBeAbleToRecoverStatsInfo()
    {
        $this->assertInstanceOf(Currency::class, $this->measurement->currency());
        $this->assertInstanceOf(Stock::class, $this->measurement->stock());
        $this->assertInstanceOf(DateTimeImmutable::class, $this->measurement->timestamp());
        $this->assertIsFloat($this->measurement->open());
        $this->assertIsFloat($this->measurement->close());
        $this->assertIsFloat( $this->measurement->change());
        $this->assertIsFloat($this->measurement->high());
        $this->assertIsFloat($this->measurement->low());
        $this->assertIsFloat($this->measurement->volatility());
        $this->assertIsFloat($this->measurement->volume());
        $this->assertIsFloat($this->measurement->volumeFrom());
        $this->assertIsFloat($this->measurement->volumeTo());
    }
}
