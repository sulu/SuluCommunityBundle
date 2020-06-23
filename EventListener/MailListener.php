<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\EventListener;

use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Sulu\Bundle\CommunityBundle\Event\AbstractCommunityEvent;
use Sulu\Bundle\CommunityBundle\Event\UserCompletedEvent;
use Sulu\Bundle\CommunityBundle\Event\UserConfirmedEvent;
use Sulu\Bundle\CommunityBundle\Event\UserPasswordForgotEvent;
use Sulu\Bundle\CommunityBundle\Event\UserPasswordResetedEvent;
use Sulu\Bundle\CommunityBundle\Event\UserProfileSavedEvent;
use Sulu\Bundle\CommunityBundle\Event\UserRegisteredEvent;
use Sulu\Bundle\CommunityBundle\Mail\Mail;
use Sulu\Bundle\CommunityBundle\Mail\MailFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Send emails when specific events are thrown.
 */
class MailListener implements EventSubscriberInterface
{
    /**
     * @var MailFactoryInterface
     */
    private $mailFactory;

    public function __construct(MailFactoryInterface $mailFactory)
    {
        $this->mailFactory = $mailFactory;
    }

    /**
     * @return array<string, mixed>
     */
    public static function getSubscribedEvents()
    {
        return [
            UserRegisteredEvent::class => ['sendRegistrationEmails', 50],
            UserConfirmedEvent::class => 'sendConfirmationEmails',
            UserPasswordForgotEvent::class => 'sendPasswordForgetEmails',
            UserPasswordResetedEvent::class => 'sendPasswordResetEmails',
            UserCompletedEvent::class => 'sendCompletionEmails',
            UserProfileSavedEvent::class => 'sendNotificationSaveProfile',
        ];
    }

    /**
     * Send registration emails.
     */
    public function sendRegistrationEmails(AbstractCommunityEvent $event): void
    {
        $this->sendTypeEmails($event, Configuration::TYPE_REGISTRATION);
    }

    /**
     * Send confirmation emails.
     */
    public function sendConfirmationEmails(AbstractCommunityEvent $event): void
    {
        $this->sendTypeEmails($event, Configuration::TYPE_CONFIRMATION);
    }

    /**
     * Send password forget emails.
     */
    public function sendPasswordForgetEmails(AbstractCommunityEvent $event): void
    {
        $this->sendTypeEmails($event, Configuration::TYPE_PASSWORD_FORGET);
    }

    /**
     * Send password reset emails.
     */
    public function sendPasswordResetEmails(AbstractCommunityEvent $event): void
    {
        $this->sendTypeEmails($event, Configuration::TYPE_PASSWORD_RESET);
    }

    /**
     * Send password reset emails.
     */
    public function sendCompletionEmails(AbstractCommunityEvent $event): void
    {
        $this->sendTypeEmails($event, Configuration::TYPE_COMPLETION);
    }

    /**
     * Send notification email for profile save.
     */
    public function sendNotificationSaveProfile(AbstractCommunityEvent $event): void
    {
        $this->sendTypeEmails($event, Configuration::TYPE_PROFILE);
    }

    /**
     * Send emails for specific type.
     */
    protected function sendTypeEmails(AbstractCommunityEvent $event, string $type): void
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
