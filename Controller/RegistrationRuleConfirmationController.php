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
use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleItem;
use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleItemRepository;
use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleUser;
use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleUserRepository;
use Sulu\Bundle\CommunityBundle\Manager\RegistrationRuleItemManager;
use Sulu\Bundle\CommunityBundle\Manager\RegistrationRuleItemManagerInterface;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handles user confirmations for administrators.
 */
class RegistrationRuleConfirmationController extends AbstractController
{
    /**
     * Confirms user with given token.
     */
    public function confirmAction(Request $request): Response
    {
        /** @var string $token */
        $token = $request->get('token');

        /** @var RegistrationRuleUser|null $registrationRuleUser */
        $registrationRuleUser = $this->getRegistrationRuleUserRepository()->findByToken($token);

        if (null === $registrationRuleUser) {
            throw new NotFoundHttpException();
        }

        $registrationRuleUser->confirm();
        $this->getEntityManager()->flush();

        $communityManager = $this->getCommunityManager($registrationRuleUser->getWebspaceKey());

        /** @var User $user */
        $user = $registrationRuleUser->getUser();
        $communityManager->sendEmails(Configuration::TYPE_REGISTRATION_RULE_CONFIRMED, $user);

        return $this->renderTemplate(
            Configuration::TYPE_REGISTRATION_RULE_CONFIRMED,
            ['user' => $registrationRuleUser->getUser()]
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

        /** @var RegistrationRuleUser|null $registrationRuleUser */
        $registrationRuleUser = $this->getRegistrationRuleUserRepository()->findByToken($token);

        if (null === $registrationRuleUser) {
            throw new NotFoundHttpException();
        }

        /** @var User $user */
        $user = $registrationRuleUser->getUser();
        $registrationRuleUser->deny();

        $communityManager = $this->getCommunityManager($registrationRuleUser->getWebspaceKey());
        if (true === $communityManager->getConfigTypeProperty(
            Configuration::TYPE_REGISTRATION_RULE_DENIED,
            Configuration::DELETE_USER
        )
        ) {
            $entityManager->remove($user->getContact());
            $entityManager->remove($user);
            $entityManager->remove($registrationRuleUser);
        }

        /** @var RegistrationRuleItem|null $item */
        $item = $this->getRegistrationRuleItemRepository()->findOneBy(['pattern' => $user->getEmail()]);

        if (!$item) {
            $item = $this->getRegistrationRuleItemManager()->create();
        }

        /** @var string $email */
        $email = $user->getEmail();

        $item->setType(RegistrationRuleItem::TYPE_BLOCK)
            ->setPattern($email);

        $entityManager->flush();

        $communityManager->sendEmails(Configuration::TYPE_REGISTRATION_RULE_DENIED, $user);

        return $this->renderTemplate(
            Configuration::TYPE_REGISTRATION_RULE_DENIED,
            ['user' => $registrationRuleUser->getUser()]
        );
    }

    protected function getRegistrationRuleUserRepository(): RegistrationRuleUserRepository
    {
        return $this->container->get('sulu_community.registration_rule.user_repository');
    }

    protected function getRegistrationRuleItemRepository(): RegistrationRuleItemRepository
    {
        return $this->container->get('sulu_community.registration_rule.item_repository');
    }

    protected function getRegistrationRuleItemManager(): RegistrationRuleItemManager
    {
        return $this->container->get('sulu_community.registration_rule.item_manager');
    }

    /**
     * @return array<string|int, string>
     */
    public static function getSubscribedServices(): array
    {
        $subscribedServices = parent::getSubscribedServices();

        $subscribedServices['sulu_community.registration_rule.user_repository'] = RegistrationRuleUserRepository::class;
        $subscribedServices['sulu_community.registration_rule.item_repository'] = RegistrationRuleItemRepository::class;
        $subscribedServices['sulu_community.registration_rule.item_manager'] = RegistrationRuleItemManagerInterface::class;

        return $subscribedServices;
    }
}
