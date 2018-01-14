<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client as TelegramClient;
use TelegramBot\Api\Exception as TelegramException;
use TelegramBot\Api\Types\Message as TelegramMessage;

class Webhook
{
    public function telegram(string $token): JsonResponse
    {
        if ($token !== $_SERVER['TELEGRAM_BOT_TOKEN'])
        {
            throw new NotFoundHttpException();
        }

        /** @var TelegramClient|BotApi $bot */
        $bot = new TelegramClient($_SERVER['TELEGRAM_BOT_TOKEN']);

        try
        {
            $bot->command(
                'hello',
                function (TelegramMessage $message) use ($bot) {
                    $welcome_message =
                        'Hey there. Welcome to Crypto Insights bot. You can use bot commands to get some insights, predictions and recommendations for your favorite cryptos.';
                    $bot->sendMessage($message->getChat()->getId(), $welcome_message);
                }
            );

            $bot->run();
        }
        catch (TelegramException $e)
        {
            return new JsonResponse(
                [
                    'error'   => \get_class($e),
                    'message' => $e->getMessage()
                ]
            );
        }

        return new JsonResponse([]);
    }
}
