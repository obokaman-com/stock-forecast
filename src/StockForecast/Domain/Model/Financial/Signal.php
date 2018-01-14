<?php

namespace Obokaman\StockForecast\Domain\Model\Financial;

class Signal
{
    private $description;
    private $score;

    private function __construct(string $a_description, int $a_score)
    {
        $this->description = $a_description;
        $this->score       = $a_score;
    }

    public static function GOOD(string $a_description): Signal
    {
        return new self($a_description, 1);
    }

    public static function EXCELLENT(string $a_description): Signal
    {
        return new self($a_description, 2);
    }

    public static function NEUTRAL(string $a_description): Signal
    {
        return new self($a_description, 0);
    }

    public static function BAD(string $a_description): Signal
    {
        return new self($a_description, -1);
    }

    public static function POOR(string $a_description): Signal
    {
        return new self($a_description, -2);
    }

    public function description(): string
    {
        return $this->description;
    }

    public function score(): int
    {
        return $this->score;
    }

    public function __toString()
    {
        return $this->description;
    }
}
