<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Tests\Application;

use Sulu\Bundle\CommunityBundle\SuluCommunityBundle;
use Sulu\Bundle\TestBundle\Kernel\SuluTestKernel;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle;
use Symfony\Component\Config\Loader\LoaderInterface;

class Kernel extends SuluTestKernel
{
    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        $bundles = parent::registerBundles();
        $bundles[] = new SuluCommunityBundle();
        $bundles[] = new SwiftmailerBundle();

        if (SuluTestKernel::CONTEXT_WEBSITE === $this->getContext()) {
            $bundles[] = new SecurityBundle();
        }

        return $bundles;
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        parent::registerContainerConfiguration($loader);

        $loader->load(__DIR__ . '/config/config_' . $this->getContext() . '.yml');
    }
}
