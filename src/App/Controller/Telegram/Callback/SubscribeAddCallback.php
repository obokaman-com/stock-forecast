<?php

namespace App\Controller\Telegram\Callback;


use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class SubscribeAddCallback extends BaseCallback
{
    public function getCallback(): string
    {
        return 'subscribe_add';
    }

    public function execute(CallbackQuery $a_callback): void
    {
        $this->telegram_client->editMessageText(
            $a_callback->getMessage()->getChat()->getId(),
            $a_callback->getMessage()->getMessageId(),
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
    }
}