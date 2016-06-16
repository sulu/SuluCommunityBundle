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

/**
 * Invalid type given.
 */
class InvalidTypeException extends \InvalidArgumentException
{
    /**
     * @var string[]
     */
    private $validTypes;

    /**
     * @var string
     */
    private $type;

    /**
     * @param string[] $validTypes
     * @param string $type
     */
    public function __construct(array $validTypes, $type)
    {
        parent::__construct(
            sprintf('Invalid type "%s" given. Valid types are [%s]', $type, implode(', ', $validTypes)),
            10000
        );

        $this->validTypes = $validTypes;
        $this->type = $type;
    }

    /**
     * @return string[]
     */
    public function getValidTypes()
    {
        return $this->validTypes;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
