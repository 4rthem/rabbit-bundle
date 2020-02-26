<?php

namespace Arthem\Bundle\RabbitBundle\Consumer;

use Arthem\Bundle\RabbitBundle\Consumer\Exception\ConsumerLoggableException;
use Arthem\Bundle\RabbitBundle\Consumer\Exception\RestartRequiredException;
use Arthem\Bundle\RabbitBundle\Consumer\Exception\RetriableExceptionInterface;
use Arthem\Bundle\RabbitBundle\Log\LoggableTrait;
use Doctrine\ORM\ORMException;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareInterface;
use Throwable;

abstract class LoggerAwareConsumer implements ConsumerInterface, LoggerAwareInterface
{
    use LoggableTrait;

    final public function execute(AMQPMessage $msg)
    {
        try {
            return $this->processMessage($msg);
        } catch (RetriableExceptionInterface $e) {
            if (null !== $e->getSleepTime()) {
                usleep($e->getSleepTime());
            }
            try {
                return $this->processMessage($msg);
            } catch (Throwable $e) {
                return $this->handleException($msg, $e);
            }
        } catch (Throwable $e) {
            return $this->handleException($msg, $e);
        }
    }

    private function handleException(AMQPMessage $msg, Throwable $e): int
    {
        if ($e instanceof ConsumerLoggableException) {
            $this->logger->log($e->getLevel(), $e->getMessage(), $e->getContext());
        }

        if ($e instanceof RestartRequiredException) {
            $this->logger->warning('Consumer restart caught: '.$e->getMessage(), [
                'exception' => $e,
            ]);
            $this->nackAndRequeueMessage($msg);
            exit(2);
        }

        $this->logger->error('Consumer error: '.$e->getMessage(), [
            'exception' => $e,
        ]);

        if ($e instanceof ORMException && 'The EntityManager is closed.' === $e->getMessage()) {
            exit(1);
        }

        return ConsumerInterface::MSG_REJECT;
    }

    private function nackAndRequeueMessage(AMQPMessage $msg): void
    {
        /** @var AMQPChannel $channel */
        $channel = $msg->delivery_info['channel'];
        $channel->basic_nack($msg->delivery_info['delivery_tag'], false, true);
    }

    abstract protected function processMessage(AMQPMessage $message): int;
}
