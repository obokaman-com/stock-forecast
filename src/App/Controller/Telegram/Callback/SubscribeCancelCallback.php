<?php

namespace App\Controller\Telegram\Callback;

use Obokaman\StockForecast\Domain\Model\Subscriber\ChatId;
use Obokaman\StockForecast\Domain\Model\Subscriber\SubscriberExistsException;
use Obokaman\StockForecast\Domain\Model\Subscriber\SubscriberRepository;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\CallbackQuery;

class SubscribeCancelCallback extends BaseCallback
{
    private $subscriber_repository;

    public function __construct(Client $a_telegram_client, SubscriberRepository $a_subscriber_repository)
    {
        parent::__construct($a_telegram_client);
        $this->subscriber_repository = $a_subscriber_repository;
    }

    public function getCallback(): string
    {
        return 'subscribe_cancel';
    }

    public function execute(CallbackQuery $a_callback): void
    {
        $chat_id = $a_callback->getMessage()->getChat()->getId();

        $subscriber = $this->subscriber_repository->findByChatId(new ChatId($chat_id));
        if ($subscriber === null) {
            throw new SubscriberExistsException("It doesn't exist any user with chat id {$chat_id}");
        }

        $response = "👍 Ok {$subscriber->visibleName()}, you'll keep receiving short-term signals of:";
        foreach ($subscriber->subscriptions() as $subscription) {
            $response .= PHP_EOL . '✅ *' . $subscription->currency() . '-' . $subscription->stock() . '*';
        }

        $this->telegram_client->editMessageText(
            $chat_id,
            $a_callback->getMessage()->getMessageId(),
            $response,
            'Markdown'
        );
    }
}
