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
 * Handles the confirmation page.
 */
class ConfirmationController extends AbstractController
{
    const TYPE = Configuration::TYPE_CONFIRMATION;

    /**
     * Confirm user email address by token.
     *
     * @param Request $request
     * @param string $token
     *
     * @return Response
     */
    public function indexAction(Request $request, $token)
    {
        $communityManager = $this->getCommunityManager($this->getWebspaceKey());

        $success = false;

        // Confirm user by token
        if ($user = $communityManager->confirm($token)) {
            // Save User
            $this->saveEntities();

            // Login
            if ($this->checkAutoLogin(Configuration::TYPE_CONFIRMATION)) {
                $communityManager->login($user, $request);
            }

            // Redirect
            $redirectTo = $communityManager->getConfigTypeProperty(self::TYPE, Configuration::REDIRECT_TO);

            if ($redirectTo) {
                if (strpos($redirectTo, '/') === 0) {
                    $url = str_replace('{localization}', $request->getLocale(), $redirectTo);
                } else {
                    $url = $this->get('router')->generate($redirectTo);
                }

                return $this->redirect($url);
            }

            $success = true;
        }

        return $this->renderTemplate(Configuration::TYPE_CONFIRMATION, ['success' => $success]);
    }
}
