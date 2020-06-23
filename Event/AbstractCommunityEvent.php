<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Event;

use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event for community actions with config and the user which throw the event.
 */
abstract class AbstractCommunityEvent extends Event
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var mixed[]
     */
    protected $config;

    /**
     * CommunityEvent constructor.
     *
     * @param mixed[] $config
     */
    public function __construct(User $user, array $config)
    {
        $this->user = $user;
        $this->config = $config;
    }

    /**
     * Get user.
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Get config.
     *
     * @return mixed[]
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get config property.
     *
     * @return mixed
     */
    public function getConfigProperty(string $property)
    {
        return $this->config[$property];
    }

    /**
     * Get config type property.
     *
     * @return mixed
     */
    public function getConfigTypeProperty(string $type, string $property)
    {
        return $this->config[$type][$property];
    }
}
