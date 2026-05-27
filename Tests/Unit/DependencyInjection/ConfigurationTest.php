<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    public function testRegistrationRuleConfigKeys(): void
    {
        $config = $this->processConfiguration([
            'webspaces' => [
                'sulu-io' => [
                    'from' => 'admin@sulu.io',
                ],
            ],
        ]);

        $webspacesConfig = $config[Configuration::WEBSPACES];
        $this->assertIsArray($webspacesConfig);

        $webspaceConfig = $webspacesConfig['sulu-io'];
        $this->assertIsArray($webspaceConfig);

        $this->assertArrayHasKey('registration_rule', $webspaceConfig);
        $this->assertArrayHasKey('registration_rule_denied', $webspaceConfig);
        $this->assertArrayHasKey('registration_rule_confirmed', $webspaceConfig);
        $this->assertArrayNotHasKey('blacklisted', $webspaceConfig);
        $this->assertArrayNotHasKey('blacklist_denied', $webspaceConfig);
        $this->assertArrayNotHasKey('blacklist_confirmed', $webspaceConfig);
    }

    /**
     * @param mixed[] $configs
     *
     * @return array<string, mixed>
     */
    private function processConfiguration(array $configs): array
    {
        $processor = new Processor();

        /** @var mixed[] */
        return $processor->processConfiguration(new Configuration(), [$configs]);
    }
}
