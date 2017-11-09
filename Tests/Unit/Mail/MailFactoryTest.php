<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Tests\Unit\Mail;

use Prophecy\Argument;
use Sulu\Bundle\CommunityBundle\Mail\Mail;
use Sulu\Bundle\CommunityBundle\Mail\MailFactory;
use Sulu\Bundle\SecurityBundle\Entity\BaseUser;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

class MailFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var EngineInterface
     */
    private $engine;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var MailFactory
     */
    private $mailFactory;

    /**
     * @var BaseUser
     */
    private $user;

    public function setUp()
    {
        $this->mailer = $this->prophesize(\Swift_Mailer::class);
        $this->engine = $this->prophesize(EngineInterface::class);
        $this->translator = $this->prophesize(TranslatorInterface::class);
        $this->user = $this->prophesize(BaseUser::class);
        $this->user->getEmail()->willReturn('test@example.com');
        $this->user->getLocale()->willReturn('de');

        $this->mailFactory = new MailFactory(
            $this->mailer->reveal(),
            $this->engine->reveal(),
            $this->translator->reveal()
        );
    }

    public function testSendEmails()
    {
        $this->engine->render('user-template', Argument::any())->willReturn('User-Template');
        $this->engine->render('admin-template', Argument::any())->willReturn('Admin-Template');

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
        $this->mailFactory->sendEmails($mail, $this->user->reveal());
    }

    public function testSendEmailsNoAdminTemplate()
    {
        $this->engine->render('user-template', Argument::any())->willReturn('User-Template');
        $this->engine->render('admin-template', Argument::any())->willReturn('Admin-Template');

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
        $this->mailFactory->sendEmails($mail, $this->user->reveal());
    }

    public function testSendEmailsNoUserTemplate()
    {
        $this->engine->render('user-template', Argument::any())->willReturn('User-Template');
        $this->engine->render('admin-template', Argument::any())->willReturn('Admin-Template');

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
        $this->mailFactory->sendEmails($mail, $this->user->reveal());
    }
}
