<?php

namespace Obokaman\StockForecast\Infrastructure\Repository\Doctrine\CustomType;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\Stock;

final class StockCustomType extends Type
{
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'varchar(3)';
    }

    public function getName()
    {
        return 'stock';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return null;
        }
        return Stock::fromCode($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return (string)$value;
    }
}
