<?php

namespace Obokaman\StockForecast\Infrastructure\Repository\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Obokaman\StockForecast\Domain\Model\Subscriber\ChatId;
use Obokaman\StockForecast\Domain\Model\Subscriber\Subscriber;
use Obokaman\StockForecast\Domain\Model\Subscriber\SubscriberId;
use Obokaman\StockForecast\Domain\Model\Subscriber\SubscriberRepository as SubscriberRepositoryContract;

class SubscriberRepository implements SubscriberRepositoryContract
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var ObjectRepository */
    private $repo;

    public function __construct(ManagerRegistry $a_manager_registry)
    {
        $this->em   = $a_manager_registry->getManagerForClass(Subscriber::class);
        $this->repo = $this->em->getRepository(Subscriber::class);
    }

    public function find(SubscriberId $a_subscriber_id): ?Subscriber
    {
        $subscriber = $this->repo->find($a_subscriber_id->id());

        if (empty($subscriber)) {
            return null;
        }

        return $subscriber;
    }

    public function findByChatId(ChatId $a_chat_id): ?Subscriber
    {
        $subscriber = $this->repo->findBy(['chat_id' => $a_chat_id->id()]);

        if (empty($subscriber)) {
            return null;
        }

        return $subscriber[0];
    }

    /** @return Subscriber[] */
    public function findAll(): array
    {
        $subscribers = $this->repo->findAll();

        if (empty($subscribers)) {
            return [];
        }

        return $subscribers;
    }

    public function persist(Subscriber $a_subscriber): SubscriberRepositoryContract
    {
        $this->em->persist($a_subscriber);

        return $this;
    }

    public function remove(SubscriberId $a_subscriber_id): SubscriberRepositoryContract
    {
        $subscriber = $this->em->getReference(Subscriber::class, $a_subscriber_id->id());
        $this->em->remove($subscriber);

        return $this;
    }

    public function flush(): void
    {
        $this->em->flush();
    }
}