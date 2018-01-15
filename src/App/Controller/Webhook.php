<?php

namespace App\Controller;

use Obokaman\StockForecast\Application\Service\GetSignalsFromForecast;
use Obokaman\StockForecast\Application\Service\GetSignalsFromForecastRequest;
use Obokaman\StockForecast\Application\Service\PredictStockValue;
use Obokaman\StockForecast\Application\Service\PredictStockValueRequest;
use Obokaman\StockForecast\Domain\Model\Financial\StockDateInterval;
use Obokaman\StockForecast\Domain\Service\Signal\CalculateScore;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client as TelegramClient;
use TelegramBot\Api\Exception as TelegramException;
use TelegramBot\Api\Types\Message as TelegramMessage;

class Webhook extends Controller
{
    private $stock_predict_service;
    private $get_signals_service;

    public function __construct(PredictStockValue $a_stock_predict_service, GetSignalsFromForecast $a_get_signals_service)
    {
        $this->stock_predict_service = $a_stock_predict_service;
        $this->get_signals_service   = $a_get_signals_service;
    }

    public function telegram(string $token): JsonResponse
    {
        if ($token !== $_SERVER['TELEGRAM_BOT_TOKEN'])
        {
            throw new NotFoundHttpException();
        }

        /** @var TelegramClient|BotApi $bot */
        $bot = new TelegramClient($_SERVER['TELEGRAM_BOT_TOKEN']);

        try
        {
            $webhook = $this;

            $bot->command(
                'start',
                function (TelegramMessage $a_message) use ($bot) {
                    $welcome_message =
                        'Hey there. Welcome to Crypto Insights bot. You can use bot commands to get some insights, predictions and recommendations for your favorite cryptos.';
                    $bot->sendMessage($a_message->getChat()->getId(), $welcome_message);
                }
            );

            $bot->command(
                'insights',
                function (TelegramMessage $message) use ($bot, $webhook) {
                    $params = explode(' ', $message->getText());

                    $currency = strtoupper($params[1] ?? 'EUR');
                    $crypto   = strtoupper($params[2] ?? 'BTC');

                    $bot->sendMessage(
                        $message->getChat()->getId(),
                        sprintf('I\'ll give you some insights for *%s-%s*:', $currency, $crypto),
                        'Markdown'
                    );

                    $signals_message = $webhook->outputSignalsBasedOn('hour', StockDateInterval::MINUTES, $currency, $crypto);
                    $signals_message .= $webhook->outputSignalsBasedOn('day', StockDateInterval::HOURS, $currency, $crypto);
                    $signals_message .= $webhook->outputSignalsBasedOn('month', StockDateInterval::DAYS, $currency, $crypto);

                    $bot->sendMessage(
                        $message->getChat()->getId(),
                        $signals_message,
                        'Markdown'
                    );
                }
            );

            $bot->run();
        }
        catch (TelegramException $e)
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

    private function outputSignalsBasedOn(string $interval, string $interval_unit, string $currency, string $stock): string
    {
        $prediction_request  = new PredictStockValueRequest($currency, $stock, $interval_unit);
        $prediction_response = $this->stock_predict_service->predict($prediction_request);
        $signals_request     = new GetSignalsFromForecastRequest(
            $prediction_response->shortTermStats(),
            $prediction_response->mediumTermStats(),
            $prediction_response->longTermStats()
        );
        $signals_response    = $this->get_signals_service->getSignals($signals_request);

        $message = 'Based on *last ' . $interval . '* (Score: ' . CalculateScore::calculate(...$signals_response) . '):' . PHP_EOL;

        foreach ($signals_response as $signal)
        {
            $message .= '_ - ' . $signal . '_' . PHP_EOL;
        }

        return $message . PHP_EOL;
    }
}
