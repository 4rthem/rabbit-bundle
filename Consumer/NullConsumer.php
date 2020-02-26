<?php

namespace Arthem\Bundle\RabbitBundle\Consumer;

use Exception;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class NullConsumer implements ConsumerInterface
{
    public function execute(AMQPMessage $msg)
    {
        throw new Exception('Consumer should never be run');
    }
}
