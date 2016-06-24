<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Mail;

use Sulu\Bundle\SecurityBundle\Entity\BaseUser;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Send emails for a specific type.
 */
class MailFactory implements MailFactoryInterface
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var EngineInterface
     */
    protected $engine;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param \Swift_Mailer $mailer
     * @param EngineInterface $engine
     * @param TranslatorInterface $translator
     */
    public function __construct(\Swift_Mailer $mailer, EngineInterface $engine, TranslatorInterface $translator)
    {
        $this->mailer = $mailer;
        $this->engine = $engine;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function sendEmails(Mail $mail, BaseUser $user, $parameters = [])
    {
        $email = $user->getEmail();
        if ($mail->getUserEmail()) {
            $email = $mail->getUserEmail();
        }
        $data = array_merge($parameters, ['user' => $user]);

        // Send User Email
        if (null !== $mail->getUserTemplate()) {
            // Render Email in specific locale
            $locale = $this->translator->getLocale();
            $this->translator->setLocale($user->getLocale());

            $this->sendEmail($mail->getFrom(), $email, $mail->getSubject(), $mail->getUserTemplate(), $data);
            $this->translator->setLocale($locale);
        }

        // Send Admin Email
        if (null !== $mail->getAdminTemplate()) {
            $this->sendEmail($mail->getFrom(), $mail->getTo(), $mail->getSubject(), $mail->getAdminTemplate(), $data);
        }
    }

    /**
     * Create and send email.
     *
     * @param string|array $from
     * @param string|array $to
     * @param string $subject
     * @param string $template
     * @param array $data
     */
    protected function sendEmail($from, $to, $subject, $template, $data)
    {
        $body = $this->engine->render($template, $data);

        $message = \Swift_Message::newInstance();
        $message->setSubject($this->translator->trans($subject));
        $message->setFrom($from);
        $message->setTo($to);
        $message->setBody($body, 'text/html');

        $this->mailer->send($message);
    }
}
