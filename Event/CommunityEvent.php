<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Event;

use Sulu\Bundle\SecurityBundle\Entity\BaseUser;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event for community actions with config and the user which throw the event.
 */
class CommunityEvent extends Event
{
    /**
     * @var BaseUser
     */
    protected $user;

    /**
     * @var array
     */
    protected $config;

    /**
     * CommunityEvent constructor.
     *
     * @param BaseUser $user
     * @param array $config
     */
    public function __construct(BaseUser $user, array $config)
    {
        $this->user = $user;
        $this->config = $config;
    }

    /**
     * Get user.
     *
     * @return BaseUser
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get config.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get config property.
     *
     * @param string $property
     *
     * @return mixed
     */
    public function getConfigProperty($property)
    {
        return $this->config[$property];
    }

    /**
     * Get config type property.
     *
     * @param string $property
     *
     * @return mixed
     */
    public function getConfigTypeProperty($type, $property)
    {
        return $this->config[$type][$property];
    }
}
