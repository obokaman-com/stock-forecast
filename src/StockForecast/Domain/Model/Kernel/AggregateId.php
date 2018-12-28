<?php

namespace Obokaman\StockForecast\Domain\Model\Kernel;

use Ramsey\Uuid\Uuid;

class AggregateId
{
    private $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public static function unique()
    {
        return new static(Uuid::uuid4()->toString());
    }

    public function id(): string
    {
        return $this->id;
    }

    public function equals(self $id): bool
    {
        return $this->id === $id->id;
    }

    public function __toString()
    {
        return $this->id;
    }
}