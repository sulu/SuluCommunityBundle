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
     * Symfony 8 no longer applies the array of options passed to
     * Constraint::__construct(), so the options are declared as named arguments.
     * The options slot is kept in first position, as Symfony does for its own
     * constraints, so that the legacy call fails loudly instead of silently
     * losing every option.
     *
     * @param string[]|null $columns
     * @param string[]|string|null $groups
     */
    public function __construct(
        ?array $options = null,
        ?array $columns = null,
        ?string $entity = null,
        ?string $message = null,
        array|string|null $groups = null,
        mixed $payload = null,
    ) {
        if (null !== $options) {
            throw new InvalidArgumentException(\sprintf('Passing an array of options to configure the "%s" constraint is no longer supported.', static::class));
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
