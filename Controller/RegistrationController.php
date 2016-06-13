<?php

namespace Sulu\Bundle\CommunityBundle\Controller;

use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Symfony\Component\HttpFoundation\Request;

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
            $communityManager->getTypeConfig(self::TYPE, Configuration::FORM_TYPE),
            $this->get('sulu.repository.user')->createNew(),
            $communityManager->getTypeConfig(self::TYPE, Configuration::FORM_TYPE_OPTIONS)
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $communityManager->register($form->getData());
        }

        return $this->render(
            $communityManager->getTypeConfig(self::TYPE, Configuration::FORM_TEMPLATE),
            [
                'form' => $form->createView(),
            ]
        );
    }
}
