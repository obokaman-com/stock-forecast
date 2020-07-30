<?php

namespace App\Controller\Telegram\Command;

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\Message as TelegramMessage;

class SubscribeCommand extends BaseCommand
{
    public function getCommand(): string
    {
        return 'subscribe';
    }

    public function getCallable(): callable
    {
        $bot = $this->telegram_client;

        return function (TelegramMessage $message) use ($bot) {
            $chat_id = $message->getChat()->getId();

            $bot->sendMessage(
                $chat_id,
                'Ok, choose an option:',
                null,
                false,
                null,
                new InlineKeyboardMarkup(
                    [
                        [
                            [
                                'text' => 'Add subscription ▶︎',
                                'callback_data' => json_encode(
                                    [
                                        'method' => 'subscribe_add'
                                    ]
                                )
                            ]
                        ],
                        [
                            [
                                'text' => 'Manage subscriptions ▶︎',
                                'callback_data' => json_encode(
                                    [
                                        'method' => 'subscribe_manage'
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
