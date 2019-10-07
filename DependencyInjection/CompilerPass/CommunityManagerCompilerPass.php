<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\DependencyInjection\CompilerPass;

use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

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

        $references = [];
        foreach ($webspacesConfig as $webspaceKey => $webspaceConfig) {
            $webspaceConfig = $this->updateWebspaceConfig($container, $webspaceKey, $webspaceConfig);
            $webspacesConfig[$webspaceKey] = $webspaceConfig;

            $definition = new ChildDefinition('sulu_community.community_manager');
            $definition->replaceArgument(0, $webspaceConfig);
            $definition->replaceArgument(1, $webspaceKey);

            $id = sprintf('sulu_community.%s.community_manager', Normalizer::normalize($webspaceKey));
            $references[$webspaceKey] = new Reference($id);
            $container->setDefinition($id, $definition);

            if (false !== strpos($webspaceKey, '-')) {
                $container->setAlias(
                    sprintf('sulu_community.%s.community_manager', $webspaceKey),
                    sprintf('sulu_community.%s.community_manager', Normalizer::normalize($webspaceKey))
                );
            }
        }

        $container->getDefinition('sulu_community.community_manager.registry')->replaceArgument(0, $references);
        $container->setParameter('sulu_community.webspaces_config', $webspacesConfig);
    }

    /**
     * Update webspace config.
     *
     * @param ContainerBuilder $container
     * @param string $webspaceKey
     * @param mixed[] $webspaceConfig
     *
     * @return mixed[]
     */
    private function updateWebspaceConfig(
        ContainerBuilder $container,
        string $webspaceKey,
        array $webspaceConfig
    ): array {
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

        // TODO maintenance mode should not be handled in compilerpass
        $maintenanceEnabled = $container->resolveEnvPlaceholders(
            $webspaceConfig[Configuration::MAINTENANCE][Configuration::ENABLED],
            true
        );

        if ($maintenanceEnabled) {
            $webspaceConfig = $this->activateMaintenanceMode($webspaceConfig);
        }

        $webspaceConfig[Configuration::WEBSPACE_KEY] = $webspaceKey;

        return $webspaceConfig;
    }

    /**
     * Activate Maintenance mode.
     *
     * @param mixed[] $webspaceConfig
     *
     * @return mixed[]
     */
    private function activateMaintenanceMode(array $webspaceConfig): array
    {
        foreach (Configuration::$TYPES as $type) {
            if (isset($webspaceConfig[$type][Configuration::TEMPLATE])) {
                $webspaceConfig[$type][Configuration::TEMPLATE] = $webspaceConfig[Configuration::MAINTENANCE][Configuration::TEMPLATE];
            }
        }

        return $webspaceConfig;
    }
}
