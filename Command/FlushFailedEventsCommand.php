<?php

namespace Arthem\Bundle\RabbitBundle\Command;

use Arthem\Bundle\RabbitBundle\Model\FailedEventManager;
use Arthem\Bundle\RabbitBundle\Producer\EventProducer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class FlushFailedEventsCommand extends Command
{
    const COMMAND_NAME = 'arthem:rabbit:flush-failed';

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
            ->addOption('no-confirmation', null, InputOption::VALUE_NONE, 'Whether it must be confirmed before flushing');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $noConfirmation = (bool) $input->getOption('no-confirmation');

        if (!$noConfirmation && $input->isInteractive()) {
            $question = new ConfirmationQuestion(
                '<question>Are you sure you wish to flush all failed events? (y/N)</question>',
                false
            );

            if (!$this->getHelper('question')->ask($input, $output, $question)) {
                $output->writeln('<error>Flush cancelled!</error>');

                return 1;
            }
        }

        $this->failedEventManager->flush();
        $output->writeln('Flushed.');
    }
}
