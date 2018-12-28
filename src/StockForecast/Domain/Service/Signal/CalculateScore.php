<?php

namespace Obokaman\StockForecast\Domain\Service\Signal;

use Obokaman\StockForecast\Domain\Model\Financial\Signal;

class CalculateScore
{
    public static function calculate(Signal ...$signals): int
    {
        $score = 0;
        foreach ($signals as $signal) {
            $score += $signal->score();
        }

        return $score;
    }
}
