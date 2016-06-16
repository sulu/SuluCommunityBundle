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

class PasswordController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function forgetAction(Request $request)
    {
        $communityManager = $this->getCommunityManager();

        $form = $this->createForm(
            $communityManager->getConfigTypeProperty(Configuration::TYPE_PASSWORD_FORGET, Configuration::FORM_TYPE),
            [],
            $communityManager->getConfigTypeProperty(
                Configuration::TYPE_PASSWORD_FORGET,
                Configuration::FORM_TYPE_OPTIONS
            )
        );

        $form->handleRequest($request);
        $success = false;

        if ($form->isSubmitted() && $form->isValid()) {
            $emailUsername = $form->get('email_username')->getData();
            $user = $communityManager->passwordForget($emailUsername);

            $success = true;

            // Redirect
            $redirectTo = $communityManager->getConfigTypeProperty(
                Configuration::TYPE_PASSWORD_FORGET,
                Configuration::REDIRECT_TO
            );

            if ($redirectTo) {
                return $this->redirect($redirectTo);
            }
        }

        return $this->render(
            $communityManager->getConfigTypeProperty(Configuration::TYPE_PASSWORD_FORGET, Configuration::TEMPLATE),
            [
                'form' => $form->createView(),
                'success' => $success,
            ]
        );
    }

    /**
     * @param Request $request
     * @param string $token
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resetAction(Request $request, $token)
    {
        $communityManager = $this->getCommunityManager();

        // Check valid token
        $user = $communityManager->loadUserByPasswordToken($token);

        if (!$user) {
            return $this->render(
                $communityManager->getConfigTypeProperty(Configuration::TYPE_PASSWORD_RESET, Configuration::TEMPLATE),
                [
                    'form' => null,
                    'success' => false,
                ]
            );
        }

        // Create Form
        $form = $this->createForm(
            $communityManager->getConfigTypeProperty(Configuration::TYPE_PASSWORD_RESET, Configuration::FORM_TYPE),
            $user,
            $communityManager->getConfigTypeProperty(
                Configuration::TYPE_PASSWORD_RESET,
                Configuration::FORM_TYPE_OPTIONS
            )
        );

        $form->handleRequest($request);
        $success = false;

        // Handle Form Success
        if ($form->isSubmitted() && $form->isValid()) {
            // Set Password and Salt
            $user = $this->setUserPasswordAndSalt($form->getData(), $form);

            // Save User with new Password
            $user = $communityManager->resetPassword($user);

            // Login
            if ($communityManager->getConfigTypeProperty(Configuration::TYPE_PASSWORD_RESET, Configuration::AUTO_LOGIN)) {
                $communityManager->login($user, $request);
            }

            // Redirect
            $redirectTo = $communityManager->getConfigTypeProperty(
                Configuration::TYPE_PASSWORD_RESET,
                Configuration::REDIRECT_TO
            );

            if ($redirectTo) {
                return $this->redirect($redirectTo);
            }

            $success = true;
        }

        return $this->render(
            $communityManager->getConfigTypeProperty(Configuration::TYPE_PASSWORD_RESET, Configuration::TEMPLATE),
            [
                'form' => $form->createView(),
                'success' => $success,
            ]
        );
    }
}
