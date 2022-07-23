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
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register the completion listener when validators are configured.
 */
class CommunityValidatorCompilerPass implements CompilerPassInterface
{
    public const COMPLETION_LISTENER_SERVICE_ID = 'sulu_community.completion_listener';

    public function process(ContainerBuilder $container): void
    {
        // If no listener exists do nothing
        if (!$container->hasDefinition(self::COMPLETION_LISTENER_SERVICE_ID)) {
            return;
        }

        // Create Validator References
        /** @var mixed[] $webspacesConfig */
        $webspacesConfig = $container->getParameter('sulu_community.webspaces_config');

        foreach ($webspacesConfig as $webspaceKey => $webspaceConfig) {
            // Get Completion Validator
            $validatorId = $webspaceConfig[Configuration::TYPE_COMPLETION][Configuration::SERVICE];

            if ($validatorId) {
                $validators[$webspaceKey] = new Reference($validatorId);
            }
        }

        // Register request listener only when validator exists.
        if (!empty($validators)) {
            $definition = $container->getDefinition(self::COMPLETION_LISTENER_SERVICE_ID);
            $definition->replaceArgument(4, $validators);
        } else {
            $container->removeDefinition(self::COMPLETION_LISTENER_SERVICE_ID);
        }
    }
}
