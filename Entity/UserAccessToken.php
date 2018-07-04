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

class UserAccessToken
{
    /**
     * @var UserInterface
     */
    private $user;

    /**
     * @var string
     */
    private $service;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $accessToken;

    public function __construct(UserInterface $user, string $service, string $identifier)
    {
        $this->user = $user;
        $this->service = $service;
        $this->identifier = $identifier;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getService(): string
    {
        return $this->service;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }
}
