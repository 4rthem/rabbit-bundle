<?php

declare(strict_types=1);

namespace Arthem\Bundle\RabbitBundle\Model;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class FailedEventManager
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var string
     */
    private $model;

    public function __construct(ObjectManager $om, string $model)
    {
        $this->om = $om;
        $this->model = $model;
    }

    public function getRepository(): ObjectRepository
    {
        return $this->om->getRepository($this->model);
    }

    public function createFailedEvent(string $type, array $payload): FailedEventInterface
    {
        /** @var FailedEventInterface $failedEvent */
        $failedEvent = new $this->model;
        $failedEvent->setType($type);
        $failedEvent->setPayload($payload);

        return $failedEvent;
    }

    /**
     * @return FailedEventInterface[]
     */
    public function iterate(): iterable
    {
        if ($this->om instanceof EntityManagerInterface) {
            return $this->om->createQueryBuilder()
                ->select('a')
                ->from($this->model, 'a')
                ->getQuery()
                ->iterate();
        }

        return $this->getRepository()->findAll();
    }

    public function remove(FailedEventInterface $failedEvent): void
    {
        $this->om->remove($failedEvent);
        $this->om->flush();
    }

    public function flush(): void
    {
        if ($this->om instanceof EntityManagerInterface) {
            $this->om->createQueryBuilder()
                ->delete()
                ->from($this->model, 'a')
                ->getQuery()
                ->execute();

            return;
        }

        throw new Exception(sprintf('Unsupported Object Manager "%s" for flush', get_class($this->om)));
    }
}
