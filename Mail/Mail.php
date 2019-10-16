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
     * @param mixed[] $config
     *
     * @return Mail
     */
    public static function create($from, $to, array $config): self
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
     * @var string|null
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
    public function __construct($from, $to, string $subject, ?string $userTemplate = null, ?string $adminTemplate = null)
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
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * Returns user-template.
     *
     * @return null|string
     */
    public function getUserTemplate(): ?string
    {
        return $this->userTemplate;
    }

    /**
     * Returns admin-template.
     *
     * @return null|string
     */
    public function getAdminTemplate(): ?string
    {
        return $this->adminTemplate;
    }

    /**
     * Returns user-email.
     *
     * @return string|null
     */
    public function getUserEmail(): ?string
    {
        return $this->userEmail;
    }

    /**
     * Set user-email.
     * This setting overwrite the user-email.
     *
     * @param string|null $userEmail
     *
     * @return self
     */
    public function setUserEmail(?string $userEmail): self
    {
        $this->userEmail = $userEmail;

        return $this;
    }
}
