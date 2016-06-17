<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for the BlockedValidator.
 */
class Blocked extends Constraint
{
    /**
     * @var string
     */
    public $message = 'The email "%email%" is blocked.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'blocked_validator';
    }
}
