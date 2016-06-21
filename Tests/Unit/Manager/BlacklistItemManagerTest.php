<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Tests\Unit\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\CommunityBundle\Entity\BlacklistItem;
use Sulu\Bundle\CommunityBundle\Entity\BlacklistItemRepository;
use Sulu\Bundle\CommunityBundle\Manager\BlacklistItemManager;

class BlacklistItemManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BlacklistItemRepository
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var BlacklistItemManager
     */
    private $manager;

    public function setUp()
    {
        $this->repository = $this->prophesize(BlacklistItemRepository::class);
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);

        $this->manager = new BlacklistItemManager($this->entityManager->reveal(), $this->repository->reveal());
    }

    public function testCreate()
    {
        $entity = new \stdClass();

        $this->repository->createNew()->willReturn($entity)->shouldBeCalled();
        $this->entityManager->persist($entity)->shouldBeCalled();

        $this->assertEquals($entity, $this->manager->create());
    }

    public function testFind()
    {
        $entity = new \stdClass();
        $this->repository->find(1)->willReturn($entity)->shouldBeCalled();

        $this->assertEquals($entity, $this->manager->find(1));
    }

    public function testDeleteSingle()
    {
        $className = BlacklistItem::class;
        $this->repository->getClassName()->willReturn($className);

        $entity = new \stdClass();
        $this->entityManager->getReference($className, 1)->willReturn($entity);
        $this->entityManager->remove($entity)->shouldBeCalled();

        $this->manager->delete(1);
    }

    public function testDeleteList()
    {
        $className = BlacklistItem::class;
        $this->repository->getClassName()->willReturn($className);

        $entity1 = new \stdClass();
        $entity2 = new \stdClass();
        $this->entityManager->getReference($className, 1)->willReturn($entity1);
        $this->entityManager->getReference($className, 2)->willReturn($entity2);
        $this->entityManager->remove($entity1)->shouldBeCalled();
        $this->entityManager->remove($entity2)->shouldBeCalled();

        $this->manager->delete([1, 2]);
    }
}
