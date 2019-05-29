
# ArthemRabbitBundle

A simple and faster setup to work with consumer through handlers.

## Installation

```bash
composer require arthem/rabbit-bundle
```

Add auto-tag feature:

```yaml
# config/services.yaml

services:
    # ...
    _instanceof:
        # ...
        Arthem\Bundle\RabbitBundle\Consumer\Event\EventMessageHandlerInterface:
            tags: ['arthem_rabbit.event_handler']
```

## Handlers

Every message are based on type which allow to define their corresponding handler.
A handler can support multiple types.
Handlers and types can be split into queues

By default, this bundle configures:
- one queue named `event`
- its direct exchange named `x-event`
- the corresponding consumer named `event`

In opposite to RabbitMQBundle, this one provides only one message producer.
The message type allows to send message to the appropriate exchange.

## Failure

Failed message processing can be logged in a database table.

First enable the feature:

```yaml
# config/packages/arthem_rabbit.yaml

arthem_rabbit:
    failure: ~
```

Create your own Entity which must implement `Arthem\Bundle\RabbitBundle\Model\FailedEventInterface`:

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Arthem\Bundle\RabbitBundle\Model\FailedEvent as BaseFailedEvent;

/**
 * @ORM\Entity
 */
class FailedEvent extends BaseFailedEvent
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    public function getId(): string
    {
        return (string)$this->id;
    }
}
```
