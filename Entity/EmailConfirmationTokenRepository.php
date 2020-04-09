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
     * @return EmailConfirmationToken|null
     */
    public function findByToken($token)
    {
        try {
            /** @var EmailConfirmationToken|null $emailConfirmationToken */
            $emailConfirmationToken = $this->findOneBy(['token' => $token]);

            return $emailConfirmationToken;
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
     * @return EmailConfirmationToken|null
     */
    public function findByUser($user)
    {
        try {
            /** @var EmailConfirmationToken|null $emailConfirmationToken */
            $emailConfirmationToken = $this->findOneBy(['user' => $user]);

            return $emailConfirmationToken;
        } catch (NonUniqueResultException $e) {
            return null;
        } catch (NoResultException $e) {
            return null;
        }
    }
}
