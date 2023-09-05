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
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleItem;
use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleItemRepository;
use Sulu\Bundle\CommunityBundle\Manager\RegistrationRuleItemManager;

class RegistrationRuleItemManagerTest extends TestCase
{
    /**
     * @var ObjectProphecy<RegistrationRuleItemRepository>
     */
    private $repository;

    /**
     * @var ObjectProphecy<EntityManagerInterface>
     */
    private $entityManager;

    /**
     * @var RegistrationRuleItemManager
     */
    private $manager;

    protected function setUp(): void
    {
        $this->repository = $this->prophesize(RegistrationRuleItemRepository::class);
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);

        $this->manager = new RegistrationRuleItemManager($this->entityManager->reveal(), $this->repository->reveal());
    }

    public function testCreate(): void
    {
        $entity = new RegistrationRuleItem();

        $this->repository->createNew()->willReturn($entity)->shouldBeCalled();
        $this->entityManager->persist($entity)->shouldBeCalled();

        $this->assertSame($entity, $this->manager->create());
    }

    public function testFind(): void
    {
        $entity = new RegistrationRuleItem();
        $this->repository->find(1)->willReturn($entity)->shouldBeCalled();

        $this->assertSame($entity, $this->manager->find(1));
    }

    public function testDeleteSingle(): void
    {
        $className = RegistrationRuleItem::class;
        $this->repository->getClassName()->willReturn($className);

        $entity = new RegistrationRuleItem();
        $this->entityManager->getReference($className, 1)->willReturn($entity);
        $this->entityManager->remove($entity)->shouldBeCalled();

        $this->manager->delete(1);
    }

    public function testDeleteList(): void
    {
        $className = RegistrationRuleItem::class;
        $this->repository->getClassName()->willReturn($className);

        $entity1 = new RegistrationRuleItem();
        $entity2 = new RegistrationRuleItem();
        $this->entityManager->getReference($className, 1)->willReturn($entity1);
        $this->entityManager->getReference($className, 2)->willReturn($entity2);
        $this->entityManager->remove($entity1)->shouldBeCalled();
        $this->entityManager->remove($entity2)->shouldBeCalled();

        $this->manager->delete([1, 2]);
    }
}
