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

/**
 * Create foreach configured webspace a community manager.
 */
class CommunityManagerCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $config = $container->getParameter('sulu_community.config');

        foreach ($config[Configuration::WEBSPACES] as $webspaceKey => $webspaceConfig) {
            // Set firewall by webspace key
            if ($webspaceConfig[Configuration::FIREWALL] === null) {
                $webspaceConfig[Configuration::FIREWALL] = $webspaceKey;
            }

            // Set role by webspace key
            if ($webspaceConfig[Configuration::ROLE] === null) {
                $webspaceConfig[Configuration::ROLE] = ucfirst($webspaceKey) . 'User';
            }
            
            $webspaceConfig[Configuration::WEBSPACE_KEY] = $webspaceKey;

            $definition = new DefinitionDecorator('sulu_community.community_manager');
            $definition->replaceArgument(0, $webspaceConfig);
            $definition->replaceArgument(1, $webspaceKey);

            $container->setDefinition(
                sprintf('sulu_community.%s.community_manager', $webspaceKey),
                $definition
            );
        }
    }
}
