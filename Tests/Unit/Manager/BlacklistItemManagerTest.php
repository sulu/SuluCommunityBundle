<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Tests\Unit\Manager;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\CommunityBundle\Entity\BlacklistItem;
use Sulu\Bundle\CommunityBundle\Entity\BlacklistItemRepository;
use Sulu\Bundle\CommunityBundle\Manager\BlacklistItemManager;

class BlacklistItemManagerTest extends TestCase
{
    private $repository;
    private $entityManager;
    private $manager;

    public function setUp(): void
    {
        $this->repository = $this->prophesize(BlacklistItemRepository::class);
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);

        $this->manager = new BlacklistItemManager($this->entityManager->reveal(), $this->repository->reveal());
    }

    public function testCreate(): void
    {
        $entity = new BlacklistItem();

        $this->repository->createNew()->willReturn($entity)->shouldBeCalled();
        $this->entityManager->persist($entity)->shouldBeCalled();

        $this->assertEquals($entity, $this->manager->create());
    }

    public function testFind(): void
    {
        $entity = new BlacklistItem();
        $this->repository->find(1)->willReturn($entity)->shouldBeCalled();

        $this->assertEquals($entity, $this->manager->find(1));
    }

    public function testDeleteSingle(): void
    {
        $className = BlacklistItem::class;
        $this->repository->getClassName()->willReturn($className);

        $entity = new BlacklistItem();
        $this->entityManager->getReference($className, 1)->willReturn($entity);
        $this->entityManager->remove($entity)->shouldBeCalled();

        $this->manager->delete(1);
    }

    public function testDeleteList(): void
    {
        $className = BlacklistItem::class;
        $this->repository->getClassName()->willReturn($className);

        $entity1 = new BlacklistItem();
        $entity2 = new BlacklistItem();
        $this->entityManager->getReference($className, 1)->willReturn($entity1);
        $this->entityManager->getReference($className, 2)->willReturn($entity2);
        $this->entityManager->remove($entity1)->shouldBeCalled();
        $this->entityManager->remove($entity2)->shouldBeCalled();

        $this->manager->delete([1, 2]);
    }
}
