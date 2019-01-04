<?php

namespace App\Controller\Telegram;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TelegramBot\Api\Client;

class Webhook
{
    private $telegram_client;
    private $telegram_commands;
    private $telegram_callbacks;

    public function __construct(
        Client $a_telegram_client,
        Command $some_telegram_commands,
        Callback $some_telegram_callbacks
    ) {
        $this->telegram_client    = $a_telegram_client;
        $this->telegram_commands  = $some_telegram_commands;
        $this->telegram_callbacks = $some_telegram_callbacks;
    }

    public function index(string $token): JsonResponse
    {
        if ($token !== $_SERVER['TELEGRAM_BOT_TOKEN']) {
            throw new NotFoundHttpException();
        }

        $this->logPostRequest();

        try {
            $this->telegram_commands->configure();
            $this->telegram_callbacks->configure();
            $this->telegram_client->run();
        } catch (\Exception $e) {
            return new JsonResponse([
                'error'   => \get_class($e),
                'message' => $e->getMessage()
            ]);
        }

        return new JsonResponse([]);
    }

    private function logPostRequest(): void
    {
        @file_put_contents(
            __DIR__ . '/../../../../var/log/telegram_payload.json',
            date('==Y-m-d H:i:s==') . PHP_EOL . @file_get_contents('php://input') . PHP_EOL . PHP_EOL,
            FILE_APPEND
        );
    }
}
