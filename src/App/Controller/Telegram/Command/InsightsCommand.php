<?php

namespace App\Controller\Telegram\Command;

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\Message as TelegramMessage;

class InsightsCommand extends BaseCommand
{
    public function getCommand(): string
    {
        return 'insights';
    }

    public function getCallable(): callable
    {
        $bot = $this->telegram_client;

        return function (TelegramMessage $message) use ($bot) {
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
                            [
                                'text' => 'USD',
                                'callback_data' => json_encode(
                                    [
                                        'method' => 'insights_ask_stock',
                                        'currency' => 'USD'
                                    ]
                                )
                            ],
                            [
                                'text' => 'EUR',
                                'callback_data' => json_encode(
                                    [
                                        'method' => 'insights_ask_stock',
                                        'currency' => 'EUR'
                                    ]
                                )
                            ]
                        ]
                    ]
                )
            );
        };
    }
}
