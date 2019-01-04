<?php

namespace App\Controller\Telegram\Command;

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client as TelegramClient;

abstract class BaseCommand
{
    protected $telegram_client;

    /**
     * TelegramCommand constructor.
     *
     * @param TelegramClient|BotApi $a_telegram_client
     */
    public function __construct(TelegramClient $a_telegram_client)
    {
        $this->telegram_client = $a_telegram_client;
    }

    abstract public function getCommand(): string;

    abstract public function getCallable(): callable;
}