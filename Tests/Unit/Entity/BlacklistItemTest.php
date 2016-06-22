<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Tests\Unit\Entity;

use Sulu\Bundle\CommunityBundle\Entity\BlacklistItem;
use Sulu\Bundle\CommunityBundle\Entity\InvalidTypeException;

class BlacklistItemTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $item = new BlacklistItem('*@sulu.io', BlacklistItem::TYPE_REQUEST);
        $this->assertEquals('*@sulu.io', $item->getPattern());
        $this->assertEquals('[^@]*@sulu\.io', $item->getRegexp());
        $this->assertEquals(BlacklistItem::TYPE_REQUEST, $item->getType());
    }

    public function testEmptyConstructor()
    {
        $item = new BlacklistItem();
        $this->assertEquals(null, $item->getPattern());
        $this->assertEquals(null, $item->getRegexp());
        $this->assertEquals(null, $item->getType());
    }

    public function testSetPattern()
    {
        $item = new BlacklistItem();
        $item->setPattern('*@sulu.io');

        $this->assertEquals('*@sulu.io', $item->getPattern());
        $this->assertEquals('[^@]*@sulu\.io', $item->getRegexp());
    }

    public function testSetPatternNoWildcard()
    {
        $item = new BlacklistItem();
        $item->setPattern('test@sulu.io');

        $this->assertEquals('test@sulu.io', $item->getPattern());
        $this->assertEquals('test@sulu\.io', $item->getRegexp());
    }

    public function setTypeRequest()
    {
        $item = new BlacklistItem();
        $item->setPattern(BlacklistItem::TYPE_REQUEST);

        $this->assertEquals(BlacklistItem::TYPE_REQUEST, $item->getType());
    }

    public function setTypeBlock()
    {
        $item = new BlacklistItem();
        $item->setPattern(BlacklistItem::TYPE_BLOCK);

        $this->assertEquals(BlacklistItem::TYPE_BLOCK, $item->getType());
    }

    public function setTypeBlockInvalid()
    {
        $this->setExpectedException(InvalidTypeException::class);

        $item = new BlacklistItem();
        $item->setPattern('test');
    }
}
