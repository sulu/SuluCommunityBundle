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
use Sulu\Bundle\CommunityBundle\Validator\Constraints\Exist;

class ExistTest extends TestCase
{
    public function testNamedArgumentsAndOptionArrayAreEquivalent(): void
    {
        $named = new Exist(
            columns: ['email'],
            entity: 'SuluContactBundle:Contact',
            message: 'Not found.',
            groups: 'password_forget',
        );
        $legacy = new Exist([
            'columns' => ['email'],
            'entity' => 'SuluContactBundle:Contact',
            'message' => 'Not found.',
            'groups' => 'password_forget',
        ]);

        foreach ([$named, $legacy] as $constraint) {
            $this->assertSame(['email'], $constraint->columns);
            $this->assertSame('SuluContactBundle:Contact', $constraint->entity);
            $this->assertSame('Not found.', $constraint->message);
            $this->assertSame(['password_forget'], $constraint->groups);
        }
    }

    public function testDefaults(): void
    {
        $constraint = new Exist();

        $this->assertSame([], $constraint->columns);
        $this->assertSame('', $constraint->entity);
        $this->assertSame('The value "%string%" was not found.', $constraint->message);
        $this->assertSame(['Default'], $constraint->groups);
    }
}
