<?php

namespace Obokaman\StockForecast\Domain\Model\Subscriber;

use Obokaman\StockForecast\Domain\Model\Kernel\Collection;

/**
 * @property Subscription[] $all_items
 */
class SubscriptionCollection extends Collection
{
    protected function getItemsClassName(): string
    {
        return Subscription::class;
    }

    /**
     * @param Subscription $item
     *
     * @return string
     */
    protected function getKey($item): string
    {
        return $item->currency() . '-' . $item->stock();
    }
}