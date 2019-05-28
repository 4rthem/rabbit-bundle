<?php

namespace Arthem\Bundle\RabbitBundle\DependencyInjection;

use Arthem\Bundle\RabbitBundle\Consumer\EventConsumer;
use Arthem\Bundle\RabbitBundle\Consumer\FailedEventConsumer;
use Arthem\Bundle\RabbitBundle\Consumer\NullConsumer;
use Arthem\Bundle\RabbitBundle\Producer\Adapter\AMQPProducerAdapter;
use Arthem\Bundle\RabbitBundle\Producer\Adapter\DirectProducerAdapter;
use Arthem\Bundle\RabbitBundle\Producer\Adapter\EventProducerAdapterInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use LogicException;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ArthemRabbitExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        if ($config['direct']) {
            $container->setAlias(EventProducerAdapterInterface::class, DirectProducerAdapter::class);
        } else {
            $container->setAlias(EventProducerAdapterInterface::class, AMQPProducerAdapter::class);
        }

        if ($config['failure']['enabled']) {
            $definition = new Definition(FailedEventConsumer::class);
            $definition->setAutowired(true);
            $definition->setPublic(true);
            $definition->setArgument('$model', $config['failure']['model']);
            $definition->addTag('monolog.logger', [
                'channel' => 'failed_event',
            ]);
            $container->setDefinition($definition->getClass(), $definition);
        }
    }

    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

        $bundles = $container->getParameter('kernel.bundles');
        $bundle = 'OldSoundRabbitMqBundle';
        if (!isset($bundles[$bundle])) {
            throw new LogicException(sprintf('You must enable %s', $bundle));
        }

        $oldSoundConfig = [
            'connections' => [
                $config['default_connection_name'] => [
                    'host' => '%env(RABBITMQ_HOST)%',
                    'port' => '%env(RABBITMQ_PORT)%',
                    'user' => '%env(RABBITMQ_USER)%',
                    'password' => '%env(RABBITMQ_PASSWORD)%',
                    'vhost' => '/',
                    'lazy' => true,
                    'connection_timeout' => 3,
                    'read_write_timeout' => 3,
                    'keepalive' => true,
                    'heartbeat' => 0,
                ]
            ],
            'producers' => $this->getProducers($config),
            'consumers' => $this->getConsumers($config),
        ];

        $container->prependExtensionConfig('old_sound_rabbit_mq', $oldSoundConfig);
    }

    private function getConsumers(array $config): array
    {
        $defaultConnection = $config['default_connection_name'];

        $consumers = [];
        $defaultQueuesOptions = [];

        if ($config['failure']['enabled']) {
            $consumers['failed_events'] = [
                'connection' => $defaultConnection,
                'exchange_options' => [
                    'name' => 'x-failed-events',
                    'type' => 'direct',
                ],
                'queue_options' => [
                    'name' => 'failed-events',
                ],
                'callback' => FailedEventConsumer::class,
            ];

            $defaultQueuesOptions['arguments'] = [
                'x-dead-letter-exchange' => ['S', 'x-failed-events'],
            ];
        }

        foreach ($config['queues'] as $queue) {
            $consumers[$queue['name']] = [
                'connection' => $defaultConnection,
                'exchange_options' => [
                    'name' => 'x-' . $queue['name'],
                    'type' => 'direct',
                ],
                'queue_options' => array_merge($defaultQueuesOptions, [
                    'name' => $queue['name'],
                ]),
                'qos_options' => [
                    'prefetch_size' => 0,
                    'prefetch_count' => 1,
                    'global' => false,
                ],
                'callback' => EventConsumer::class,
            ];
        }

        return $consumers;
    }

    private function getProducers(array $config): array
    {
        $defaultConnection = $config['default_connection_name'];

        $producers = [];
        foreach ($config['queues'] as $queue) {
            $producers[$queue['name']] = [
                'connection' => $defaultConnection,
                'exchange_options' => [
                    'name' => 'x-' . $queue['name'],
                    'type' => 'direct',
                ],
            ];
        }

        return $producers;
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($container->getParameter('kernel.debug'));
    }
}
