<?php

namespace Obokaman\StockForecast\Domain\Model\Subscriber;

interface SubscriberRepository
{
    public function find(SubscriberId $a_subscriber_id): ?Subscriber;

    public function findByChatId(ChatId $a_chat_id): ?Subscriber;

    /** @return Subscriber[] */
    public function findAll(): array;

    public function persist(Subscriber $a_subscriber): void;

    public function remove(SubscriberId $a_subscriber_id): void;

    public function flush(): void;
}