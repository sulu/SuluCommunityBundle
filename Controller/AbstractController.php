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

use Sulu\Bundle\CommunityBundle\DependencyInjection\CompilerPass\Normalizer;
use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Sulu\Bundle\CommunityBundle\Manager\CommunityManagerInterface;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
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
                sprintf('sulu_community.%s.community_manager', Normalizer::normalize($webspaceKey))
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
        if (null === $this->webspaceKey) {
            return $this->get('sulu_core.webspace.request_analyzer')->getWebspace()->getKey();
        }

        return $this->webspaceKey;
    }

    /**
     * Set Password and Salt by a Symfony Form.
     *
     * @param User $user
     * @param FormInterface $form
     *
     * @return User
     */
    protected function setUserPasswordAndSalt(User $user, FormInterface $form)
    {
        $plainPassword = $form->get('plainPassword')->getData();
        if (null === $plainPassword) {
            return $user;
        }

        $salt = $user->getSalt();
        if (!$salt) {
            $salt = $this->get('sulu_security.salt_generator')->getRandomSalt();
        }

        $user->setSalt($salt);
        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
        $password = $encoder->encodePassword($plainPassword, $salt);

        $user->setPassword($password);

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
    protected function renderTemplate($type, $data = [])
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
        return $this->get('sulu_website.resolver.template_attribute')->resolve($custom);
    }
}
