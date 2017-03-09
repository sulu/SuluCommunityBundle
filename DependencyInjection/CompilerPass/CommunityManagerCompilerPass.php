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
        $webspacesConfig = $container->getParameter('sulu_community.webspaces_config');

        foreach ($webspacesConfig as $webspaceKey => $webspaceConfig) {
            // Set firewall by webspace key
            if ($webspaceConfig[Configuration::FIREWALL] === null) {
                $webspaceConfig[Configuration::FIREWALL] = $webspaceKey;
            }

            // Set role by webspace key
            if ($webspaceConfig[Configuration::ROLE] === null) {
                $webspaceConfig[Configuration::ROLE] = ucfirst($webspaceKey) . 'User';
            }

            if (isset($webspaceConfig[Configuration::EMAIL_FROM])) {
                $webspaceConfig[Configuration::EMAIL_FROM] = [
                    $webspaceConfig[Configuration::EMAIL_FROM][Configuration::EMAIL_FROM_EMAIL] => $webspaceConfig[Configuration::EMAIL_FROM][Configuration::EMAIL_FROM_NAME]
                ];
            }
            else {
                $webspaceConfig[Configuration::EMAIL_FROM] = null;
            }

            if (isset($webspaceConfig[Configuration::EMAIL_TO])) {
                $webspaceConfig[Configuration::EMAIL_TO] = [
                    $webspaceConfig[Configuration::EMAIL_TO][Configuration::EMAIL_TO_EMAIL] => $webspaceConfig[Configuration::EMAIL_TO][Configuration::EMAIL_TO_NAME]
                ];
            }
            else {
                $webspaceConfig[Configuration::EMAIL_TO] = null;
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
