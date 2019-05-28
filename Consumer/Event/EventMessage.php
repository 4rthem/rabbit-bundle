<?php

namespace Arthem\Bundle\RabbitBundle\Consumer\Event;

class EventMessage
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $payload;

    public function __construct(string $type, array $payload)
    {
        $this->type = $type;
        $this->payload = $payload;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function toJson(): string
    {
        return json_encode([
            't' => $this->type,
            'p' => $this->payload,
        ]);
    }

    public static function fromJson($serialized): self
    {
        $data = json_decode($serialized, true);

        return new self($data['t'], $data['p']);
    }
}
