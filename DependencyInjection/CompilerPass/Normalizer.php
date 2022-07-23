<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\DependencyInjection\CompilerPass;

/**
 * Only used for internal usages.
 *
 * @internal
 */
class Normalizer
{
    public static function normalize(string $text): string
    {
        return \str_replace('-', '_', $text);
    }
}
