<?php

namespace Obokaman\StockForecast\Domain\Model\Financial;

final class StockDateInterval
{
    private const VALID_INTERVALS = ['minutes', 'hours', 'days'];

    private $interval;

    public function __construct(string $date_interval)
    {
        $this->validateDateInterval($date_interval);

        $this->interval = $date_interval;
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

    private function validateDateInterval(string $date_interval): void
    {
        if (!\in_array($date_interval, self::VALID_INTERVALS, true))
        {
            throw new \InvalidArgumentException('Invalid date interval: ' . $date_interval . '. Valid intervals include: ' . implode(', ', self::VALID_INTERVALS));
        }
    }
}
