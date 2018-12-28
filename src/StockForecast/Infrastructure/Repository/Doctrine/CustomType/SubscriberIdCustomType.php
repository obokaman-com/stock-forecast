<?php

namespace Obokaman\StockForecast\Infrastructure\Repository\Doctrine\CustomType;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Obokaman\StockForecast\Domain\Model\Subscriber\SubscriberId;

final class SubscriberIdCustomType extends Type
{
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'varchar(255)';
    }

    public function getName()
    {
        return 'subscriber_id';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return null;
        }
        return new SubscriberId($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return (string)$value;
    }
}
