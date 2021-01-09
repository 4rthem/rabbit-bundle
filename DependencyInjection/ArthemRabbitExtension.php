<?php

namespace Arthem\Bundle\RabbitBundle\DependencyInjection;

use Arthem\Bundle\RabbitBundle\Consumer\EventConsumer;
use Arthem\Bundle\RabbitBundle\Consumer\FailedEventConsumer;
use Arthem\Bundle\RabbitBundle\Model\FailedEventManager;
use Arthem\Bundle\RabbitBundle\Producer\Adapter\AMQPProducerAdapter;
use Arthem\Bundle\RabbitBundle\Producer\Adapter\DefferedPhpCommandProducerAdapter;
use Arthem\Bundle\RabbitBundle\Producer\Adapter\DirectPhpCommandProducerAdapter;
use Arthem\Bundle\RabbitBundle\Producer\Adapter\EventProducerAdapterInterface;
use LogicException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

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

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        if ($config['direct']) {
            if ($config['deffered']) {
                $adapterClass = DefferedPhpCommandProducerAdapter::class;
            } else {
                $adapterClass = DirectPhpCommandProducerAdapter::class;
            }
        } else {
            $adapterClass = AMQPProducerAdapter::class;
        }

        $container->setAlias(EventProducerAdapterInterface::class, $adapterClass);

        if ($config['failure']['enabled']) {
            $loader->load('failed_events.yml');
            $this->configureFailedEvents($container, $config['failure']);
        }
    }

    private function configureFailedEvents(ContainerBuilder $container, $config): void
    {
        $definition = $container->getDefinition(FailedEventManager::class);
        $definition->setArgument('$model', $config['model']);
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
                    'vhost' => '%env(RABBITMQ_VHOST)%',
                    'lazy' => true,
                    'connection_timeout' => 3,
                    'read_write_timeout' => 3,
                    'keepalive' => true,
                    'heartbeat' => 0,
                ],
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
            $consumers['failed_event'] = [
                'connection' => $defaultConnection,
                'exchange_options' => [
                    'name' => 'x-failed-event',
                    'type' => 'direct',
                ],
                'queue_options' => [
                    'name' => 'failed-event',
                ],
                'callback' => FailedEventConsumer::class,
            ];

            $defaultQueuesOptions['arguments'] = [
                'x-dead-letter-exchange' => ['S', 'x-failed-event'],
            ];
        }

        foreach ($config['queues'] as $name => $queue) {
            $consumers[$name] = [
                'connection' => $defaultConnection,
                'exchange_options' => array_merge([
                    'name' => 'x-'.$name,
                    'type' => 'direct',
                ], $queue['exchange_options'] ?? []),
                'queue_options' => array_merge_recursive($defaultQueuesOptions, [
                    'name' => $name,
                ], $queue['queue_options'] ?? []),
                'qos_options' => array_merge([
                    'prefetch_size' => 0,
                    'prefetch_count' => 1,
                    'global' => false,
                ], $queue['qos_options'] ?? []),
                'callback' => EventConsumer::class,
            ];
        }

        return $consumers;
    }

    private function getProducers(array $config): array
    {
        $defaultConnection = $config['default_connection_name'];

        $producers = [];
        foreach ($config['queues'] as $name => $queue) {
            $producers[$name] = [
                'connection' => $defaultConnection,
                'exchange_options' => [
                    'name' => 'x-'.$name,
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
