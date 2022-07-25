<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Controller;

use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Sulu\Bundle\CommunityBundle\Entity\BlacklistItem;
use Sulu\Bundle\CommunityBundle\Entity\BlacklistItemRepository;
use Sulu\Bundle\CommunityBundle\Entity\BlacklistUser;
use Sulu\Bundle\CommunityBundle\Entity\BlacklistUserRepository;
use Sulu\Bundle\CommunityBundle\Manager\BlacklistItemManager;
use Sulu\Bundle\CommunityBundle\Manager\BlacklistItemManagerInterface;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handles user confirmations for administrators.
 */
class BlacklistConfirmationController extends AbstractController
{
    /**
     * Confirms user with given token.
     */
    public function confirmAction(Request $request): Response
    {
        /** @var string $token */
        $token = $request->get('token');

        /** @var BlacklistUser|null $blacklistUser */
        $blacklistUser = $this->getBlacklistUserRepository()->findByToken($token);

        if (null === $blacklistUser) {
            throw new NotFoundHttpException();
        }

        $blacklistUser->confirm();
        $this->getEntityManager()->flush();

        $communityManager = $this->getCommunityManager($blacklistUser->getWebspaceKey());

        /** @var User $user */
        $user = $blacklistUser->getUser();
        $communityManager->sendEmails(Configuration::TYPE_BLACKLIST_CONFIRMED, $user);

        return $this->renderTemplate(
            Configuration::TYPE_BLACKLIST_CONFIRMED,
            ['user' => $blacklistUser->getUser()]
        );
    }

    /**
     * Denies user with given token.
     */
    public function denyAction(Request $request): Response
    {
        $entityManager = $this->getEntityManager();

        /** @var string $token */
        $token = $request->get('token');

        /** @var BlacklistUser|null $blacklistUser */
        $blacklistUser = $this->getBlacklistUserRepository()->findByToken($token);

        if (null === $blacklistUser) {
            throw new NotFoundHttpException();
        }

        /** @var User $user */
        $user = $blacklistUser->getUser();
        $blacklistUser->deny();

        $communityManager = $this->getCommunityManager($blacklistUser->getWebspaceKey());
        if (true === $communityManager->getConfigTypeProperty(
            Configuration::TYPE_BLACKLIST_DENIED,
            Configuration::DELETE_USER
        )
        ) {
            $entityManager->remove($user->getContact());
            $entityManager->remove($user);
            $entityManager->remove($blacklistUser);
        }

        /** @var BlacklistItem|null $item */
        $item = $this->getBlacklistItemRepository()->findOneBy(['pattern' => $user->getEmail()]);

        if (!$item) {
            $item = $this->getBlacklistItemManager()->create();
        }

        /** @var string $email */
        $email = $user->getEmail();

        $item->setType(BlacklistItem::TYPE_BLOCK)
            ->setPattern($email);

        $entityManager->flush();

        $communityManager->sendEmails(Configuration::TYPE_BLACKLIST_DENIED, $user);

        return $this->renderTemplate(
            Configuration::TYPE_BLACKLIST_DENIED,
            ['user' => $blacklistUser->getUser()]
        );
    }

    protected function getBlacklistUserRepository(): BlacklistUserRepository
    {
        return $this->container->get('sulu_community.blacklisting.user_repository');
    }

    protected function getBlacklistItemRepository(): BlacklistItemRepository
    {
        return $this->container->get('sulu_community.blacklisting.item_repository');
    }

    protected function getBlacklistItemManager(): BlacklistItemManager
    {
        return $this->container->get('sulu_community.blacklisting.item_manager');
    }

    /**
     * @return array<string|int, string>
     */
    public static function getSubscribedServices(): array
    {
        $subscribedServices = parent::getSubscribedServices();

        $subscribedServices['sulu_community.blacklisting.user_repository'] = BlacklistUserRepository::class;
        $subscribedServices['sulu_community.blacklisting.item_repository'] = BlacklistItemRepository::class;
        $subscribedServices['sulu_community.blacklisting.item_manager'] = BlacklistItemManagerInterface::class;

        return $subscribedServices;
    }
}
