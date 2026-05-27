<?php

declare(strict_types=1);

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Last login listener to refresh the users last login timestamp.
 */
class LastLoginListener implements EventSubscriberInterface
{
    public function __construct(
        protected TokenStorageInterface $tokenStorage,
        protected EntityManagerInterface $entityManager,
        protected int $interval = 0,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$this->interval) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        if (!$token) {
            return;
        }

        $user = $token->getUser();

        if (!$user instanceof User || $this->isActiveNow($user)) {
            return;
        }

        $user->setLastLogin(new \DateTime());
        $this->entityManager->flush();
    }

    private function isActiveNow(User $user): bool
    {
        if (null === $user->getLastLogin()) {
            return false;
        }

        $delay = new \DateTime($this->interval . ' seconds ago');

        return $user->getLastLogin() > $delay;
    }
}
