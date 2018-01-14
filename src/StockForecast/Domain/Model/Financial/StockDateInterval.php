<?php

namespace Obokaman\StockForecast\Domain\Model\Financial;

final class StockDateInterval
{
    public const DAYS = 'days';
    public const HOURS = 'hours';
    public const MINUTES = 'minutes';
    private const VALID_INTERVALS = [self::DAYS, self::HOURS, self::MINUTES];

    private $interval;

    public function __construct(string $date_interval)
    {
        $this->validateDateInterval($date_interval);

        $this->interval = $date_interval;
    }

    private function validateDateInterval(string $date_interval): void
    {
        if (!\in_array($date_interval, self::VALID_INTERVALS, true))
        {
            throw new \InvalidArgumentException('Invalid date interval: ' . $date_interval . '. Valid intervals include: ' . implode(', ', self::VALID_INTERVALS));
        }
    }

    public static function fromStringDateInterval(string $date_interval): StockDateInterval
    {
        return new self($date_interval);
    }

    public function isDays(): bool
    {
        return 'days' === $this->interval;
    }

    public function isHours(): bool
    {
        return 'hours' === $this->interval;
    }

    public function isMinutes(): bool
    {
        return 'minutes' === $this->interval;
    }

    public function interval(): string
    {
        return $this->interval;
    }
}
