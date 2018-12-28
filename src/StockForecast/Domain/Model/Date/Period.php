<?php

namespace Obokaman\StockForecast\Domain\Model\Date;

class Period
{
    public const SHORT  = 'short';
    public const MEDIUM = 'medium';
    public const LONG   = 'long';

    private const SHORT_PERIOD = [
        Interval::DAYS    => 7,
        Interval::HOURS   => 6,
        Interval::MINUTES => 15
    ];

    private const MEDIUM_PERIOD = [
        Interval::DAYS    => 15,
        Interval::HOURS   => 12,
        Interval::MINUTES => 30
    ];

    private const LONG_PERIOD = [
        Interval::DAYS    => 30,
        Interval::HOURS   => 24,
        Interval::MINUTES => 60
    ];

    public static function getPeriod(Interval $an_interval, string $a_period): int
    {
        if (self::SHORT === $a_period) {
            return self::getShort($an_interval);
        }
        if (self::MEDIUM === $a_period) {
            return self::getMedium($an_interval);
        }

        return self::getLong($an_interval);
    }

    public static function getShort(Interval $an_interval): int
    {
        return self::SHORT_PERIOD[$an_interval->interval()];
    }

    public static function getMedium(Interval $an_interval): int
    {
        return self::MEDIUM_PERIOD[$an_interval->interval()];
    }

    public static function getLong(Interval $an_interval): int
    {
        return self::LONG_PERIOD[$an_interval->interval()];
    }
}
