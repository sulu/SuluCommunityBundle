<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\EventListener;

use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Sulu\Bundle\CommunityBundle\Event\CommunityEvent;
use Sulu\Bundle\CommunityBundle\Mail\Mail;
use Sulu\Bundle\CommunityBundle\Mail\MailFactoryInterface;

/**
 * Send emails when specific events are thrown.
 */
class MailListener
{
    /**
     * @var MailFactoryInterface
     */
    private $mailFactory;

    /**
     * @param MailFactoryInterface $mailFactory
     */
    public function __construct(MailFactoryInterface $mailFactory)
    {
        $this->mailFactory = $mailFactory;
    }

    /**
     * Send registration emails.
     *
     * @param CommunityEvent $event
     */
    public function sendRegistrationEmails(CommunityEvent $event)
    {
        $this->sendTypeEmails($event, Configuration::TYPE_REGISTRATION);
    }

    /**
     * Send confirmation emails.
     *
     * @param CommunityEvent $event
     */
    public function sendConfirmationEmails(CommunityEvent $event)
    {
        $this->sendTypeEmails($event, Configuration::TYPE_CONFIRMATION);
    }

    /**
     * Send password forget emails.
     *
     * @param CommunityEvent $event
     */
    public function sendPasswordForgetEmails(CommunityEvent $event)
    {
        $this->sendTypeEmails($event, Configuration::TYPE_PASSWORD_FORGET);
    }

    /**
     * Send password reset emails.
     *
     * @param CommunityEvent $event
     */
    public function sendPasswordResetEmails(CommunityEvent $event)
    {
        $this->sendTypeEmails($event, Configuration::TYPE_PASSWORD_RESET);
    }

    /**
     * Send password reset emails.
     *
     * @param CommunityEvent $event
     */
    public function sendCompletionEmails(CommunityEvent $event)
    {
        $this->sendTypeEmails($event, Configuration::TYPE_COMPLETION);
    }

    /**
     * Send notification email for profile save.
     *
     * @param CommunityEvent $event
     */
    public function sendNotificationSaveProfile(CommunityEvent $event)
    {
        $this->sendTypeEmails($event, Configuration::TYPE_PROFILE);
    }

    /**
     * Send emails for specific type.
     *
     * @param CommunityEvent $event
     * @param string $type
     */
    protected function sendTypeEmails(CommunityEvent $event, $type)
    {
        $config = $event->getConfig();
        $mail = Mail::create(
            $config[Configuration::EMAIL_FROM],
            $config[Configuration::EMAIL_TO],
            $config[$type][Configuration::EMAIL]
        );

        $this->mailFactory->sendEmails($mail, $event->getUser());
    }
}
