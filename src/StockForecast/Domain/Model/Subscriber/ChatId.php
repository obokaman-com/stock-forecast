<?php

namespace Obokaman\StockForecast\Domain\Model\Subscriber;

class ChatId
{
    private $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function __toString()
    {
        return $this->id;
    }
}