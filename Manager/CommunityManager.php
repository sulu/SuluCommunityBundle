<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Manager;

use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Sulu\Bundle\CommunityBundle\Event\CommunityEvent;
use Sulu\Bundle\CommunityBundle\Mail\Mail;
use Sulu\Bundle\CommunityBundle\Mail\MailFactoryInterface;
use Sulu\Bundle\ContactBundle\Entity\ContactRepository;
use Sulu\Bundle\SecurityBundle\Entity\BaseUser;
use Sulu\Bundle\SecurityBundle\Entity\RoleRepository;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\SecurityBundle\Entity\UserRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Handles registration, confirmation, password reset and forget.
 */
class CommunityManager implements CommunityManagerInterface
{
    const EVENT_REGISTERED = 'sulu.community.registered';
    const EVENT_CONFIRMED = 'sulu.community.confirmed';
    const EVENT_PASSWORD_FORGOT = 'sulu.community.password_forgot';
    const EVENT_PASSWORD_RESETED = 'sulu.community.password_reseted';
    const EVENT_COMPLETED = 'sulu.community.completed';
    const EVENT_SAVE_PROFILE = 'sulu.community.save_profile';

    /**
     * @var array
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
     * @param array $config
     * @param string $webspaceKey
     * @param EventDispatcherInterface $eventDispatcher
     * @param TokenStorageInterface $tokenStorage
     * @param UserManagerInterface $userManager
     * @param MailFactoryInterface $mailFactory
     */
    public function __construct(
        array $config,
        $webspaceKey,
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

    /**
     * {@inheritdoc}
     */
    public function getWebspaceKey()
    {
        return $this->webspaceKey;
    }

    /**
     * {@inheritdoc}
     */
    public function register(User $user)
    {
        // User need locale
        if (null === $user->getLocale()) {
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
        $event = new CommunityEvent($user, $this->config);
        $this->eventDispatcher->dispatch(self::EVENT_REGISTERED, $event);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function completion(User $user)
    {
        // Event
        $event = new CommunityEvent($user, $this->config);
        $this->eventDispatcher->dispatch(self::EVENT_COMPLETED, $event);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function login(User $user, Request $request)
    {
        if (!$user->getEnabled()) {
            return null;
        }

        $token = new UsernamePasswordToken(
            $user,
            null,
            $this->getConfigProperty(Configuration::FIREWALL),
            $user->getRoles()
        );

        $this->tokenStorage->setToken($token);

        $event = new InteractiveLoginEvent($request, $token);
        $this->eventDispatcher->dispatch('security.interactive_login', $event);

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function confirm($token)
    {
        $user = $this->userManager->findByConfirmationKey($token);

        if (!$user) {
            return null;
        }

        // Remove Confirmation Key
        $user->setConfirmationKey(null);
        $user->setEnabled($this->getConfigTypeProperty(Configuration::TYPE_CONFIRMATION, Configuration::ACTIVATE_USER));

        // Event
        $event = new CommunityEvent($user, $this->config);
        $this->eventDispatcher->dispatch(self::EVENT_CONFIRMED, $event);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function passwordForget($emailUsername)
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
        $event = new CommunityEvent($user, $this->config);
        $this->eventDispatcher->dispatch(self::EVENT_PASSWORD_FORGOT, $event);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function passwordReset(User $user)
    {
        $user->setPasswordResetTokenExpiresAt(null);
        $user->setPasswordResetToken(null);
        $user->setEnabled(true);

        // Event
        $event = new CommunityEvent($user, $this->config);
        $this->eventDispatcher->dispatch(self::EVENT_PASSWORD_RESETED, $event);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function sendEmails($type, BaseUser $user)
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

    /**
     * {@inheritdoc}
     */
    public function saveProfile(BaseUser $user)
    {
        $this->userManager->updateUser($user);

        // Event
        $event = new CommunityEvent($user, $this->config);
        $this->eventDispatcher->dispatch(self::EVENT_SAVE_PROFILE, $event);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigProperty($property)
    {
        if (!array_key_exists($property, $this->config)) {
            throw new \Exception(
                sprintf(
                    'Property "%s" not found for webspace "%s" in Community Manager.',
                    $property,
                    $this->webspaceKey
                )
            );
        }

        return $this->config[$property];
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTypeProperty($type, $property)
    {
        if (!array_key_exists($type, $this->config) || !array_key_exists($property, $this->config[$type])) {
            throw new \Exception(
                sprintf(
                    'Property "%s" from type "%s" not found for webspace "%s" in Community Manager.',
                    $property,
                    $type,
                    $this->webspaceKey
                )
            );
        }

        return $this->config[$type][$property];
    }
}
