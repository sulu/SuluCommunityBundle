<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Last login listener to refresh the users last login timestamp.
 */
class LastLoginListener
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var int
     */
    protected $interval;

    /**
     * LastLoginListener constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     * @param EntityManager $entityManager
     * @param int $interval
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        EntityManager $entityManager,
        $interval = null
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
        $this->interval = (int) $interval;
    }

    /**
     * Update the last login in specific interval.
     *
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if (!$this->interval) {
            return;
        }

        // Check token authentication availability
        if ($this->tokenStorage->getToken()) {
            $user = $this->tokenStorage->getToken()->getUser();

            if ($user instanceof User && !$this->isActiveNow($user)) {
                $user->setLastLogin(new \DateTime());
                $this->entityManager->flush($user);
            }
        }
    }

    /**
     * Check if user was active shortly.
     *
     * @param User $user
     *
     * @return bool
     */
    private function isActiveNow(User $user)
    {
        $delay = new \DateTime($this->interval . ' seconds ago');

        return $user->getLastLogin() > $delay;
    }
}
