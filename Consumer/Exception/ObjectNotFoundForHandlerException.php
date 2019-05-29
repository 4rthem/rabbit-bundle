<?php

declare(strict_types=1);

namespace Arthem\Bundle\RabbitBundle\Consumer\Exception;

use RuntimeException;
use Throwable;

class ObjectNotFoundForHandlerException extends RuntimeException
{
    public function __construct(string $class, $id, string $action, $code = 0, Throwable $previous = null)
    {
        if (is_array($id)) {
            $id = implode('-', $id);
        }

        parent::__construct(sprintf('%s #%s not found for: %s', $class, $id, $action), $code, $previous);
    }
}
