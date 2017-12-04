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

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Sulu\Component\Persistence\Repository\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Provides special queries for email-confirmation-tokens.
 */
class EmailConfirmationTokenRepository extends EntityRepository
{
    /**
     * Return email-confirmation for given token.
     *
     * @param string $token
     *
     * @return EmailConfirmationToken|object|null
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

    /**
     * Return email-confirmation for given token.
     *
     * @param UserInterface $user
     *
     * @return EmailConfirmationToken|object|null
     */
    public function findByUser($user)
    {
        try {
            return $this->findOneBy(['user' => $user]);
        } catch (NonUniqueResultException $e) {
            return null;
        } catch (NoResultException $e) {
            return null;
        }
    }
}
