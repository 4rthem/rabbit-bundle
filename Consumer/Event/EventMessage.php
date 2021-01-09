<?php

namespace Arthem\Bundle\RabbitBundle\Consumer\Event;

class EventMessage
{
    private string $type;
    private array $payload;
    private ?string $routingKey;
    private array $properties;
    private ?array $headers;

    public function __construct(
        string $type,
        array $payload,
        ?string $routingKey = null,
        array $properties = [],
        ?array $headers = null
    ) {
        $this->type = $type;
        $this->payload = $payload;
        $this->routingKey = $routingKey;
        $this->properties = $properties;
        $this->headers = $headers;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getRoutingKey(): ?string
    {
        return $this->routingKey;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getHeaders(): ?array
    {
        return $this->headers;
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
