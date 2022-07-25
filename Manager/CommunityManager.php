<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Manager;

use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Sulu\Bundle\CommunityBundle\Event\UserCompletedEvent;
use Sulu\Bundle\CommunityBundle\Event\UserConfirmedEvent;
use Sulu\Bundle\CommunityBundle\Event\UserPasswordForgotEvent;
use Sulu\Bundle\CommunityBundle\Event\UserPasswordResetedEvent;
use Sulu\Bundle\CommunityBundle\Event\UserProfileSavedEvent;
use Sulu\Bundle\CommunityBundle\Event\UserRegisteredEvent;
use Sulu\Bundle\CommunityBundle\Mail\Mail;
use Sulu\Bundle\CommunityBundle\Mail\MailFactoryInterface;
use Sulu\Bundle\ContactBundle\Entity\ContactRepository;
use Sulu\Bundle\SecurityBundle\Entity\RoleRepository;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\SecurityBundle\Entity\UserRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * Handles registration, confirmation, password reset and forget.
 *
 * @phpstan-import-type Config from CommunityManagerInterface
 * @phpstan-import-type TypeConfigProperties from CommunityManagerInterface
 */
class CommunityManager implements CommunityManagerInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var string
     */
    protected $webspaceKey;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var RoleRepository
     */
    protected $roleRepository;

    /**
     * @var ContactRepository
     */
    protected $contactRepository;

    /**
     * @var MailFactoryInterface
     */
    protected $mailFactory;

    /**
     * @param Config $config
     */
    public function __construct(
        array $config,
        string $webspaceKey,
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        UserManagerInterface $userManager,
        MailFactoryInterface $mailFactory
    ) {
        $this->config = $config;
        $this->webspaceKey = $webspaceKey;
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
        $this->userManager = $userManager;
        $this->mailFactory = $mailFactory;
    }

    public function getWebspaceKey(): string
    {
        return $this->webspaceKey;
    }

    public function register(User $user): User
    {
        /** @var string|null $userLocale */
        $userLocale = $user->getLocale();

        // User need locale
        if (null === $userLocale) {
            $user->setLocale('en');
        }

        // Enable User by config
        $user->setEnabled(
            $this->getConfigTypeProperty(Configuration::TYPE_REGISTRATION, Configuration::ACTIVATE_USER)
        );

        // Create Confirmation Key
        $user->setConfirmationKey($this->userManager->getUniqueToken('confirmationKey'));

        // Create User
        $this->userManager->createUser($user, $this->webspaceKey, $this->getConfigProperty(Configuration::ROLE));

        // Event
        $event = new UserRegisteredEvent($user, $this->config);
        $this->eventDispatcher->dispatch($event);

        return $user;
    }

    public function completion(User $user): User
    {
        // Event
        $event = new UserCompletedEvent($user, $this->config);
        $this->eventDispatcher->dispatch($event);

        return $user;
    }

    public function login(User $user, Request $request): void
    {
        if (!$user->getEnabled()) {
            return;
        }

        $token = new UsernamePasswordToken(
            $user,
            null,
            $this->getConfigProperty(Configuration::FIREWALL),
            $user->getRoles()
        );

        $this->tokenStorage->setToken($token);

        $event = new InteractiveLoginEvent($request, $token);
        $this->eventDispatcher->dispatch($event, SecurityEvents::INTERACTIVE_LOGIN);
    }

    public function confirm(string $token): ?User
    {
        $user = $this->userManager->findByConfirmationKey($token);

        if (!$user) {
            return null;
        }

        // Remove Confirmation Key
        $user->setConfirmationKey(null);
        $user->setEnabled($this->getConfigTypeProperty(Configuration::TYPE_CONFIRMATION, Configuration::ACTIVATE_USER));

        // Event
        $event = new UserConfirmedEvent($user, $this->config);
        $this->eventDispatcher->dispatch($event);

        return $user;
    }

    public function passwordForget(string $emailUsername): ?User
    {
        $user = $this->userManager->findUser($emailUsername);

        if (!$user) {
            return null;
        }

        $user->setPasswordResetToken($this->userManager->getUniqueToken('passwordResetToken'));
        $expireDateTime = (new \DateTime())->add(new \DateInterval('PT24H'));
        $user->setPasswordResetTokenExpiresAt($expireDateTime);
        $user->setPasswordResetTokenEmailsSent(
            $user->getPasswordResetTokenEmailsSent() + 1
        );

        // Event
        $event = new UserPasswordForgotEvent($user, $this->config);
        $this->eventDispatcher->dispatch($event);

        return $user;
    }

    public function passwordReset(User $user): User
    {
        $user->setPasswordResetTokenExpiresAt(null);
        $user->setPasswordResetToken(null);
        $user->setEnabled(true);

        // Event
        $event = new UserPasswordResetedEvent($user, $this->config);
        $this->eventDispatcher->dispatch($event);

        return $user;
    }

    public function sendEmails(string $type, User $user): void
    {
        $this->mailFactory->sendEmails(
            Mail::create(
                $this->getConfigProperty(Configuration::EMAIL_FROM),
                $this->getConfigProperty(Configuration::EMAIL_TO),
                $this->getConfigTypeProperty($type, Configuration::EMAIL)
            ),
            $user
        );
    }

    public function saveProfile(User $user): ?User
    {
        $this->userManager->updateUser($user);

        // Event
        $event = new UserProfileSavedEvent($user, $this->config);
        $this->eventDispatcher->dispatch($event);

        return $user;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getConfigProperty(string $property)
    {
        if (!\array_key_exists($property, $this->config)) {
            throw new \InvalidArgumentException(\sprintf('Property "%s" not found for webspace "%s" in Community Manager.', $property, $this->webspaceKey));
        }

        return $this->config[$property];
    }

    public function getConfigTypeProperty(string $type, string $property)
    {
        if (!\array_key_exists($type, $this->config) || !\array_key_exists($property, $this->config[$type])) {
            throw new \InvalidArgumentException(\sprintf('Property "%s" from type "%s" not found for webspace "%s" in Community Manager.', $property, $type, $this->webspaceKey));
        }

        return $this->config[$type][$property];
    }
}
