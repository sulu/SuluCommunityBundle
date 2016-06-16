<?php

namespace Sulu\Bundle\CommunityBundle\Validator\Constraints;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ExistValidator extends ConstraintValidator
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;
    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ($constraint instanceof Exist && $constraint->entity) {
            /** @var EntityRepository $repository */
            $repository = $this->entityManager->getRepository($constraint->entity);
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
