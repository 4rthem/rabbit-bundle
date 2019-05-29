<?php

namespace Arthem\Bundle\RabbitBundle\Command;

use Arthem\Bundle\RabbitBundle\Consumer\Event\EventMessage;
use Arthem\Bundle\RabbitBundle\Model\FailedEventInterface;
use Arthem\Bundle\RabbitBundle\Model\FailedEventManager;
use Arthem\Bundle\RabbitBundle\Producer\EventProducer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RequeueFailedEventsCommand extends Command
{
    const COMMAND_NAME = 'arthem:rabbit:requeue-failed';

    protected static $defaultName = self::COMMAND_NAME;

    /**
     * @var FailedEventManager
     */
    private $failedEventManager;

    /**
     * @var EventProducer
     */
    private $eventProducer;

    public function __construct(FailedEventManager $failedEventManager, EventProducer $eventProducer)
    {
        parent::__construct();
        $this->failedEventManager = $failedEventManager;
        $this->eventProducer = $eventProducer;
    }

    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDefinition([
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $failedEvents = $this->failedEventManager->iterate();

        foreach ($failedEvents as $failedEvent) {
            /** @var FailedEventInterface $failedEvent */
            $failedEvent = $failedEvent[0];

            $message = new EventMessage($failedEvent->getType(), $failedEvent->getPayload());

            $this->eventProducer->publish($message);

            $this->failedEventManager->remove($failedEvent);
        }
    }
}
