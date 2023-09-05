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

use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleItem;

/**
 * Interface for registration-rule-item manager.
 */
interface RegistrationRuleItemManagerInterface
{
    /**
     * Returns registration-rule-item.
     */
    public function find(int $id): RegistrationRuleItem;

    /**
     * Return new registration-rule-item.
     */
    public function create(): RegistrationRuleItem;

    /**
     * Deletes given registration-rule-item.
     *
     * @param int|int[] $ids
     */
    public function delete($ids): void;
}
