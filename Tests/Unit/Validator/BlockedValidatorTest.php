<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleItem;
use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleItemRepository;
use Sulu\Bundle\CommunityBundle\Validator\Constraints\Blocked;
use Sulu\Bundle\CommunityBundle\Validator\Constraints\BlockedValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class BlockedValidatorTest extends TestCase
{
    public function testValidate(): void
    {
        $context = $this->prophesize(ExecutionContextInterface::class);
        $repository = $this->prophesize(RegistrationRuleItemRepository::class);
        $validator = new BlockedValidator($repository->reveal());

        $validator->initialize($context->reveal());
        $repository->findBySender('test@sulu.io')
            ->willReturn(
                [
                    new RegistrationRuleItem('*@sulu.io', RegistrationRuleItem::TYPE_REQUEST),
                    new RegistrationRuleItem('test@sulu.io', RegistrationRuleItem::TYPE_BLOCK),
                ]
            );

        $validator->validate('test@sulu.io', new Blocked());

        $context->addViolation('The email "%email%" is blocked.', ['%email%' => 'test@sulu.io'])->shouldBeCalled();
    }

    public function testValidateNoBlocking(): void
    {
        $context = $this->prophesize(ExecutionContextInterface::class);
        $repository = $this->prophesize(RegistrationRuleItemRepository::class);
        $validator = new BlockedValidator($repository->reveal());

        $validator->initialize($context->reveal());
        $repository->findBySender('test@sulu.io')
            ->willReturn([new RegistrationRuleItem('*@sulu.io', RegistrationRuleItem::TYPE_REQUEST)]);

        $validator->validate('test@sulu.io', new Blocked());

        $context->addViolation('The email "%email%" is blocked.', ['%email%' => 'test@sulu.io'])->shouldNotBeCalled();
    }

    public function testValidateNoMatch(): void
    {
        $context = $this->prophesize(ExecutionContextInterface::class);
        $repository = $this->prophesize(RegistrationRuleItemRepository::class);
        $validator = new BlockedValidator($repository->reveal());

        $validator->initialize($context->reveal());
        $repository->findBySender('test@sulu.io')->willReturn([]);

        $validator->validate('test@sulu.io', new Blocked());

        $context->addViolation('The email "%email%" is blocked.', ['%email%' => 'test@sulu.io'])->shouldNotBeCalled();
    }
}
