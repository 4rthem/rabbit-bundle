<?php

declare(strict_types=1);

namespace Arthem\Bundle\RabbitBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass()
 */
abstract class FailedEvent implements FailedEventInterface
{
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150)
     */
    protected $type;

    /**
     * @var array
     *
     * @ORM\Column(type="json")
     */
    protected $payload;

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }
}
