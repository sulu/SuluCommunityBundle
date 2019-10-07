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
use Sulu\Bundle\CommunityBundle\Entity\EmailConfirmationToken;
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
    const TYPE = Configuration::TYPE_EMAIL_CONFIRMATION;

    /**
     * Overwrite user email with contact email.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $repository = $this->get('sulu_community.email_confirmation.repository');

        $success = false;
        /** @var EmailConfirmationToken $token */
        $token = $repository->findByToken($request->get('token'));

        if (null !== $token) {
            /** @var User $user */
            $user = $token->getUser();
            $user->setEmail($user->getContact()->getMainEmail());
            $userContact = $user->getContact();
            if (0 === count($userContact->getEmails())) {
                /** @var EmailType $emailType */
                $emailType = $entityManager->getReference(EmailType::class, 1);

                $contactEmail = new Email();
                $contactEmail->setEmail($user->getContact()->getMainEmail());
                $contactEmail->setEmailType($emailType);
                $userContact->addEmail($contactEmail);
            }
            $userContact->getEmails()->first()->setEmail($userContact->getMainEmail());
            $entityManager->remove($token);
            $this->saveEntities();

            $success = true;
        }

        return $this->renderTemplate(self::TYPE, ['success' => $success]);
    }
}
