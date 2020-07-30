<?php

namespace App\Controller\Telegram\Callback;

use Obokaman\StockForecast\Domain\Model\Financial\Currency;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\Stock;
use Obokaman\StockForecast\Domain\Model\Subscriber\ChatId;
use Obokaman\StockForecast\Domain\Model\Subscriber\SubscriberExistsException;
use Obokaman\StockForecast\Domain\Model\Subscriber\SubscriberRepository;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\CallbackQuery;

class SubscribeRemoveCallback extends BaseCallback
{
    private $subscriber_repository;

    public function __construct(Client $a_telegram_client, SubscriberRepository $a_subscriber_repository)
    {
        parent::__construct($a_telegram_client);
        $this->subscriber_repository = $a_subscriber_repository;
    }

    public function getCallback(): string
    {
        return 'subscribe_remove';
    }

    public function execute(CallbackQuery $a_callback): void
    {
        $callback_data = $this->getCallbackData($a_callback);
        $currency = $callback_data['currency'];
        $crypto = $callback_data['crypto'];

        $chat_id = $a_callback->getMessage()->getChat()->getId();
        $subscriber = $this->subscriber_repository->findByChatId(new ChatId($chat_id));
        if ($subscriber === null) {
            throw new SubscriberExistsException("It doesn't exist any user with chat id {$chat_id}");
        }
        $subscriber->unsubscribeFrom(Currency::fromCode($currency), Stock::fromCode($crypto));
        $this->subscriber_repository->persist($subscriber)->flush();

        $response = "👍 Ok {$subscriber->visibleName()}, now you'll only keep receiving short-term signals of:";
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
