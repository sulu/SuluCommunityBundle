<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleItem;
use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleItemRepository;

/**
 * Manages registration-rule-items.
 */
class RegistrationRuleItemManager implements RegistrationRuleItemManagerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var RegistrationRuleItemRepository
     */
    private $blacklistItemRepository;

    public function __construct(EntityManagerInterface $entityManager, RegistrationRuleItemRepository $blacklistItemRepository)
    {
        $this->entityManager = $entityManager;
        $this->blacklistItemRepository = $blacklistItemRepository;
    }

    public function find(int $id): RegistrationRuleItem
    {
        /** @var RegistrationRuleItem $blacklistItem */
        $blacklistItem = $this->blacklistItemRepository->find($id);

        return $blacklistItem;
    }

    public function create(): RegistrationRuleItem
    {
        $item = $this->blacklistItemRepository->createNew();

        $this->entityManager->persist($item);

        return $item;
    }

    public function delete($ids): void
    {
        if (!\is_array($ids)) {
            $ids = [$ids];
        }

        foreach ($ids as $id) {
            /** @var RegistrationRuleItem $object */
            $object = $this->entityManager->getReference($this->blacklistItemRepository->getClassName(), $id);

            $this->entityManager->remove($object);
        }
    }
}
