<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
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
    public function getName()
    {
        return 'community';
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return ['fixtures'];
    }

    public function build(): void
    {
        $this->execCommand('Init community', InitCommand::NAME);
    }
}
