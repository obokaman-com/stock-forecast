<?php

namespace App\Controller\Webhook\Telegram;

use Obokaman\StockForecast\Application\Service\GetSignalsFromForecast;
use Obokaman\StockForecast\Application\Service\GetSignalsFromForecastRequest;
use Obokaman\StockForecast\Application\Service\PredictStockValue;
use Obokaman\StockForecast\Application\Service\PredictStockValueRequest;
use Obokaman\StockForecast\Domain\Service\Signal\CalculateScore;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client as TelegramClient;

class Telegram
{
    private $stock_predict_service;
    private $get_signals_service;

    public function __construct(PredictStockValue $a_stock_predict_service, GetSignalsFromForecast $a_get_signals_service)
    {
        $this->stock_predict_service = $a_stock_predict_service;
        $this->get_signals_service   = $a_get_signals_service;
    }

    public function index(string $token): JsonResponse
    {
        if ($token !== $_SERVER['TELEGRAM_BOT_TOKEN'])
        {
            throw new NotFoundHttpException();
        }

        /** @var TelegramClient|BotApi $bot */
        $bot = new TelegramClient($_SERVER['TELEGRAM_BOT_TOKEN']);

        try
        {
            Command::configure($bot);
            Callback::configure($bot, $this);

            $bot->run();
        }
        catch (\Exception $e)
        {
            return new JsonResponse(
                [
                    'error'   => \get_class($e),
                    'message' => $e->getMessage()
                ]
            );
        }

        return new JsonResponse([]);
    }

    public function outputSignalsBasedOn(string $interval, string $interval_unit, string $currency, string $stock): string
    {
        $prediction_request  = new PredictStockValueRequest($currency, $stock, $interval_unit);
        $prediction_response = $this->stock_predict_service->predict($prediction_request);

        $signals_request  = new GetSignalsFromForecastRequest(
            $prediction_response->shortTermStats(),
            $prediction_response->mediumTermStats(),
            $prediction_response->longTermStats()
        );
        $signals_response = $this->get_signals_service->getSignals($signals_request);

        $message = 'Based on *last ' . $interval . '* (Score: ' . CalculateScore::calculate(...$signals_response) . '):' . PHP_EOL;

        foreach ($signals_response as $signal)
        {
            $message .= '_ - ' . $signal . '_' . PHP_EOL;
        }

        return $message . PHP_EOL;
    }
}
