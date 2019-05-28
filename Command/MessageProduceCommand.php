<?php

namespace Arthem\Bundle\RabbitBundle\Command;

use Arthem\Bundle\RabbitBundle\Consumer\Event\EventMessage;
use Arthem\Bundle\RabbitBundle\Consumer\EventConsumer;
use Arthem\Bundle\RabbitBundle\Producer\EventProducer;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MessageProduceCommand extends Command
{
    const COMMAND_NAME = 'arthem:rabbit:produce';

    protected static $defaultName = self::COMMAND_NAME;

    /**
     * @var EventProducer
     */
    private $eventProducer;

    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDefinition([
                new InputArgument('type', InputArgument::REQUIRED, 'The message type'),
                new InputArgument('message', InputArgument::REQUIRED, 'The message payload'),
            ]);
    }

    public function __construct(EventProducer $eventProducer)
    {
        parent::__construct();
        $this->eventProducer = $eventProducer;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type');
        $message = $input->getArgument('message');

        $eventMessage = new EventMessage($type, json_decode($message, true));

        $this->eventProducer->publish($eventMessage);
    }
}
