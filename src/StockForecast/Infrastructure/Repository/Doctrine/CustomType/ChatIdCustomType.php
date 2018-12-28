<?php

namespace Obokaman\StockForecast\Infrastructure\Repository\Doctrine\CustomType;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Obokaman\StockForecast\Domain\Model\Subscriber\ChatId;

final class ChatIdCustomType extends Type
{
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'varchar(255)';
    }

    public function getName()
    {
        return 'chat_id';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return new ChatId($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return (string)$value;
    }
}
