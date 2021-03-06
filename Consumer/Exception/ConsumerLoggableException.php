<?php

namespace Arthem\Bundle\RabbitBundle\Consumer\Exception;

use Exception;
use Throwable;

class ConsumerLoggableException extends Exception
{
    /**
     * @var array
     */
    private $context;

    /**
     * @var string
     */
    private $level;

    public function __construct(string $level, $message = '', array $context = [], $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
        $this->level = $level;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getLevel(): string
    {
        return $this->level;
    }
}
