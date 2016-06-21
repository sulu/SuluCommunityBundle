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
use Sulu\Bundle\CommunityBundle\EventListener\CompletionListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles the completion page.
 */
class CompletionController extends AbstractController
{
    const TYPE = Configuration::TYPE_COMPLETION;

    /**
     * Handle registration form.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $user = $this->getUser();

        if (!$user) {
            throw new HttpException(403, 'You need to be logged in for completion form.');
        }

        $communityManager = $this->getCommunityManager($this->getWebspaceKey());

        // Create Form
        $form = $this->createForm(
            $communityManager->getConfigTypeProperty(self::TYPE, Configuration::FORM_TYPE),
            $user,
            $communityManager->getConfigTypeProperty(self::TYPE, Configuration::FORM_TYPE_OPTIONS)
        );

        $form->handleRequest($request);
        $success = false;

        // Handle Form Success
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            // Completion User
            $communityManager->completion($user);

            // Redirect
            $session = $request->getSession();
            $redirectTo = $session->get(CompletionListener::SESSION_STORE);

            if (!$redirectTo) {
                $redirectTo = $communityManager->getConfigTypeProperty(self::TYPE, Configuration::REDIRECT_TO);
            } else {
                $session->remove(CompletionListener::SESSION_STORE);
            }

            if ($redirectTo) {
                return $this->redirect($redirectTo);
            }

            $success = true;
        }

        return $this->renderTemplate(
            self::TYPE,
            [
                'form' => $form->createView(),
                'success' => $success,
            ]
        );
    }
}
