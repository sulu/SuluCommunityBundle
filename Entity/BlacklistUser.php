<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
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
    const TYPE_NEW = 0;
    const TYPE_CONFIRMED = 1;
    const TYPE_DENIED = 2;

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

    /**
     * @param string $token
     * @param string $webspaceKey
     * @param UserInterface $user
     */
    public function __construct($token, $webspaceKey, UserInterface $user)
    {
        $this->token = $token;
        $this->webspaceKey = $webspaceKey;
        $this->user = $user;

        $this->type = self::TYPE_NEW;
    }

    /**
     * Returns id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns token.
     *
     * @return string|null
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Returns type.
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns webspace-key.
     *
     * @return string
     */
    public function getWebspaceKey()
    {
        return $this->webspaceKey;
    }

    /**
     * Returns user.
     *
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set type to denied.
     *
     * @return BlacklistUser
     */
    public function deny()
    {
        $this->type = self::TYPE_DENIED;
        $this->token = null;

        return $this;
    }

    /**
     * Set type to denied.
     *
     * @return BlacklistUser
     */
    public function confirm()
    {
        $this->type = self::TYPE_CONFIRMED;
        $this->token = null;

        return $this;
    }
}
