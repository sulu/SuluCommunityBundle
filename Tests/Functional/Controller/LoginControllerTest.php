<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Tests\Functional\Controller;

use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class LoginControllerTest extends SuluTestCase
{
    public function testLoginForm()
    {
        $client = $this->createWebsiteClient();

        $crawler = $client->request('GET', '/login');

        $this->assertHttpStatusCode(200, $client->getResponse());

        $this->assertCount(1, $crawler->filter('#username'));
        $this->assertCount(1, $crawler->filter('#password'));
        $this->assertCount(1, $crawler->filter('#remember_me'));
        $this->assertCount(1, $crawler->filter('button'));
    }
}
