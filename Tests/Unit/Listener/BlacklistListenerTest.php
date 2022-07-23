<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Tests\Unit\Listener;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Sulu\Bundle\CommunityBundle\Entity\BlacklistItem;
use Sulu\Bundle\CommunityBundle\Entity\BlacklistItemRepository;
use Sulu\Bundle\CommunityBundle\Entity\BlacklistUser;
use Sulu\Bundle\CommunityBundle\Event\UserRegisteredEvent;
use Sulu\Bundle\CommunityBundle\EventListener\BlacklistListener;
use Sulu\Bundle\CommunityBundle\Mail\Mail;
use Sulu\Bundle\CommunityBundle\Mail\MailFactoryInterface;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\SecurityBundle\Util\TokenGeneratorInterface;

class BlacklistListenerTest extends TestCase
{
    private $repository;
    private $entityManager;
    private $tokenGenerator;
    private $mailFactory;
    private $listener;

    protected function setUp(): void
    {
        $this->repository = $this->prophesize(BlacklistItemRepository::class);
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);
        $this->tokenGenerator = $this->prophesize(TokenGeneratorInterface::class);
        $this->mailFactory = $this->prophesize(MailFactoryInterface::class);

        $this->listener = new BlacklistListener(
            $this->repository->reveal(),
            $this->entityManager->reveal(),
            $this->tokenGenerator->reveal(),
            $this->mailFactory->reveal()
        );
    }

    public function testValidateEmail(): void
    {
        $this->repository->findBySender('test@sulu.io')
            ->willReturn([new BlacklistItem('*@sulu.io', BlacklistItem::TYPE_REQUEST)]);
        $this->tokenGenerator->generateToken()->willReturn('123-123-123');

        $user = $this->prophesize(User::class);
        $user->getEmail()->willReturn('test@sulu.io');

        $event = $this->prophesize(UserRegisteredEvent::class);
        $event->getConfigProperty(Configuration::WEBSPACE_KEY)->willReturn('sulu-io');
        $event->getConfigProperty(Configuration::EMAIL_TO)->willReturn(['admin@sulu.io' => 'admin@sulu.io']);
        $event->getConfigProperty(Configuration::EMAIL_FROM)->willReturn(['from@sulu.io' => 'from@sulu.io']);
        $event->getConfigTypeProperty(Configuration::TYPE_BLACKLISTED, Configuration::EMAIL)->willReturn(
            [
                Configuration::EMAIL_SUBJECT => 'subject',
                Configuration::EMAIL_USER_TEMPLATE => 'user_template',
                Configuration::EMAIL_ADMIN_TEMPLATE => 'admin_template',
            ]
        );
        $event->getUser()->willReturn($user->reveal());

        $this->entityManager->persist(
            Argument::that(
                function (BlacklistUser $item) use ($user) {
                    return '123-123-123' === $item->getToken()
                    && 'sulu-io' === $item->getWebspaceKey()
                    && $item->getUser() === $user->reveal();
                }
            )
        )->shouldBeCalled();
        $this->entityManager->flush()->shouldBeCalled();

        $this->mailFactory->sendEmails(Argument::type(Mail::class), $user->reveal(), ['token' => '123-123-123'])
            ->shouldBeCalled();

        $event->stopPropagation()->shouldBeCalled();

        $this->listener->validateEmail($event->reveal());
    }

    public function testValidateEmailNoMatch(): void
    {
        $this->repository->findBySender('test@sulu.io')
            ->willReturn([]);

        $user = $this->prophesize(User::class);
        $user->getEmail()->willReturn('test@sulu.io');

        $event = $this->prophesize(UserRegisteredEvent::class);
        $event->getUser()->willReturn($user->reveal());
        $event->stopPropagation()->shouldNotBeCalled();

        $this->listener->validateEmail($event->reveal());
    }
}
