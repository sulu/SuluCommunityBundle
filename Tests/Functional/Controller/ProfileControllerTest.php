<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Functional\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sulu\Bundle\ContactBundle\Entity\AddressType;
use Sulu\Bundle\ContactBundle\Entity\Country;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\SecurityBundle\Entity\UserRepository;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class ProfileControllerTest extends SuluTestCase
{
    protected function setUp()
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

        $country = new Country();
        $country->setName('Star Trek');
        $country->setCode('ST');
        $country->setId(1);

        $metadata = $entityManager->getClassMetadata(get_class($country));
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $entityManager->persist($addressType);
        $entityManager->persist($country);
        $entityManager->flush();
    }

    public function testForm()
    {
        $client = $this->createClient(
            [
                'sulu_context' => 'website',
                'environment' => 'dev',
            ],
            [
                'PHP_AUTH_USER' => 'test',
                'PHP_AUTH_PW' => 'test',
            ]
        );

        $crawler = $client->request('GET', '/profile');
        $this->assertHttpStatusCode(200, $client->getResponse());

        $this->assertCount(1, $crawler->filter('#profile_contact_firstName'));
        $this->assertCount(1, $crawler->filter('#profile_contact_lastName'));
        $this->assertCount(1, $crawler->filter('#profile_contact_contactAddresses_0_address_street'));
        $this->assertCount(1, $crawler->filter('#profile_contact_contactAddresses_0_address_number'));
        $this->assertCount(1, $crawler->filter('#profile_contact_contactAddresses_0_address_zip'));
        $this->assertCount(1, $crawler->filter('#profile_contact_contactAddresses_0_address_city'));
        $this->assertCount(1, $crawler->filter('#profile_contact_contactAddresses_0_address_country'));
        $this->assertCount(1, $crawler->filter('#profile_contact_notes_0_value'));

        $form = $crawler->selectButton('profile[submit]')->form(
            [
                'profile[contact][formOfAddress]' => 0,
                'profile[contact][firstName]' => 'Hikaru',
                'profile[contact][lastName]' => 'Sulu',
                'profile[contact][contactAddresses][0][address][street]' => 'Rathausstraße',
                'profile[contact][contactAddresses][0][address][number]' => 16,
                'profile[contact][contactAddresses][0][address][zip]' => 12351,
                'profile[contact][contactAddresses][0][address][city]' => 'USS Excelsior',
                'profile[contact][contactAddresses][0][address][country]' => 1,
                'profile[contact][notes][0][value]' => 'Test',
                'profile[contact][contactAddresses][0][main]' => 1,
                'profile[_token]' => $crawler->filter('#profile__token')->first()->attr('value'),
        ]);
        $crawler = $client->submit($form);
        $this->assertHttpStatusCode(200, $client->getResponse());

        $this->assertCount(1, $crawler->filter('.success'));

        /** @var UserRepository $repository */
        $repository = $this->getEntityManager()->getRepository(User::class);
        $user = $repository->findOneBy(['username' => 'test']);

        $this->assertEquals('Hikaru Sulu', $user->getFullname());
        $this->assertEquals('Rathausstraße', $user->getContact()->getMainAddress()->getStreet());
        $this->assertEquals(16, $user->getContact()->getMainAddress()->getNumber());
        $this->assertEquals(12351, $user->getContact()->getMainAddress()->getZip());
        $this->assertEquals(1, $user->getContact()->getMainAddress()->getCountry()->getId());
    }
}
