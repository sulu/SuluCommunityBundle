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
use Sulu\Bundle\CommunityBundle\Entity\BlacklistItem;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class BlacklistItemRepositoryTest extends SuluTestCase
{
    public function setUp(): void
    {
        $this->purgeDatabase();
    }

    public function testFindBySender(): void
    {
        $item1 = new BlacklistItem('*@sulu.io', BlacklistItem::TYPE_BLOCK);
        $item2 = new BlacklistItem('test@sulu.io', BlacklistItem::TYPE_REQUEST);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getEntityManager();
        $repository = $entityManager->getRepository(BlacklistItem::class);

        $entityManager->persist($item1);
        $entityManager->persist($item2);
        $entityManager->flush();
        $entityManager->clear();

        $items = array_map(
            function(BlacklistItem $item) {
                return ['pattern' => $item->getPattern(), 'type' => $item->getType()];
            },
            $repository->findBySender('test@sulu.io')
        );

        $this->assertContains(['pattern' => '*@sulu.io', 'type' => BlacklistItem::TYPE_BLOCK], $items);
        $this->assertContains(['pattern' => 'test@sulu.io', 'type' => BlacklistItem::TYPE_REQUEST], $items);
    }
}
