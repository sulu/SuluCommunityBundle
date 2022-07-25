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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * A validator to check if a value exist in a database column.
 */
class ExistValidator extends ConstraintValidator
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * ExistValidator constructor.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if ($constraint instanceof Exist && $constraint->entity) {
            /** @var class-string $class */
            $class = $constraint->entity;
            /** @var EntityRepository $repository */
            $repository = $this->entityManager->getRepository($class);
            $qb = $repository->createQueryBuilder('u');

            foreach ($constraint->columns as $column) {
                $qb->orWhere($qb->expr()->like('u.' . $column, ':value'));
            }

            $qb->setParameter('value', $value);
            $qb->setMaxResults(1);
            $result = $qb->getQuery()->getScalarResult();

            if (empty($result)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('%string%', $value)
                    ->addViolation();
            }
        }
    }
}
