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
 * Represents a requested user which has to be confirmed.
 */
class BlacklistUser
{
    public const TYPE_NEW = 0;
    public const TYPE_CONFIRMED = 1;
    public const TYPE_DENIED = 2;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string|null
     */
    private $token;

    /**
     * @var int
     */
    private $type;

    /**
     * @var string
     */
    private $webspaceKey;

    /**
     * @var UserInterface
     */
    private $user;

    public function __construct(string $token, string $webspaceKey, UserInterface $user)
    {
        $this->token = $token;
        $this->webspaceKey = $webspaceKey;
        $this->user = $user;

        $this->type = self::TYPE_NEW;
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
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Returns type.
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * Returns webspace-key.
     */
    public function getWebspaceKey(): string
    {
        return $this->webspaceKey;
    }

    /**
     * Returns user.
     */
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    /**
     * Set type to denied.
     */
    public function deny(): self
    {
        $this->type = self::TYPE_DENIED;
        $this->token = null;

        return $this;
    }

    /**
     * Set type to denied.
     */
    public function confirm(): self
    {
        $this->type = self::TYPE_CONFIRMED;
        $this->token = null;

        return $this;
    }
}
