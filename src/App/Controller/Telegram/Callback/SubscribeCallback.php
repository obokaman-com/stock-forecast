<?php

namespace App\Controller\Telegram\Callback;


use Obokaman\StockForecast\Domain\Model\Financial\Currency;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\Stock;
use Obokaman\StockForecast\Domain\Model\Subscriber\ChatId;
use Obokaman\StockForecast\Domain\Model\Subscriber\Subscriber;
use Obokaman\StockForecast\Domain\Model\Subscriber\SubscriberRepository;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\CallbackQuery;

class SubscribeCallback extends BaseCallback
{
    private $subscriber_repository;

    public function __construct(Client $a_telegram_client, SubscriberRepository $a_subscriber_repository)
    {
        parent::__construct($a_telegram_client);
        $this->subscriber_repository = $a_subscriber_repository;
    }

    public function getCallback(): string
    {
        return 'subscribe';
    }

    public function execute(CallbackQuery $a_callback): void
    {
        $callback_data = $this->getCallbackData($a_callback);
        $currency      = $callback_data['currency'];
        $crypto        = $callback_data['crypto'];

        $message = $a_callback->getMessage();

        $chat_id    = new ChatId($message->getChat()->getId());
        $subscriber = $this->subscriber_repository->findByChatId($chat_id);
        if (null === $subscriber) {
            $subscriber = Subscriber::create($chat_id,
                $a_callback->getFrom()->getUsername(),
                $a_callback->getFrom()->getFirstName(),
                $a_callback->getFrom()->getLastName(),
                $a_callback->getFrom()->getLanguageCode()
            );
        }

        $subscriber->subscribeTo(Currency::fromCode($currency), Stock::fromCode($crypto));

        $this->subscriber_repository->persist($subscriber)->flush();

        $response = "👍 Ok {$subscriber->visibleName()}, you're now subscribed to short-term signals of:";
        foreach ($subscriber->subscriptions() as $subscription) {
            $response .= PHP_EOL . '✅ *' . $subscription->currency() . '-' . $subscription->stock() . '*';
        }

        $this->telegram_client->editMessageText($message->getChat()->getId(),
            $message->getMessageId(),
            $response,
            'Markdown');
    }

}