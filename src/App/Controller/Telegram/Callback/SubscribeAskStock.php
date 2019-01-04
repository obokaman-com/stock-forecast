<?php

namespace App\Controller\Telegram\Callback;


use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class SubscribeAskStock extends BaseCallback
{
    public function getCallback(): string
    {
        return 'subscribe_ask_stock';
    }

    public function execute(CallbackQuery $a_callback): void
    {
        $callback_data = $this->getCallbackData($a_callback);

        $this->telegram_client->editMessageText(
            $a_callback->getMessage()->getChat()->getId(),
            $a_callback->getMessage()->getMessageId(),
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
    }

}