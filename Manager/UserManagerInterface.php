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

use Sulu\Bundle\SecurityBundle\Entity\User;

/**
 * Manage the community user entities.
 */
interface UserManagerInterface
{
    /**
     * Create a new User entity.
     *
     * @param User $user
     * @param string $webspaceKey
     * @param string $roleName
     *
     * @return User
     */
    public function createUser(User $user, $webspaceKey, $roleName);

    /**
     * Update User entity.
     *
     * @param User $user
     *
     * @return User
     */
    public function updateUser(User $user);

    /**
     * Generates a unique token.
     *
     * @param string $field
     *
     * @return string
     */
    public function getUniqueToken($field);

    /**
     * Find a user by the password reset token.
     *
     * @param string $token
     *
     * @return User|null
     */
    public function findByPasswordResetToken($token);

    /**
     * Find a user by a the confirmation key.
     *
     * @param string $token
     *
     * @return User|null
     */
    public function findByConfirmationKey($token);

    /**
     * Find a user by username or email.
     *
     * @param string $identifier
     *
     * @return User|null
     */
    public function findUser($identifier);
}
