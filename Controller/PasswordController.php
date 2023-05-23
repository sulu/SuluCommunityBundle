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
use Sulu\Bundle\CommunityBundle\Manager\UserManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles password forget and reset pages.
 */
class PasswordController extends AbstractController
{
    /**
     * Handles the forget form.
     */
    public function forgetAction(Request $request): Response
    {
        $communityManager = $this->getCommunityManager($this->getWebspaceKey());

        // Create Form
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
            // Handle form success
            $emailUsername = $form->get('email_username')->getData();

            // Handle Password forget
            $communityManager->passwordForget($emailUsername);

            // Save User
            $this->saveEntities();

            // Redirect
            $redirectTo = $communityManager->getConfigTypeProperty(
                Configuration::TYPE_PASSWORD_FORGET,
                Configuration::REDIRECT_TO
            );

            if ($redirectTo) {
                return $this->redirect($redirectTo);
            }

            $success = true;
        }

        return $this->renderTemplate(
            Configuration::TYPE_PASSWORD_FORGET,
            [
                'form' => $form,
                'success' => $success,
            ]
        );
    }

    /**
     * Handles the reset password form.
     */
    public function resetAction(Request $request, string $token): Response
    {
        $communityManager = $this->getCommunityManager($this->getWebspaceKey());

        // Check valid token
        $user = $this->getUserManager()->findByPasswordResetToken($token);

        if (!$user) {
            return $this->renderTemplate(
                Configuration::TYPE_PASSWORD_RESET,
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
            $user = $communityManager->passwordReset($user);

            // Save User
            $this->saveEntities();

            // Login
            if ($this->checkAutoLogin(Configuration::TYPE_PASSWORD_RESET)) {
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

        return $this->renderTemplate(
            Configuration::TYPE_PASSWORD_RESET,
            [
                'form' => $form,
                'success' => $success,
            ]
        );
    }

    protected function getUserManager(): UserManagerInterface
    {
        return $this->container->get('sulu_community.user_manager');
    }

    /**
     * @return array<string|int, string>
     */
    public static function getSubscribedServices(): array
    {
        $subscribedServices = parent::getSubscribedServices();

        $subscribedServices['sulu_community.user_manager'] = UserManagerInterface::class;

        return $subscribedServices;
    }
}
