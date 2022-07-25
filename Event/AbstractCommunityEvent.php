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

use Sulu\Bundle\CommunityBundle\Manager\CommunityManagerInterface;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event for community actions with config and the user which throw the event.
 *
 * @phpstan-import-type Config from CommunityManagerInterface
 * @phpstan-import-type TypeConfigProperties from CommunityManagerInterface
 */
abstract class AbstractCommunityEvent extends Event
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var Config
     */
    protected $config;

    /**
     * CommunityEvent constructor.
     *
     * @param Config $config
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
     * @return Config
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get config property.
     *
     * @template TConfig of string&key-of<Config>
     *
     * @param TConfig $property
     *
     * @return Config[TTypeConfig]
     */
    public function getConfigProperty(string $property)
    {
        return $this->config[$property];
    }

    /**
     * Get config type property.
     *
     * @template TConfig of string&key-of<Config>
     * @template TTypeConfigProperty of string&key-of<TypeConfigProperties>
     *
     * @param TConfig $type
     * @param TTypeConfigProperty $property
     *
     * @return Config[TConfig][TTypeConfigProperty]
     */
    public function getConfigTypeProperty(string $type, string $property)
    {
        return $this->config[$type][$property];
    }
}
