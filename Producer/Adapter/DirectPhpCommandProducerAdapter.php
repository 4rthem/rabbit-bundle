<?php

namespace Arthem\Bundle\RabbitBundle\Producer\Adapter;

use Arthem\Bundle\RabbitBundle\Command\DirectConsumerCommand;
use Arthem\Bundle\RabbitBundle\Log\LoggableTrait;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DirectPhpCommandProducerAdapter implements EventProducerAdapterInterface
{
    use LoggableTrait;

    private string $kernelEnvironment;
    private string $workingDir;

    public function __construct(string $kernelEnvironment, string $workingDir)
    {
        $this->kernelEnvironment = $kernelEnvironment;
        $this->workingDir = $workingDir;
    }

    public function publish(
        string $eventType,
        string $msgBody,
        string $routingKey = null,
        array $additionalProperties = [],
        ?array $headers = null
    ): void
    {
        $process = $this->createProcess($msgBody);

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

    public function createProcess(string $msgBody): Process
    {
        $process = new Process([
            './bin/console',
            '-vvv',
            '--env='.$this->kernelEnvironment,
            DirectConsumerCommand::COMMAND_NAME,
            $msgBody,
        ]);

        $process->setWorkingDirectory($this->workingDir);

        return $process;
    }

    private function escapeMessage(string $message): string
    {
        return addcslashes($message, '"\\');
    }
}
