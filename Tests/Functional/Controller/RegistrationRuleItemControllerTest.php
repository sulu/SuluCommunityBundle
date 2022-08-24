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

use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleItem;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class RegistrationRuleItemControllerTest extends SuluTestCase
{
    /**
     * @var KernelBrowser
     */
    private $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createAuthenticatedClient();
        $this->purgeDatabase();
    }

    public function testCgetEmpty(): void
    {
        $this->client->request('GET', '/admin/api/registration-rule-items');
        $this->assertHttpStatusCode(200, $this->client->getResponse());

        /** @var string $content */
        $content = $this->client->getResponse()->getContent();
        $result = \json_decode($content, true);

        $this->assertIsArray($result);
        $this->assertSame(0, $result['total']);
        $this->assertCount(0, $result['_embedded']['registration_rule_items']);
    }

    /**
     * @return mixed[]
     */
    public function testPost(string $pattern = '*@sulu.io'): array
    {
        $this->client->request(
            'POST',
            '/admin/api/registration-rule-items',
            ['pattern' => $pattern, 'type' => RegistrationRuleItem::TYPE_REQUEST]
        );
        $this->assertHttpStatusCode(200, $this->client->getResponse());

        /** @var string $content */
        $content = $this->client->getResponse()->getContent();
        $result = \json_decode($content, true);

        $this->assertIsArray($result);
        $this->assertSame($pattern, $result['pattern']);
        $this->assertSame(RegistrationRuleItem::TYPE_REQUEST, $result['type']);

        return $result;
    }

    /**
     * @return mixed[]
     */
    public function testGet(): array
    {
        $item = $this->testPost();

        $this->client->request('GET', '/admin/api/registration-rule-items/' . $item['id']);
        $this->assertHttpStatusCode(200, $this->client->getResponse());

        /** @var string $content */
        $content = $this->client->getResponse()->getContent();
        $result = \json_decode($content, true);

        $this->assertIsArray($result);
        $this->assertSame($item['id'], $result['id']);
        $this->assertSame($item['pattern'], $result['pattern']);
        $this->assertSame($item['type'], $result['type']);

        return $result;
    }

    public function testCget(): void
    {
        $item = $this->testPost();

        $this->client->request('GET', '/admin/api/registration-rule-items');
        $this->assertHttpStatusCode(200, $this->client->getResponse());

        /** @var string $content */
        $content = $this->client->getResponse()->getContent();
        $result = \json_decode($content, true);

        $this->assertIsArray($result);
        $this->assertSame(1, $result['total']);
        $this->assertCount(1, $result['_embedded']['registration_rule_items']);
        $this->assertSame($item['id'], $result['_embedded']['registration_rule_items'][0]['id']);
        $this->assertSame($item['pattern'], $result['_embedded']['registration_rule_items'][0]['pattern']);
        $this->assertSame($item['type'], $result['_embedded']['registration_rule_items'][0]['type']);
    }

    public function testDelete(): void
    {
        $item = $this->testPost();

        $this->client->request('DELETE', '/admin/api/registration-rule-items/' . $item['id']);
        $this->assertHttpStatusCode(204, $this->client->getResponse());

        $this->client->request('GET', '/admin/api/registration-rule-items');
        $this->assertHttpStatusCode(200, $this->client->getResponse());

        /** @var string $content */
        $content = $this->client->getResponse()->getContent();
        $result = \json_decode($content, true);

        $this->assertIsArray($result);
        $this->assertSame(0, $result['total']);
        $this->assertCount(0, $result['_embedded']['registration_rule_items']);
    }

    public function testCDelete(): void
    {
        $item1 = $this->testPost();
        $item2 = $this->testPost('test@sulu.io');

        $this->client->request('DELETE', '/admin/api/registration-rule-items?ids=' . \implode(',', [$item1['id'], $item2['id']]));
        $this->assertHttpStatusCode(204, $this->client->getResponse());

        $this->client->request('GET', '/admin/api/registration-rule-items');
        $this->assertHttpStatusCode(200, $this->client->getResponse());

        /** @var string $content */
        $content = $this->client->getResponse()->getContent();
        $result = \json_decode($content, true);

        $this->assertIsArray($result);
        $this->assertSame(0, $result['total']);
        $this->assertCount(0, $result['_embedded']['registration_rule_items']);
    }

    public function testPostInvalidType(): void
    {
        $this->client->request(
            'POST',
            '/admin/api/registration-rule-items',
            ['pattern' => '*@sulu.io', 'type' => 'test']
        );
        $this->assertHttpStatusCode(409, $this->client->getResponse());
    }

    public function testPut(): void
    {
        $item = $this->testPost();

        $this->client->request(
            'PUT',
            '/admin/api/registration-rule-items/' . $item['id'],
            ['pattern' => 'test@sulu.io', 'type' => RegistrationRuleItem::TYPE_BLOCK]
        );
        $this->assertHttpStatusCode(200, $this->client->getResponse());

        /** @var string $content */
        $content = $this->client->getResponse()->getContent();
        $result = \json_decode($content, true);

        $this->assertIsArray($result);
        $this->assertSame('test@sulu.io', $result['pattern']);
        $this->assertSame(RegistrationRuleItem::TYPE_BLOCK, $result['type']);
    }

    public function testPutInvalidType(): void
    {
        $item = $this->testPost();

        $this->client->request(
            'PUT',
            '/admin/api/registration-rule-items/' . $item['id'],
            ['pattern' => 'test@sulu.io', 'type' => 'test']
        );
        $this->assertHttpStatusCode(409, $this->client->getResponse());
    }
}
