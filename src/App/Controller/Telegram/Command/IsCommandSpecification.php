<?php

namespace App\Controller\Telegram\Command;

use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Update;

class IsCommandSpecification
{
    public function isSatisfiedBy(Update $an_update): bool
    {
        $received_message = $an_update->getMessage();

        $is_not_a_message = (null === $received_message || !$received_message instanceof Message);
        if ($is_not_a_message) {
            return false;
        }

        $does_it_seems_a_command = preg_match('/^\//', $an_update->getMessage()->getText());

        return !$does_it_seems_a_command;
    }
}