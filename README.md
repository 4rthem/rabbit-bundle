
# ArthemRabbitBundle

A simple and faster setup to work with consumer through handlers.

## Installation

```bash
composer require arthem/rabbit-bundle
```

Add auto-tag feature:

```yaml
# config/services.yml

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
