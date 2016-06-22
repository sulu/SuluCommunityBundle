<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Mail;

use Sulu\Bundle\SecurityBundle\Entity\BaseUser;

/**
 * Interface for user sending-emails.
 */
interface MailFactoryInterface
{
    /**
     * Send emails by specific settings.
     *
     * @param Mail $mail
     * @param BaseUser $user
     * @param array $parameters
     */
    public function sendEmails(Mail $mail, BaseUser $user, $parameters = []);
}
