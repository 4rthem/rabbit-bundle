<?php

declare(strict_types=1);

namespace Arthem\Bundle\RabbitBundle\Event;

class PreHandleEvent extends AbstractEvent
{
    const NAME = 'rabbit_handler.pre_handle';
}
