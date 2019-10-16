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

use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;

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
    public function getWebspaceKey(): string;

    /**
     * Register user for the system.
     *
     * @param User $user
     *
     * @return User
     */
    public function register(User $user): User;

    /**
     * Complete the user registration.
     *
     * @param User $user
     *
     * @return User
     */
    public function completion(User $user): User;

    /**
     * Login user into the system.
     *
     * @param User $user
     * @param Request $request
     */
    public function login(User $user, Request $request): void;

    /**
     * Confirm the user registration.
     *
     * @param string $token
     *
     * @return User|null
     */
    public function confirm(string $token): ?User;

    /**
     * Generate password reset token and save.
     *
     * @param string $emailUsername
     *
     * @return User|null
     */
    public function passwordForget(string $emailUsername): ?User;

    /**
     * Reset user password token.
     *
     * @param User $user
     *
     * @return User
     */
    public function passwordReset(User $user): User;

    /**
     * Get community webspace config.
     *
     * @return mixed[]
     */
    public function getConfig(): array;

    /**
     * Get community webspace config property.
     *
     * @param string $property
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getConfigProperty(string $property);

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
    public function getConfigTypeProperty(string $type, string $property);

    /**
     * Send email to user and admin by type.
     *
     * @param string $type
     * @param User $user
     */
    public function sendEmails(string $type, User $user): void;

    /**
     * Save profile for given user.
     *
     * @param User $user
     *
     * @return User
     */
    public function saveProfile(User $user): ?User;
}
