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
use Sulu\Bundle\CommunityBundle\Entity\EmailConfirmationToken;
use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\ContactBundle\Entity\Email;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class EmailConfirmationControllerTest extends SuluTestCase
{
    protected static $emailTypeEntityName = 'SuluContactBundle:EmailType';

    /**
     * @var User
     */
    private $user;

    public function setUp()
    {
        parent::setUp();

        $this->purgeDatabase();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getEntityManager();

        $mainEmailAddress = 'new@sulu.io';

        $emailType = $entityManager->getReference(EmailType::class, 1);

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

    public function testConfirm()
    {
        $client = $this->createClient(
            [
                'sulu_context' => 'website',
                'environment' => 'dev',
            ]
        );

        $crawler = $client->request('GET', '/profile/email-confirmation?token=123-123-123');
        $this->assertHttpStatusCode(200, $client->getResponse());

        $this->assertCount(1, $crawler->filter('.success'));
        $this->assertCount(0, $crawler->filter('.fail'));

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getEntityManager();
        $entityManager->clear();

        $this->assertNull($entityManager->getRepository(EmailConfirmationToken::class)->findByToken('123-123-123'));

        $user = $entityManager->find(User::class, $this->user->getId());
        $contact = $user->getContact();

        $this->assertEquals($user->getEmail(), $contact->getMainEmail());
    }

    public function testConfirmWrongToken()
    {
        $client = $this->createClient(
            [
                'sulu_context' => 'website',
                'environment' => 'dev',
            ]
        );

        $crawler = $client->request('GET', '/profile/email-confirmation?token=312-312-312');
        $this->assertHttpStatusCode(200, $client->getResponse());

        $this->assertCount(0, $crawler->filter('.success'));
        $this->assertCount(1, $crawler->filter('.fail'));

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getEntityManager();

        $this->assertNotNull($entityManager->getRepository(EmailConfirmationToken::class)->findByToken('123-123-123'));
    }
}
