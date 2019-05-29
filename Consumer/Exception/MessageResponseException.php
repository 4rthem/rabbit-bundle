<?php

declare(strict_types=1);

namespace Arthem\Bundle\RabbitBundle\Consumer\Exception;

use Exception;

class MessageResponseException extends Exception
{
    /**
     * @var int
     */
    private $response;

    public function __construct(int $response)
    {
        parent::__construct();
        $this->response = $response;
    }

    public function getResponse(): int
    {
        return $this->response;
    }
}
