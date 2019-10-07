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
use Sulu\Bundle\ContactBundle\Entity\AddressType;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Bundle\ContactBundle\Entity\EmailType;
use Sulu\Bundle\SecurityBundle\Entity\Role;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\SecurityBundle\Entity\UserRepository;
use Sulu\Bundle\SecurityBundle\Entity\UserRole;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;
use Sulu\Component\HttpKernel\SuluKernel;

class ProfileControllerTest extends SuluTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDatabase();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getEntityManager();

        $addressType = new AddressType();
        $addressType->setName('Home');
        $addressType->setId(1);

        $metadata = $entityManager->getClassMetadata(get_class($addressType));
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $emailType = new EmailType();
        $emailType->setName('work');
        $emailType->setId(1);

        $metadata = $entityManager->getClassMetadata(get_class($emailType));
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $entityManager->persist($addressType);
        $entityManager->persist($emailType);

        $contact = $this->getContainer()->get('sulu.repository.contact')->createNew();
        $contact->setFirstName('Max');
        $contact->setLastName('Mustermann');
        $entityManager->persist($contact);

        /** @var User $user */
        $user = $this->getContainer()->get('sulu.repository.user')->createNew();
        $user->setUsername('test');
        $user->setPassword('test');
        $user->setSalt('');
        $user->setLocale('en');
        $user->setContact($contact);
        $entityManager->persist($user);

        /** @var Role $role */
        $role = $this->getContainer()->get('sulu.repository.role')->createNew();
        $role->setName('Sulu-ioUser');
        $role->setSystem('Website');
        $entityManager->persist($role);

        /** @var UserRole $userRole */
        $userRole = new UserRole();
        $userRole->setUser($user);
        $userRole->setRole($role);
        $userRole->setLocale('en');
        $entityManager->persist($userRole);

        $user->addUserRole($userRole);

        $entityManager->flush();
    }

    private function submitProfile($data)
    {
        $client = $this->createAuthenticatedClient();

        $crawler = $client->request('GET', '/profile');
        $this->assertHttpStatusCode(200, $client->getResponse());

        $this->assertCount(1, $crawler->filter('#profile_formOfAddress'));
        $this->assertCount(1, $crawler->filter('#profile_firstName'));
        $this->assertCount(1, $crawler->filter('#profile_lastName'));
        $this->assertCount(1, $crawler->filter('#profile_mainEmail'));
        $this->assertCount(1, $crawler->filter('#profile_street'));
        $this->assertCount(1, $crawler->filter('#profile_number'));
        $this->assertCount(1, $crawler->filter('#profile_zip'));
        $this->assertCount(1, $crawler->filter('#profile_city'));
        $this->assertCount(1, $crawler->filter('#profile_countryCode'));
        $this->assertCount(1, $crawler->filter('#profile_note'));

        $form = $crawler->selectButton('profile[submit]')->form(array_merge(
            $data,
            [
                'profile[_token]' => $crawler->filter('#profile__token')->first()->attr('value'),
            ]
        ));

        $crawler = $client->submit($form);
        $this->assertHttpStatusCode(200, $client->getResponse());

        $this->assertCount(1, $crawler->filter('.success'));

        $this->getEntityManager()->clear();

        /** @var UserRepository $repository */
        $repository = $this->getEntityManager()->getRepository(User::class);

        return $repository->findOneBy(['username' => 'test']);
    }

    public function testProfile()
    {
        $user = $this->submitProfile([
            'profile[formOfAddress]' => 0,
            'profile[firstName]' => 'Hikaru',
            'profile[lastName]' => 'Sulu',
            'profile[mainEmail]' => 'sulu@example.org',
            'profile[street]' => 'Rathausstraße',
            'profile[number]' => 16,
            'profile[zip]' => 12351,
            'profile[city]' => 'USS Excelsior',
            'profile[countryCode]' => 'AT',
            'profile[note]' => 'Test',
        ]);

        $this->assertEquals(0, $user->getContact()->getFormOfAddress());
        $this->assertEquals('Hikaru Sulu', $user->getFullname());
        $this->assertEquals('Rathausstraße', $user->getContact()->getMainAddress()->getStreet());
        $this->assertEquals('USS Excelsior', $user->getContact()->getMainAddress()->getCity());
        $this->assertEquals(16, $user->getContact()->getMainAddress()->getNumber());
        $this->assertEquals(12351, $user->getContact()->getMainAddress()->getZip());
        $this->assertEquals('AT', $user->getContact()->getMainAddress()->getCountryCode());
        $this->assertEquals('Test', $user->getContact()->getNote());
    }

    public function testProfileWithoutNote()
    {
        $user = $this->submitProfile([
            'profile[formOfAddress]' => 0,
            'profile[firstName]' => 'Hikaru',
            'profile[lastName]' => 'Sulu',
            'profile[mainEmail]' => 'sulu@example.org',
            'profile[street]' => 'Rathausstraße',
            'profile[number]' => 16,
            'profile[zip]' => 12351,
            'profile[city]' => 'USS Excelsior',
            'profile[countryCode]' => 'AT',
        ]);

        $this->assertNull($user->getContact()->getNote());
    }

    protected static function getKernelConfiguration(): array
    {
        return [
            'sulu.context' => SuluKernel::CONTEXT_WEBSITE,
        ];
    }
}
