<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Tests\Functional\Controller;

use Sulu\Bundle\TestBundle\Testing\SuluTestCase;
use Sulu\Component\HttpKernel\SuluKernel;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class LoginControllerTest extends SuluTestCase
{
    /**
     * @var KernelBrowser
     */
    private $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createWebsiteClient();
    }

    public function testLoginForm(): void
    {
        $crawler = $this->client->request('GET', '/login');

        $this->assertHttpStatusCode(200, $this->client->getResponse());

        $this->assertCount(1, $crawler->filter('#username'));
        $this->assertCount(1, $crawler->filter('#password'));
        $this->assertCount(1, $crawler->filter('#remember_me'));
        $this->assertCount(1, $crawler->filter('button'));
    }

    protected static function getKernelConfiguration(): array
    {
        return [
            'sulu.context' => SuluKernel::CONTEXT_WEBSITE,
        ];
    }
}
