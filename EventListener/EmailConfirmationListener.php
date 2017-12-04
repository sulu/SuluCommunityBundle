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

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Sulu\Bundle\CommunityBundle\Entity\EmailConfirmationToken;
use Sulu\Bundle\CommunityBundle\Entity\EmailConfirmationTokenRepository;
use Sulu\Bundle\CommunityBundle\Event\CommunityEvent;
use Sulu\Bundle\CommunityBundle\Mail\Mail;
use Sulu\Bundle\CommunityBundle\Mail\MailFactoryInterface;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\SecurityBundle\Util\TokenGeneratorInterface;

/**
 * Compares user-email and contact main-email.
 * If they are different a confirmation link will be send.
 */
class EmailConfirmationListener
{
    /**
     * @var MailFactoryInterface
     */
    private $mailFactory;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TokenGeneratorInterface
     */
    private $tokenGenerator;

    /**
     * @var EmailConfirmationTokenRepository
     */
    private $emailConformationRepository;

    /**
     * @param MailFactoryInterface $mailFactory
     * @param EntityManagerInterface $entityManager
     * @param EmailConfirmationTokenRepository $emailConformationRepository
     * @param TokenGeneratorInterface $tokenGenerator
     */
    public function __construct(
        MailFactoryInterface $mailFactory,
        EntityManagerInterface $entityManager,
        EmailConfirmationTokenRepository $emailConformationRepository,
        TokenGeneratorInterface $tokenGenerator
    ) {
        $this->mailFactory = $mailFactory;
        $this->entityManager = $entityManager;
        $this->emailConformationRepository = $emailConformationRepository;
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
     * Send confirmation-email if email-address has changed.
     *
     * @param CommunityEvent $event
     */
    public function sendConfirmationOnEmailChange(CommunityEvent $event)
    {
        $user = $event->getUser();

        if (!$user instanceof User) {
            throw new \RuntimeException('Community bundle user need to be instance uf Sulu User');
        }

        if ($user->getEmail() === $user->getContact()->getMainEmail()) {
            return;
        }

        $entity = $this->emailConformationRepository->findByUser($user);
        $token = $this->tokenGenerator->generateToken();
        if (!$entity instanceof EmailConfirmationToken) {
            $entity = new EmailConfirmationToken($user);
            $this->entityManager->persist($entity);
        }

        $entity->setToken($token);
        $this->entityManager->flush();

        $this->mailFactory->sendEmails(
            Mail::create(
                $event->getConfigProperty(Configuration::EMAIL_FROM),
                $event->getConfigProperty(Configuration::EMAIL_TO),
                $event->getConfigTypeProperty(Configuration::TYPE_EMAIL_CONFIRMATION, Configuration::EMAIL)
            )->setUserEmail($user->getContact()->getMainEmail()),
            $user,
            ['token' => $entity->getToken()]
        );
    }
}
