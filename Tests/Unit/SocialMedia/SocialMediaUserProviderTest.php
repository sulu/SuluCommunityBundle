<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Tests\Unit\SocialMedia;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sulu\Bundle\CommunityBundle\Entity\UserAccessToken;
use Sulu\Bundle\CommunityBundle\Entity\UserAccessTokenRepository;
use Sulu\Bundle\CommunityBundle\Manager\CommunityManagerInterface;
use Sulu\Bundle\CommunityBundle\Manager\CommunityManagerRegistryInterface;
use Sulu\Bundle\CommunityBundle\SocialMedia\SocialMediaUserProvider;
use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Bundle\ContactBundle\Entity\ContactRepositoryInterface;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Component\Security\Authentication\UserInterface;
use Sulu\Component\Security\Authentication\UserRepositoryInterface;
use Sulu\Component\Webspace\Analyzer\Attributes\RequestAttributes;
use Sulu\Component\Webspace\Webspace;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SocialMediaUserProviderTest extends TestCase
{
    /**
     * @var CommunityManagerRegistryInterface
     */
    private $communityManagerRegistry;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var ContactRepositoryInterface
     */
    private $contactRepository;

    /**
     * @var UserAccessTokenRepository
     */
    private $userAccessTokenRepository;

    protected function setUp()
    {
        $this->communityManagerRegistry = $this->prophesize(CommunityManagerRegistryInterface::class);
        $this->requestStack = $this->prophesize(RequestStack::class);
        $this->registry = $this->prophesize(Registry::class);

        $this->entityManager = $this->prophesize(EntityManagerInterface::class);
        $userMetadata = $this->prophesize(ClassMetadata::class);
        $userMetadata->getName()->willReturn(User::class);
        $contactMetadata = $this->prophesize(ClassMetadata::class);
        $contactMetadata->getName()->willReturn(Contact::class);
        $this->entityManager->getClassMetadata(UserInterface::class)->willReturn($userMetadata->reveal());
        $this->entityManager->getClassMetadata(ContactInterface::class)->willReturn($contactMetadata->reveal());

        $this->userRepository = $this->prophesize(UserRepositoryInterface::class);
        $this->contactRepository = $this->prophesize(ContactRepositoryInterface::class);
        $this->userAccessTokenRepository = $this->prophesize(UserAccessTokenRepository::class);

        $this->entityManager->getRepository(User::class)->willReturn($this->userRepository->reveal());
        $this->entityManager->getRepository(Contact::class)->willReturn($this->contactRepository->reveal());
        $this->entityManager->getRepository(UserAccessToken::class)->willReturn($this->userAccessTokenRepository->reveal());
    }

    public function testConnect()
    {
        $provider = new SocialMediaUserProvider(
            $this->communityManagerRegistry->reveal(),
            $this->requestStack->reveal(),
            $this->registry->reveal()
        );

        $user = $this->prophesize(UserInterface::class);
        $response = $this->prophesize(UserResponseInterface::class);
        $resourceOwner = $this->prophesize(ResourceOwnerInterface::class);
        $resourceOwner->getName()->willReturn('facebook');
        $response->getResourceOwner()->willReturn($resourceOwner->reveal());
        $response->getUsername()->willReturn('test');
        $response->getAccessToken()->willReturn('123-123-123');

        $this->userAccessTokenRepository->findByIdentifier('facebook', 'test')->willReturn(null);

        $accessToken = $this->prophesize(UserAccessToken::class);
        $accessToken->setAccessToken('123-123-123')->willReturn($accessToken)->shouldBeCalled();

        $this->entityManager->flush()->shouldBeCalled();

        $provider->connect($user->reveal(), $response->reveal());
    }

    public function testConnectRemoveExisting()
    {
        $provider = new SocialMediaUserProvider(
            $this->communityManagerRegistry->reveal(),
            $this->requestStack->reveal(),
            $this->registry->reveal()
        );

        $user = $this->prophesize(UserInterface::class);
        $response = $this->prophesize(UserResponseInterface::class);
        $resourceOwner = $this->prophesize(ResourceOwnerInterface::class);
        $resourceOwner->getName()->willReturn('facebook');
        $response->getResourceOwner()->willReturn($resourceOwner->reveal());
        $response->getUsername()->willReturn('test');
        $response->getAccessToken()->willReturn('123-123-123');

        $oldUserAccessToken = $this->prophesize(UserAccessToken::class);
        $this->userAccessTokenRepository->findByIdentifier('facebook', 'test')
            ->willReturn($oldUserAccessToken->reveal());

        $this->entityManager->remove($oldUserAccessToken->reveal());
        $this->entityManager->flush()->shouldBeCalled();

        $accessToken = $this->prophesize(UserAccessToken::class);
        $accessToken->setAccessToken('123-123-123')->willReturn($accessToken->reveal())->shouldBeCalled();

        $provider->connect($user->reveal(), $response->reveal());
    }

    public function testLoadUserByOAuthUserResponseExistingUser()
    {
        $provider = new SocialMediaUserProvider(
            $this->communityManagerRegistry->reveal(),
            $this->requestStack->reveal(),
            $this->registry->reveal()
        );

        $response = $this->prophesize(UserResponseInterface::class);
        $resourceOwner = $this->prophesize(ResourceOwnerInterface::class);
        $resourceOwner->getName()->willReturn('facebook');
        $response->getResourceOwner()->willReturn($resourceOwner->reveal());
        $response->getUsername()->willReturn('test');
        $response->getFirstName()->willReturn('Max');
        $response->getLastName()->willReturn('Mustermann');
        $response->getEmail()->willReturn(null);
        $response->getAccessToken()->willReturn('123-123-123');

        $accessToken = $this->prophesize(UserAccessToken::class);
        $this->userAccessTokenRepository->findByIdentifier('facebook', 'test')
            ->willReturn($accessToken->reveal());
        $accessToken->setAccessToken('123-123-123')->willReturn($accessToken->reveal())->shouldBeCalled();

        $user = $this->prophesize(User::class);
        $contact = $this->prophesize(ContactInterface::class);
        $user->getContact()->willReturn($contact->reveal());

        $contact->setFirstName('Max')->shouldBeCalled();
        $contact->setLastName('Mustermann')->shouldBeCalled();

        $this->entityManager->flush()->shouldBeCalled();

        $accessToken->getUser()->willReturn($user->reveal());

        $result = $provider->loadUserByOAuthUserResponse($response->reveal());
        $this->assertEquals($user->reveal(), $result);
    }

    public function testLoadUserByOAuthUserResponse()
    {
        $provider = new SocialMediaUserProvider(
            $this->communityManagerRegistry->reveal(),
            $this->requestStack->reveal(),
            $this->registry->reveal()
        );

        $response = $this->prophesize(UserResponseInterface::class);
        $resourceOwner = $this->prophesize(ResourceOwnerInterface::class);
        $resourceOwner->getName()->willReturn('facebook');
        $response->getResourceOwner()->willReturn($resourceOwner->reveal());
        $response->getUsername()->willReturn('test');
        $response->getFirstName()->willReturn('Max');
        $response->getLastName()->willReturn('Mustermann');
        $response->getEmail()->willReturn('max@mustermann.at');
        $response->getAccessToken()->willReturn('123-123-123');

        $this->userAccessTokenRepository->findByIdentifier('facebook', 'test')->willReturn(null);

        $accessToken = $this->prophesize(UserAccessToken::class);
        $accessToken->setAccessToken('123-123-123')->willReturn($accessToken->reveal())->shouldBeCalled();

        $user = $this->prophesize(User::class);
        $this->userRepository->createNew()->willReturn($user->reveal());

        $contact = $this->prophesize(ContactInterface::class);
        $this->contactRepository->createNew()->willReturn($contact->reveal());
        $contact->setFirstName('Max')->shouldBeCalled();
        $contact->setLastName('Mustermann')->shouldBeCalled();

        $user->setContact($contact->reveal())->shouldBeCalled();
        $user->setUsername(Argument::type('string'))->shouldBeCalled();
        $user->setEmail('max@mustermann.at')->shouldBeCalled();
        $user->setSalt(Argument::type('string'))->shouldBeCalled();
        $user->setPassword(Argument::type('string'))->shouldBeCalled();

        $request = new Request();
        $requestAttributes = $this->prophesize(RequestAttributes::class);
        $request->attributes->set('_sulu', $requestAttributes->reveal());

        $webspace = $this->prophesize(Webspace::class);
        $webspace->getKey()->willReturn('sulu_io');
        $requestAttributes->getAttribute('webspace')->willReturn($webspace->reveal());

        $communityManager = $this->prophesize(CommunityManagerInterface::class);
        $communityManager->register($user->reveal())->shouldBeCalled();
        $this->communityManagerRegistry->get('sulu_io')->willReturn($communityManager->reveal());
        $user->setEnabled(true)->shouldBeCalled();

        $this->entityManager->flush()->shouldBeCalled();

        $result = $provider->loadUserByOAuthUserResponse($response->reveal());
        $this->assertEquals($user->reveal(), $result);
    }
}
