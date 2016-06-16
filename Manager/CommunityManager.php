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

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Sulu\Bundle\CommunityBundle\Event\CommunityEvent;
use Sulu\Bundle\ContactBundle\Entity\ContactRepository;
use Sulu\Bundle\SecurityBundle\Entity\RoleRepository;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\SecurityBundle\Entity\UserRepository;
use Sulu\Bundle\SecurityBundle\Entity\UserRole;
use Sulu\Bundle\SecurityBundle\Util\TokenGeneratorInterface;
use Sulu\Component\Security\Authentication\RoleInterface;
use Sulu\Component\Security\Authentication\UserInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Handles registration, confirmation, password reset and forget.
 */
class CommunityManager
{
    const EVENT_REGISTERED = 'sulu.community.registered';
    const EVENT_CONFIRMED = 'sulu.community.confirmed';
    const EVENT_PASSWORD_FORGOT = 'sulu.community.password_forgot';
    const EVENT_PASSWORD_RESETED = 'sulu.community.password_reseted';

    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $webspaceKey;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var TokenGeneratorInterface
     */
    protected $tokenGenerator;

    /**
     * @var WebspaceManagerInterface
     */
    protected $webspaceManager;

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
     * CommunityManager constructor.
     *
     * @param array $config
     * @param string $webspaceKey
     * @param EntityManagerInterface $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param TokenStorageInterface $tokenStorage
     * @param TokenGeneratorInterface $tokenGenerator
     * @param WebspaceManagerInterface $webspaceManager
     * @param UserRepository $userRepository
     * @param RoleRepository $roleRepository
     * @param ContactRepository $contactRepository
     */
    public function __construct(
        array $config,
        $webspaceKey,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        TokenGeneratorInterface $tokenGenerator,
        WebspaceManagerInterface $webspaceManager,
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        ContactRepository $contactRepository
    ) {
        $this->config = $config;
        $this->webspaceKey = $webspaceKey;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
        $this->tokenGenerator = $tokenGenerator;
        $this->webspaceManager = $webspaceManager;
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
        $this->contactRepository = $contactRepository;
    }

    /**
     * @return string
     */
    public function getWebspaceKey()
    {
        return $this->webspaceKey;
    }

    /**
     * @param User $user
     *
     * @return User
     */
    public function register(User $user)
    {
        // User need locale
        if ($user->getLocale() === null) {
            $user->setLocale('en');
        }

        // Enable User by config
        $user->setEnabled(
            $this->getConfigTypeProperty(Configuration::TYPE_REGISTRATION, Configuration::ACTIVATE_USER)
        );

        // Create Confirmation Key
        $user->setConfirmationKey($this->getUniqueKey('confirmationKey'));

        // User needs contact
        $contact = $user->getContact();

        if (!$contact) {
            $contact = $this->contactRepository->createNew();
            $user->setContact($contact);
        }

        if ($contact->getFirstName() === null) {
            $contact->setFirstName('');
        }

        if ($contact->getLastName() === null) {
            $contact->setLastName('');
        }

        // Create and Add User Role
        $userRole = $this->createUserRole($user);
        $user->addUserRole($userRole);

        // Save Entity
        $this->entityManager->persist($userRole);
        $this->entityManager->persist($contact);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Event
        $event = new CommunityEvent($user, $this->config);
        $this->eventDispatcher->dispatch(self::EVENT_REGISTERED, $event);

        return $user;
    }

    /**
     * @param User $user
     * @param Request $request
     *
     * @return UsernamePasswordToken
     *
     * @throws \Exception
     */
    public function login(User $user, Request $request)
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
        $this->eventDispatcher->dispatch('security.interactive_login', $event);

        return $token;
    }

    /**
     * @param $token
     *
     * @return User
     */
    public function confirm($token)
    {
        /** @var User $user */
        $user = $this->userRepository->findOneBy(['confirmationKey' => $token]);

        if (!$user) {
            return;
        }

        // Remove Confirmation Key
        $user->setConfirmationKey(null);
        $user->setEnabled($this->getConfigTypeProperty(Configuration::TYPE_CONFIRMATION, Configuration::ACTIVATE_USER));
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Event
        $event = new CommunityEvent($user, $this->config);
        $this->eventDispatcher->dispatch(self::EVENT_CONFIRMED, $event);

        return $user;
    }

    public function passwordForget($emailUsername)
    {
        /** @var User $user */
        $user = $this->userRepository->findUserByIdentifier($emailUsername);

        if (!$user) {
            return;
        }

        $user->setPasswordResetToken($this->getUniqueKey('passwordResetToken'));
        $expireDateTime = (new \DateTime())->add(new \DateInterval('PT24H'));
        $user->setPasswordResetTokenExpiresAt($expireDateTime);
        $user->setPasswordResetTokenEmailsSent(
            $user->getPasswordResetTokenEmailsSent() + 1
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Event
        $event = new CommunityEvent($user, $this->config);
        $this->eventDispatcher->dispatch(self::EVENT_PASSWORD_FORGOT, $event);

        return $user;
    }

    /**
     * @param $token
     *
     * @return User
     */
    public function loadUserByPasswordToken($token)
    {
        /** @var User $user */
        $user = $this->userRepository->findOneBy(['passwordResetToken' => $token]);
        if (!$user || $user->getPasswordResetTokenExpiresAt() < new \DateTime()) {
            return;
        }

        return $user;
    }

    /**
     * @param User $user
     *
     * @return User
     */
    public function resetPassword(User $user)
    {
        $user->setPasswordResetTokenExpiresAt(null);
        $user->setPasswordResetToken(null);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Event
        $event = new CommunityEvent($user, $this->config);
        $this->eventDispatcher->dispatch(self::EVENT_PASSWORD_RESETED, $event);

        return $user;
    }

    /**
     * @param UserInterface $user
     *
     * @return UserRole
     *
     * @throws \Exception
     */
    protected function createUserRole(UserInterface $user)
    {
        /** @var RoleInterface $role */
        $role = $this->roleRepository->findOneBy(['name' => $this->getConfigProperty(Configuration::ROLE)]);
        $userRole = new UserRole();

        $locales = [];

        foreach ($this->webspaceManager->findWebspaceByKey($this->webspaceKey)->getLocalizations() as $localization) {
            $locales[] = $localization->getLocale();
        }

        $userRole->setLocale(json_encode($locales));
        $userRole->setRole($role);
        $userRole->setUser($user);

        return $userRole;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param string $property
     *
     * @return string
     *
     * @throws \Exception
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
     * @param string $type
     * @param string $property
     *
     * @return string
     *
     * @throws \Exception
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

    /**
     * @return string
     */
    protected function getUniqueKey($field)
    {
        $token = $this->tokenGenerator->generateToken();
        $user = $this->userRepository->findOneBy([$field => $token]);

        if ($user) {
            return $this->getUniqueKey($field);
        }

        return $token;
    }
}
