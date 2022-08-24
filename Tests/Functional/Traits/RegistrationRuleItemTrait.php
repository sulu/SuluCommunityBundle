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
use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleItem;
use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleItemRepository;

trait RegistrationRuleItemTrait
{
    private function createRegistrationRuleItem(
        EntityManagerInterface $entityManager,
        string $pattern,
        string $type
    ): RegistrationRuleItem {
        /** @var RegistrationRuleItemRepository $blackListItemRepository */
        $blackListItemRepository = $entityManager->getRepository(RegistrationRuleItem::class);

        /** @var RegistrationRuleItem $blackListItem */
        $blackListItem = $blackListItemRepository->createNew();
        $blackListItem->setPattern($pattern)
            ->setType($type);
        $entityManager->persist($blackListItem);
        $entityManager->flush();

        return $blackListItem;
    }
}
