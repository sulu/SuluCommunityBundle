<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\CommunityBundle\Entity\UserAccessToken;
use Sulu\Component\Security\Authentication\UserInterface;

class UserAccessTokenTest extends TestCase
{
    public function testGetUser()
    {
        $user = $this->prophesize(UserInterface::class);
        $entity = new UserAccessToken($user->reveal(), 'facebook', '123-123-123');

        $this->assertEquals($user->reveal(), $entity->getUser());
    }

    public function testGetService()
    {
        $user = $this->prophesize(UserInterface::class);
        $entity = new UserAccessToken($user->reveal(), 'facebook', '123-123-123');

        $this->assertEquals('facebook', $entity->getService());
    }

    public function testGetIdentifier()
    {
        $user = $this->prophesize(UserInterface::class);
        $entity = new UserAccessToken($user->reveal(), 'facebook', '123-123-123');

        $this->assertEquals('123-123-123', $entity->getService());
    }

    public function testGetAccessToken()
    {
        $user = $this->prophesize(UserInterface::class);
        $entity = new UserAccessToken($user->reveal(), 'facebook', '123-123-123');

        $this->assertEquals('', $entity->getAccessToken());
    }

    public function testSetAccessToken()
    {
        $user = $this->prophesize(UserInterface::class);
        $entity = new UserAccessToken($user->reveal(), 'facebook', '123-123-123');

        $this->assertEquals($entity, $entity->setAccessToken('my-token'));
        $this->assertEquals('my-token', $entity->getAccessToken());
    }
}
