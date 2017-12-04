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
use Sulu\Bundle\ContactBundle\Contact\ContactManagerInterface;
use Sulu\Bundle\ContactBundle\Entity\Email;
use Sulu\Bundle\ContactBundle\Entity\EmailType;
use Sulu\Bundle\SecurityBundle\Entity\BaseUser;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\SecurityBundle\Entity\UserRole;
use Sulu\Bundle\SecurityBundle\Util\TokenGeneratorInterface;
use Sulu\Component\Contact\Model\ContactRepositoryInterface;
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
     *
     * @param EntityManagerInterface $entityManager
     * @param WebspaceManagerInterface $webspaceManager
     * @param TokenGeneratorInterface $tokenGenerator
     * @param UserRepositoryInterface $userRepository
     * @param RoleRepositoryInterface $roleRepository
     * @param ContactRepositoryInterface $contactRepository
     * @param ContactManagerInterface $contactManager
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

        if (null === $contact->getFirstName()) {
            $contact->setFirstName('');
        }

        if (null === $contact->getLastName()) {
            $contact->setLastName('');
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

    /**
     * {@inheritdoc}
     */
    public function updateUser(User $user)
    {
        $contact = $user->getContact();

        if (!$contact->getEmails()->isEmpty()) {
            /** @var Email $email */
            $email = $contact->getEmails()->first();
            $email->setEmail($contact->getMainEmail());

            return $user;
        }

        /** @var EmailType $emailType */
        $emailType = $this->entityManager->getReference(EmailType::class, 1);
        $contactEmail = new Email();
        $contactEmail->setEmail($contact->getMainEmail());
        $contactEmail->setEmailType($emailType);
        $contact->addEmail($contactEmail);

        $this->entityManager->persist($contactEmail);

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
        /** @var BaseUser $user */
        $user = $this->userRepository->findOneBy(['passwordResetToken' => $token]);

        if (!$user || $user->getPasswordResetTokenExpiresAt() < new \DateTime()) {
            return null;
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
        $user = $this->userRepository->findUserByIdentifier($identifier);

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }
}
