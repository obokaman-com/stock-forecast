<?php

namespace App\Controller\Telegram\Command;

use TelegramBot\Api\Types\Update;

class OpenTextInterpreter
{
    public function answer(Update $an_update): string
    {
        $answer_message = <<<MARKDOWN
🤷‍♂️ Sorry, I didn't understood "{$an_update->getMessage()->getText()}". Currently I only understand a few commands. 🙄

ℹ️ Please, use /help to see available commands.
MARKDOWN;

        return $answer_message;
    }
}