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
 * Constraint for the ExistValidator.
 */
class Exist extends Constraint
{
    /**
     * @var string
     */
    public $message = 'The value "%string%" was not found.';

    /**
     * @var string[]
     */
    public $columns = [];

    /**
     * @var string
     */
    public $entity = '';

    /**
     * Symfony 8 no longer applies the array of options passed to Constraint::__construct(),
     * so the options are declared as named arguments. The options slot is kept in first
     * position because the mapping loaders of Symfony 7.2 and older hand them over as an
     * array; 7.3 and later spread them as named arguments, thanks to the attribute below.
     *
     * @param array<string, mixed>|null $options
     * @param string[]|null $columns
     * @param string[]|string|null $groups
     */
    #[HasNamedArguments]
    public function __construct(
        ?array $options = null,
        ?array $columns = null,
        ?string $entity = null,
        ?string $message = null,
        array|string|null $groups = null,
        mixed $payload = null,
    ) {
        if (null !== $options) {
            $columns ??= $options['columns'] ?? null;
            $entity ??= $options['entity'] ?? null;
            $message ??= $options['message'] ?? null;
            $groups ??= $options['groups'] ?? null;
            $payload ??= $options['payload'] ?? null;
        }

        parent::__construct(null, null === $groups ? null : (array) $groups, $payload);

        $this->columns = $columns ?? $this->columns;
        $this->entity = $entity ?? $this->entity;
        $this->message = $message ?? $this->message;
    }

    public function validatedBy(): string
    {
        return 'exist_validator';
    }
}
