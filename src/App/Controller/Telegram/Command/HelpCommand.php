<?php

namespace App\Controller\Telegram\Command;

use TelegramBot\Api\Types\Message as TelegramMessage;


class HelpCommand extends BaseCommand
{
    public function getCommand(): string
    {
        return 'help';
    }

    public function getCallable(): callable
    {
        $bot = $this->telegram_client;

        return function (TelegramMessage $a_message) use ($bot) {
            $help_message = <<<MARKDOWN
I can give you some forecast, analysis and insights using historical data and sentiment analysis from several sources.

/insights - Will ask you for a currency / crypto pair to give some insights based on last changes in the valuation.

/subscribe - Allows you to receive updates on relevant signals for your selected currency / crypto pairs.
MARKDOWN;
            $bot->sendMessage($a_message->getChat()->getId(), $help_message, 'Markdown');
        };
    }
}