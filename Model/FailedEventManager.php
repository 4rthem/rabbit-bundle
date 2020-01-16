<?php

declare(strict_types=1);

namespace Arthem\Bundle\RabbitBundle\Model;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class FailedEventManager
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var string
     */
    private $model;

    public function __construct(EntityManagerInterface $em, string $model)
    {
        $this->em = $em;
        $this->model = $model;
    }

    public function getRepository(): EntityRepository
    {
        return $this->em->getRepository($this->model);
    }

    public function createFailedEvent(string $type, array $payload): FailedEventInterface
    {
        /** @var FailedEventInterface $failedEvent */
        $failedEvent = new $this->model();
        $failedEvent->setType($type);
        $failedEvent->setPayload($payload);

        return $failedEvent;
    }

    /**
     * @return FailedEventInterface[]
     */
    public function iterate(): iterable
    {
        return $this->em->createQueryBuilder()
            ->select('a')
            ->from($this->model, 'a')
            ->getQuery()
            ->iterate();
    }

    public function remove(FailedEventInterface $failedEvent): void
    {
        $this->em->remove($failedEvent);
        $this->em->flush();
    }

    public function flush(): void
    {
        $this->em->createQueryBuilder()
            ->delete()
            ->from($this->model, 'a')
            ->getQuery()
            ->execute();
    }
}
