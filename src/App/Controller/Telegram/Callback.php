<?php

namespace App\Controller\Telegram;

use Obokaman\StockForecast\Domain\Model\Date\Interval;
use Obokaman\StockForecast\Domain\Model\Financial\Currency;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\Stock;
use Obokaman\StockForecast\Domain\Model\Subscriber\ChatId;
use Obokaman\StockForecast\Domain\Model\Subscriber\Subscriber;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client as TelegramClient;
use TelegramBot\Api\Exception as TelegramException;
use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

final class Callback
{
    /**
     * @param TelegramClient|BotApi $bot
     * @param Webhook               $webhook
     *
     * @return void
     */
    public static function configure(TelegramClient $bot, Webhook $webhook): void
    {
        $bot->callbackQuery(function (CallbackQuery $callback_query) use ($bot, $webhook) {
            $callback_data = @json_decode($callback_query->getData(), true) ?: ['method' => 'empty'];
            switch ($callback_data['method']) {
                case 'insights_ask_stock':
                    $bot->editMessageText($callback_query->getMessage()->getChat()->getId(),
                        $callback_query->getMessage()->getMessageId(),
                        'Ok, now select the crypto:',
                        null,
                        false,
                        new InlineKeyboardMarkup([
                            [
                                [
                                    'text'          => 'BTC',
                                    'callback_data' => json_encode([
                                        'method'   => 'insights',
                                        'currency' => $callback_data['currency'],
                                        'crypto'   => 'BTC'
                                    ])
                                ],
                                [
                                    'text'          => 'BCH',
                                    'callback_data' => json_encode([
                                        'method'   => 'insights',
                                        'currency' => $callback_data['currency'],
                                        'crypto'   => 'BCH'
                                    ])
                                ],
                                [
                                    'text'          => 'ETH',
                                    'callback_data' => json_encode([
                                        'method'   => 'insights',
                                        'currency' => $callback_data['currency'],
                                        'crypto'   => 'ETH'
                                    ])
                                ]
                            ],
                            [
                                [
                                    'text'          => 'XRP',
                                    'callback_data' => json_encode([
                                        'method'   => 'insights',
                                        'currency' => $callback_data['currency'],
                                        'crypto'   => 'XRP'
                                    ])
                                ],
                                [
                                    'text'          => 'LTC',
                                    'callback_data' => json_encode([
                                        'method'   => 'insights',
                                        'currency' => $callback_data['currency'],
                                        'crypto'   => 'LTC'
                                    ])
                                ],
                            ]
                        ]));
                    break;

                case 'insights':
                    $callback_data = @json_decode($callback_query->getData(), true) ?: ['method' => 'empty'];
                    $currency      = $callback_data['currency'];
                    $crypto        = $callback_data['crypto'];

                    $bot->editMessageText($callback_query->getMessage()->getChat()->getId(),
                        $callback_query->getMessage()->getMessageId(),
                        sprintf('I\'ll give you some insights for *%s-%s*:', $currency, $crypto),
                        'Markdown');

                    try {
                        $signals_message = $webhook->outputSignalsBasedOn('hour', Interval::MINUTES, $currency, $crypto);
                        $signals_message .= $webhook->outputSignalsBasedOn('day', Interval::HOURS, $currency, $crypto);
                        $signals_message .= $webhook->outputSignalsBasedOn('month', Interval::DAYS, $currency, $crypto);

                        $bot->sendMessage($callback_query->getMessage()->getChat()->getId(),
                            $signals_message,
                            'Markdown',
                            false,
                            null,
                            new InlineKeyboardMarkup([
                                [
                                    [
                                        'text' => 'View ' . $currency . '-' . $crypto . ' chart online',
                                        'url'  => 'https://www.cryptocompare.com/coins/' . strtolower($crypto) . '/charts/' . strtolower($currency)
                                    ]
                                ]
                            ]));
                    } catch (TelegramException $e) {
                        throw $e;
                    } catch (\Exception $e) {
                        $bot->sendMessage($callback_query->getMessage()->getChat()->getId(), 'There was an error: ' . $e->getMessage());
                    }

                    break;

                case 'subscribe_add':
                    $bot->editMessageText($callback_query->getMessage()->getChat()->getId(),
                        $callback_query->getMessage()->getMessageId(),
                        'Ok, now select the currency:',
                        null,
                        false,
                        new InlineKeyboardMarkup([
                            [
                                [
                                    'text'          => 'USD',
                                    'callback_data' => json_encode([
                                        'method'   => 'subscribe_ask_stock',
                                        'currency' => 'USD'
                                    ])
                                ],
                                [
                                    'text'          => 'EUR',
                                    'callback_data' => json_encode([
                                        'method'   => 'subscribe_ask_stock',
                                        'currency' => 'EUR'
                                    ])
                                ]
                            ]
                        ]));
                    break;

                case 'subscribe_ask_stock':
                    $bot->editMessageText($callback_query->getMessage()->getChat()->getId(),
                        $callback_query->getMessage()->getMessageId(),
                        'Ok, now select the crypto:',
                        null,
                        false,
                        new InlineKeyboardMarkup([
                            [
                                [
                                    'text'          => 'BTC',
                                    'callback_data' => json_encode([
                                        'method'   => 'subscribe',
                                        'currency' => $callback_data['currency'],
                                        'crypto'   => 'BTC'
                                    ])
                                ],
                                [
                                    'text'          => 'BCH',
                                    'callback_data' => json_encode([
                                        'method'   => 'subscribe',
                                        'currency' => $callback_data['currency'],
                                        'crypto'   => 'BCH'
                                    ])
                                ],
                                [
                                    'text'          => 'ETH',
                                    'callback_data' => json_encode([
                                        'method'   => 'subscribe',
                                        'currency' => $callback_data['currency'],
                                        'crypto'   => 'ETH'
                                    ])
                                ]
                            ],
                            [
                                [
                                    'text'          => 'XRP',
                                    'callback_data' => json_encode([
                                        'method'   => 'subscribe',
                                        'currency' => $callback_data['currency'],
                                        'crypto'   => 'XRP'
                                    ])
                                ],
                                [
                                    'text'          => 'LTC',
                                    'callback_data' => json_encode([
                                        'method'   => 'subscribe',
                                        'currency' => $callback_data['currency'],
                                        'crypto'   => 'LTC'
                                    ])
                                ],
                            ]
                        ]));
                    break;

                case 'subscribe':
                    $callback_data = @json_decode($callback_query->getData(), true) ?: ['method' => 'empty'];
                    $currency      = $callback_data['currency'];
                    $crypto        = $callback_data['crypto'];

                    $message = $callback_query->getMessage();

                    $chat_id    = new ChatId($message->getChat()->getId());
                    $subscriber = $webhook->subscriberRepo()->findByChatId($chat_id);
                    if (null === $subscriber) {
                        $subscriber = Subscriber::create($chat_id,
                            $callback_query->getFrom()->getUsername(),
                            $callback_query->getFrom()->getFirstName(),
                            $callback_query->getFrom()->getLastName(),
                            $callback_query->getFrom()->getLanguageCode()
                        );
                    }

                    $subscriber->subscribeTo(Currency::fromCode($currency), Stock::fromCode($crypto));

                    $webhook->subscriberRepo()->persist($subscriber);
                    $webhook->subscriberRepo()->flush();

                    $response = 'Ok, you\'re now subscribed to short-term signals of:';
                    foreach ($subscriber->subscriptions() as $subscription) {
                        $response .= PHP_EOL . '✅ *' . $subscription->currency() . '-' . $subscription->stock() . '*';
                    }

                    $bot->editMessageText($message->getChat()->getId(),
                        $message->getMessageId(),
                        $response,
                        'Markdown');
                    break;
            }
        });
    }
}
