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
use Sulu\Bundle\ContactBundle\Entity\Address;
use Sulu\Bundle\ContactBundle\Entity\AddressType;
use Sulu\Bundle\ContactBundle\Entity\ContactAddress;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contains helper function for all controllers.
 */
abstract class AbstractController extends Controller
{
    use RenderCompatibilityTrait;

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
        return $this->get('sulu_community.community_manager.registry')->get($webspaceKey);
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
        $password = $this->get('security.password_encoder')->encodePassword($user, $plainPassword);
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

    /**
     * {@inheritdoc}
     *
     * @return User
     */
    public function getUser()
    {
        /** @var User $user */
        $user = parent::getUser();

        if (null === $user->getContact()->getMainAddress()) {
            // TODO this should be done by the form type not by the controller
            $this->addAddress($user);
        }

        return $user;
    }

    /**
     * Add address to user.
     *
     * @param User $user
     */
    private function addAddress(User $user)
    {
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $contact = $user->getContact();

        $address = new Address();
        $address->setPrimaryAddress(true);
        $address->setNote('');
        $address->setAddressType($entityManager->getRepository(AddressType::class)->find(1));
        $contactAddress = new ContactAddress();
        $contactAddress->setMain(1);
        $contactAddress->setAddress($address);
        $contactAddress->setContact($contact);

        $contact->addContactAddress($contactAddress);
    }
}
