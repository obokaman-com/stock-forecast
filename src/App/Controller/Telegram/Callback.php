<?php

namespace App\Controller\Telegram;

use App\Controller\Telegram\Callback\BaseCallback;
use InvalidArgumentException;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\CallbackQuery;

final class Callback
{
    private $telegram_client;
    private $callbacks;

    public function __construct(Client $a_telegram_client, BaseCallback ...$some_callbacks)
    {
        $this->telegram_client = $a_telegram_client;
        $this->callbacks = $some_callbacks;
    }

    public function configure(): void
    {
        $callbacks = $this->callbacks;

        $this->telegram_client->callbackQuery(
            function (CallbackQuery $callback_query) use ($callbacks) {
                $callback_data = @json_decode($callback_query->getData(), true) ?: ['method' => 'empty'];

                foreach ($callbacks as $callback) {
                    if ($callback_data['method'] === $callback->getCallback()) {
                        $callback->execute($callback_query);
                        return;
                    }
                }

                throw new InvalidArgumentException('Unknown callback: ' . $callback_data['method']);
            }
        );
    }
}
