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

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Sulu\Component\Persistence\Repository\ORM\EntityRepository;

class BlacklistUserRepository extends EntityRepository
{
    /**
     * Return blacklist-user for given token.
     *
     * @param string $token
     *
     * @return BlacklistUser|object|null
     */
    public function findByToken($token)
    {
        try {
            return $this->findOneBy(['token' => $token]);
        } catch (NonUniqueResultException $e) {
            return null;
        } catch (NoResultException $e) {
            return null;
        }
    }
}
