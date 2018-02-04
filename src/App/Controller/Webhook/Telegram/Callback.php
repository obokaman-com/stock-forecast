<?php

namespace App\Controller\Webhook\Telegram;

use Obokaman\StockForecast\Domain\Model\Date\Interval;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client as TelegramClient;
use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Exception as TelegramException;

final class Callback
{
    /**
     * @param TelegramClient|BotApi $bot
     * @param Telegram              $webhook
     *
     * @return void
     */
    public static function configure(TelegramClient $bot, Telegram $webhook): void
    {
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
                            $signals_message = $webhook->outputSignalsBasedOn('hour', Interval::MINUTES, $currency, $crypto);
                            $signals_message .= $webhook->outputSignalsBasedOn('day', Interval::HOURS, $currency, $crypto);
                            $signals_message .= $webhook->outputSignalsBasedOn('month', Interval::DAYS, $currency, $crypto);

                            $bot->sendMessage(
                                $callback_query->getMessage()->getChat()->getId(),
                                $signals_message,
                                'Markdown',
                                false,
                                null,
                                new InlineKeyboardMarkup(
                                    [
                                        [
                                            [
                                                'text' => 'View ' . $currency . '-' . $crypto . ' chart online',
                                                'url'  => 'https://www.cryptocompare.com/coins/' . strtolower($crypto) . '/charts/' . strtolower($currency)
                                            ]
                                        ]
                                    ]
                                )
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
}
