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

        // TODO remove this is for testing
        $user = $this->get('sulu.repository.user')->findOneBy(['username' => 'adsfasdf']);

        $token = new UsernamePasswordToken($user, null, 'example', $user->getRoles());
        $this->get('security.context')->setToken($token); //now the user is logged in

        $session = $request->getSession();
        $session->set('_security_'.'example', serialize($token));
        $session->save();

        //now dispatch the login event
        $event = new InteractiveLoginEvent($request, $token);
        $this->get('event_dispatcher')->dispatch('security.interactive_login', $event);
        // End TOD

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
            $user = $communityManager->register($form->getData());


            // Login User
            if ($user->getEnabled()
                && $communityManager->getConfigTypeProperty(Configuration::TYPE_REGISTRATION, Configuration::AUTO_LOGIN)
            ) {
                $communityManager->login($user, $request);
            }

            // Redirect
            return $this->redirect(
                $communityManager->getConfigTypeProperty(self::TYPE, Configuration::REDIRECT_TO)
            );
        }

        return $this->render(
            $communityManager->getConfigTypeProperty(self::TYPE, Configuration::FORM_TEMPLATE),
            [
                'form' => $form->createView(),
            ]
        );
    }
}
