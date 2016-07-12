<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Tests\Unit\Validator;

use Sulu\Bundle\CommunityBundle\Entity\BlacklistItem;
use Sulu\Bundle\CommunityBundle\Entity\BlacklistItemRepository;
use Sulu\Bundle\CommunityBundle\Validator\Constraints\Blocked;
use Sulu\Bundle\CommunityBundle\Validator\Constraints\BlockedValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class BlockedValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testValidate()
    {
        $context = $this->prophesize(ExecutionContextInterface::class);
        $repository = $this->prophesize(BlacklistItemRepository::class);
        $validator = new BlockedValidator($repository->reveal());

        $validator->initialize($context->reveal());
        $repository->findBySender('test@sulu.io')
            ->willReturn(
                [
                    new BlacklistItem('*@sulu.io', BlacklistItem::TYPE_REQUEST),
                    new BlacklistItem('test@sulu.io', BlacklistItem::TYPE_BLOCK),
                ]
            );

        $validator->validate('test@sulu.io', new Blocked());

        $context->addViolation('The email "%email%" is blocked.', ['%email%' => 'test@sulu.io'])->shouldBeCalled();
    }

    public function testValidateNoBlocking()
    {
        $context = $this->prophesize(ExecutionContextInterface::class);
        $repository = $this->prophesize(BlacklistItemRepository::class);
        $validator = new BlockedValidator($repository->reveal());

        $validator->initialize($context->reveal());
        $repository->findBySender('test@sulu.io')
            ->willReturn([new BlacklistItem('*@sulu.io', BlacklistItem::TYPE_REQUEST)]);

        $validator->validate('test@sulu.io', new Blocked());

        $context->addViolation('The email "%email%" is blocked.', ['%email%' => 'test@sulu.io'])->shouldNotBeCalled();
    }

    public function testValidateNoMatch()
    {
        $context = $this->prophesize(ExecutionContextInterface::class);
        $repository = $this->prophesize(BlacklistItemRepository::class);
        $validator = new BlockedValidator($repository->reveal());

        $validator->initialize($context->reveal());
        $repository->findBySender('test@sulu.io')->willReturn([]);

        $validator->validate('test@sulu.io', new Blocked());

        $context->addViolation('The email "%email%" is blocked.', ['%email%' => 'test@sulu.io'])->shouldNotBeCalled();
    }
}
