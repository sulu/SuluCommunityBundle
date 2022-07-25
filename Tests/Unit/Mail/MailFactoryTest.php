<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Tests\Unit\Mail;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\CommunityBundle\Mail\Mail;
use Sulu\Bundle\CommunityBundle\Mail\MailFactory;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\Translation\Translator;
use Twig\Environment;

class MailFactoryTest extends TestCase
{
    /**
     * @var ObjectProphecy<\Swift_Mailer>
     */
    private $mailer;

    /**
     * @var ObjectProphecy<Environment>
     */
    private $twig;

    /**
     * @var ObjectProphecy<Translator>
     */
    private $translator;

    /**
     * @var MailFactory
     */
    private $mailFactory;

    /**
     * @var ObjectProphecy<User>
     */
    private $user;

    protected function setUp(): void
    {
        $this->mailer = $this->prophesize(\Swift_Mailer::class);
        $this->twig = $this->prophesize(Environment::class);
        $this->translator = $this->prophesize(Translator::class);
        $this->translator->getLocale()->willReturn('en');
        $this->user = $this->prophesize(User::class);
        $this->user->getEmail()->willReturn('test@example.com');
        $this->user->getLocale()->willReturn('de');

        $this->mailFactory = new MailFactory(
            $this->mailer->reveal(),
            $this->twig->reveal(),
            $this->translator->reveal()
        );
    }

    public function testSendEmails(): void
    {
        $this->twig->render('user-template', Argument::any())->willReturn('User-Template');
        $this->twig->render('admin-template', Argument::any())->willReturn('Admin-Template');

        $this->mailer->send(
            Argument::that(
                function (\Swift_Message $message) {
                    return 'User-Template' === $message->getBody()
                    && $message->getFrom() === ['test@sulu.io' => null]
                    && $message->getTo() === ['test@example.com' => null];
                }
            )
        )->shouldBeCalledTimes(1);
        $this->mailer->send(
            Argument::that(
                function (\Swift_Message $message) {
                    return 'Admin-Template' === $message->getBody()
                    && $message->getFrom() === ['test@sulu.io' => null]
                    && $message->getTo() === ['user@sulu.io' => null];
                }
            )
        )->shouldBeCalledTimes(1);

        $mail = new Mail('test@sulu.io', 'user@sulu.io', 'testcase', 'user-template', 'admin-template');
        $this->translator->trans('testcase')->shouldBeCalled();
        $this->translator->setLocale(Argument::type('string'))->shouldBeCalled();
        $this->mailFactory->sendEmails($mail, $this->user->reveal());
    }

    public function testSendEmailsNoAdminTemplate(): void
    {
        $this->twig->render('user-template', Argument::any())->willReturn('User-Template');
        $this->twig->render('admin-template', Argument::any())->willReturn('Admin-Template');

        $this->mailer->send(
            Argument::that(
                function (\Swift_Message $message) {
                    return 'User-Template' === $message->getBody()
                    && $message->getFrom() === ['test@sulu.io' => null]
                    && $message->getTo() === ['test@example.com' => null];
                }
            )
        )->shouldBeCalledTimes(1);
        $this->mailer->send(
            Argument::that(
                function (\Swift_Message $message) {
                    return 'Admin-Template' === $message->getBody()
                    && $message->getFrom() === ['test@sulu.io' => null]
                    && $message->getTo() === ['user@sulu.io' => null];
                }
            )
        )->shouldNotBeCalled();

        $mail = new Mail('test@sulu.io', 'user@sulu.io', 'testcase', 'user-template', null);
        $this->translator->trans('testcase')->shouldBeCalled();
        $this->translator->setLocale(Argument::type('string'))->shouldBeCalled();
        $this->mailFactory->sendEmails($mail, $this->user->reveal());
    }

    public function testSendEmailsNoUserTemplate(): void
    {
        $this->twig->render('user-template', Argument::any())->willReturn('User-Template');
        $this->twig->render('admin-template', Argument::any())->willReturn('Admin-Template');

        $this->mailer->send(
            Argument::that(
                function (\Swift_Message $message) {
                    return 'User-Template' === $message->getBody()
                    && $message->getFrom() === ['test@sulu.io' => null]
                    && $message->getTo() === ['test@example.com' => null];
                }
            )
        )->shouldNotBeCalled();
        $this->mailer->send(
            Argument::that(
                function (\Swift_Message $message) {
                    return 'Admin-Template' === $message->getBody()
                    && $message->getFrom() === ['test@sulu.io' => null]
                    && $message->getTo() === ['user@sulu.io' => null];
                }
            )
        )->shouldBeCalledTimes(1);

        $mail = new Mail('test@sulu.io', 'user@sulu.io', 'testcase', null, 'admin-template');
        $this->translator->trans('testcase')->shouldBeCalled();
        $this->mailFactory->sendEmails($mail, $this->user->reveal());
    }
}
