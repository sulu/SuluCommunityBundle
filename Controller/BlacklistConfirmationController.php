<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Controller;

use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles user confirmations for administrators.
 */
class BlacklistConfirmationController extends AbstractController
{
    /**
     * Confirms user with given token.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function confirmAction(Request $request)
    {
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $repository = $this->get('sulu_community.blacklisting.user_repository');

        $blacklistUser = $repository->findByToken($request->get('token'));

        if (null === $blacklistUser) {
            return new Response(null, 404);
        }

        $blacklistUser->confirm();
        $entityManager->flush();

        $communityManager = $this->getCommunityManager($blacklistUser->getWebspaceKey());
        $communityManager->sendEmails(Configuration::TYPE_REGISTRATION, $blacklistUser->getUser());

        return $this->renderTemplate(
            Configuration::TYPE_BLACKLISTED,
            ['user' => $blacklistUser->getUser(), 'confirmed' => true]
        );
    }

    /**
     * Denies user with given token.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function denyAction(Request $request)
    {
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $repository = $this->get('sulu_community.blacklisting.user_repository');

        $blacklistUser = $repository->findByToken($request->get('token'));

        if (null === $blacklistUser) {
            return new Response(null, 404);
        }

        $blacklistUser->deny();

        $communityManager = $this->getCommunityManager($blacklistUser->getWebspaceKey());
        if (true === $communityManager->getConfigTypeProperty(Configuration::TYPE_DENIED, Configuration::DELETE_USER)) {
            $entityManager->remove($blacklistUser->getUser());
            $entityManager->remove($blacklistUser);
        }

        $entityManager->flush();

        $communityManager->sendEmails(Configuration::TYPE_DENIED, $blacklistUser->getUser());

        return $this->renderTemplate(
            Configuration::TYPE_BLACKLISTED,
            ['user' => $blacklistUser->getUser(), 'confirmed' => true]
        );
    }
}
