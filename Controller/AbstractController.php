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

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Sulu\Bundle\CommunityBundle\Manager\CommunityManagerInterface;
use Sulu\Bundle\CommunityBundle\Manager\CommunityManagerRegistryInterface;
use Sulu\Bundle\ContactBundle\Entity\Address;
use Sulu\Bundle\ContactBundle\Entity\AddressType;
use Sulu\Bundle\ContactBundle\Entity\ContactAddress;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\WebsiteBundle\Resolver\TemplateAttributeResolverInterface;
use Sulu\Component\Security\Authentication\SaltGenerator;
use Sulu\Component\Webspace\Analyzer\RequestAnalyzerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Contains helper function for all controllers.
 */
abstract class AbstractController extends SymfonyAbstractController
{
    /**
     * @var string|null
     */
    private $webspaceKey;

    /**
     * Returns current or specific communityManager.
     */
    protected function getCommunityManager(string $webspaceKey): CommunityManagerInterface
    {
        return $this->getCommunityManagerRegistry()->get($webspaceKey);
    }

    /**
     * Returns current webspace key.
     */
    protected function getWebspaceKey(): string
    {
        if (null === $this->webspaceKey) {
            $this->webspaceKey = $this->getRequestAnalyzer()->getWebspace()->getKey();
        }

        return $this->webspaceKey;
    }

    /**
     * Set Password and Salt by a Symfony Form.
     */
    protected function setUserPasswordAndSalt(User $user, FormInterface $form): User
    {
        /** @var string|null $plainPassword */
        $plainPassword = $form->get('plainPassword')->getData();
        if (null === $plainPassword) {
            return $user;
        }

        $salt = $user->getSalt();
        if (!$salt) {
            $salt = $this->getSaltGenerator()->getRandomSalt();
        }

        $user->setSalt($salt);
        $password = $this->getUserPasswordEncoder()->encodePassword($user, $plainPassword);
        $user->setPassword($password);

        return $user;
    }

    /**
     * Check if user should be logged in.
     */
    protected function checkAutoLogin(string $type): bool
    {
        /** @var bool */
        return $this->getCommunityManager($this->getWebspaceKey())->getConfigTypeProperty(
            $type,
            Configuration::AUTO_LOGIN
        );
    }

    /**
     * Render a specific type template.
     *
     * @param mixed[] $data
     */
    protected function renderTemplate(string $type, array $data = []): Response
    {
        /** @var string $template */
        $template = $this->getCommunityManager($this->getWebspaceKey())->getConfigTypeProperty(
            $type,
            Configuration::TEMPLATE
        );

        return $this->render(
            $template,
            $data
        );
    }

    /**
     * Save all persisted entities.
     */
    protected function saveEntities(): void
    {
        $this->getEntityManager()->flush();
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
        return $this->getTemplateAttributeResolver()->resolve($custom);
    }

    /**
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
     */
    private function addAddress(User $user): void
    {
        $contact = $user->getContact();

        $address = new Address();
        $address->setPrimaryAddress(true);
        $address->setNote('');
        /** @var AddressType $addressType */
        $addressType = $this->getEntityManager()->getReference(AddressType::class, 1);
        $address->setAddressType($addressType);
        $contactAddress = new ContactAddress();
        $contactAddress->setMain(true);
        $contactAddress->setAddress($address);
        $contactAddress->setContact($contact);

        $contact->addContactAddress($contactAddress);
    }

    /**
     * @param mixed[] $parameters
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
     * @param mixed[] $parameters
     */
    public function renderView(string $view, array $parameters = []): string
    {
        return parent::renderView($view, $this->getTemplateAttributes($parameters));
    }

    protected function getCommunityManagerRegistry(): CommunityManagerRegistryInterface
    {
        return $this->container->get('sulu_community.community_manager.registry');
    }

    protected function getRequestAnalyzer(): RequestAnalyzerInterface
    {
        return $this->container->get('sulu_core.webspace.request_analyzer');
    }

    protected function getSaltGenerator(): SaltGenerator
    {
        return $this->container->get('sulu_security.salt_generator');
    }

    protected function getUserPasswordEncoder(): UserPasswordEncoderInterface
    {
        return $this->container->get('security.password_encoder');
    }

    protected function getTemplateAttributeResolver(): TemplateAttributeResolverInterface
    {
        return $this->container->get('sulu_website.resolver.template_attribute');
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }

    /**
     * @return array<string|int, string>
     */
    public static function getSubscribedServices(): array
    {
        $subscribedServices = parent::getSubscribedServices();

        $subscribedServices['sulu_community.community_manager.registry'] = CommunityManagerRegistryInterface::class;
        $subscribedServices['sulu_core.webspace.request_analyzer'] = RequestAnalyzerInterface::class;
        $subscribedServices['sulu_security.salt_generator'] = SaltGenerator::class;
        $subscribedServices['security.password_encoder'] = UserPasswordEncoderInterface::class;
        $subscribedServices['sulu_website.resolver.template_attribute'] = TemplateAttributeResolverInterface::class;
        $subscribedServices['doctrine.orm.entity_manager'] = EntityManagerInterface::class;

        return $subscribedServices;
    }
}
