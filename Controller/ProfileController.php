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
 * Handle profile page.
 */
class ProfileController extends AbstractController
{
    use SaveMediaTrait {
        getSubscribedServices as getSubscribedServicesOfSaveMediaTrait;
    }

    public const TYPE = Configuration::TYPE_PROFILE;

    /**
     * Handle profile form.
     */
    public function indexAction(Request $request): Response
    {
        $communityManager = $this->getCommunityManager($this->getWebspaceKey());

        $user = $this->getUser();

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
            // Set Password and Salt
            $user = $this->setUserPasswordAndSalt($form->getData(), $form);

            if (!$user->getLocale()) {
                $user->setLocale($request->getLocale());
            }

            $this->saveMediaFields($form, $user, $request->getLocale());

            // Register User
            $communityManager->saveProfile($user);
            $this->saveEntities();

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
                'form' => $form->createView(),
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
