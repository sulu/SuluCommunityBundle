<?php

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
            $success = true;

            // Login
            if ($communityManager->getConfigTypeProperty(Configuration::TYPE_REGISTRATION, Configuration::AUTO_LOGIN)) {
                $communityManager->login($user, $request);
            }

            // Redirect
            $redirectTo = $communityManager->getConfigTypeProperty(self::TYPE, Configuration::REDIRECT_TO);

            if ($redirectTo) {
                return $this->redirect($redirectTo);
            }
        }

        return $this->render(
            $communityManager->getConfigTypeProperty(self::TYPE, Configuration::TEMPLATE),
            [
                'success' => $success,
            ]
        );
    }
}
