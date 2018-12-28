<?php

namespace Obokaman\StockForecast\Domain\Model\Financial\Stock;

final class Stock
{
    private $code;

    private function __construct(string $a_code)
    {
        $this->code = $a_code;
    }

    public static function fromCode(string $a_code): Stock
    {
        $a_code = strtoupper($a_code);

        return new self($a_code);
    }

    public function __toString()
    {
        return $this->code;
    }

    public function equals(Stock $a_stock): bool
    {
        return $this->code === $a_stock->code;
    }
}
