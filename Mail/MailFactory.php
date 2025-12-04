<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Mail;

use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Send emails for a specific type.
 */
class MailFactory implements MailFactoryInterface
{
    /**
     * @var MailerInterface|\Swift_Mailer
     */
    protected $mailer;

    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct($mailer, Environment $twig, TranslatorInterface $translator)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->translator = $translator;
    }

    public function sendEmails(Mail $mail, User $user, array $parameters = []): void
    {
        $email = $mail->getUserEmail();
        if (!$email) {
            $email = $user->getEmail();
        }
        $data = \array_merge($parameters, ['user' => $user]);

        // Send User Email
        if (null !== $mail->getUserTemplate() && $email) {
            /** @var LocaleAwareInterface $translator */
            $translator = $this->translator;
            // Render Email in specific locale
            $locale = $translator->getLocale();
            $translator->setLocale($user->getLocale());

            $this->sendEmail($mail->getFrom(), $email, $mail->getSubject(), $mail->getUserTemplate(), $data);
            $translator->setLocale($locale);
        }

        // Send Admin Email
        if (null !== $mail->getAdminTemplate()) {
            $this->sendEmail($mail->getFrom(), $mail->getTo(), $mail->getSubject(), $mail->getAdminTemplate(), $data);
        }
    }

    /**
     * Create and send email.
     *
     * @param string|array<string, string> $from
     * @param string|array<string, string> $to
     * @param mixed[] $data
     */
    protected function sendEmail($from, $to, string $subject, string $template, array $data): void
    {
        $body = $this->twig->render($template, $data);

        if ($this->mailer instanceof \Swift_Mailer) {
            $email = $this->mailer->createMessage()
                ->setSubject($this->translator->trans($subject))
                ->setFrom($from)
                ->setTo($to)
                ->setBody($body, 'text/html');
        } else {
            if (!$this->getAddress($from) || !$this->getAddress($to)) {
                return;
            }

            $email = (new Email())
                ->subject($this->translator->trans($subject))
                ->from($this->getAddress($from))
                ->to($this->getAddress($to))
                ->html($body);
        }

        $this->mailer->send($email);
    }

    /**
     * Convert string/array email address to an Address object.
     *
     * @param mixed $address
     */
    protected function getAddress($address): ?Address
    {
        $name = '';

        if (\is_array($address)) {
            if (empty($address)) {
                return null;
            } elseif (!isset($address['email'])) {
                $email = \array_keys($address)[0];
                $name = $address[\array_keys($address)[0]];
            } else {
                $email = $address['email'];
                $name = $address['name'] ?? '';
            }
        } else {
            $email = $address;
        }

        return new Address($email, $name);
    }
}
