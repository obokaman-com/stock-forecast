<?php

namespace Obokaman\StockForecast\Domain\Model\Subscriber;


use Obokaman\StockForecast\Domain\Model\Financial\Currency;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\Stock;

class Subscription
{
    private $subscriber;
    private $currency;
    private $stock;

    public function __construct(Subscriber $a_subscriber, Currency $currency, Stock $stock)
    {
        $this->subscriber = $a_subscriber;
        $this->currency = $currency;
        $this->stock    = $stock;
    }

    public function subscriber(): Subscriber
    {
        return $this->subscriber;
    }

    public function currency(): Currency
    {
        return $this->currency;
    }

    public function stock(): Stock
    {
        return $this->stock;
    }
}