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

use Sulu\Bundle\CommunityBundle\Entity\BlacklistItem;

/**
 * Interface for blacklist-item manager.
 */
interface BlacklistItemManagerInterface
{
    /**
     * Returns blacklist-item.
     *
     * @param int $id
     *
     * @return BlacklistItem
     */
    public function find($id);

    /**
     * Return new blacklist-item.
     *
     * @return BlacklistItem
     */
    public function create();

    /**
     * Deletes given blacklist-item.
     *
     * @param int|int[] $ids
     */
    public function delete($ids);
}
