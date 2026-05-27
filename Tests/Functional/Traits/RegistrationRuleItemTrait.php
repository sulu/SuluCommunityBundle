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
        /** @var RegistrationRuleItemRepository $registrationRuleItemRepository */
        $registrationRuleItemRepository = $entityManager->getRepository(RegistrationRuleItem::class);

        /** @var RegistrationRuleItem $registrationRuleItem */
        $registrationRuleItem = $registrationRuleItemRepository->createNew();
        $registrationRuleItem->setPattern($pattern)
            ->setType($type);
        $entityManager->persist($registrationRuleItem);
        $entityManager->flush();

        return $registrationRuleItem;
    }
}
