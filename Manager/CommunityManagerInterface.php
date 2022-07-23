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
     */
    public function getWebspaceKey(): string;

    /**
     * Register user for the system.
     */
    public function register(User $user): User;

    /**
     * Complete the user registration.
     */
    public function completion(User $user): User;

    /**
     * Login user into the system.
     */
    public function login(User $user, Request $request): void;

    /**
     * Confirm the user registration.
     */
    public function confirm(string $token): ?User;

    /**
     * Generate password reset token and save.
     */
    public function passwordForget(string $emailUsername): ?User;

    /**
     * Reset user password token.
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
     * @throws \Exception
     *
     * @return mixed
     */
    public function getConfigProperty(string $property);

    /**
     * Get community webspace config type property.
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function getConfigTypeProperty(string $type, string $property);

    /**
     * Send email to user and admin by type.
     */
    public function sendEmails(string $type, User $user): void;

    /**
     * Save profile for given user.
     *
     * @return User
     */
    public function saveProfile(User $user): ?User;
}
