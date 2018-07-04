<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Sulu\Component\Security\Authentication\UserInterface;

class UserAccessTokenRepository extends EntityRepository
{
    public function create(UserInterface $user, string $service, string $identifier): UserAccessToken
    {
        $class = $this->getClassName();

        return new $class($user, $service, $identifier);
    }

    public function findByIdentifier(string $service, string $identifier): ?UserAccessToken
    {
        return $this->findOneBy(['service' => $service, 'identifier' => $identifier]);
    }
}
