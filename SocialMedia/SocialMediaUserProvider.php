<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\SocialMedia;

use Doctrine\Common\Persistence\ManagerRegistry;
use HWI\Bundle\OAuthBundle\Connect\AccountConnectorInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\EntityUserProvider;
use Sulu\Bundle\CommunityBundle\Entity\UserAccessToken;
use Sulu\Bundle\CommunityBundle\Entity\UserAccessTokenRepository;
use Sulu\Bundle\CommunityBundle\Manager\CommunityManagerInterface;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Bundle\ContactBundle\Entity\ContactRepositoryInterface;
use Sulu\Component\Security\Authentication\UserInterface as SuluUserInterface;
use Sulu\Component\Security\Authentication\UserRepositoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SocialMediaUserProvider extends EntityUserProvider implements AccountConnectorInterface
{
    /**
     * @var UserAccessTokenRepository
     */
    private $userAccessTokenRepository;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var ContactRepositoryInterface
     */
    private $contactRepository;

    /**
     * @var CommunityManagerInterface
     */
    private $communityManager;

    public function __construct(
        CommunityManagerInterface $community,
        ManagerRegistry $registry,
        $class = SuluUserInterface::class,
        array $properties = [],
        $managerName = null
    ) {
        parent::__construct($registry, $class, $properties, $managerName);

        $this->userAccessTokenRepository = $this->em->getRepository(UserAccessToken::class);
        $this->userRepository = $this->em->getRepository($this->em->getClassMetadata(SuluUserInterface::class)->getName());
        $this->contactRepository = $this->em->getRepository($this->em->getClassMetadata(ContactInterface::class)->getName());
    }

    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        // On connect, retrieve the access token and the user id
        $service = $response->getResourceOwner()->getName();
        $username = $response->getUsername();

        // Disconnect previously connected users
        $previousAccessToken = $this->userAccessTokenRepository->findByIdentifier($service, $username);
        if ($previousAccessToken) {
            $this->em->remove($previousAccessToken);

            $this->em->flush();
        }

        $accessToken = $this->userAccessTokenRepository->create($user, $service, $username);
        $accessToken->setAccessToken($response->getAccessToken());

        $this->em->flush();
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $service = $response->getResourceOwner()->getName();
        $username = $response->getUsername();
        $email = $response->getEmail() ? $response->getEmail() : $username;

        $accessToken = $this->userAccessTokenRepository->findByIdentifier($service, $username);

        // If the user exists
        if ($accessToken) {
            // set access-token and return
            $accessToken->setAccessToken($response->getAccessToken());

            $user = $accessToken->getUser();
            $user->setEmail($email);

            $contact = $user->getContact();
            $contact->setFirstName($response->getFirstName());
            $contact->setLastName($response->getLastName());

            $this->em->flush();

            return $accessToken->getUser();
        }

        // else create new user
        $user = $this->userRepository->createNew();

        $accessToken = $this->userAccessTokenRepository->create($user, $service, $username);
        $accessToken->setAccessToken($response->getAccessToken());

        $contact = $this->contactRepository->createNew();
        $contact->setFirstName($response->getFirstName());
        $contact->setLastName($response->getLastName());
        $user->setContact($contact);

        $user->setUsername($this->generateRandomUsername($service, $username));
        $user->setEmail($email);
        $user->setSalt(uniqid());
        $user->setPassword($username);

        $this->communityManager->register($user);
        $user->setEnabled(true);

        $this->em->flush();

        return $user;
    }

    private function generateRandomUsername($serviceName, $username)
    {
        if (!$username) {
            $username = uniqid((rand()), true);
        }

        return $serviceName . '-' . $username;
    }
}
