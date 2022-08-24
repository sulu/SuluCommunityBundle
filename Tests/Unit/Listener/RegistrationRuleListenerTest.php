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
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleItem;
use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleItemRepository;
use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleUser;
use Sulu\Bundle\CommunityBundle\Event\UserRegisteredEvent;
use Sulu\Bundle\CommunityBundle\EventListener\RegistrationRuleListener;
use Sulu\Bundle\CommunityBundle\Mail\Mail;
use Sulu\Bundle\CommunityBundle\Mail\MailFactoryInterface;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\SecurityBundle\Util\TokenGeneratorInterface;

class RegistrationRuleListenerTest extends TestCase
{
    /**
     * @var ObjectProphecy<RegistrationRuleItemRepository>
     */
    private $repository;

    /**
     * @var ObjectProphecy<EntityManagerInterface>
     */
    private $entityManager;

    /**
     * @var ObjectProphecy<TokenGeneratorInterface>
     */
    private $tokenGenerator;

    /**
     * @var ObjectProphecy<MailFactoryInterface>
     */
    private $mailFactory;

    /**
     * @var RegistrationRuleListener
     */
    private $listener;

    protected function setUp(): void
    {
        $this->repository = $this->prophesize(RegistrationRuleItemRepository::class);
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);
        $this->tokenGenerator = $this->prophesize(TokenGeneratorInterface::class);
        $this->mailFactory = $this->prophesize(MailFactoryInterface::class);

        $this->listener = new RegistrationRuleListener(
            $this->repository->reveal(),
            $this->entityManager->reveal(),
            $this->tokenGenerator->reveal(),
            $this->mailFactory->reveal()
        );
    }

    public function testValidateEmail(): void
    {
        $this->repository->findBySender('test@sulu.io')
            ->willReturn([new RegistrationRuleItem('*@sulu.io', RegistrationRuleItem::TYPE_REQUEST)]);
        $this->tokenGenerator->generateToken()->willReturn('123-123-123');

        $user = $this->prophesize(User::class);
        $user->getEmail()->willReturn('test@sulu.io');

        $event = $this->prophesize(UserRegisteredEvent::class);
        $event->getConfigProperty(Configuration::WEBSPACE_KEY)->willReturn('sulu-io');
        $event->getConfigProperty(Configuration::EMAIL_TO)->willReturn(['admin@sulu.io' => 'admin@sulu.io']);
        $event->getConfigProperty(Configuration::EMAIL_FROM)->willReturn(['from@sulu.io' => 'from@sulu.io']);
        $event->getConfigTypeProperty(Configuration::TYPE_REGISTRATION_RULEED, Configuration::EMAIL)->willReturn(
            [
                Configuration::EMAIL_SUBJECT => 'subject',
                Configuration::EMAIL_USER_TEMPLATE => 'user_template',
                Configuration::EMAIL_ADMIN_TEMPLATE => 'admin_template',
            ]
        );
        $event->getUser()->willReturn($user->reveal());

        $this->entityManager->persist(
            Argument::that(
                function(RegistrationRuleUser $item) use ($user) {
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
