<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Manager;

interface CommunityManagerRegistryInterface
{
    public function get(string $webspaceKey): CommunityManagerInterface;

    public function has(string $webspaceKey): bool;
}
