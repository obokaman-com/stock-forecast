<?php

namespace Obokaman\StockForecast\Domain\Model\Subscriber;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Obokaman\StockForecast\Domain\Model\Financial\Currency;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\Stock;

class Subscriber
{
    private $id;
    private $chat_id;
    private $username;
    private $first_name;
    private $last_name;
    private $language;

    /** @var ArrayCollection|PersistentCollection|Subscription[] */
    private $subscriptions;

    public function __construct(
        SubscriberId $subscriberId,
        ChatId $chat_id,
        ?string $username,
        ?string $first_name,
        ?string $last_name,
        ?string $language,
        array $subscriptions
    ) {
        $this->id            = $subscriberId;
        $this->chat_id       = $chat_id;
        $this->username      = $username;
        $this->first_name    = $first_name;
        $this->last_name     = $last_name;
        $this->language      = $language;
        $this->subscriptions = new ArrayCollection($subscriptions);
    }

    public static function create(ChatId $chat_id, ?string $username, ?string $first_name, ?string $last_name, ?string $language): self
    {
        return new self(SubscriberId::unique(), $chat_id, $username, $first_name, $last_name, $language, []);
    }

    public function subscriberId(): SubscriberId
    {
        return $this->id;
    }

    public function chatId(): ChatId
    {
        return $this->chat_id;
    }

    public function username(): ?string
    {
        return $this->username;
    }

    public function firstName(): ?string
    {
        return $this->first_name;
    }

    public function visibleName(): string
    {
        return $this->first_name ?? $this->username ?? 'Anonymous';
    }

    public function lastName(): ?string
    {
        return $this->last_name;
    }

    public function language(): ?string
    {
        return $this->language;
    }

    /** @return Subscription[] */
    public function subscriptions(): array
    {
        return $this->subscriptions->toArray();
    }

    public function subscribeTo(Currency $a_currency, Stock $a_stock): void
    {
        if ($this->subscriptionExists($a_currency, $a_stock)) {
            return;
        }
        $subscription = new Subscription($this, $a_currency, $a_stock);
        $this->subscriptions->add($subscription);
    }

    public function unsubscribeFrom(Currency $a_currency, Stock $a_stock): void
    {
        if (false === $this->subscriptionExists($a_currency, $a_stock)) {
            return;
        }

        foreach ($this->subscriptions as $subscription) {
            if ($subscription->currency()->equals($a_currency) && $subscription->stock()->equals($a_stock)) {
                $this->subscriptions->removeElement($subscription);
            }
        }
    }

    private function subscriptionExists(Currency $a_currency, Stock $a_stock): bool
    {
        foreach ($this->subscriptions as $subscription) {
            if ($a_currency->equals($subscription->currency()) && $a_stock->equals($subscription->stock())) {
                return true;
            }
        }

        return false;
    }
}