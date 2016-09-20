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
use Sulu\Bundle\ContactBundle\Contact\ContactManager;
use Sulu\Bundle\ContactBundle\Entity\ContactRepository;
use Sulu\Bundle\ContactBundle\Entity\Email;
use Sulu\Bundle\ContactBundle\Entity\EmailType;
use Sulu\Bundle\SecurityBundle\Entity\RoleRepository;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\SecurityBundle\Entity\UserRepository;
use Sulu\Bundle\SecurityBundle\Entity\UserRole;
use Sulu\Bundle\SecurityBundle\Util\TokenGeneratorInterface;
use Sulu\Component\Security\Authentication\RoleInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

/**
 * Manage the community user entities.
 */
class UserManager implements UserManagerInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var WebspaceManagerInterface
     */
    protected $webspaceManager;

    /**
     * @var TokenGeneratorInterface
     */
    protected $tokenGenerator;

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
     * @var ContactRepository
     */
    protected $contactManager;

    /**
     * UserManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param WebspaceManagerInterface $webspaceManager
     * @param TokenGeneratorInterface $tokenGenerator
     * @param UserRepository $userRepository
     * @param RoleRepository $roleRepository
     * @param ContactRepository $contactRepository
     * @param ContactManger $contactManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        WebspaceManagerInterface $webspaceManager,
        TokenGeneratorInterface $tokenGenerator,
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        ContactRepository $contactRepository,
        ContactManager $contactManager
    ) {
        $this->entityManager = $entityManager;
        $this->webspaceManager = $webspaceManager;
        $this->tokenGenerator = $tokenGenerator;
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
        $this->contactRepository = $contactRepository;
        $this->contactManager = $contactManager;
    }

    /**
     * {@inheritdoc}
     */
    public function createUser(User $user, $webspaceKey, $roleName)
    {
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

        $emailType = $this->entityManager
            ->getRepository($this->entityManager->getReference(EmailType::class, 1))
            ->findAll();

        $contactEmail = new Email();
        $contactEmail->setEmail($user->getEmail());
        $contactEmail->setEmailType($emailType[0]);
        $contact->addEmail($contactEmail);
        $contact->setMainEmail($user->getEmail());

        // Create and Add User Role
        $userRole = $this->createUserRole($user, $webspaceKey, $roleName);
        $user->addUserRole($userRole);

        // Save Entity
        $this->entityManager->persist($userRole);
        $this->entityManager->persist($contactEmail);
        $this->entityManager->persist($contact);
        $this->entityManager->persist($user);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getUniqueToken($field)
    {
        $token = $this->tokenGenerator->generateToken();
        $user = $this->userRepository->findOneBy([$field => $token]);

        if ($user) {
            return $this->getUniqueToken($field);
        }

        return $token;
    }

    /**
     * Create a user roles add permissions for all webspace locales.
     *
     * @param User $user
     * @param string $webspaceKey
     * @param string $roleName
     *
     * @return UserRole
     */
    protected function createUserRole(User $user, $webspaceKey, $roleName)
    {
        /** @var RoleInterface $role */
        $role = $this->roleRepository->findOneBy(['name' => $roleName]);
        $userRole = new UserRole();

        $locales = [];

        foreach ($this->webspaceManager->findWebspaceByKey($webspaceKey)->getLocalizations() as $localization) {
            $locales[] = $localization->getLocale();
        }

        $userRole->setLocale(json_encode($locales));
        $userRole->setRole($role);
        $userRole->setUser($user);

        return $userRole;
    }

    /**
     * {@inheritdoc}
     */
    public function findByPasswordResetToken($token)
    {
        $user = $this->userRepository->findOneBy(['passwordResetToken' => $token]);

        if (!$user || $user->getPasswordResetTokenExpiresAt() < new \DateTime()) {
            return;
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function findByConfirmationKey($token)
    {
        return $this->userRepository->findOneBy(['confirmationKey' => $token]);
    }

    /**
     * {@inheritdoc}
     */
    public function findUser($identifier)
    {
        return $this->userRepository->findUserByIdentifier($identifier);
    }
}
