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
use Sulu\Bundle\CommunityBundle\Entity\BlacklistItem;
use Sulu\Bundle\CommunityBundle\Entity\InvalidTypeException;

class BlacklistItemTest extends TestCase
{
    public function testConstructor(): void
    {
        $item = new BlacklistItem('*@sulu.io', BlacklistItem::TYPE_REQUEST);
        $this->assertSame('*@sulu.io', $item->getPattern());
        $this->assertSame('[^@]*@sulu\.io', $item->getRegexp());
        $this->assertSame(BlacklistItem::TYPE_REQUEST, $item->getType());
    }

    public function testEmptyConstructor(): void
    {
        $item = new BlacklistItem();
        $this->assertNull($item->getPattern());
        $this->assertNull($item->getRegexp());
        $this->assertNull($item->getType());
    }

    public function testSetPattern(): void
    {
        $item = new BlacklistItem();
        $item->setPattern('*@sulu.io');

        $this->assertSame('*@sulu.io', $item->getPattern());
        $this->assertSame('[^@]*@sulu\.io', $item->getRegexp());
    }

    public function testSetPatternNoWildcard(): void
    {
        $item = new BlacklistItem();
        $item->setPattern('test@sulu.io');

        $this->assertSame('test@sulu.io', $item->getPattern());
        $this->assertSame('test@sulu\.io', $item->getRegexp());
    }

    public function testSetTypeRequest(): void
    {
        $item = new BlacklistItem();
        $item->setType(BlacklistItem::TYPE_REQUEST);

        $this->assertSame(BlacklistItem::TYPE_REQUEST, $item->getType());
    }

    public function testSetTypeBlock(): void
    {
        $item = new BlacklistItem();
        $item->setType(BlacklistItem::TYPE_BLOCK);

        $this->assertSame(BlacklistItem::TYPE_BLOCK, $item->getType());
    }

    public function testSetTypeBlockInvalid(): void
    {
        $this->expectException(InvalidTypeException::class);

        $item = new BlacklistItem();
        $item->setType('test');
    }
}
