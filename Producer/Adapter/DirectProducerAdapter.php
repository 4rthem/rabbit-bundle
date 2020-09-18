<?php

namespace Arthem\Bundle\RabbitBundle\Producer\Adapter;

use App\Kernel;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Arthem\Bundle\RabbitBundle\Command\DirectConsumerCommand;
use Arthem\Bundle\RabbitBundle\Log\LoggableTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

class DirectProducerAdapter implements EventProducerAdapterInterface
{
    use LoggableTrait;

    private string $kernelEnvironment;
    private string $workingDir;

    public function __construct(string $kernelEnvironment, string $workingDir)
    {
        $this->kernelEnvironment = $kernelEnvironment;
        $this->workingDir = $workingDir;
    }

    public function publish(string $eventType, string $msgBody, string $routingKey = null, array $additionalProperties = []): void
    {
        /** @var BaseKernel $kernel */
        $kernel = new Kernel($this->kernelEnvironment, true);
        $kernel->boot();
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => DirectConsumerCommand::COMMAND_NAME,
            'message' => $msgBody,
        ]);

        $output = new NullOutput();
        $application->run($input, $output);

        $kernel->shutdown();
    }
}
