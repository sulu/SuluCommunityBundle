<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Controller;

use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Sulu\Bundle\CommunityBundle\Entity\EmailConfirmationTokenRepository;
use Sulu\Bundle\ContactBundle\Entity\Email;
use Sulu\Bundle\ContactBundle\Entity\EmailType;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handle email confirmation.
 */
class EmailConfirmationController extends AbstractController
{
    public const TYPE = Configuration::TYPE_EMAIL_CONFIRMATION;

    /**
     * Overwrite user email with contact email.
     */
    public function indexAction(Request $request): Response
    {
        $entityManager = $this->getEntityManager();
        $repository = $this->getEmailConfirmationTokenRepository();

        $success = false;
        /** @var string $token */
        $token = $request->get('token');
        $token = $repository->findByToken($token);

        if (null !== $token) {
            /** @var User $user */
            $user = $token->getUser();
            $user->setEmail($user->getContact()->getMainEmail());
            $userContact = $user->getContact();
            if (0 === \count($userContact->getEmails())) {
                /** @var EmailType $emailType */
                $emailType = $entityManager->getReference(EmailType::class, 1);

                $contactEmail = new Email();
                $contactEmail->setEmail((string) $user->getContact()->getMainEmail());
                $contactEmail->setEmailType($emailType);
                $userContact->addEmail($contactEmail);
            }
            $email = $userContact->getEmails()->first();
            $mainEmail = $userContact->getMainEmail();
            if ($email && $mainEmail) {
                $email->setEmail($mainEmail);
            }
            $entityManager->remove($token);
            $this->saveEntities();

            $success = true;
        }

        return $this->renderTemplate(self::TYPE, ['success' => $success]);
    }

    protected function getEmailConfirmationTokenRepository(): EmailConfirmationTokenRepository
    {
        return $this->container->get('sulu_community.email_confirmation.repository');
    }

    /**
     * @return array<string|int, string>
     */
    public static function getSubscribedServices(): array
    {
        $subscribedServices = parent::getSubscribedServices();

        $subscribedServices['sulu_community.email_confirmation.repository'] = EmailConfirmationTokenRepository::class;

        return $subscribedServices;
    }
}
