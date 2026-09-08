<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

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
     * @see Exist::__construct()
     *
     * @param string[]|null $groups
     */
    public function __construct(
        ?array $options = null,
        ?string $message = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        if (null !== $options) {
            throw new InvalidArgumentException(\sprintf('Passing an array of options to configure the "%s" constraint is no longer supported.', static::class));
        }

        parent::__construct(null, $groups, $payload);

        $this->message = $message ?? $this->message;
    }

    public function validatedBy(): string
    {
        return 'blocked_validator';
    }
}
