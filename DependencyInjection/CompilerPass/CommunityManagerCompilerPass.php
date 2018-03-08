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
            $webspaceConfig = $this->updateWebspaceConfig($webspaceKey, $webspaceConfig);
            $webspacesConfig[$webspaceKey] = $webspaceConfig;

            $definition = new DefinitionDecorator('sulu_community.community_manager');
            $definition->replaceArgument(0, $webspaceConfig);
            $definition->replaceArgument(1, $webspaceKey);

            $container->setDefinition(
                sprintf('sulu_community.%s.community_manager', Normalizer::normalize($webspaceKey)),
                $definition
            );

            if (false !== strpos($webspaceKey, '-')) {
                $container->setAlias(
                    sprintf('sulu_community.%s.community_manager', $webspaceKey),
                    sprintf('sulu_community.%s.community_manager', Normalizer::normalize($webspaceKey))
                );
            }
        }

        $container->setParameter('sulu_community.webspaces_config', $webspacesConfig);
    }

    /**
     * Update webspace config.
     *
     * @param string $webspaceKey
     * @param array $webspaceConfig
     *
     * @return array
     */
    private function updateWebspaceConfig($webspaceKey, array $webspaceConfig)
    {
        // Set firewall by webspace key
        if (null === $webspaceConfig[Configuration::FIREWALL]) {
            $webspaceConfig[Configuration::FIREWALL] = $webspaceKey;
        }

        // TODO currently symfony normalize the security firewalls key which will replace "-" with "_".
        $webspaceConfig[Configuration::FIREWALL] = Normalizer::normalize($webspaceConfig[Configuration::FIREWALL]);

        // Set role by webspace key
        if (null === $webspaceConfig[Configuration::ROLE]) {
            $webspaceConfig[Configuration::ROLE] = ucfirst($webspaceKey) . 'User';
        }

        // Set email from
        if (isset($webspaceConfig[Configuration::EMAIL_FROM])) {
            $webspaceConfig[Configuration::EMAIL_FROM] = [
                $webspaceConfig[Configuration::EMAIL_FROM][Configuration::EMAIL_FROM_EMAIL] => $webspaceConfig[Configuration::EMAIL_FROM][Configuration::EMAIL_FROM_NAME],
            ];
        } else {
            $webspaceConfig[Configuration::EMAIL_FROM] = null;
        }

        // Set email to
        if (isset($webspaceConfig[Configuration::EMAIL_TO])) {
            $webspaceConfig[Configuration::EMAIL_TO] = [
                $webspaceConfig[Configuration::EMAIL_TO][Configuration::EMAIL_TO_EMAIL] => $webspaceConfig[Configuration::EMAIL_TO][Configuration::EMAIL_TO_NAME],
            ];
        } else {
            $webspaceConfig[Configuration::EMAIL_TO] = $webspaceConfig[Configuration::EMAIL_FROM];
        }

        // Set maintenance mode
        if ($webspaceConfig[Configuration::MAINTENANCE][Configuration::ENABLED]) {
            $webspaceConfig = $this->activateMaintenanceMode($webspaceConfig);
        }

        $webspaceConfig[Configuration::WEBSPACE_KEY] = $webspaceKey;

        return $webspaceConfig;
    }

    /**
     * Activate Maintenance mode.
     *
     * @param array $webspaceConfig
     *
     * @return array
     */
    private function activateMaintenanceMode(array $webspaceConfig)
    {
        foreach (Configuration::$TYPES as $type) {
            if (isset($webspaceConfig[$type][Configuration::TEMPLATE])) {
                $webspaceConfig[$type][Configuration::TEMPLATE] = $webspaceConfig[Configuration::MAINTENANCE][Configuration::TEMPLATE];
            }
        }

        return $webspaceConfig;
    }
}
