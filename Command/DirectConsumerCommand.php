<?php

namespace Arthem\Bundle\RabbitBundle\Command;

use Arthem\Bundle\RabbitBundle\Consumer\EventConsumer;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DirectConsumerCommand extends Command
{
    const COMMAND_NAME = 'arthem:rabbit:direct-consumer';

    protected static $defaultName = self::COMMAND_NAME;

    /**
     * @var EventConsumer
     */
    private $eventConsumer;

    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDefinition([
                new InputArgument('message', InputArgument::REQUIRED, 'The message payload'),
            ]);
    }

    public function __construct(EventConsumer $eventProducer)
    {
        parent::__construct();
        $this->eventConsumer = $eventProducer;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $input->getArgument('message');

        $additionalProperties = [];
        $message = new AMQPMessage($message, $additionalProperties);
        $this->eventConsumer->processMessage($message);
    }
}
