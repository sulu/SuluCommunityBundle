<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\DependencyInjection\CompilerPass;

use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register the completion listener when validators are configured.
 */
class CommunityValidatorCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $config = $container->getParameter('sulu_community.config');

        foreach ($config[Configuration::WEBSPACES] as $webspaceKey => $webspaceConfig) {
            // Get Completion Validator
            $validatorId = $webspaceConfig[Configuration::TYPE_COMPLETION][Configuration::SERVICE];

            if ($validatorId) {
                $validators[$webspaceKey] = new Reference($validatorId);
            }
        }

        // Register request listener only when validator exists.
        if (!empty($validators)) {
            $definition = new DefinitionDecorator('sulu_community.completion_listener.abstract');
            $definition->replaceArgument(3, $validators);
            $definition->addTag('kernel.event_listener', ['event' => 'kernel.request', 'method' => 'onRequest']);
            $container->setDefinition(
                sprintf('sulu_community.completion_listener', $validators),
                $definition
            );
        }
    }
}
