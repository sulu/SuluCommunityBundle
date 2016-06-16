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

class ConfirmationController extends AbstractController
{
    const TYPE = Configuration::TYPE_CONFIRMATION;

    /**
     * @param Request $request
     * @param string $token
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, $token)
    {
        $communityManager = $this->getCommunityManager();

        $success = false;

        if ($user = $communityManager->confirm($token)) {
            // Login
            if ($communityManager->getConfigTypeProperty(Configuration::TYPE_REGISTRATION, Configuration::AUTO_LOGIN)) {
                $communityManager->login($user, $request);
            }

            // Redirect
            $redirectTo = $communityManager->getConfigTypeProperty(self::TYPE, Configuration::REDIRECT_TO);

            if ($redirectTo) {
                return $this->redirect($redirectTo);
            }

            $success = true;
        }

        return $this->render(
            $communityManager->getConfigTypeProperty(self::TYPE, Configuration::TEMPLATE),
            [
                'success' => $success,
            ]
        );
    }
}
