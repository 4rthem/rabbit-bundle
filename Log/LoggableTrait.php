<?php

namespace Arthem\Bundle\RabbitBundle\Log;

use Psr\Log\LoggerInterface;

trait LoggableTrait
{
    /**
     * The logger instance.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     *
     * @required
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
