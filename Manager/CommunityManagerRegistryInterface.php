<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Manager;

interface CommunityManagerRegistryInterface
{
    /**
     * @param string $webspaceKey
     *
     * @return CommunityManagerInterface
     */
    public function get($webspaceKey);

    /**
     * @param string $webspaceKey
     *
     * @return bool
     */
    public function has($webspaceKey);
}
