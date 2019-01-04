<?php

namespace App\Controller\Telegram\Command;

use TelegramBot\Api\Types\Message as TelegramMessage;

class StartCommand extends BaseCommand
{
    public function getCommand(): string
    {
        return 'start';
    }

    public function getCallable(): callable
    {
        $telegram_client = $this->telegram_client;

        $function = function (TelegramMessage $a_message) use ($telegram_client) {
            $welcome_message = <<<MARKDOWN
👋 Hey there. Welcome to Crypto Insights bot. You can use bot commands to get some insights, predictions and recommendations for your favorite cryptos, and subscribe to receive relevant alerts in real time. 

ℹ️ Use /help to see available commands.
MARKDOWN;
            $telegram_client->sendMessage($a_message->getChat()->getId(), $welcome_message, 'Markdown');
        };

        return $function;
    }
}