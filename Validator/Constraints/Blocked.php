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

use Symfony\Component\Validator\Attribute\HasNamedArguments;
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
     * @see Exist::__construct()
     *
     * @param array<string, mixed>|null $options
     * @param string[]|string|null $groups
     */
    #[HasNamedArguments]
    public function __construct(
        ?array $options = null,
        ?string $message = null,
        array|string|null $groups = null,
        mixed $payload = null,
    ) {
        // Symfony 7.2 and older hand the options of a mapping to the constructor as an
        // array; 7.3 and later spread them as named arguments, thanks to the attribute above
        if (null !== $options) {
            $message ??= $options['message'] ?? null;
            $groups ??= $options['groups'] ?? null;
            $payload ??= $options['payload'] ?? null;
        }

        parent::__construct(null, null === $groups ? null : (array) $groups, $payload);

        $this->message = $message ?? $this->message;
    }

    public function validatedBy(): string
    {
        return 'blocked_validator';
    }
}
