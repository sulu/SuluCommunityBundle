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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\NoResultException;
use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleItem;
use Sulu\Bundle\CommunityBundle\Tests\Functional\Traits\RegistrationRuleItemTrait;
use Sulu\Bundle\ContactBundle\Entity\EmailType;
use Sulu\Bundle\SecurityBundle\Entity\Role;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;
use Sulu\Component\HttpKernel\SuluKernel;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\Mime\RawMessage;

/**
 * This testcases covers the whole registration, confirmation and login process.
 */
class RegistrationTest extends SuluTestCase
{
    use RegistrationRuleItemTrait;

    /**
     * @var KernelBrowser
     */
    private $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createClient();
        $this->purgeDatabase();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getEntityManager();

        $role = new Role();
        $role->setName('Sulu-ioUser');
        $role->setSystem('Website');

        $emailType = new EmailType();
        $emailType->setName('private');
        $emailType->setId(1);

        $metadata = $entityManager->getClassMetadata(\get_class($emailType));
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $entityManager->persist($role);
        $entityManager->persist($emailType);
        $entityManager->flush();
    }

    public function testRegister(): void
    {
        $crawler = $this->client->request('GET', '/registration');

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
        $this->client->submit($form);
        $this->assertHttpStatusCode(302, $this->client->getResponse());
    }

    public function testRegisterInvalid(): void
    {
        $crawler = $this->client->request('GET', '/registration');

        $form = $crawler->selectButton('registration[submit]')->form([
            'registration[username]' => null,
        ]);
        $this->client->submit($form);
        $this->assertHttpStatusCode(422, $this->client->getResponse());
    }

    public function testConfirmation(): User
    {
        $this->testRegister();
        /** @var User $user */
        $user = $this->findUser();

        $confirmationKey = $user->getConfirmationKey();

        $this->client->request('GET', '/confirmation/' . $confirmationKey);
        $this->assertHttpStatusCode(200, $this->client->getResponse());

        /** @var User $user */
        $user = $this->findUser();
        $this->assertNull($user->getConfirmationKey());

        return $user;
    }

    public function testLogin(): void
    {
        $this->testConfirmation();
        $user = $this->findUser();

        if ($user) {
            $user->setSalt('');
            $user->setPassword('my-sulu');
            $this->getEntityManager()->flush();
        }

        $crawler = $this->client->request('GET', '/login');
        $this->assertHttpStatusCode(200, $this->client->getResponse());

        $this->assertCount(1, $crawler->filter('input[name="_username"]'));
        $this->assertCount(1, $crawler->filter('input[name="_password"]'));

        $form = $crawler->selectButton('submit')->form(
            [
                '_username' => 'sulu',
                '_password' => 'my-sulu',
            ]
        );
        $this->client->submit($form);

        $this->assertHttpStatusCode(302, $this->client->getResponse());
        $this->assertInstanceOf(RedirectResponse::class, $this->client->getResponse());
        $this->assertSame('http://localhost/profile', $this->client->getResponse()->getTargetUrl());
    }

    public function testLoginWrongPassword(): void
    {
        $this->testConfirmation();

        $crawler = $this->client->request('GET', '/login');
        $this->assertHttpStatusCode(200, $this->client->getResponse());

        $this->assertCount(1, $crawler->filter('input[name="_username"]'));
        $this->assertCount(1, $crawler->filter('input[name="_password"]'));

        $form = $crawler->selectButton('submit')->form(
            [
                '_username' => 'sulu',
                '_password' => 'your-sulu',
            ]
        );
        $this->client->submit($form);

        $this->assertHttpStatusCode(302, $this->client->getResponse());
        $this->assertInstanceOf(RedirectResponse::class, $this->client->getResponse());
        $this->assertSame('http://localhost/login', $this->client->getResponse()->getTargetUrl());
    }

    public function testRegistrationRegistrationRuleedBlocked(): void
    {
        $this->createRegistrationRuleItem($this->getEntityManager(), '*@sulu.io', RegistrationRuleItem::TYPE_BLOCK);

        $crawler = $this->client->request('GET', '/registration');
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
        $this->client->submit($form);
        $this->assertHttpStatusCode(422, $this->client->getResponse());
        $content = $this->client->getResponse()->getContent();

        $this->assertIsString($content);
        $this->assertStringContainsString('is blocked', $content);
        $this->assertNull($this->findUser());
    }

    public function testRegistrationRegistrationRuleRequested(): ?RawMessage
    {
        if (\class_exists(\Swift_Mailer::class)) {
            $this->markTestSkipped('Skip test for swift mailer.');
        }

        $this->createRegistrationRuleItem($this->getEntityManager(), '*@sulu.io', RegistrationRuleItem::TYPE_REQUEST);

        $crawler = $this->client->request('GET', '/registration');
        $this->assertHttpStatusCode(200, $this->client->getResponse());

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
        $this->client->submit($form);
        $this->assertHttpStatusCode(302, $this->client->getResponse());

        $this->assertNotNull($this->findUser());

        // check email to admin
        /** @var Profile $profile */
        $profile = $this->client->getProfile();
        $this->assertNotFalse($profile, 'Could not found response profile, is profiler activated?');

        $this->assertEmailCount(1);

        $message = $this->getMailerMessage();
        $this->assertSame('admin@localhost', $message->getTo()[0]->getAddress());

        return $message;
    }

    public function testRegistrationRuleConfirm(): void
    {
        $message = $this->testRegistrationRegistrationRuleRequested();

        $emailCrawler = new Crawler();
        $emailCrawler->addContent($message->getHtmlBody());

        $links = $emailCrawler->filter('a');
        $firstLink = $links->first()->attr('href');
        $this->assertIsString($firstLink);
        $this->assertStringContainsString('/_community/confirm', $firstLink);

        $this->client->request('GET', $firstLink);
        $content = $this->client->getResponse()->getContent();

        $this->assertIsString($content);
        $this->assertStringContainsString('User "hikaru@sulu.io" confirmed', $content);

        // check email to user
        /** @var Profile $profile */
        $profile = $this->client->getProfile();
        $this->assertNotFalse($profile, 'Could not found response profile, is profiler activated?');

        $this->assertEmailCount(1);

        $message = $this->getMailerMessage();
        $this->assertSame('hikaru@sulu.io', $message->getTo()[0]->getAddress());
    }

    public function testRegistrationRuleBlocked(): void
    {
        $message = $this->testRegistrationRegistrationRuleRequested();

        $emailCrawler = new Crawler();
        $emailCrawler->addContent($message->getHtmlBody());

        $links = $emailCrawler->filter('a');
        $lastLink = $links->last()->attr('href');
        $this->assertIsString($lastLink);
        $this->assertStringContainsString('/_community/deny', $lastLink);

        $this->client->request('GET', $lastLink);
        $content = $this->client->getResponse()->getContent();

        $this->assertIsString($content);
        $this->assertStringContainsString('User "hikaru@sulu.io" denied', $content);

        // check email to user
        /** @var Profile $profile */
        $profile = $this->client->getProfile();
        $this->assertNotFalse($profile, 'Could not found response profile, is profiler activated?');

        $this->assertEmailCount(0);
    }

    public function testPasswordForget(): void
    {
        if (\class_exists(\Swift_Mailer::class)) {
            $this->markTestSkipped('Skip test for swift mailer.');
        }

        $user = $this->testConfirmation();

        $crawler = $this->client->request('GET', '/password-forget');

        $this->assertCount(1, $crawler->filter('input[name="password_forget[email_username]"]'));

        $form = $crawler->selectButton('password_forget[submit]')->form(
            [
                'password_forget[email_username]' => $user->getUsername(),
                'password_forget[_token]' => $crawler->filter('*[name="password_forget[_token]"]')
                    ->first()->attr('value'),
            ]
        );
        $this->client->submit($form);

        // check email to user
        /** @var Profile $profile */
        $profile = $this->client->getProfile();
        $this->assertNotFalse($profile, 'Could not found response profile, is profiler activated?');

        $this->assertEmailCount(1);

        $message = $this->getMailerMessage();
        $this->assertSame('hikaru@sulu.io', $message->getTo()[0]->getAddress());

        $emailCrawler = new Crawler();
        $emailCrawler->addContent($message->getHtmlBody());
        $links = $emailCrawler->filter('a');

        $firstLink = $links->first()->attr('href');
        $this->assertIsString($firstLink);
        $this->assertStringContainsString('/password-reset/', $firstLink);

        $crawler = $this->client->request('GET', $firstLink);

        $this->assertCount(1, $crawler->filter('input[name="password_reset[plainPassword]"]'));

        $form = $crawler->selectButton('password_reset[submit]')->form(
            [
                'password_reset[plainPassword]' => 'my-new-password',
                'password_reset[_token]' => $crawler->filter('*[name="password_reset[_token]"]')
                    ->first()->attr('value'),
            ]
        );
        $this->client->submit($form);

        //$this->getEntityManager()->clear();

        /** @var User $user */
        $user = $this->findUser('sulu');
        $password = $user->getPassword();
        $this->assertNotNull($password);
        $this->assertStringStartsWith('my-new-password', $password);
    }

    public function testPasswordForgetInvalid(): void
    {
        $crawler = $this->client->request('GET', '/password-forget');

        $form = $crawler->selectButton('password_forget[submit]')->form([
            'password_forget[email_username]' => 'hikaru@sulu.io',
        ]);
        $this->client->submit($form);
        $this->assertHttpStatusCode(422, $this->client->getResponse());
    }

    /**
     * Find user by username.
     */
    private function findUser(string $username = 'sulu'): ?User
    {
        $repository = $this->getContainer()->get('sulu.repository.user');

        try {
            /** @var User $user */
            $user = $repository->findUserByUsername($username);
            $this->getEntityManager()->refresh($user);

            return $user;
        } catch (NoResultException $exception) {
            return null;
        }
    }

    /**
     * @return array{
     *     'sulu.context': SuluKernel::CONTEXT_WEBSITE,
     * }
     */
    protected static function getKernelConfiguration(): array
    {
        return [
            'sulu.context' => SuluKernel::CONTEXT_WEBSITE,
        ];
    }
}
