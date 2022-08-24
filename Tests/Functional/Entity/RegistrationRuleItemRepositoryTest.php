<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Tests\Functional\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleItem;
use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleItemRepository;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class RegistrationRuleItemRepositoryTest extends SuluTestCase
{
    protected function setUp(): void
    {
        $this->purgeDatabase();
    }

    public function testFindBySender(): void
    {
        $item1 = new RegistrationRuleItem('*@sulu.io', RegistrationRuleItem::TYPE_BLOCK);
        $item2 = new RegistrationRuleItem('test@sulu.io', RegistrationRuleItem::TYPE_REQUEST);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getEntityManager();
        /** @var RegistrationRuleItemRepository $repository */
        $repository = $entityManager->getRepository(RegistrationRuleItem::class);

        $entityManager->persist($item1);
        $entityManager->persist($item2);
        $entityManager->flush();
        $entityManager->clear();

        $items = \array_map(
            function (RegistrationRuleItem $item) {
                return ['pattern' => $item->getPattern(), 'type' => $item->getType()];
            },
            $repository->findBySender('test@sulu.io')
        );

        $this->assertContains(['pattern' => '*@sulu.io', 'type' => RegistrationRuleItem::TYPE_BLOCK], $items);
        $this->assertContains(['pattern' => 'test@sulu.io', 'type' => RegistrationRuleItem::TYPE_REQUEST], $items);
    }
}
