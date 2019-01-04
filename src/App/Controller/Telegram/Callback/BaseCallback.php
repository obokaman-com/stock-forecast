<?php

namespace App\Controller\Telegram\Callback;

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client as TelegramClient;
use TelegramBot\Api\Types\CallbackQuery;

abstract class BaseCallback
{
    /** @var BotApi|TelegramClient */
    protected $telegram_client;

    public function __construct(TelegramClient $a_telegram_client)
    {
        $this->telegram_client = $a_telegram_client;
    }

    protected function getCallbackData(CallbackQuery $a_callback): array
    {
        return @json_decode($a_callback->getData(), true) ?: ['method' => 'empty'];
    }

    abstract public function getCallback(): string;

    abstract public function execute(CallbackQuery $a_callback): void;
}