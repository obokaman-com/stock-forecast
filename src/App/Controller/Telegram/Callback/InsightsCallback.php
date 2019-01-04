<?php

namespace App\Controller\Telegram\Callback;


use Obokaman\StockForecast\Domain\Model\Date\Interval;
use Obokaman\StockForecast\Domain\Model\Financial\Currency;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\Stock;
use Obokaman\StockForecast\Domain\Service\Signal\CalculateScore;
use Obokaman\StockForecast\Domain\Service\Signal\GetSignalsFromMeasurements;
use Obokaman\StockForecast\Infrastructure\Http\StockMeasurement\Collector;
use TelegramBot\Api\Client;
use TelegramBot\Api\Exception as TelegramException;
use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class InsightsCallback extends BaseCallback
{
    private $measurement_collector;
    private $get_signals_service;

    public function __construct(Client $a_telegram_client, Collector $a_stock_measurement_collector, GetSignalsFromMeasurements $a_get_signals_service)
    {
        parent::__construct($a_telegram_client);
        $this->measurement_collector = $a_stock_measurement_collector;
        $this->get_signals_service   = $a_get_signals_service;
    }

    public function getCallback(): string
    {
        return 'insights';
    }

    public function execute(CallbackQuery $a_callback): void
    {
        $callback_data = $this->getCallbackData($a_callback);

        $currency = $callback_data['currency'];
        $crypto   = $callback_data['crypto'];

        $this->telegram_client->editMessageText(
            $a_callback->getMessage()->getChat()->getId(),
            $a_callback->getMessage()->getMessageId(),
            sprintf('I\'ll give you some insights for *%s-%s*:', $currency, $crypto),
            'Markdown');

        try {
            $signals_message = $this->outputSignalsBasedOn('hour', Interval::MINUTES, $currency, $crypto);
            $signals_message .= $this->outputSignalsBasedOn('day', Interval::HOURS, $currency, $crypto);
            $signals_message .= $this->outputSignalsBasedOn('month', Interval::DAYS, $currency, $crypto);

            $this->telegram_client->sendMessage(
                $a_callback->getMessage()->getChat()->getId(),
                $signals_message,
                'Markdown',
                false,
                null,
                new InlineKeyboardMarkup([
                    [
                        [
                            'text' => '📈 View ' . $currency . '-' . $crypto . ' chart online »',
                            'url'  => 'https://www.cryptocompare.com/coins/' . strtolower($crypto) . '/charts/' . strtolower($currency)
                        ]
                    ]
                ]));
        } catch (TelegramException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->telegram_client->sendMessage(
                $a_callback->getMessage()->getChat()->getId(),
                'There was an error: ' . $e->getMessage()
            );
        }

    }

    public function outputSignalsBasedOn(string $interval, string $interval_unit, string $currency, string $stock): string
    {
        $measurements = $this->measurement_collector->getMeasurements(
            Currency::fromCode($currency),
            Stock::fromCode($stock),
            Interval::fromStringDateInterval($interval_unit)
        );

        $signals_response = $this->get_signals_service->getSignals($measurements);

        $message = 'Based on *last ' . $interval . '* (Score: ' . CalculateScore::calculate(...$signals_response) . '):' . PHP_EOL;

        foreach ($signals_response as $signal) {
            $message .= '_ - ' . $signal . '_' . PHP_EOL;
        }

        if (Interval::MINUTES === $interval_unit) {
            $message = 'Last price: ' . $measurements->end()->close() . ' ' . $currency . PHP_EOL . $message;
        }

        return $message . PHP_EOL;
    }
}