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
use Sulu\Bundle\CommunityBundle\Manager\CommunityManagerInterface;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contains helper function for all controllers.
 */
abstract class AbstractController extends Controller
{
    /**
     * @var CommunityManagerInterface[]
     */
    private $communityManagers;

    /**
     * @var string
     */
    private $webspaceKey;

    /**
     * Returns current or specific communityManager.
     *
     * @param string $webspaceKey
     *
     * @return CommunityManagerInterface
     */
    protected function getCommunityManager($webspaceKey)
    {
        if (!isset($this->communityManagers[$webspaceKey])) {
            $this->communityManagers[$webspaceKey] = $this->get(
                sprintf('sulu_community.%s.community_manager', $webspaceKey)
            );
        }

        return $this->communityManagers[$webspaceKey];
    }

    /**
     * Returns current webspace key.
     *
     * @return string
     */
    protected function getWebspaceKey()
    {
        if ($this->webspaceKey === null) {
            return $this->get('sulu_core.webspace.request_analyzer')->getWebspace()->getKey();
        }

        return $this->webspaceKey;
    }

    /**
     * Set Password and Salt by a Symfony Form.
     *
     * @param User $user
     * @param Form $form
     *
     * @return User
     */
    protected function setUserPasswordAndSalt(User $user, Form $form)
    {
        $salt = $this->get('sulu_security.salt_generator')->getRandomSalt();
        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
        $password = $encoder->encodePassword($form->get('plainPassword')->getData(), $salt);

        $user->setPassword($password);
        $user->setSalt($salt);

        return $user;
    }

    /**
     * Check if user should be logged in.
     *
     * @param string $type
     *
     * @return string
     */
    protected function checkAutoLogin($type)
    {
        return $this->getCommunityManager($this->getWebspaceKey())->getConfigTypeProperty(
            $type,
            Configuration::AUTO_LOGIN
        );
    }

    /**
     * Render a specific type template.
     *
     * @param string $type
     * @param array $data
     *
     * @return Response
     */
    protected function renderTemplate($type, $data)
    {
        return $this->render(
            $this->getCommunityManager($this->getWebspaceKey())->getConfigTypeProperty(
                $type,
                Configuration::TEMPLATE
            ),
            $data
        );
    }

    /**
     * Save all persisted entities.
     */
    protected function saveEntities()
    {
        $this->get('doctrine.orm.entity_manager')->flush();
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
     * Set Sulu template attributes.
     *
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
