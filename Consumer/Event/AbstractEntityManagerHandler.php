<?php

namespace Arthem\Bundle\RabbitBundle\Consumer\Event;

use Arthem\Bundle\RabbitBundle\Consumer\Exception\RestartRequiredException;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractEntityManagerHandler extends AbstractLogHandler
{
    private EntityManagerInterface $em;

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
        parent::preHandle();

        $this->pingOrRestart();
        $this->em->clear();
    }

    public function postHandle(): void
    {
        parent::postHandle();

        $i = 0;
        // Rollback all unclosed transactions
        while ($this->em->getConnection()->isTransactionActive() && $i < 5) {
            $this->logger->critical(sprintf(
                'Unterminated transaction in handler %s at level %d',
                get_class($this),
                $i
            ));
            $this->em->rollback();
            ++$i;
        }
    }

    private function pingOrRestart()
    {
        $connection = $this->em->getConnection();
        if (false === $connection->ping()) {
            $this->logger->info('Lost connection, restarting...');
            throw new RestartRequiredException();
        }

        if (!$this->em->isOpen()) {
            $this->logger->error('EM is not open, restarting...');
            throw new RestartRequiredException();
        }
    }
}
