<?php

namespace Arthem\Bundle\RabbitBundle\Consumer\Event;

use Arthem\Bundle\RabbitBundle\Log\LoggableTrait;
use Psr\Log\LoggerAwareInterface;

abstract class AbstractLogHandler implements EventMessageHandlerInterface, LoggerAwareInterface
{
    use LoggableTrait;

    public static function getQueueName(): string
    {
        return 'event';
    }

    public function preHandle(): void
    {
    }

    public function postHandle(): void
    {
    }
}
