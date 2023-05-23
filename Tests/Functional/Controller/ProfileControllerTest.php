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
use Sulu\Bundle\ContactBundle\Entity\EmailType;
use Sulu\Bundle\SecurityBundle\Entity\Role;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\SecurityBundle\Entity\UserRepository;
use Sulu\Bundle\SecurityBundle\Entity\UserRole;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;
use Sulu\Component\HttpKernel\SuluKernel;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class ProfileControllerTest extends SuluTestCase
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

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getEntityManager();

        $addressType = new AddressType();
        $addressType->setName('Home');
        $addressType->setId(1);

        $metadata = $entityManager->getClassMetadata(\get_class($addressType));
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $emailType = new EmailType();
        $emailType->setName('work');
        $emailType->setId(1);

        $metadata = $entityManager->getClassMetadata(\get_class($emailType));
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

    /**
     * @param mixed[] $data
     */
    private function submitProfile(array $data): User
    {
        $crawler = $this->client->request('GET', '/profile');
        $this->assertHttpStatusCode(200, $this->client->getResponse());

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

        $form = $crawler->selectButton('profile[submit]')->form(\array_merge(
            $data,
            [
                'profile[_token]' => $crawler->filter('#profile__token')->first()->attr('value'),
            ]
        ));

        $crawler = $this->client->submit($form);
        $this->assertHttpStatusCode(200, $this->client->getResponse());

        $this->assertCount(1, $crawler->filter('.success'));

        $this->getEntityManager()->clear();

        /** @var UserRepository $repository */
        $repository = $this->getEntityManager()->getRepository(User::class);

        /** @var User */
        return $repository->findOneBy(['username' => 'test']);
    }

    public function testProfile(): void
    {
        $user = $this->submitProfile([
            'profile[formOfAddress]' => 0,
            'profile[firstName]' => 'Hikaru',
            'profile[lastName]' => 'Sulu',
            'profile[mainEmail]' => 'sulu@example.org',
            'profile[street]' => 'Rathausstraße',
            'profile[number]' => '16',
            'profile[zip]' => '12351',
            'profile[city]' => 'USS Excelsior',
            'profile[countryCode]' => 'AT',
            'profile[note]' => 'Test',
        ]);

        $this->assertSame(0, $user->getContact()->getFormOfAddress());
        $this->assertSame('Hikaru Sulu', $user->getFullname());
        $mainAddress = $user->getContact()->getMainAddress();
        $this->assertNotNull($mainAddress);
        $this->assertSame('Rathausstraße', $mainAddress->getStreet());
        $this->assertSame('USS Excelsior', $mainAddress->getCity());
        $this->assertSame('16', $mainAddress->getNumber());
        $this->assertSame('12351', $mainAddress->getZip());
        $this->assertSame('AT', $mainAddress->getCountryCode());
        $this->assertSame('Test', $user->getContact()->getNote());
    }

    public function testProfileWithoutNote(): void
    {
        $user = $this->submitProfile([
            'profile[formOfAddress]' => 0,
            'profile[firstName]' => 'Hikaru',
            'profile[lastName]' => 'Sulu',
            'profile[mainEmail]' => 'sulu@example.org',
            'profile[street]' => 'Rathausstraße',
            'profile[number]' => '16',
            'profile[zip]' => '12351',
            'profile[city]' => 'USS Excelsior',
            'profile[countryCode]' => 'AT',
        ]);

        $this->assertNull($user->getContact()->getNote());
    }

    public function testProfileInvalid(): void
    {
        $crawler = $this->client->request('GET', '/profile');
        $this->assertHttpStatusCode(200, $this->client->getResponse());

        $form = $crawler->selectButton('profile[submit]')->form([
            'profile[firstName]' => null,
        ]);

        $this->client->submit($form);
        $this->assertHttpStatusCode(422, $this->client->getResponse());
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
