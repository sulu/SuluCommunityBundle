<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Build;

use Sulu\Bundle\CommunityBundle\Command\InitCommand;
use Sulu\Bundle\CoreBundle\Build\SuluBuilder;

/**
 * Builder for initializing the community-bundle.
 */
class CommunityBuilder extends SuluBuilder
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'community';
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return ['fixtures'];
    }

    public function build()
    {
        $this->execCommand('Init community', InitCommand::NAME);
    }
}
