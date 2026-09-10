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
     * @param array{message?: string, groups?: string[]|string, payload?: mixed}|null $options
     * @param string[]|string|null $groups
     */
    #[HasNamedArguments]
    public function __construct(
        ?array $options = null,
        ?string $message = null,
        array|string|null $groups = null,
        mixed $payload = null,
    ) {
        // The mapping loaders spread the options as named arguments, thanks to the attribute
        // above. The array slot is only reached by code building the constraint by hand, the
        // way Constraint::__construct() accepted before Symfony 8.
        if (null !== $options) {
            $message ??= $options['message'] ?? null;
            $groups ??= $options['groups'] ?? null;
            $payload ??= $options['payload'] ?? null;
        }

        parent::__construct(null, null === $groups ? null : \array_values((array) $groups), $payload);

        $this->message = $message ?? $this->message;
    }

    public function validatedBy(): string
    {
        return 'blocked_validator';
    }
}
