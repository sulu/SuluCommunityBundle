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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class MailFactory implements MailFactoryInterface
{
    public function __construct(
        protected MailerInterface $mailer,
        protected Environment $twig,
        protected TranslatorInterface $translator,
    ) {
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
     * @param string|array<string, string> $from
     * @param string|array<string, string> $to
     * @param mixed[] $data
     */
    protected function sendEmail($from, $to, string $subject, string $template, array $data): void
    {
        $fromAddress = $this->getAddress($from);
        $toAddress = $this->getAddress($to);

        if (null === $fromAddress || null === $toAddress) {
            return;
        }

        $email = (new Email())
            ->subject($this->translator->trans($subject))
            ->from($fromAddress)
            ->to($toAddress)
            ->html($this->twig->render($template, $data));

        $this->mailer->send($email);
    }

    /**
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
