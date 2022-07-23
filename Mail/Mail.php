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
     * @param string|string[] $from
     * @param string|string[] $to
     * @param array{
     *     subject: string,
     *     user_template: string|null,
     *     admin_template: string|null,
     * } $config
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
     * @var string|string[]
     */
    private $from;

    /**
     * @var string|string[]
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
     * @param string|array<string, string> $from
     * @param string|array<string, string> $to
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
     * @return string|array<string, string>
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Returns to.
     *
     * @return string|array<string, string>
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Returns subject.
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * Returns user-template.
     */
    public function getUserTemplate(): ?string
    {
        return $this->userTemplate;
    }

    /**
     * Returns admin-template.
     */
    public function getAdminTemplate(): ?string
    {
        return $this->adminTemplate;
    }

    /**
     * Returns user-email.
     */
    public function getUserEmail(): ?string
    {
        return $this->userEmail;
    }

    /**
     * Set user-email.
     * This setting overwrite the user-email.
     */
    public function setUserEmail(?string $userEmail): self
    {
        $this->userEmail = $userEmail;

        return $this;
    }
}
