<?php

namespace Arthem\Bundle\RabbitBundle\Consumer\Event;

use Arthem\Bundle\RabbitBundle\Consumer\Exception\RestartRequiredException;
use Arthem\Bundle\RabbitBundle\Log\LoggableTrait;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;

abstract class AbstractEntityManagerHandler extends AbstractLogHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @required
     */
    public function setEntityManager(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }

    public function preHandle(): void
    {
        $this->pingOrRestart();
        $this->em->clear();
    }

    public function postHandle(): void
    {
        $i = 0;
        // Rollback all unclosed transactions
        while ($this->em->getConnection()->isTransactionActive() && $i++ < 5) {
            $this->logger->critical(sprintf('Unterminated transaction in handler %s', get_class($this)));
            $this->em->rollback();
        }
    }

    private function pingOrRestart()
    {
        $connection = $this->em->getConnection();
        if ($connection->ping() === false) {
            $this->logger->info('Lost connection, restarting...');
            throw new RestartRequiredException();
        }

        if (!$this->em->isOpen()) {
            $this->logger->error('EM is not open, restarting...');
            throw new RestartRequiredException();
        }
    }
}
