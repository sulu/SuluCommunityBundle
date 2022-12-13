<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\Kernel;

return static function(PhpFileLoader $loader, ContainerBuilder $container) {
    $context = $container->getParameter('sulu.context');

    $loader->import('context_' . $context . '.yml');

    if ('website' === $context) {
        if (\version_compare(Kernel::VERSION, '6.0.0', '>=')) {
            $loader->import('security-6.yml');
        } else {
            $loader->import('security-5-4.yml');
        }
    }
};
