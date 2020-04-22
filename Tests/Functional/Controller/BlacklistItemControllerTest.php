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

use Sulu\Bundle\CommunityBundle\Entity\BlacklistItem;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class BlacklistItemControllerTest extends SuluTestCase
{
    public function setUp(): void
    {
        $this->purgeDatabase();
    }

    public function testCgetEmpty(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/admin/api/blacklist-items');
        $this->assertHttpStatusCode(200, $client->getResponse());

        /** @var string $content */
        $content = $client->getResponse()->getContent();
        $result = json_decode($content, true);

        $this->assertIsArray($result);
        $this->assertEquals(0, $result['total']);
        $this->assertCount(0, $result['_embedded']['blacklist_items']);
    }

    /**
     * @return mixed[]
     */
    public function testPost(string $pattern = '*@sulu.io'): array
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'POST',
            '/admin/api/blacklist-items',
            ['pattern' => $pattern, 'type' => BlacklistItem::TYPE_REQUEST]
        );
        $this->assertHttpStatusCode(200, $client->getResponse());

        /** @var string $content */
        $content = $client->getResponse()->getContent();
        $result = json_decode($content, true);

        $this->assertIsArray($result);
        $this->assertEquals($pattern, $result['pattern']);
        $this->assertEquals(BlacklistItem::TYPE_REQUEST, $result['type']);

        return $result;
    }

    /**
     * @return mixed[]
     */
    public function testGet(): array
    {
        $item = $this->testPost();

        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/admin/api/blacklist-items/' . $item['id']);
        $this->assertHttpStatusCode(200, $client->getResponse());

        /** @var string $content */
        $content = $client->getResponse()->getContent();
        $result = json_decode($content, true);

        $this->assertIsArray($result);
        $this->assertEquals($item['id'], $result['id']);
        $this->assertEquals($item['pattern'], $result['pattern']);
        $this->assertEquals($item['type'], $result['type']);

        return $result;
    }

    public function testCget(): void
    {
        $item = $this->testPost();

        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/admin/api/blacklist-items');
        $this->assertHttpStatusCode(200, $client->getResponse());

        /** @var string $content */
        $content = $client->getResponse()->getContent();
        $result = json_decode($content, true);

        $this->assertIsArray($result);
        $this->assertEquals(1, $result['total']);
        $this->assertCount(1, $result['_embedded']['blacklist_items']);
        $this->assertEquals($item['id'], $result['_embedded']['blacklist_items'][0]['id']);
        $this->assertEquals($item['pattern'], $result['_embedded']['blacklist_items'][0]['pattern']);
        $this->assertEquals($item['type'], $result['_embedded']['blacklist_items'][0]['type']);
    }

    public function testDelete(): void
    {
        $item = $this->testPost();

        $client = $this->createAuthenticatedClient();

        $client->request('DELETE', '/admin/api/blacklist-items/' . $item['id']);
        $this->assertHttpStatusCode(204, $client->getResponse());

        $client->request('GET', '/admin/api/blacklist-items');
        $this->assertHttpStatusCode(200, $client->getResponse());

        /** @var string $content */
        $content = $client->getResponse()->getContent();
        $result = json_decode($content, true);

        $this->assertIsArray($result);
        $this->assertEquals(0, $result['total']);
        $this->assertCount(0, $result['_embedded']['blacklist_items']);
    }

    public function testCDelete(): void
    {
        $item1 = $this->testPost();
        $item2 = $this->testPost('test@sulu.io');

        $client = $this->createAuthenticatedClient();

        $client->request('DELETE', '/admin/api/blacklist-items?ids=' . implode(',', [$item1['id'], $item2['id']]));
        $this->assertHttpStatusCode(204, $client->getResponse());

        $client->request('GET', '/admin/api/blacklist-items');
        $this->assertHttpStatusCode(200, $client->getResponse());

        /** @var string $content */
        $content = $client->getResponse()->getContent();
        $result = json_decode($content, true);

        $this->assertIsArray($result);
        $this->assertEquals(0, $result['total']);
        $this->assertCount(0, $result['_embedded']['blacklist_items']);
    }

    public function testPostInvalidType(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'POST',
            '/admin/api/blacklist-items',
            ['pattern' => '*@sulu.io', 'type' => 'test']
        );
        $this->assertHttpStatusCode(409, $client->getResponse());
    }

    public function testPut(): void
    {
        $item = $this->testPost();

        $client = $this->createAuthenticatedClient();

        $client->request(
            'PUT',
            '/admin/api/blacklist-items/' . $item['id'],
            ['pattern' => 'test@sulu.io', 'type' => BlacklistItem::TYPE_BLOCK]
        );
        $this->assertHttpStatusCode(200, $client->getResponse());

        /** @var string $content */
        $content = $client->getResponse()->getContent();
        $result = json_decode($content, true);

        $this->assertIsArray($result);
        $this->assertEquals('test@sulu.io', $result['pattern']);
        $this->assertEquals(BlacklistItem::TYPE_BLOCK, $result['type']);
    }

    public function testPutInvalidType(): void
    {
        $item = $this->testPost();

        $client = $this->createAuthenticatedClient();

        $client->request(
            'PUT',
            '/admin/api/blacklist-items/' . $item['id'],
            ['pattern' => 'test@sulu.io', 'type' => 'test']
        );
        $this->assertHttpStatusCode(409, $client->getResponse());
    }
}
