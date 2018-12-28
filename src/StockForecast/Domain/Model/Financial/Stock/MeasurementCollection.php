<?php

namespace Obokaman\StockForecast\Domain\Model\Financial\Stock;

use Obokaman\StockForecast\Domain\Model\Date\Interval;
use Obokaman\StockForecast\Domain\Model\Date\Period;
use Obokaman\StockForecast\Domain\Model\Kernel\Collection;

/**
 * @property Measurement[] $all_items
 */
class MeasurementCollection extends Collection
{
    protected function getItemsClassName(): string
    {
        return Measurement::class;
    }

    /**
     * @param Measurement $item
     *
     * @return string
     */
    protected function getKey($item): string
    {
        return (string)$item->timestamp()->getTimestamp();
    }

    public function getIntervalBetweenMeasurements(): \DateInterval
    {
        [$penultimate_stock_measurement, $last_stock_measurement] = \array_slice($this->all_items, -2, 2);

        return $last_stock_measurement->timestamp()->diff($penultimate_stock_measurement->timestamp());
    }

    public function filterByPeriod(string $a_period): MeasurementCollection
    {
        $interval = Interval::fromDateInterval($this->getIntervalBetweenMeasurements());
        $period   = Period::getPeriod($interval, $a_period);

        return $this->filterByQuantity($period);
    }

    public function filterByQuantity(int $quantity): MeasurementCollection
    {
        if ($quantity > \count($this->all_items)) {
            throw new \InvalidArgumentException('Trying to get more items than currently available.');
        }

        $items = \array_slice($this->all_items, -$quantity, $quantity);

        return new self($items);
    }

    public function priceChangeAmount(): float
    {
        $first_measurement = reset($this->all_items);
        $last_measurement  = end($this->all_items);

        return $last_measurement->close() - $first_measurement->close();
    }

    public function priceChangePercent(): float
    {
        $first_measurement = reset($this->all_items);
        $last_measurement  = end($this->all_items);

        return round((($last_measurement->close() - $first_measurement->close()) / abs($first_measurement->close())) * 100, 2);
    }
}
