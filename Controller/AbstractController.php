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

use Sulu\Bundle\CommunityBundle\Manager\CommunityManager;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractController extends Controller
{
    /**
     * @var CommunityManager
     */
    protected $communityManager;

    /**
     * @param string $webspaceKey
     *
     * @return CommunityManager
     */
    public function getCommunityManager($webspaceKey = null)
    {
        if ($this->communityManager === null) {
            if (!$webspaceKey) {
                $webspaceKey = $this->get('sulu_core.webspace.request_analyzer')->getWebspace()->getKey();
            }

            $this->communityManager = $this->get(sprintf('sulu_community.%s.community_manager', $webspaceKey));
        }

        return $this->communityManager;
    }

    /**
     * @param User $user
     * @param Form $form
     *
     * @return User
     */
    public function setUserPasswordAndSalt(User $user, Form $form)
    {
        $salt = $this->get('sulu_security.salt_generator')->getRandomSalt();
        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
        $password = $encoder->encodePassword($form->get('plainPassword')->getData(), $salt);

        $user->setPassword($password);
        $user->setSalt($salt);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function render($view, array $parameters = [], Response $response = null)
    {
        return parent::render(
            $view,
            $this->getTemplateAttributes($parameters),
            $response
        );
    }

    /**
     * {@inheritdoc}
     */
    public function renderView($view, array $parameters = [])
    {
        return parent::renderView($view, $this->getTemplateAttributes($parameters));
    }

    /**
     * @param array $custom
     *
     * @return array
     */
    private function getTemplateAttributes($custom = [])
    {
        $defaults = [
            'isCommunityTemplate' => true,
            'extension' => [
                'excerpt' => [
                ],
                'seo' => [
                ],
            ],
            'content' => [],
            'shadowBaseLocale' => null,
        ];

        $requestAnalyzer = $this->get('sulu_core.webspace.request_analyzer');

        $default = array_merge(
            $defaults,
            $this->get('sulu_website.resolver.request_analyzer')->resolve($requestAnalyzer)
        );

        if (!isset($custom['urls'])) {
            $router = $this->get('router');
            $request = $this->get('request_stack')->getCurrentRequest();
            $urls = [];

            if ($request->get('_route')) {
                foreach ($requestAnalyzer->getWebspace()->getLocalizations() as $localization) {
                    $url = $router->generate(
                        $request->get('_route'),
                        $request->get('_route_params')
                    );

                    // will remove locale because it will be added automatically
                    if (preg_match('/^\/[a-z]{2}(-[a-z]{2})?+\/(.*)/', $url)) {
                        $url = substr($url, strlen($localization->getLocale()) + 1);
                    }

                    $urls[$localization->getLocale()] = $url;
                }
            }

            $custom['urls'] = $urls;
        }

        return array_merge(
            $default,
            $custom
        );
    }
}
