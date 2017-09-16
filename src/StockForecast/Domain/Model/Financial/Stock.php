<?php

namespace Obokaman\StockForecast\Domain\Model\Financial;

final class Stock
{
    private $code;

    private function __construct(string $a_code)
    {
        $this->code = $a_code;
    }

    public static function fromCode(string $a_code)
    {
        $a_code = mb_strtoupper($a_code);

        return new self($a_code);
    }

    public function __toString()
    {
        return $this->code;
    }
}
