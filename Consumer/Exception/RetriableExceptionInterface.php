<?php

declare(strict_types=1);

namespace Arthem\Bundle\RabbitBundle\Consumer\Exception;

interface RetriableExceptionInterface
{
    /**
     * In microseconds. NULL for no sleep.
     */
    public function getSleepTime(): ?int;
}
