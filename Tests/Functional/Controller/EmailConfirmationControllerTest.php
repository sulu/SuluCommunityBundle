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
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sulu\Bundle\CommunityBundle\Entity\EmailConfirmationToken;
use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\ContactBundle\Entity\Email;
use Sulu\Bundle\ContactBundle\Entity\EmailType;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;
use Sulu\Component\HttpKernel\SuluKernel;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class EmailConfirmationControllerTest extends SuluTestCase
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var KernelBrowser
     */
    private $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createAuthenticatedClient();

        $this->purgeDatabase();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getEntityManager();

        $mainEmailAddress = 'new@sulu.io';

        $metadata = $entityManager->getClassMetaData(EmailType::class);
        $metadata->setIdGenerator(new AssignedGenerator());
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $emailType = new EmailType();
        $emailType->setName('email.work');
        $emailType->setId(1);
        $entityManager->persist($emailType);

        $contactEmail = new Email();
        $contactEmail->setEmail($mainEmailAddress);
        $contactEmail->setEmailType($emailType);

        $contact = new Contact();
        $contact->setMainEmail($mainEmailAddress);
        $contact->setFirstName('Hikaru');
        $contact->setLastName('Sulu');
        $contact->addEmail($contactEmail);

        $entityManager->persist($contact);
        $entityManager->flush();

        $this->user = new User();
        $this->user->setEmail($mainEmailAddress);
        $this->user->setUsername('test');
        $this->user->setPassword('test');
        $this->user->setSalt('test');
        $this->user->setLocale('de');
        $this->user->setContact($contact);

        $token = new EmailConfirmationToken($this->user);
        $token->setToken('123-123-123');

        $entityManager->persist($this->user);
        $entityManager->persist($token);
        $entityManager->flush();
    }

    public function testConfirm(): User
    {
        $crawler = $this->client->request('GET', '/profile/email-confirmation?token=123-123-123');
        $this->assertHttpStatusCode(200, $this->client->getResponse());

        $this->assertCount(1, $crawler->filter('.success'));
        $this->assertCount(0, $crawler->filter('.fail'));

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getEntityManager();
        $entityManager->clear();

        $this->assertNull($this->getContainer()->get('sulu_community.email_confirmation.repository')->findByToken('123-123-123'));

        $user = $entityManager->find(User::class, $this->user->getId());
        $this->assertInstanceOf(User::class, $user);
        $contact = $user->getContact();

        $this->assertSame($user->getEmail(), $contact->getMainEmail());

        return $user;
    }

    public function testConfirmWrongToken(): void
    {
        $crawler = $this->client->request('GET', '/profile/email-confirmation?token=312-312-312');
        $this->assertHttpStatusCode(200, $this->client->getResponse());

        $this->assertCount(0, $crawler->filter('.success'));
        $this->assertCount(1, $crawler->filter('.fail'));

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getEntityManager();

        $this->assertNotNull($entityManager->getRepository(EmailConfirmationToken::class)->findByToken('123-123-123'));
    }

    public function testConfirmWithoutEmail(): void
    {
        $this->getEntityManager()->remove($this->user->getContact()->getEmails()->first());
        $this->user->getContact()->getEmails()->clear();
        $this->getEntityManager()->flush();

        /** @var User $user */
        $user = $this->testConfirm();

        $this->assertCount(1, $user->getContact()->getEmails());
        $this->assertSame($user->getEmail(), $user->getContact()->getEmails()->first()->getEmail());
    }

    protected static function getKernelConfiguration(): array
    {
        return [
            'sulu.context' => SuluKernel::CONTEXT_WEBSITE,
        ];
    }
}
