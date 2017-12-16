<?php

namespace Obokaman\StockForecast\Infrastructure\Http\StocksStats\Cryptocompare;

use Obokaman\StockForecast\Domain\Model\Financial\Currency;
use Obokaman\StockForecast\Domain\Model\Financial\Stock;
use Obokaman\StockForecast\Domain\Model\Financial\StockDateInterval;
use Obokaman\StockForecast\Domain\Model\Financial\StockStats;
use Obokaman\StockForecast\Infrastructure\Http\StocksStats\CollectException;
use Obokaman\StockForecast\Infrastructure\Http\StocksStats\Collector as CollectorContract;

class Collector implements CollectorContract
{
    private const API_URL = 'https://min-api.cryptocompare.com/data/%s?fsym=%s&tsym=%s&limit=%d&aggregate=1';

    public function getStats(Currency $a_currency, Stock $a_stock, StockDateInterval $a_date_interval): array
    {
        $api_method  = $this->getApiMethodForInterval($a_date_interval);
        $api_url     = sprintf(self::API_URL, $api_method, $a_stock, $a_currency, CollectorContract::LONG_INTERVAL - 1);
        $response    = $this->collectStockInformationFromRemoteApi($api_url);
        $stats_array = [];

        foreach ($response as $stats)
        {
            $stats_array[] = new StockStats(
                $a_currency,
                $a_stock,
                (new \DateTimeImmutable())->setTimestamp($stats['time']),
                $stats['open'],
                $stats['close'],
                $stats['high'],
                $stats['low'],
                $stats['volumefrom'],
                $stats['volumeto']
            );
        }

        return $stats_array;
    }

    protected function collectStockInformationFromRemoteApi(string $api_url): array
    {
        $response = json_decode(file_get_contents($api_url), true) ?: [];

        if (empty($response))
        {
            throw new CollectException();
        }

        if (empty($response['Data']))
        {
            throw new CollectException($response['Message'] ?? null);
        }

        return $response['Data'];
    }

    private function getApiMethodForInterval(StockDateInterval $a_date_interval)
    {
        if ($a_date_interval->isDays())
        {
            return 'histoday';
        }

        if ($a_date_interval->isHours())
        {
            return 'histohour';
        }

        if ($a_date_interval->isMinutes())
        {
            return 'histominute';
        }

        throw new \UnexpectedValueException('Unknown given date interval: ' . $a_date_interval->interval());
    }
}
