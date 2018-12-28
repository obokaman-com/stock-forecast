<?php

namespace Obokaman\StockForecast\Infrastructure\Repository\Doctrine\CustomType;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Obokaman\StockForecast\Domain\Model\Financial\Currency;

final class CurrencyCustomType extends Type
{
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'varchar(3)';
    }

    public function getName()
    {
        return 'currency';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return null;
        }
        return Currency::fromCode($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return (string)$value;
    }
}
