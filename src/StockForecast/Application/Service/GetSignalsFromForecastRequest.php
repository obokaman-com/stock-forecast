<?php

namespace Obokaman\StockForecast\Application\Service;

use Obokaman\StockForecast\Domain\Model\Financial\StockStats;

final class GetSignalsFromForecastRequest
{
    private $short_term_stats;
    private $medium_term_stats;
    private $long_term_stats;

    public function __construct(StockStats $a_short_term_stats, StockStats $a_medium_term_stats, StockStats $a_long_term_stats)
    {
        $this->short_term_stats  = $a_short_term_stats;
        $this->medium_term_stats = $a_medium_term_stats;
        $this->long_term_stats   = $a_long_term_stats;
    }

    public function shortTermStats(): StockStats
    {
        return $this->short_term_stats;
    }

    public function mediumTermStats(): StockStats
    {
        return $this->medium_term_stats;
    }

    public function longTermStats(): StockStats
    {
        return $this->long_term_stats;
    }
}