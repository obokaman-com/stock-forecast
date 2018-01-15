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
use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
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
            $this->assignCommands($bot);
            $this->assignCallbacks($bot);

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

    /**
     * @param TelegramClient|BotApi $bot
     *
     * @return void
     */
    private function assignCommands(TelegramClient $bot): void
    {
        $bot->command(
            'start',
            function (TelegramMessage $a_message) use ($bot) {
                $welcome_message = <<<MARKDOWN
Hey there. Welcome to Crypto Insights bot. You can use bot commands to get some insights, predictions and recommendations for your favorite cryptos.
MARKDOWN;
                $bot->sendMessage($a_message->getChat()->getId(), $welcome_message, 'Markdown');
            }
        );

        $bot->command(
            'help',
            function (TelegramMessage $a_message) use ($bot) {
                $help_message = <<<MARKDOWN
I can give you some forecast, analysis and insights using historical data and sentiment analysis from several sources.

*Insights*

/insights - Will ask you for a currency / crypto pair to give some insights based on last changes in the valuation.
MARKDOWN;
                $bot->sendMessage($a_message->getChat()->getId(), $help_message, 'Markdown');
            }
        );

        $webhook = $this;
        $bot->command(
            'insights',
            function (TelegramMessage $message) use ($bot, $webhook) {
                $chat_id = $message->getChat()->getId();

                $bot->sendMessage(
                    $chat_id,
                    'Ok, select base currency.',
                    null,
                    false,
                    null,
                    new InlineKeyboardMarkup(
                        [
                            [
                                ['text' => 'USD', 'callback_data' => json_encode(['method' => 'insights_ask_stock', 'currency' => 'USD'])],
                                ['text' => 'EUR', 'callback_data' => json_encode(['method' => 'insights_ask_stock', 'currency' => 'EUR'])]
                            ]
                        ]
                    )
                );
            }
        );
    }

    /**
     * @param TelegramClient|BotApi $bot
     *
     * @return void
     */
    private function assignCallbacks(TelegramClient $bot): void
    {
        $webhook = $this;
        $bot->callbackQuery(
            function (CallbackQuery $callback_query) use ($bot, $webhook) {
                $callback_data = @json_decode($callback_query->getData(), true) ?: ['method' => 'empty'];
                switch ($callback_data['method'])
                {
                    case 'insights_ask_stock':
                        $bot->editMessageText(
                            $callback_query->getMessage()->getChat()->getId(),
                            $callback_query->getMessage()->getMessageId(),
                            'Ok, now select the crypto:',
                            null,
                            false,
                            new InlineKeyboardMarkup(
                                [
                                    [
                                        [
                                            'text'          => 'BTC',
                                            'callback_data' => json_encode(
                                                ['method' => 'insights', 'currency' => $callback_data['currency'], 'crypto' => 'BTC']
                                            )
                                        ],
                                        [
                                            'text'          => 'ETH',
                                            'callback_data' => json_encode(
                                                ['method' => 'insights', 'currency' => $callback_data['currency'], 'crypto' => 'ETH']
                                            )
                                        ]
                                    ],
                                    [
                                        [
                                            'text'          => 'XRP',
                                            'callback_data' => json_encode(
                                                ['method' => 'insights', 'currency' => $callback_data['currency'], 'crypto' => 'XRP']
                                            )
                                        ],
                                        [
                                            'text'          => 'LTC',
                                            'callback_data' => json_encode(
                                                ['method' => 'insights', 'currency' => $callback_data['currency'], 'crypto' => 'LTC']
                                            )
                                        ],
                                    ]
                                ]
                            )
                        );
                        break;

                    case 'insights':
                        $callback_data = @json_decode($callback_query->getData(), true) ?: ['method' => 'empty'];
                        $currency      = $callback_data['currency'];
                        $crypto        = $callback_data['crypto'];

                        $bot->editMessageText(
                            $callback_query->getMessage()->getChat()->getId(),
                            $callback_query->getMessage()->getMessageId(),
                            sprintf('I\'ll give you some insights for *%s-%s*:', $currency, $crypto),
                            'Markdown'
                        );

                        try
                        {
                            $signals_message = $webhook->outputSignalsBasedOn('hour', StockDateInterval::MINUTES, $currency, $crypto);
                            $signals_message .= $webhook->outputSignalsBasedOn('day', StockDateInterval::HOURS, $currency, $crypto);
                            $signals_message .= $webhook->outputSignalsBasedOn('month', StockDateInterval::DAYS, $currency, $crypto);

                            $bot->sendMessage(
                                $callback_query->getMessage()->getChat()->getId(),
                                $signals_message,
                                'Markdown'
                            );
                        }
                        catch (TelegramException $e)
                        {
                            throw $e;
                        }
                        catch (\Exception $e)
                        {
                            $bot->sendMessage(
                                $callback_query->getMessage()->getChat()->getId(),
                                'There was an error: ' . $e->getMessage()
                            );
                        }

                        break;
                }
            }
        );
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
