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

use Sulu\Bundle\CommunityBundle\Entity\BlacklistItem;

/**
 * Interface for blacklist-item manager.
 */
interface BlacklistItemManagerInterface
{
    /**
     * Returns blacklist-item.
     */
    public function find(int $id): BlacklistItem;

    /**
     * Return new blacklist-item.
     */
    public function create(): BlacklistItem;

    /**
     * Deletes given blacklist-item.
     *
     * @param int|int[] $ids
     */
    public function delete($ids): void;
}
