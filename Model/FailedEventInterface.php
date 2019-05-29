<?php

declare(strict_types=1);

namespace Arthem\Bundle\RabbitBundle\Model;

interface FailedEventInterface
{
    public function getId(): string;

    public function setType(string $type): void;

    public function setPayload(array $payload): void;

    public function getType(): string;

    public function getPayload(): array;
}
