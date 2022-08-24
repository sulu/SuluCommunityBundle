<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Entity;

use Sulu\Component\Persistence\Repository\ORM\EntityRepository;

/**
 * Entity-Repository for registration-rule-items.
 *
 * @method RegistrationRuleItem createNew()
 */
class RegistrationRuleItemRepository extends EntityRepository
{
    /**
     * Returns items which matches given email.
     *
     * @param string $email
     *
     * @return RegistrationRuleItem[]
     */
    public function findBySender($email)
    {
        $queryBuilder = $this->createQueryBuilder('entity')
            ->where('REGEXP(:email, entity.regexp) = true')
            ->setParameter('email', $email);

        /** @var RegistrationRuleItem[] */
        return $queryBuilder->getQuery()->getResult();
    }
}
