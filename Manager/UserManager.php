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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Sulu\Bundle\ContactBundle\Contact\ContactManagerInterface;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Bundle\ContactBundle\Entity\ContactRepositoryInterface;
use Sulu\Bundle\ContactBundle\Entity\Email;
use Sulu\Bundle\ContactBundle\Entity\EmailType;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\SecurityBundle\Entity\UserRole;
use Sulu\Bundle\SecurityBundle\Util\TokenGeneratorInterface;
use Sulu\Component\Security\Authentication\RoleInterface;
use Sulu\Component\Security\Authentication\RoleRepositoryInterface;
use Sulu\Component\Security\Authentication\UserRepositoryInterface;
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
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @var RoleRepositoryInterface
     */
    protected $roleRepository;

    /**
     * @var ContactRepositoryInterface
     */
    protected $contactRepository;

    /**
     * @var ContactManagerInterface
     */
    protected $contactManager;

    /**
     * UserManager constructor.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        WebspaceManagerInterface $webspaceManager,
        TokenGeneratorInterface $tokenGenerator,
        UserRepositoryInterface $userRepository,
        RoleRepositoryInterface $roleRepository,
        ContactRepositoryInterface $contactRepository,
        ContactManagerInterface $contactManager
    ) {
        $this->entityManager = $entityManager;
        $this->webspaceManager = $webspaceManager;
        $this->tokenGenerator = $tokenGenerator;
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
        $this->contactRepository = $contactRepository;
        $this->contactManager = $contactManager;
    }

    public function createUser(User $user, string $webspaceKey, string $roleName): User
    {
        // User needs contact
        /** @var ContactInterface|null $contact */
        $contact = $user->getContact();

        if (!$contact) {
            $contact = $this->contactRepository->createNew();
            $user->setContact($contact);
        }

        $contact->setMainEmail($user->getEmail());
        $user = $this->updateUser($user);

        // Create and Add User Role
        $userRole = $this->createUserRole($user, $webspaceKey, $roleName);
        $user->addUserRole($userRole);

        // Save Entity
        $this->entityManager->persist($userRole);
        $this->entityManager->persist($contact);
        $this->entityManager->persist($user);

        return $user;
    }

    public function updateUser(User $user): User
    {
        $contact = $user->getContact();

        $mainEmail = $contact->getMainEmail();
        if (!$mainEmail) {
            $mainEmail = $user->getEmail();
        }

        if (!$mainEmail) {
            return $user;
        }

        if (!$contact->getEmails()->isEmpty()) {
            /** @var Email $email */
            $email = $contact->getEmails()->first();
            $email->setEmail($mainEmail);

            return $user;
        }

        /** @var EmailType $emailType */
        $emailType = $this->entityManager->getReference(EmailType::class, 1);
        $contactEmail = new Email();
        $contactEmail->setEmail($mainEmail);
        $contactEmail->setEmailType($emailType);
        $contact->addEmail($contactEmail);

        $this->entityManager->persist($contactEmail);

        return $user;
    }

    public function getUniqueToken(string $field): string
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
     */
    protected function createUserRole(User $user, string $webspaceKey, string $roleName): UserRole
    {
        /** @var RoleInterface $role */
        $role = $this->roleRepository->findOneBy(['name' => $roleName]);
        $userRole = new UserRole();

        $locales = [];

        $webspace = $this->webspaceManager->findWebspaceByKey($webspaceKey);

        if (!$webspace) {
            throw new \InvalidArgumentException(\sprintf('Webspace with key "%s" could not be found.', $webspaceKey));
        }

        foreach ($webspace->getLocalizations() as $localization) {
            $locales[] = $localization->getLocale();
        }

        /** @var string $localeString */
        $localeString = \json_encode($locales);

        $userRole->setLocale($localeString);
        $userRole->setRole($role);
        $userRole->setUser($user);

        return $userRole;
    }

    public function findByPasswordResetToken(string $token): ?User
    {
        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['passwordResetToken' => $token]);

        if (!$user || $user->getPasswordResetTokenExpiresAt() < new \DateTime()) {
            return null;
        }

        return $user;
    }

    public function findByConfirmationKey(string $token): ?User
    {
        $user = $this->userRepository->findOneBy(['confirmationKey' => $token]);

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }

    public function findUser(string $identifier): ?User
    {
        try {
            $user = $this->userRepository->findUserByIdentifier($identifier);
        } catch (NoResultException $e) {
            return null;
        }

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }
}
