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
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles the completion page.
 */
class CompletionController extends AbstractController
{
    use SaveMediaTrait {
        getSubscribedServices as getSubscribedServicesOfSaveMediaTrait;
    }

    public const TYPE = Configuration::TYPE_COMPLETION;

    /**
     * Handle registration form.
     */
    public function indexAction(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw new HttpException(403, 'You need to be logged in for completion form.');
        }

        $communityManager = $this->getCommunityManager($this->getWebspaceKey());

        $formType = $communityManager->getConfigTypeProperty(self::TYPE, Configuration::FORM_TYPE);

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

            $this->saveMediaFields($form, $user, $request->getLocale());

            // Completion User
            $communityManager->completion($user);

            // Save User
            $this->saveEntities();

            // Redirect
            $redirectTo = $request->query->get('re');

            if (!$redirectTo) {
                $redirectTo = $communityManager->getConfigTypeProperty(self::TYPE, Configuration::REDIRECT_TO);
            }

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
