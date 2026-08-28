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
     * Symfony 8 removed the array of options accepted by Constraint::__construct(),
     * so the options have to be declared as named arguments.
     *
     * @param string[]|null $columns
     * @param string[]|string|null $groups
     */
    public function __construct(
        ?array $columns = null,
        ?string $entity = null,
        ?string $message = null,
        array|string|null $groups = null,
        mixed $payload = null,
    ) {
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
