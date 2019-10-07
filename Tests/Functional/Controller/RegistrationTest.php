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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\NoResultException;
use Sulu\Bundle\CommunityBundle\Entity\BlacklistItem;
use Sulu\Bundle\CommunityBundle\Tests\Functional\Traits\BlacklistItemTrait;
use Sulu\Bundle\ContactBundle\Entity\EmailType;
use Sulu\Bundle\SecurityBundle\Entity\Role;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;
use Sulu\Component\HttpKernel\SuluKernel;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * This testcases covers the whole registration, confirmation and login process.
 */
class RegistrationTest extends SuluTestCase
{
    use BlacklistItemTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDatabase();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getEntityManager();

        $role = new Role();
        $role->setName('Sulu-ioUser');
        $role->setSystem('Website');

        $emailType = new EmailType();
        $emailType->setName('private');
        $emailType->setId(1);

        $metadata = $entityManager->getClassMetadata(get_class($emailType));
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $entityManager->persist($role);
        $entityManager->persist($emailType);
        $entityManager->flush();
    }

    public function testRegister()
    {
        $client = $this->createClient();

        $crawler = $client->request('GET', '/registration');

        $this->assertCount(1, $crawler->filter('input[name="registration[username]"]'));
        $this->assertCount(1, $crawler->filter('input[name="registration[email]"]'));
        $this->assertCount(1, $crawler->filter('input[name="registration[plainPassword]"]'));
        $this->assertCount(1, $crawler->filter('input[name="registration[firstName]"]'));
        $this->assertCount(1, $crawler->filter('input[name="registration[lastName]"]'));
        $this->assertCount(1, $crawler->filter('input[name="registration[terms]"]'));
        $this->assertCount(1, $crawler->filter('input[name="registration[_token]"]'));
        $this->assertCount(1, $crawler->filter('button[name="registration[submit]"]'));

        $form = $crawler->selectButton('registration[submit]')->form(
            [
                'registration[username]' => 'sulu',
                'registration[email]' => 'hikaru@sulu.io',
                'registration[plainPassword]' => 'my-sulu',
                'registration[firstName]' => 'Hikaru',
                'registration[lastName]' => 'Sulu',
                'registration[terms]' => 1,
                'registration[_token]' => $crawler->filter('*[name="registration[_token]"]')->first()->attr('value'),
            ]
        );
        $client->submit($form);
        $this->assertHttpStatusCode(302, $client->getResponse());
    }

    public function testConfirmation()
    {
        $this->testRegister();
        $user = $this->findUser();

        $confirmationKey = $user->getConfirmationKey();

        $client = $this->createClient();

        $client->request('GET', '/confirmation/' . $confirmationKey);
        $this->assertHttpStatusCode(200, $client->getResponse());

        $user = $this->findUser();
        $this->assertNull($user->getConfirmationKey());

        return $user;
    }

    public function testLogin()
    {
        $this->testConfirmation();

        $client = $this->createClient();

        $crawler = $client->request('GET', '/login');
        $this->assertHttpStatusCode(200, $client->getResponse());

        $this->assertCount(1, $crawler->filter('input[name="_username"]'));
        $this->assertCount(1, $crawler->filter('input[name="_password"]'));

        $form = $crawler->selectButton('submit')->form(
            [
                '_username' => 'sulu',
                '_password' => 'my-sulu',
            ]
        );
        $client->submit($form);

        $this->assertHttpStatusCode(302, $client->getResponse());
        $this->assertInstanceOf(RedirectResponse::class, $client->getResponse());
        $this->assertEquals('http://localhost/profile', $client->getResponse()->getTargetUrl());
    }

    public function testLoginWrongPassword()
    {
        $this->testConfirmation();

        $client = $this->createClient();

        $crawler = $client->request('GET', '/login');
        $this->assertHttpStatusCode(200, $client->getResponse());

        $this->assertCount(1, $crawler->filter('input[name="_username"]'));
        $this->assertCount(1, $crawler->filter('input[name="_password"]'));

        $form = $crawler->selectButton('submit')->form(
            [
                '_username' => 'sulu',
                '_password' => 'your-sulu',
            ]
        );
        $client->submit($form);

        $this->assertHttpStatusCode(302, $client->getResponse());
        $this->assertInstanceOf(RedirectResponse::class, $client->getResponse());
        $this->assertEquals('http://localhost/login', $client->getResponse()->getTargetUrl());
    }

    public function testRegistrationBlacklistedBlocked()
    {
        $this->createBlacklistItem($this->getEntityManager(), '*@sulu.io', BlacklistItem::TYPE_BLOCK);

        $client = $this->createClient();

        $crawler = $client->request('GET', '/registration');
        $form = $crawler->selectButton('registration[submit]')->form(
            [
                'registration[username]' => 'sulu',
                'registration[email]' => 'hikaru@sulu.io',
                'registration[plainPassword]' => 'my-sulu',
                'registration[firstName]' => 'Hikaru',
                'registration[lastName]' => 'Sulu',
                'registration[terms]' => 1,
                'registration[_token]' => $crawler->filter('*[name="registration[_token]"]')->first()->attr('value'),
            ]
        );
        $client->submit($form);
        $this->assertHttpStatusCode(200, $client->getResponse());

        $this->assertStringContainsString('is blocked', $client->getResponse()->getContent());
        $this->assertNull($this->findUser());
    }

    public function testRegistrationBlacklistedRequested()
    {
        $this->createBlacklistItem($this->getEntityManager(), '*@sulu.io', BlacklistItem::TYPE_REQUEST);

        $client = $this->createClient();

        $crawler = $client->request('GET', '/registration');
        $this->assertHttpStatusCode(200, $client->getResponse());

        $form = $crawler->selectButton('registration[submit]')->form(
            [
                'registration[username]' => 'sulu',
                'registration[email]' => 'hikaru@sulu.io',
                'registration[plainPassword]' => 'my-sulu',
                'registration[firstName]' => 'Hikaru',
                'registration[lastName]' => 'Sulu',
                'registration[terms]' => 1,
                'registration[_token]' => $crawler->filter('*[name="registration[_token]"]')->first()->attr('value'),
            ]
        );
        $client->submit($form);
        $this->assertHttpStatusCode(302, $client->getResponse());

        $this->assertNotNull($this->findUser());

        // check email to admin
        $mailCollector = $client->getProfile()->getCollector('swiftmailer');

        $this->assertEquals(1, $mailCollector->getMessageCount());
        $message = $mailCollector->getMessages()[0];
        $this->assertEquals('admin@localhost', key($message->getTo()));

        return $message;
    }

    public function testBlacklistConfirm()
    {
        $message = $this->testRegistrationBlacklistedRequested();

        $emailCrawler = new Crawler();
        $emailCrawler->addContent($message->getBody());

        $links = $emailCrawler->filter('a');

        $client = $this->createClient();

        $client->request('GET', $links->first()->attr('href'));
        $this->assertStringContainsString('User "hikaru@sulu.io" confirmed', $client->getResponse()->getContent());

        // check email to user
        $mailCollector = $client->getProfile()->getCollector('swiftmailer');
        $this->assertEquals(1, $mailCollector->getMessageCount());
        $message = $mailCollector->getMessages()[0];
        $this->assertEquals('hikaru@sulu.io', key($message->getTo()));
    }

    public function testBlacklistBlocked()
    {
        $message = $this->testRegistrationBlacklistedRequested();

        $emailCrawler = new Crawler();
        $emailCrawler->addContent($message->getBody());

        $links = $emailCrawler->filter('a');

        $client = $this->createClient();

        $client->request('GET', $links->last()->attr('href'));
        $this->assertStringContainsString('User "hikaru@sulu.io" denied', $client->getResponse()->getContent());

        // check email to user
        $mailCollector = $client->getProfile()->getCollector('swiftmailer');
        $this->assertEquals(0, $mailCollector->getMessageCount());
    }

    public function testPasswordForget()
    {
        $user = $this->testConfirmation();

        $client = $this->createClient();
        $crawler = $client->request('GET', '/password-forget');

        $this->assertCount(1, $crawler->filter('input[name="password_forget[email_username]"]'));

        $form = $crawler->selectButton('password_forget[submit]')->form(
            [
                'password_forget[email_username]' => $user->getUsername(),
                'password_forget[_token]' => $crawler->filter('*[name="password_forget[_token]"]')
                    ->first()->attr('value'),
            ]
        );
        $client->submit($form);

        // check email to user
        $mailCollector = $client->getProfile()->getCollector('swiftmailer');
        $this->assertEquals(1, $mailCollector->getMessageCount());
        $message = $mailCollector->getMessages()[0];
        $this->assertEquals('hikaru@sulu.io', key($message->getTo()));

        $emailCrawler = new Crawler();
        $emailCrawler->addContent($message->getBody());
        $links = $emailCrawler->filter('a');

        $crawler = $client->request('GET', $links->first()->attr('href'));

        $this->assertCount(1, $crawler->filter('input[name="password_reset[plainPassword]"]'));

        $form = $crawler->selectButton('password_reset[submit]')->form(
            [
                'password_reset[plainPassword]' => 'my-new-password',
                'password_reset[_token]' => $crawler->filter('*[name="password_reset[_token]"]')
                    ->first()->attr('value'),
            ]
        );
        $client->submit($form);

        $this->getEntityManager()->clear();

        $this->assertStringStartsWith('my-new-password', $this->findUser()->getPassword());
    }

    /**
     * Find user by username.
     *
     * @param string $username
     *
     * @return User
     */
    private function findUser($username = 'sulu')
    {
        // clear entity-manager to ensure newest user
        $this->getEntityManager()->clear();

        $repository = $this->getEntityManager()->getRepository(User::class);

        try {
            return $repository->findUserByUsername($username);
        } catch (NoResultException $exception) {
            return;
        }
    }

    protected static function getKernelConfiguration(): array
    {
        return [
            'sulu.context' => SuluKernel::CONTEXT_WEBSITE,
        ];
    }
}
