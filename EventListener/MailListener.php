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
use Sulu\Bundle\SecurityBundle\Entity\BaseUser;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

class MailListener
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * MailListener constructor.
     *
     * @param \Swift_Mailer $mailer
     * @param TranslatorInterface $translator
     * @param EngineInterface $templating
     */
    public function __construct(
        \Swift_Mailer $mailer,
        TranslatorInterface $translator,
        EngineInterface $templating
    ) {
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->templating = $templating;
    }

    /**
     * @param CommunityEvent $event
     */
    public function sendRegistrationEmails(CommunityEvent $event)
    {
        $mailSettings = $this->getMailSettings($event->getConfig(), Configuration::TYPE_REGISTRATION);

        $this->sendEmails(
            $mailSettings,
            $event->getUser()
        );
    }

    /**
     * @param CommunityEvent $event
     */
    public function sendConfirmationEmails(CommunityEvent $event)
    {
        $mailSettings = $this->getMailSettings($event->getConfig(), Configuration::TYPE_CONFIRMATION);

        $this->sendEmails(
            $mailSettings,
            $event->getUser()
        );
    }

    /**
     * @param CommunityEvent $event
     */
    public function sendPasswordForgetEmails(CommunityEvent $event)
    {
        $mailSettings = $this->getMailSettings($event->getConfig(), Configuration::TYPE_PASSWORD_FORGET);

        $this->sendEmails(
            $mailSettings,
            $event->getUser()
        );
    }

    /**
     * @param CommunityEvent $event
     */
    public function sendPasswordResetEmails(CommunityEvent $event)
    {
        $mailSettings = $this->getMailSettings($event->getConfig(), Configuration::TYPE_PASSWORD_RESET);

        $this->sendEmails(
            $mailSettings,
            $event->getUser()
        );
    }

    /**
     * @param $mailSettings
     * @param BaseUser $user
     */
    protected function sendEmails($mailSettings, BaseUser $user)
    {
        $email = $user->getEmail();
        $data = ['user' => $user];

        // Send User Email
        if ($mailSettings[Configuration::EMAIL_USER_TEMPLATE]) {
            // Render Email in specific locale
            $locale = $this->translator->getLocale();
            $this->translator->setLocale($user->getLocale());

            $this->sendEmail(
                $mailSettings[Configuration::EMAIL_FROM],
                $email,
                $mailSettings[Configuration::EMAIL_SUBJECT],
                $mailSettings[Configuration::EMAIL_USER_TEMPLATE],
                $data
            );

            $this->translator->setLocale($locale);
        }

        // Send Admin Email
        if ($mailSettings[Configuration::EMAIL_ADMIN_TEMPLATE]) {
            $this->sendEmail(
                $mailSettings[Configuration::EMAIL_FROM],
                $mailSettings[Configuration::EMAIL_TO],
                $mailSettings[Configuration::EMAIL_SUBJECT],
                $mailSettings[Configuration::EMAIL_ADMIN_TEMPLATE],
                $data
            );
        }
    }

    /**
     * @param string|array $from
     * @param string|array $to
     * @param string $subject
     * @param string $template
     * @param array $data
     */
    protected function sendEmail($from, $to, $subject, $template, $data)
    {
        $body = $this->templating->render($template, $data);

        $message = \Swift_Message::newInstance();
        $message->setSubject($this->translator->trans($subject));
        $message->setFrom($from);
        $message->setTo($to);
        $message->setBody($body, 'text/html');

        $this->mailer->send($message);
    }

    /**
     * @param array $config
     * @param string $type
     *
     * @return array
     */
    protected static function getMailSettings($config, $type)
    {
        return [
            Configuration::EMAIL_FROM => $config[Configuration::EMAIL_FROM],
            Configuration::EMAIL_TO => $config[Configuration::EMAIL_TO],
            Configuration::EMAIL_SUBJECT => $config[$type][Configuration::EMAIL][Configuration::EMAIL_SUBJECT],
            Configuration::EMAIL_USER_TEMPLATE => $config[$type][Configuration::EMAIL][Configuration::EMAIL_USER_TEMPLATE],
            Configuration::EMAIL_ADMIN_TEMPLATE => $config[$type][Configuration::EMAIL][Configuration::EMAIL_ADMIN_TEMPLATE],
        ];
    }
}
