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

use Doctrine\Common\Persistence\ObjectManager;
use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Sulu\Bundle\CommunityBundle\Entity\BlacklistItem;
use Sulu\Bundle\CommunityBundle\Entity\BlacklistItemRepository;
use Sulu\Bundle\CommunityBundle\Entity\BlacklistUser;
use Sulu\Bundle\CommunityBundle\Event\CommunityEvent;
use Sulu\Bundle\CommunityBundle\Mail\Mail;
use Sulu\Bundle\CommunityBundle\Mail\MailFactoryInterface;
use Sulu\Bundle\SecurityBundle\Util\TokenGeneratorInterface;

/**
 * Interrupts registration to avoid register request-type emails.
 */
class BlacklistListener
{
    /**
     * @var BlacklistItemRepository
     */
    private $blacklistItemRepository;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var TokenGeneratorInterface
     */
    private $tokenGenerator;

    /**
     * @var MailFactoryInterface
     */
    private $mailFactory;

    /**
     * @param BlacklistItemRepository $blacklistItemRepository
     * @param ObjectManager $objectManager
     * @param TokenGeneratorInterface $tokenGenerator
     * @param MailFactoryInterface $mailFactory
     */
    public function __construct(
        BlacklistItemRepository $blacklistItemRepository,
        ObjectManager $objectManager,
        TokenGeneratorInterface $tokenGenerator,
        MailFactoryInterface $mailFactory
    ) {
        $this->blacklistItemRepository = $blacklistItemRepository;
        $this->objectManager = $objectManager;
        $this->tokenGenerator = $tokenGenerator;
        $this->mailFactory = $mailFactory;
    }

    /**
     * Validates email and interrupts registration process if email matches blacklist.
     *
     * @param CommunityEvent $event
     */
    public function validateEmail(CommunityEvent $event)
    {
        if (BlacklistItem::TYPE_REQUEST !== $this->getType($event->getUser()->getEmail())) {
            return;
        }

        $blacklistUser = new BlacklistUser(
            $this->tokenGenerator->generateToken(),
            $event->getConfigProperty(Configuration::WEBSPACE_KEY),
            $event->getUser()
        );
        $this->objectManager->persist($blacklistUser);
        $this->objectManager->flush();

        $this->mailFactory->sendEmails(
            Mail::create(
                $event->getConfigProperty(Configuration::EMAIL_FROM),
                $event->getConfigProperty(Configuration::EMAIL_TO),
                $event->getConfigTypeProperty(Configuration::TYPE_BLACKLISTED, Configuration::EMAIL)
            ),
            $event->getUser(),
            ['token' => $blacklistUser->getToken()]
        );

        $event->stopPropagation();
    }

    /**
     * Returns blacklist-type of given email.
     *
     * @param string $email
     *
     * @return string|null
     */
    private function getType($email)
    {
        $items = $this->blacklistItemRepository->findBySender($email);

        if (0 === count($items)) {
            return null;
        }

        foreach ($items as $item) {
            if (BlacklistItem::TYPE_BLOCK === $item->getType()) {
                return BlacklistItem::TYPE_BLOCK;
            }
        }

        return BlacklistItem::TYPE_REQUEST;
    }
}
