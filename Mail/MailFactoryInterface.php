<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Mail;

use Sulu\Bundle\SecurityBundle\Entity\User;

/**
 * Interface for user sending-emails.
 */
interface MailFactoryInterface
{
    /**
     * Send emails by specific settings.
     *
     * @param mixed[] $parameters
     */
    public function sendEmails(Mail $mail, User $user, array $parameters = []): void;
}
