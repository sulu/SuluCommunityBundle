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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handle registration page.
 */
class RegistrationController extends AbstractController
{
    use SaveMediaTrait {
        getSubscribedServices as getSubscribedServicesOfSaveMediaTrait;
    }

    public const TYPE = Configuration::TYPE_REGISTRATION;

    /**
     * Handle registration form.
     */
    public function indexAction(Request $request): Response
    {
        $communityManager = $this->getCommunityManager($this->getWebspaceKey());

        // Create Form
        $form = $this->createForm(
            $communityManager->getConfigTypeProperty(self::TYPE, Configuration::FORM_TYPE),
            null,
            $communityManager->getConfigTypeProperty(self::TYPE, Configuration::FORM_TYPE_OPTIONS)
        );

        $form->handleRequest($request);
        $success = false;

        // Handle Form Success
        if ($form->isSubmitted() && $form->isValid()) {
            // Set Password and Salt
            $user = $this->setUserPasswordAndSalt($form->getData(), $form);

            if (!$user->getLocale()) {
                $user->setLocale($request->getLocale());
            }

            $this->saveMediaFields($form, $user, $request->getLocale());

            // Register User
            $user = $communityManager->register($user);

            // Save User
            $this->saveEntities();

            // Login User
            if ($this->checkAutoLogin(Configuration::TYPE_REGISTRATION)) {
                $communityManager->login($user, $request);
            }

            // Redirect
            $redirectTo = $communityManager->getConfigTypeProperty(self::TYPE, Configuration::REDIRECT_TO);

            if ($redirectTo) {
                return $this->redirect($redirectTo);
            }

            $success = true;
        }

        return $this->renderTemplate(
            self::TYPE,
            [
                'form' => $form,
                'success' => $success,
            ]
        );
    }

    /**
     * @return array<string|int, string>
     */
    public static function getSubscribedServices(): array
    {
        return \array_merge(parent::getSubscribedServices(), self::getSubscribedServicesOfSaveMediaTrait());
    }
}
