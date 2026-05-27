<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\CommunityBundle\Entity\InvalidTypeException;
use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleItem;

class RegistrationRuleItemTest extends TestCase
{
    public function testConstructor(): void
    {
        $item = new RegistrationRuleItem('*@sulu.io', RegistrationRuleItem::TYPE_REQUEST);
        $this->assertSame('*@sulu.io', $item->getPattern());
        $this->assertSame('[^@]*@sulu\.io', $item->getRegexp());
        $this->assertSame(RegistrationRuleItem::TYPE_REQUEST, $item->getType());
    }

    public function testEmptyConstructor(): void
    {
        $item = new RegistrationRuleItem();
        $this->assertNull($item->getPattern());
        $this->assertNull($item->getRegexp());
        $this->assertNull($item->getType());
    }

    public function testSetPattern(): void
    {
        $item = new RegistrationRuleItem();
        $item->setPattern('*@sulu.io');

        $this->assertSame('*@sulu.io', $item->getPattern());
        $this->assertSame('[^@]*@sulu\.io', $item->getRegexp());
    }

    public function testSetPatternNoWildcard(): void
    {
        $item = new RegistrationRuleItem();
        $item->setPattern('test@sulu.io');

        $this->assertSame('test@sulu.io', $item->getPattern());
        $this->assertSame('test@sulu\.io', $item->getRegexp());
    }

    public function testSetTypeRequest(): void
    {
        $item = new RegistrationRuleItem();
        $item->setType(RegistrationRuleItem::TYPE_REQUEST);

        $this->assertSame(RegistrationRuleItem::TYPE_REQUEST, $item->getType());
    }

    public function testSetTypeBlock(): void
    {
        $item = new RegistrationRuleItem();
        $item->setType(RegistrationRuleItem::TYPE_BLOCK);

        $this->assertSame(RegistrationRuleItem::TYPE_BLOCK, $item->getType());
    }

    public function testSetTypeBlockInvalid(): void
    {
        $this->expectException(InvalidTypeException::class);

        $item = new RegistrationRuleItem();
        $item->setType('test');
    }
}
