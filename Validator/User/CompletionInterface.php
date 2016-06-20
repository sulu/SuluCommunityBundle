<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Validator\User;

use Sulu\Bundle\SecurityBundle\Entity\User;

/**
 * Defines the completion validate functions.
 */
interface CompletionInterface
{
    /**
     * Validates the user data.
     *
     * @param User $user
     * @param string $webspaceKey
     *
     * @return bool
     */
    public function validate(User $user, $webspaceKey);
}
