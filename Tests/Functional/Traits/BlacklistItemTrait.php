<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Tests\Functional\Traits;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\CommunityBundle\Entity\BlacklistItem;

trait BlacklistItemTrait
{
    private function createBlacklistItem(
        EntityManagerInterface $entityManager,
        string $pattern,
        string $type
    ): BlacklistItem {
        /** @var BlacklistItem $blackListItem */
        $blackListItem = $entityManager->getRepository(BlacklistItem::class)->createNew();
        $blackListItem->setPattern($pattern)
            ->setType($type);
        $entityManager->persist($blackListItem);
        $entityManager->flush();

        return $blackListItem;
    }
}
