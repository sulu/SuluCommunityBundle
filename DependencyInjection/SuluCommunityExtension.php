<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\DependencyInjection;

use DoctrineExtensions\Query\Mysql\Regexp;
use Sulu\Bundle\CommunityBundle\Entity\InvalidTypeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages community bundle configuration.
 */
class SuluCommunityExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('sulu_community.webspaces_config', $config[Configuration::WEBSPACES]);

        $lastLoginEnabled = $config[Configuration::LAST_LOGIN]['enabled'];

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('validator.xml');

        if ($lastLoginEnabled) {
            $lastLoginRefreshInterval = $config[Configuration::LAST_LOGIN][Configuration::REFRESH_INTERVAL];

            $container->setParameter(
                'sulu_community.last_login.refresh_interval',
                $lastLoginRefreshInterval
            );

            $loader->load('last-login.xml');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        if ($container->hasExtension('massive_build')) {
            $container->prependExtensionConfig(
                'massive_build',
                [
                    'targets' => [
                        'prod' => [
                            'dependencies' => [
                                'community' => [],
                            ],
                        ],
                        'dev' => [
                            'dependencies' => [
                                'community' => [],
                            ],
                        ],
                        'maintain' => [
                            'dependencies' => [
                                'community' => [],
                            ],
                        ],
                    ],
                ]
            );
        }

        if ($container->hasExtension('fos_rest')) {
            $container->prependExtensionConfig(
                'fos_rest',
                [
                    'exception' => [
                        'codes' => [
                            InvalidTypeException::class => 409,
                        ],
                    ],
                ]
            );
        }

        if ($container->hasExtension('jms_serializer')) {
            $container->prependExtensionConfig(
                'jms_serializer',
                [
                    'metadata' => [
                        'directories' => [
                            [
                                'path' => __DIR__ . '/../Resources/config/serializer',
                                'namespace_prefix' => 'Sulu\Bundle\CommunityBundle\Entity',
                            ],
                        ],
                    ],
                ]
            );
        }

        if ($container->hasExtension('doctrine')) {
            $container->prependExtensionConfig(
                'doctrine',
                [
                    'orm' => [
                        'dql' => [
                            'string_functions' => [
                                'regexp' => RegExp::class,
                            ],
                        ],
                    ],
                ]
            );
        }
    }
}
