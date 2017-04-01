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

use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;

/**
 * Contains information for sending emails.
 */
class Mail
{
    /**
     * Get mail settings for specific type.
     *
     * @param string|array $from
     * @param string|array $to
     * @param array $config
     *
     * @return Mail
     */
    public static function create($from, $to, $config)
    {
        return new self(
            $from,
            $to,
            $config[Configuration::EMAIL_SUBJECT],
            $config[Configuration::EMAIL_USER_TEMPLATE],
            $config[Configuration::EMAIL_ADMIN_TEMPLATE]
        );
    }

    /**
     * @var string|array
     */
    private $from;

    /**
     * @var string|array
     */
    private $to;

    /**
     * @var string
     */
    private $userEmail;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string|null
     */
    private $userTemplate;

    /**
     * @var string|null
     */
    private $adminTemplate;

    /**
     * @param string|array $from
     * @param string|array $to
     * @param string $subject
     * @param null|string $userTemplate
     * @param null|string $adminTemplate
     */
    public function __construct($from, $to, $subject, $userTemplate = null, $adminTemplate = null)
    {
        $this->from = $from;
        $this->to = $to;
        $this->subject = $subject;
        $this->userTemplate = $userTemplate;
        $this->adminTemplate = $adminTemplate;
    }

    /**
     * Returns from.
     *
     * @return string|array
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Returns to.
     *
     * @return string|array
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Returns subject.
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Returns user-template.
     *
     * @return null|string
     */
    public function getUserTemplate()
    {
        return $this->userTemplate;
    }

    /**
     * Returns admin-template.
     *
     * @return null|string
     */
    public function getAdminTemplate()
    {
        return $this->adminTemplate;
    }

    /**
     * Returns user-email.
     *
     * @return string
     */
    public function getUserEmail()
    {
        return $this->userEmail;
    }

    /**
     * Set user-email.
     * This setting overwrite the user-email.
     *
     * @param string $userEmail
     *
     * @return $this
     */
    public function setUserEmail($userEmail)
    {
        $this->userEmail = $userEmail;

        return $this;
    }
}
