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
use Sulu\Bundle\CommunityBundle\Validator\Constraints\Blocked;

class BlockedTest extends TestCase
{
    public function testNamedArgumentsAndOptionArrayAreEquivalent(): void
    {
        $named = new Blocked(message: 'Blocked.', groups: 'registration');
        $legacy = new Blocked(['message' => 'Blocked.', 'groups' => 'registration']);

        foreach ([$named, $legacy] as $constraint) {
            $this->assertSame('Blocked.', $constraint->message);
            $this->assertSame(['registration'], $constraint->groups);
        }
    }

    public function testDefaults(): void
    {
        $constraint = new Blocked();

        $this->assertSame('The email "%email%" is blocked.', $constraint->message);
        $this->assertSame(['Default'], $constraint->groups);
    }
}
