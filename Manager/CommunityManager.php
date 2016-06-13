<?php

namespace Sulu\Bundle\CommunityBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Sulu\Bundle\SecurityBundle\Entity\User;

class CommunityManager
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $webspaceKey;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @param array $config
     * @param string $webspaceKey
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        array $config,
        $webspaceKey,
        EntityManagerInterface $entityManager
    ) {
        $this->config = $config;
        $this->webspaceKey = $webspaceKey;
        $this->entityManager = $entityManager;
    }

    /**
     * @return string
     */
    public function getWebspaceKey()
    {
        return $this->webspaceKey;
    }

    /**
     * @param User $user
     *
     * @return User
     */
    public function register(User $user)
    {
        $this->entityManager->persist($user->getContact());
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @param string $type
     * @param string $property
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getTypeConfig($type, $property)
    {
        if (
            !isset($this->config[$type])
            || !isset($this->config[$type][$property])
        ) {
            throw new \Exception(
                sprintf(
                    'Property "%s" from type "%s" not found for webspace "%s" in Community Manager.',
                    $property,
                    $type,
                    $this->webspaceKey
                )
            );
        }

        return $this->config[$type][$property];
    }
}
