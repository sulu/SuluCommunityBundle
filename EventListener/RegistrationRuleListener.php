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

use Doctrine\Persistence\ObjectManager;
use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleItem;
use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleItemRepository;
use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleUser;
use Sulu\Bundle\CommunityBundle\Event\UserRegisteredEvent;
use Sulu\Bundle\CommunityBundle\Mail\Mail;
use Sulu\Bundle\CommunityBundle\Mail\MailFactoryInterface;
use Sulu\Bundle\SecurityBundle\Util\TokenGeneratorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Interrupts registration to avoid register request-type emails.
 */
class RegistrationRuleListener implements EventSubscriberInterface
{
    /**
     * @var RegistrationRuleItemRepository
     */
    private $registrationRuleRepository;

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

    public function __construct(
        RegistrationRuleItemRepository $registrationRuleRepository,
        ObjectManager $objectManager,
        TokenGeneratorInterface $tokenGenerator,
        MailFactoryInterface $mailFactory
    ) {
        $this->registrationRuleRepository = $registrationRuleRepository;
        $this->objectManager = $objectManager;
        $this->tokenGenerator = $tokenGenerator;
        $this->mailFactory = $mailFactory;
    }

    /**
     * @return array<string, mixed>
     */
    public static function getSubscribedEvents()
    {
        return [
            UserRegisteredEvent::class => ['validateEmail', 51],
        ];
    }

    /**
     * Validates email and interrupts registration process if email matches blacklist.
     */
    public function validateEmail(UserRegisteredEvent $event): void
    {
        if (RegistrationRuleItem::TYPE_REQUEST !== $this->getType((string) $event->getUser()->getEmail())) {
            return;
        }

        $blacklistUser = new RegistrationRuleUser(
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
                $event->getConfigTypeProperty(Configuration::TYPE_REGISTRATION_RULEED, Configuration::EMAIL)
            ),
            $event->getUser(),
            ['token' => $blacklistUser->getToken()]
        );

        $event->stopPropagation();
    }

    /**
     * Returns blacklist-type of given email.
     */
    private function getType(string $email): ?string
    {
        $items = $this->registrationRuleRepository->findBySender($email);

        if (0 === \count($items)) {
            return null;
        }

        foreach ($items as $item) {
            if (RegistrationRuleItem::TYPE_BLOCK === $item->getType()) {
                return RegistrationRuleItem::TYPE_BLOCK;
            }
        }

        return RegistrationRuleItem::TYPE_REQUEST;
    }
}
