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

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\CommunityBundle\Manager\CommunityManager;
use Sulu\Bundle\CommunityBundle\Manager\CommunityManagerRegistry;

class CommunityManagerRegistryTest extends TestCase
{
    public function testGet()
    {
        $manager = $this->prophesize(CommunityManager::class);
        $registry = new CommunityManagerRegistry(['sulu_io' => $manager->reveal()]);

        $this->assertEquals($manager->reveal(), $registry->get('sulu_io'));
    }

    public function testGetNotExists()
    {
        $this->expectException(\Exception::class);

        $registry = new CommunityManagerRegistry();

        $registry->get('sulu_io');
    }

    public function testHas()
    {
        $manager = $this->prophesize(CommunityManager::class);
        $registry = new CommunityManagerRegistry(['sulu_io' => $manager->reveal()]);

        $this->assertTrue($registry->has('sulu_io'));
        $this->assertFalse($registry->has('test_io'));
    }
}
