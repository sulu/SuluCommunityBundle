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

use Sulu\Bundle\CommunityBundle\Validator\Constraints\BlockedValidator;
use Sulu\Bundle\CommunityBundle\Validator\Constraints\ExistValidator;
use Symfony\Component\DependencyInjection\Reference;

return static function(ContainerConfigurator $container) {
    $services = $container->services();

    // Exist Validator
    $services->set('sulu_community.validator.exist', ExistValidator::class)
        ->args([
            new Reference('doctrine.orm.entity_manager'),
        ])
        ->tag('validator.constraint_validator', ['alias' => 'exist_validator']);

    // Blocked Validator
    $services->set('sulu_community.validator.blocked', BlockedValidator::class)
        ->args([
            new Reference('sulu.repository.registration_rule_item'),
        ])
        ->tag('validator.constraint_validator', ['alias' => 'blocked_validator']);
};
