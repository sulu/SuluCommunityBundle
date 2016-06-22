<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\CommunityBundle\Entity\BlacklistItemRepository;

/**
 * Manages blacklist-items.
 */
class BlacklistItemManager implements BlacklistItemManagerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var BlacklistItemRepository
     */
    private $blacklistItemRepository;

    /**
     * @param EntityManagerInterface $entityManager
     * @param BlacklistItemRepository $blacklistItemRepository
     */
    public function __construct(EntityManagerInterface $entityManager, BlacklistItemRepository $blacklistItemRepository)
    {
        $this->entityManager = $entityManager;
        $this->blacklistItemRepository = $blacklistItemRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        return $this->blacklistItemRepository->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        $item = $this->blacklistItemRepository->createNew();

        $this->entityManager->persist($item);

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($ids)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        foreach ($ids as $id) {
            $this->entityManager->remove(
                $this->entityManager->getReference($this->blacklistItemRepository->getClassName(), $id)
            );
        }
    }
}
