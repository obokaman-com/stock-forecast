<?php

namespace Obokaman\StockForecast\Infrastructure\Http\StockMeasurement\Cryptocompare;

use DateTimeImmutable;
use Obokaman\StockForecast\Domain\Model\Date\Interval;
use Obokaman\StockForecast\Domain\Model\Date\Period;
use Obokaman\StockForecast\Domain\Model\Financial\Currency;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\Measurement;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\MeasurementCollection;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\Stock;
use Obokaman\StockForecast\Infrastructure\Http\StockMeasurement\CollectException;
use Obokaman\StockForecast\Infrastructure\Http\StockMeasurement\Collector as CollectorContract;
use UnexpectedValueException;

class Collector implements CollectorContract
{
    private const API_URL = 'https://min-api.cryptocompare.com/data/%s?fsym=%s&tsym=%s&limit=%d&aggregate=1';

    public function getMeasurements(
        Currency $a_currency,
        Stock $a_stock,
        Interval $a_date_interval
    ): MeasurementCollection {
        $api_url = $this->getApiUrl($a_currency, $a_stock, $a_date_interval);
        $response = $this->collectStockInformationFromRemoteApi($api_url);

        $stats_array = new MeasurementCollection();

        foreach ($response as $stats) {
            $stats_array->addItem(
                new Measurement(
                    $a_currency,
                    $a_stock,
                    (new DateTimeImmutable())->setTimestamp($stats['time']),
                    $stats['open'],
                    $stats['close'],
                    $stats['high'],
                    $stats['low'],
                    $stats['volumefrom'],
                    $stats['volumeto']
                )
            );
        }

        return $stats_array;
    }

    protected function collectStockInformationFromRemoteApi(string $api_url): array
    {
        $response = json_decode(file_get_contents($api_url), true) ?: [];

        if (empty($response)) {
            throw new CollectException();
        }

        if (empty($response['Data'])) {
            throw new CollectException($response['Message'] ?? null);
        }

        return $response['Data'];
    }

    private function getApiMethodForInterval(Interval $a_date_interval): string
    {
        if ($a_date_interval->isDays()) {
            return 'histoday';
        }

        if ($a_date_interval->isHours()) {
            return 'histohour';
        }

        if ($a_date_interval->isMinutes()) {
            return 'histominute';
        }

        throw new UnexpectedValueException('Unknown given date interval: ' . $a_date_interval->interval());
    }

    private function getApiUrl(Currency $a_currency, Stock $a_stock, Interval $a_date_interval): string
    {
        $api_method = $this->getApiMethodForInterval($a_date_interval);
        $api_url = sprintf(self::API_URL, $api_method, $a_stock, $a_currency, Period::getLong($a_date_interval) - 1);
        $cryptocompare_token = $_ENV['CRYPTOCOMPARE_TOKEN'] ?? null;

        if ('YourCryptocompareToken' === $cryptocompare_token || null === $cryptocompare_token) {
            return $api_url;
        }

        return $api_url . '&api_key=' . $cryptocompare_token;
    }
}
