<?php

namespace Arthem\Bundle\RabbitBundle\Producer\Adapter;

use Arthem\Bundle\RabbitBundle\HandlerEvents;
use Arthem\Bundle\RabbitBundle\Log\LoggableTrait;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class DefferedPhpCommandProducerAdapter implements EventProducerAdapterInterface, EventSubscriberInterface
{
    use LoggableTrait;

    private DirectPhpCommandProducerAdapter $producer;
    private array $events = [];

    public function __construct(DirectPhpCommandProducerAdapter $producer)
    {
        $this->producer = $producer;
    }

    public function publish(
        string $eventType,
        string $msgBody,
        string $routingKey = null,
        array $additionalProperties = [],
        ?array $headers = null
    ): void
    {
        $this->events[] = $msgBody;
    }

    public function runEvents(): void
    {
        $processes = [];
        while ($event = array_shift($this->events)) {
            $proc = $this->producer->createProcess($event);
            $proc->start();
            $processes[] = $proc;
        }

        foreach ($processes as $process) {
            $process->wait();
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::TERMINATE => 'runEvents',
            ConsoleEvents::TERMINATE => 'runEvents',
            HandlerEvents::TERMINATE => 'runEvents',
        ];
    }
}
