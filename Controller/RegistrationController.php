<?php

namespace Sulu\Bundle\CommunityBundle\Controller;

use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;

class RegistrationController extends AbstractController
{
    const TYPE = Configuration::TYPE_REGISTRATION;

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $communityManager = $this->getCommunityManager();

        // Create Form
        $form = $this->createForm(
            $communityManager->getConfigTypeProperty(self::TYPE, Configuration::FORM_TYPE),
            $this->get('sulu.repository.user')->createNew(),
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

            // Register User
            $user = $communityManager->register($user);

            // Login User
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
                'form' => $form->createView(),
                'success' => $success,
            ]
        );
    }
}
