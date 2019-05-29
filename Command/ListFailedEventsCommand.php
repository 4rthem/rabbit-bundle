<?php

namespace Arthem\Bundle\RabbitBundle\Command;

use Arthem\Bundle\RabbitBundle\Model\FailedEvent;
use Arthem\Bundle\RabbitBundle\Model\FailedEventManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListFailedEventsCommand extends Command
{
    const COMMAND_NAME = 'arthem:rabbit:list-failed';

    protected static $defaultName = self::COMMAND_NAME;

    /**
     * @var FailedEventManager
     */
    private $failedEventManager;

    public function __construct(FailedEventManager $failedEventManager)
    {
        parent::__construct();
        $this->failedEventManager = $failedEventManager;
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
        $failedEvents = $this->failedEventManager
            ->getRepository()
            ->findBy([], [], 100);

        $table = new Table($output);
        $table
            ->setHeaders(['Type', 'Payload'])
            ->setRows(array_map(function (FailedEvent $failedEvent) {
                return [$failedEvent->getType(), json_encode($failedEvent->getPayload())];
            }, $failedEvents))
        ;
        $table->render();
    }
}
