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

/**
 * Represents a single item in the blacklist.
 */
class BlacklistItem
{
    const TYPE_REQUEST = 'request';
    const TYPE_BLOCK = 'block';

    /**
     * @var string[]
     */
    private static $types = [self::TYPE_REQUEST, self::TYPE_BLOCK];

    /**
     * @var int
     */
    private $id;

    /**
     * @var string|null
     */
    private $pattern;

    /**
     * @var string|null
     */
    private $regexp;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @param string $pattern
     * @param string $type
     */
    public function __construct(?string $pattern = null, ?string $type = null)
    {
        $this->type = $type;

        if (null !== $pattern) {
            $this->setPattern($pattern);
        }
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get pattern.
     *
     * @return string|null
     */
    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    /**
     * Set pattern.
     *
     * @param string $pattern
     *
     * @return self
     */
    public function setPattern(string $pattern): self
    {
        $this->pattern = $pattern;
        $this->regexp = str_replace('\*', '[^@]*', preg_quote($pattern));

        return $this;
    }

    /**
     * Get regexp.
     *
     * @return string|null
     */
    public function getRegexp(): ?string
    {
        return $this->regexp;
    }

    /**
     * Get type.
     *
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return self
     */
    public function setType(string $type): self
    {
        if (!in_array($type, self::$types)) {
            throw new InvalidTypeException(self::$types, $type);
        }

        $this->type = $type;

        return $this;
    }
}
