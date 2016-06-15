<?php

namespace Sulu\Bundle\CommunityBundle\Controller;

use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class RegistrationController extends AbstractController
{
    const TYPE = Configuration::TYPE_REGISTRATION;

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function indexAction(Request $request)
    {
        $communityManager = $this->getCommunityManager();

        $form = $this->createForm(
            $communityManager->getConfigTypeProperty(self::TYPE, Configuration::FORM_TYPE),
            $this->get('sulu.repository.user')->createNew(),
            $communityManager->getConfigTypeProperty(self::TYPE, Configuration::FORM_TYPE_OPTIONS)
        );

        $form->handleRequest($request);
        $success = false;

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();

            // Set Password and Salt
            $encoder = $this->get('security.encoder_factory')->getEncoder($user);
            $salt = $this->get('sulu_security.salt_generator')->getRandomSalt();
            $password = $encoder->encodePassword($form->get('plainPassword')->getData(), $salt);

            $user->setPassword($password);
            $user->setSalt($salt);

            if (!$user->getLocale()) {
                $user->setLocale($request->getLocale());
            }

            // Register User
            $user = $communityManager->register($user);

            // Login User
            if ($communityManager->getConfigTypeProperty(Configuration::TYPE_REGISTRATION, Configuration::AUTO_LOGIN)) {
                $communityManager->login($user, $request);
            }

            $success = true;

            // Redirect
            $redirectTo = $communityManager->getConfigTypeProperty(self::TYPE, Configuration::REDIRECT_TO);

            if ($redirectTo) {
                return $this->redirect($redirectTo);
            }
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
