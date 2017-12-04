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

use Sulu\Bundle\SecurityBundle\Entity\BaseUser;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Handles registration, confirmation, password reset and forget.
 */
interface CommunityManagerInterface
{
    /**
     * Return the webspace key.
     *
     * @return string
     */
    public function getWebspaceKey();

    /**
     * Register user for the system.
     *
     * @param User $user
     *
     * @return User
     */
    public function register(User $user);

    /**
     * Complete the user registration.
     *
     * @param User $user
     *
     * @return User
     */
    public function completion(User $user);

    /**
     * Login user into the system.
     *
     * @param User $user
     * @param Request $request
     *
     * @return UsernamePasswordToken|null
     */
    public function login(User $user, Request $request);

    /**
     * Confirm the user registration.
     *
     * @param string $token
     *
     * @return User|null
     */
    public function confirm($token);

    /**
     * Generate password reset token and save.
     *
     * @param string $emailUsername
     *
     * @return User|null
     */
    public function passwordForget($emailUsername);

    /**
     * Reset user password token.
     *
     * @param User $user|null
     *
     * @return User
     */
    public function passwordReset(User $user);

    /**
     * Get community webspace config.
     *
     * @return array
     */
    public function getConfig();

    /**
     * Get community webspace config property.
     *
     * @param string $property
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getConfigProperty($property);

    /**
     * Get community webspace config type property.
     *
     * @param string $type
     * @param string $property
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getConfigTypeProperty($type, $property);

    /**
     * Send email to user and admin by type.
     *
     * @param string $type
     * @param BaseUser $user
     */
    public function sendEmails($type, BaseUser $user);

    /**
     * Save profile for given user.
     *
     * @param BaseUser $user
     *
     * @return BaseUser
     */
    public function saveProfile(BaseUser $user);
}
