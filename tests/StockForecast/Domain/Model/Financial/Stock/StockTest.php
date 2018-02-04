<?php

namespace Obokaman\StockForecast\Domain\Model\Financial\Stock;

use PHPUnit\Framework\TestCase;

class StockTest extends TestCase
{
    /** @var Stock */
    private $stock;

    /**
     * @test
     */
    public function shouldConvertStockCodeToUppercase()
    {
        $this->whenITryToCreateAnValidStock('appl');
        $this->thenIObtainAValidCurrency('APPL');
    }

    private function whenITryToCreateAnValidStock($stock_code)
    {
        $this->stock = Stock::fromCode($stock_code);
    }

    private function thenIObtainAValidCurrency($currency_code)
    {
        $this->assertEquals($currency_code, (string) $this->stock);
    }
}
