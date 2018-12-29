<?php

namespace App\Controller\Telegram;

use Obokaman\StockForecast\Domain\Model\Date\Interval;
use Obokaman\StockForecast\Domain\Model\Financial\Currency;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\Stock;
use Obokaman\StockForecast\Domain\Model\Subscriber\SubscriberRepository;
use Obokaman\StockForecast\Domain\Service\Signal\CalculateScore;
use Obokaman\StockForecast\Domain\Service\Signal\GetSignalsFromMeasurements;
use Obokaman\StockForecast\Infrastructure\Http\StockMeasurement\Collector;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client as TelegramClient;

class Webhook
{
    private $stock_measurement_collector;
    private $get_signals_service;
    private $subscriber_repository;

    public function __construct(
        Collector $a_stock_measurement_collector,
        GetSignalsFromMeasurements $a_get_signals_service,
        SubscriberRepository $a_subscriber_repository
    ) {
        $this->stock_measurement_collector = $a_stock_measurement_collector;
        $this->get_signals_service         = $a_get_signals_service;
        $this->subscriber_repository       = $a_subscriber_repository;
    }

    public function index(string $token): JsonResponse
    {
        if ($token !== $_SERVER['TELEGRAM_BOT_TOKEN']) {
            throw new NotFoundHttpException();
        }

        /** @var TelegramClient|BotApi $bot */
        $bot = new TelegramClient($_SERVER['TELEGRAM_BOT_TOKEN']);

        try {
            Command::configure($bot);
            Callback::configure($bot, $this);

            $bot->run();
        } catch (\Exception $e) {
            return new JsonResponse([
                'error'   => \get_class($e),
                'message' => $e->getMessage()
            ]);
        }

        return new JsonResponse([]);
    }

    public function outputSignalsBasedOn(
        string $interval,
        string $interval_unit,
        string $currency,
        string $stock
    ): string {
        $measurements = $this->stock_measurement_collector->getMeasurements(
            Currency::fromCode($currency),
            Stock::fromCode($stock),
            Interval::fromStringDateInterval($interval_unit)
        );

        $signals_response = $this->get_signals_service->getSignals($measurements);

        $message = 'Based on *last ' . $interval . '* (Score: ' . CalculateScore::calculate(...
                $signals_response) . '):' . PHP_EOL;

        foreach ($signals_response as $signal) {
            $message .= '_ - ' . $signal . '_' . PHP_EOL;
        }

        if (Interval::MINUTES === $interval_unit) {
            $message = 'Last price: ' . $measurements->end()->close() . ' ' . $currency . PHP_EOL . $message;
        }

        return $message . PHP_EOL;
    }

    public function subscriberRepo(): SubscriberRepository
    {
        return $this->subscriber_repository;
    }
}
