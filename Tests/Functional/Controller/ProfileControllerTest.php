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
use Sulu\Bundle\ContactBundle\Entity\AddressType;
use Sulu\Bundle\ContactBundle\Entity\Country;
use Sulu\Bundle\ContactBundle\Entity\EmailType;
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

        $emailType = new EmailType();
        $emailType->setName('work');
        $emailType->setId(1);

        $metadata = $entityManager->getClassMetadata(get_class($emailType));
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $entityManager->persist($addressType);
        $entityManager->persist($country);
        $entityManager->persist($emailType);
        $entityManager->flush();
    }

    private function submitProfile($data)
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

        $this->assertCount(1, $crawler->filter('#profile_formOfAddress'));
        $this->assertCount(1, $crawler->filter('#profile_firstName'));
        $this->assertCount(1, $crawler->filter('#profile_lastName'));
        $this->assertCount(1, $crawler->filter('#profile_mainEmail'));
        $this->assertCount(1, $crawler->filter('#profile_street'));
        $this->assertCount(1, $crawler->filter('#profile_number'));
        $this->assertCount(1, $crawler->filter('#profile_zip'));
        $this->assertCount(1, $crawler->filter('#profile_city'));
        $this->assertCount(1, $crawler->filter('#profile_country'));
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
            'profile[country]' => 1,
            'profile[note]' => 'Test',
        ]);

        $this->assertEquals(0, $user->getContact()->getFormOfAddress());
        $this->assertEquals('Hikaru Sulu', $user->getFullname());
        $this->assertEquals('Rathausstraße', $user->getContact()->getMainAddress()->getStreet());
        $this->assertEquals('USS Excelsior', $user->getContact()->getMainAddress()->getCity());
        $this->assertEquals(16, $user->getContact()->getMainAddress()->getNumber());
        $this->assertEquals(12351, $user->getContact()->getMainAddress()->getZip());
        $this->assertEquals(1, $user->getContact()->getMainAddress()->getCountry()->getId());
        $this->assertEquals('Test', $user->getContact()->getNotes()[0]->getValue());
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
            'profile[country]' => 1,
        ]);

        $this->assertSame('', $user->getContact()->getNotes()[0]->getValue());
    }

    protected function getKernelConfiguration()
    {
        return [
            'sulu_context' => 'website',
        ];
    }
}
