<?php

namespace Obokaman\StockForecast\Domain\Model\Financial;

final class StockStats
{
    private $currency;
    private $stock;
    private $timestamp;
    private $close;
    private $high;
    private $low;
    private $open;
    private $volume_from;
    private $volume_to;

    public function __construct(
        Currency $a_currency,
        Stock $a_stock,
        \DateTimeImmutable $a_timestamp,
        float $a_close,
        float $a_high,
        float $a_low,
        float $an_open,
        float $a_volume_from,
        float $a_volume_to
    )
    {
        $this->currency    = $a_currency;
        $this->stock       = $a_stock;
        $this->timestamp   = $a_timestamp;
        $this->close       = $a_close;
        $this->high        = $a_high;
        $this->low         = $a_low;
        $this->open        = $an_open;
        $this->volume_from = $a_volume_from;
        $this->volume_to   = $a_volume_to;
    }

    public function currency()
    {
        return $this->currency;
    }

    public function stock()
    {
        return $this->stock;
    }

    public function timestamp()
    {
        return $this->timestamp;
    }

    public function close()
    {
        return round($this->close, 2);
    }

    public function high()
    {
        return round($this->high, 2);
    }

    public function low()
    {
        return round($this->low, 2);
    }

    public function open()
    {
        return round($this->open, 2);
    }

    public function volumeFrom()
    {
        return round($this->volume_from, 2);
    }

    public function volumeTo()
    {
        return round($this->volume_to, 2);
    }
}
