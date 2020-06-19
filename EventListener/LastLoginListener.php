<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\EventListener;

use Doctrine\ORM\EntityManager;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Last login listener to refresh the users last login timestamp.
 */
class LastLoginListener implements EventSubscriberInterface
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
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        EntityManager $entityManager,
        int $interval = 0
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
        $this->interval = $interval;
    }

    /**
     * @return array<string, mixed>
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
        ];
    }

    /**
     * Update the last login in specific interval.
     */
    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if (!$this->interval) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        // Check token authentication availability
        if (!$token) {
            return;
        }

        $user = $token->getUser();

        if (!$user instanceof User || $this->isActiveNow($user)) {
            return;
        }

        $user->setLastLogin(new \DateTime());
        $this->entityManager->flush($user);
    }

    /**
     * Check if user was active shortly.
     */
    private function isActiveNow(User $user): bool
    {
        $delay = new \DateTime($this->interval . ' seconds ago');

        return $user->getLastLogin() > $delay;
    }
}
