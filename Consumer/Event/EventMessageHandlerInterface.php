<?php

namespace Arthem\Bundle\RabbitBundle\Consumer\Event;

interface EventMessageHandlerInterface
{
    public static function getHandledEvents(): array;

    public static function getQueueName(): string;

    public function preHandle(): void;

    public function postHandle(): void;

    public function handle(EventMessage $message): void;
}
