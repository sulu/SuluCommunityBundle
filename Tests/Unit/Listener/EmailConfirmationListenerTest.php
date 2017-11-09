<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Tests\Unit\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Prophecy\Argument;
use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Sulu\Bundle\CommunityBundle\Entity\EmailConfirmationToken;
use Sulu\Bundle\CommunityBundle\Entity\EmailConfirmationTokenRepository;
use Sulu\Bundle\CommunityBundle\Event\CommunityEvent;
use Sulu\Bundle\CommunityBundle\EventListener\EmailConfirmationListener;
use Sulu\Bundle\CommunityBundle\Mail\Mail;
use Sulu\Bundle\CommunityBundle\Mail\MailFactoryInterface;
use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\SecurityBundle\Util\TokenGeneratorInterface;

class EmailConfirmationListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MailFactoryInterface
     */
    private $mailFactory;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EmailConfirmationTokenRepository
     */
    private $repository;

    /**
     * @var TokenGeneratorInterface
     */
    private $tokenGenerator;

    /**
     * @var EmailConfirmationListener
     */
    private $listener;

    /**
     * @var CommunityEvent
     */
    private $event;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Contact
     */
    private $contact;

    /**
     * @var EmailConfirmationToken
     */
    private $token;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->mailFactory = $this->prophesize(MailFactoryInterface::class);
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);
        $this->repository = $this->prophesize(EmailConfirmationTokenRepository::class);
        $this->tokenGenerator = $this->prophesize(TokenGeneratorInterface::class);

        $this->listener = new EmailConfirmationListener(
            $this->mailFactory->reveal(),
            $this->entityManager->reveal(),
            $this->repository->reveal(),
            $this->tokenGenerator->reveal()
        );

        $this->event = $this->prophesize(CommunityEvent::class);
        $this->user = $this->prophesize(User::class);
        $this->contact = $this->prophesize(Contact::class);
        $this->token = $this->prophesize(EmailConfirmationToken::class);

        $this->event->getUser()->willReturn($this->user->reveal());
        $this->event->getConfigProperty(Argument::any())->willReturnArgument(0);
        $this->event->getConfigTypeProperty(Argument::cetera())->willReturn(
            [
                Configuration::EMAIL_SUBJECT => '',
                Configuration::EMAIL_USER_TEMPLATE => '',
                Configuration::EMAIL_ADMIN_TEMPLATE => '',
            ]
        );
        $this->user->getContact()->willReturn($this->contact->reveal());
        $this->token->getUser()->willReturn($this->user->reveal());
    }

    public function testSendConfirmation()
    {
        $this->user->getEmail()->willReturn('test@sulu.io');
        $this->contact->getMainEmail()->willReturn('new@sulu.io');
        $this->repository->findByUser($this->user->reveal())->willReturn(null);
        $this->tokenGenerator->generateToken()->willReturn('123-123-123');

        $this->entityManager->persist(
            Argument::that(
                function (EmailConfirmationToken $token) {
                    return '123-123-123' === $token->getToken() && $token->getUser() === $this->user->reveal();
                }
            )
        );
        $this->entityManager->flush();

        $this->mailFactory->sendEmails(
            Argument::type(Mail::class),
            $this->user->reveal(),
            ['token' => '123-123-123']
        )->shouldBeCalled();

        $this->listener->sendConfirmationOnEmailChange($this->event->reveal());
    }

    public function testSendConfirmationExistingToken()
    {
        $this->user->getEmail()->willReturn('test@sulu.io');
        $this->contact->getMainEmail()->willReturn('new@sulu.io');
        $this->repository->findByUser($this->user->reveal())->willReturn($this->token->reveal());
        $this->tokenGenerator->generateToken()->willReturn('123-123-123');

        $this->token->setToken('123-123-123')->shouldBeCalled();
        $this->token->getToken()->willReturn('123-123-123');

        $this->entityManager->persist(Argument::any())->shouldNotBeCalled();
        $this->entityManager->flush()->shouldBeCalled();

        $this->mailFactory->sendEmails(
            Argument::type(Mail::class),
            $this->user->reveal(),
            ['token' => '123-123-123']
        )->shouldBeCalled();

        $this->listener->sendConfirmationOnEmailChange($this->event->reveal());
    }

    public function testSendConfirmationNoChange()
    {
        $this->user->getEmail()->willReturn('test@sulu.io');
        $this->contact->getMainEmail()->willReturn('test@sulu.io');

        $this->entityManager->persist(Argument::any())->shouldNotBeCalled();
        $this->entityManager->flush()->shouldNotBeCalled();

        $this->mailFactory->sendEmails(Argument::cetera())->shouldNotBeCalled();

        $this->listener->sendConfirmationOnEmailChange($this->event->reveal());
    }
}
