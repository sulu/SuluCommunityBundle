<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sulu\Bundle\CommunityBundle\EventListener\LastLoginListener;
use Symfony\Component\DependencyInjection\Reference;

return static function(ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sulu_community.event_listener.last_login', LastLoginListener::class)
        ->args([
            new Reference('security.token_storage'),
            new Reference('doctrine.orm.entity_manager'),
            '%sulu_community.last_login.refresh_interval%',
        ])
        ->tag('kernel.event_subscriber')
        ->tag('sulu.context', ['context' => 'website']);
};
