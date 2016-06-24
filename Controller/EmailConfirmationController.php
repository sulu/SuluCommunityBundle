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
 * Handle email confirmation.
 */
class EmailConfirmationController extends AbstractController
{
    const TYPE = Configuration::TYPE_EMAIL_CONFIRMATION;

    /**
     * Overwrite user email with contact email.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $repository = $this->get('sulu_community.email_confirmation.repository');

        $success = false;
        $token = $repository->findByToken($request->get('token'));

        if ($token !== null) {
            $user = $token->getUser();
            $user->setEmail($user->getContact()->getMainEmail());
            $this->get('doctrine.orm.entity_manager')->remove($token);
            $this->saveEntities();

            $success = true;
        }

        return $this->renderTemplate(self::TYPE, ['success' => $success]);
    }
}
