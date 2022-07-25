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
use Sulu\Bundle\CommunityBundle\Manager\CommunityManagerInterface;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Create foreach configured webspace a community manager.
 *
 * @phpstan-type TypeConfigProperties array{
 *      enabled: bool,
 *      template: string,
 *      service: string|null,
 *      embed_template: string,
 *      type: string,
 *      options: mixed[],
 *      activate_user: bool,
 *      auto_login: bool,
 *      redirect_to: string|null,
 *      email: array{
 *          subject: string,
 *          admin_template: string|null,
 *          user_template: string|null,
 *      },
 *      delete_user: bool,
 * }
 *
 * @phpstan-type Config array{
 *     from: string|string[],
 *     to: string|string[],
 *     webspace_key: string,
 *     role: string|null,
 *     firewall: string|null,
 *     maintenance: array{
 *         enabled: bool,
 *         template: string,
 *     },
 *     login: TypeConfigProperties,
 *     registration: TypeConfigProperties,
 *     completion: TypeConfigProperties,
 *     confirmation: TypeConfigProperties,
 *     password_forget: TypeConfigProperties,
 *     password_reset: TypeConfigProperties,
 *     profile: TypeConfigProperties,
 *     blacklisted: TypeConfigProperties,
 *     blacklist_confirmed: TypeConfigProperties,
 *     blacklist_denied: TypeConfigProperties,
 *     email_confirmation: TypeConfigProperties,
 * }
 *
 * @phpstan-import-type Config from CommunityManagerInterface as CommunityConfig
 */
class CommunityManagerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        /** @var array<string, Config> $webspacesConfig */
        $webspacesConfig = $container->getParameter('sulu_community.webspaces_config');

        $references = [];
        foreach ($webspacesConfig as $webspaceKey => $webspaceConfig) {
            $webspaceConfig = $this->updateWebspaceConfig($container, $webspaceKey, $webspaceConfig);
            $webspacesConfig[$webspaceKey] = $webspaceConfig;

            $definition = new ChildDefinition('sulu_community.community_manager');
            $definition->replaceArgument(0, $webspaceConfig);
            $definition->replaceArgument(1, $webspaceKey);

            $id = \sprintf('sulu_community.%s.community_manager', Normalizer::normalize($webspaceKey));
            $references[$webspaceKey] = new Reference($id);
            $container->setDefinition($id, $definition);

            if (false !== \strpos($webspaceKey, '-')) {
                $container->setAlias(
                    \sprintf('sulu_community.%s.community_manager', $webspaceKey),
                    \sprintf('sulu_community.%s.community_manager', Normalizer::normalize($webspaceKey))
                );
            }
        }

        $container->getDefinition('sulu_community.community_manager.registry')->replaceArgument(0, $references);
        $container->setParameter('sulu_community.webspaces_config', $webspacesConfig);
    }

    /**
     * Update webspace config.
     *
     * @param Config $webspaceConfig
     *
     * @return CommunityConfig
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
            $webspaceConfig[Configuration::ROLE] = \ucfirst($webspaceKey) . 'User';
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
