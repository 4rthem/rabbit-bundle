<?php

namespace Arthem\Bundle\RabbitBundle\Producer\Adapter;

use Arthem\Bundle\RabbitBundle\Command\DirectConsumerCommand;
use Arthem\Bundle\RabbitBundle\Log\LoggableTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DirectProducerAdapter implements EventProducerAdapterInterface
{
    use LoggableTrait;

    /**
     * @var string
     */
    private $kernelEnvironment;
    /**
     * @var string
     */
    private $workingDir;

    public function __construct(string $kernelEnvironment, string $workingDir)
    {
        $this->kernelEnvironment = $kernelEnvironment;
        $this->workingDir = $workingDir;
    }

    public function publish(string $eventType, string $msgBody, string $routingKey = null, array $additionalProperties = [])
    {
        $process = Process::fromShellCommandline(sprintf(
            './bin/console -vvv --env=%s %s "%s"',
            $this->kernelEnvironment,
            DirectConsumerCommand::COMMAND_NAME,
            $this->escapeMessage($msgBody)
        ));
        $process->setWorkingDirectory($this->workingDir);
        $process->run();
        $this->logger->debug(preg_replace('#\n\r?#', "\n >>> ", sprintf(
            'Command log [%s]: %s',
            $process->getCommandLine(),
            $process->getErrorOutput()
        )));


        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    private function escapeMessage(string $message): string
    {
        return addcslashes($message, '"\\');
    }
}
