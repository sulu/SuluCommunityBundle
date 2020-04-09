<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Entity;

use Sulu\Component\Security\Authentication\UserInterface;

/**
 * Represents a email-confirmation request.
 */
class EmailConfirmationToken
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $token;

    /**
     * @var UserInterface
     */
    private $user;

    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * Returns id.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Returns token.
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Set token.
     */
    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Returns user.
     */
    public function getUser(): UserInterface
    {
        return $this->user;
    }
}
