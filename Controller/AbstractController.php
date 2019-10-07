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
use Sulu\Bundle\CommunityBundle\Manager\CommunityManagerInterface;
use Sulu\Bundle\ContactBundle\Entity\Address;
use Sulu\Bundle\ContactBundle\Entity\AddressType;
use Sulu\Bundle\ContactBundle\Entity\ContactAddress;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Contains helper function for all controllers.
 */
abstract class AbstractController extends Controller
{
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
    protected function getCommunityManager(string $webspaceKey): CommunityManagerInterface
    {
        return $this->get('sulu_community.community_manager.registry')->get($webspaceKey);
    }

    /**
     * Returns current webspace key.
     *
     * @return string
     */
    protected function getWebspaceKey(): string
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
    protected function setUserPasswordAndSalt(User $user, FormInterface $form): User
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
     * @return bool
     */
    protected function checkAutoLogin(string $type): bool
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
     * @param mixed[] $data
     *
     * @return Response
     */
    protected function renderTemplate(string $type, array $data = []): Response
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
    protected function saveEntities(): void
    {
        $this->get('doctrine.orm.entity_manager')->flush();
    }

    /**
     * Set Sulu template attributes.
     *
     * @param mixed[] $custom
     *
     * @return mixed[]
     */
    private function getTemplateAttributes(array $custom = []): array
    {
        return $this->get('sulu_website.resolver.template_attribute')->resolve($custom);
    }

    /**
     * {@inheritdoc}
     *
     * @return User
     */
    public function getUser(): ?User
    {
        $user = parent::getUser();

        if (!$user instanceof User) {
            throw new HttpException(403);
        }

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
    private function addAddress(User $user): void
    {
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $contact = $user->getContact();

        $address = new Address();
        $address->setPrimaryAddress(true);
        $address->setNote('');
        /** @var AddressType $addressType */
        $addressType = $entityManager->getReference(AddressType::class, 1);
        $address->setAddressType($addressType);
        $contactAddress = new ContactAddress();
        $contactAddress->setMain(true);
        $contactAddress->setAddress($address);
        $contactAddress->setContact($contact);

        $contact->addContactAddress($contactAddress);
    }

    /**
     * {@inheritdoc}
     */
    public function render(string $view, array $parameters = [], Response $response = null): Response
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
    public function renderView(string $view, array $parameters = []): string
    {
        return parent::renderView($view, $this->getTemplateAttributes($parameters));
    }
}
