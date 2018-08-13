<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Tests\Unit\Controller;

use Sulu\Bundle\CommunityBundle\Controller\SaveMediaTrait;
use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\MediaBundle\Api\Media as ApiMedia;
use Sulu\Bundle\MediaBundle\Entity\Media;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Component\Media\SystemCollections\SystemCollectionManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SaveMediaTraitTest extends \PHPUnit_Framework_TestCase
{
    use SaveMediaTrait {
        getMediaManager as mockedGetMediaManager;
        getSystemCollectionManager as mockedGetSystemCollectionManager;
    }

    /**
     * @var MediaManagerInterface
     */
    private $mediaManager;

    /**
     * @var SystemCollectionManagerInterface
     */
    private $systemCollectionManager;

    /**
     * @var User
     */
    private $user;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var FormInterface
     */
    private $form;

    /**
     * @var FormInterface
     */
    private $contactForm;

    /**
     * @var FormInterface
     */
    private $avatarForm;

    /**
     * @var FormInterface
     */
    private $mediasForm;

    /**
     * @var string[]
     */
    private $tempFilePaths = [];

    /**
     * @var Contact
     */
    private $contact;

    /**
     * @var Media
     */
    private $media;

    /**
     * @var ApiMedia
     */
    private $apiMedia;

    protected function setUp()
    {
        $this->form = $this->prophesize(FormInterface::class);
        $this->contactForm = $this->prophesize(FormInterface::class);
        $this->avatarForm = $this->prophesize(FormInterface::class);
        $this->mediasForm = $this->prophesize(FormInterface::class);
        $this->contact = $this->prophesize(Contact::class);
        $this->mediaManager = $this->prophesize(MediaManagerInterface::class);
        $this->systemCollectionManager = $this->prophesize(SystemCollectionManagerInterface::class);
        $this->user = $this->prophesize(User::class);
        $this->media = $this->prophesize(Media::class);
        $this->media->getId()->willReturn(3);
        $this->apiMedia = $this->prophesize(ApiMedia::class);
        $this->apiMedia->getEntity()->willReturn($this->media->reveal());
        $this->user->getContact()->willReturn($this->contact->reveal());
        $this->locale = 'de';
    }

    protected function tearDown()
    {
        foreach ($this->tempFilePaths as $tempFilePath) {
            unlink($tempFilePath);
        }
    }

    public function testNoContact()
    {
        $this->form->has('contact')->willReturn(false)->shouldBeCalled();
        $this->form->get('contact')->shouldNotBeCalled();

        $this->saveMediaFields($this->form->reveal(), $this->user->reveal(), $this->locale);
    }

    public function testNoAvatarAndNoMedias()
    {
        $this->form->has('contact')->willReturn(true)->shouldBeCalled();
        $this->contactForm->has('avatar')->willReturn(false)->shouldBeCalled();
        $this->contactForm->get('avatar')->shouldNotBeCalled();
        $this->contactForm->has('medias')->willReturn(false)->shouldBeCalled();
        $this->contactForm->get('medias')->shouldNotBeCalled();
        $this->form->get('contact')->willReturn($this->contactForm->reveal());

        $this->saveMediaFields($this->form->reveal(), $this->user->reveal(), $this->locale);
    }

    public function testAvatarAndNoMedias()
    {
        $this->contact->setAvatar($this->media->reveal())->shouldBeCalled();

        $this->tempFilePaths[] = tempnam(sys_get_temp_dir(), 'sulu_community_test_media');
        $uploadedFile = new UploadedFile($this->tempFilePaths[0], 'test.jpg');

        $this->user->getId()->willReturn(1)->shouldBeCalled();
        $this->contact->getAvatar()->willReturn(null)->shouldBeCalled();

        $this->form->has('contact')->willReturn(true)->shouldBeCalled();
        $this->contactForm->has('medias')->willReturn(false)->shouldBeCalled();
        $this->contactForm->get('medias')->shouldNotBeCalled();
        $this->contactForm->has('avatar')->willReturn(true)->shouldBeCalled();
        $this->avatarForm->getData()->willReturn($uploadedFile)->shouldBeCalled();
        $this->contactForm->get('avatar')->willReturn($this->avatarForm->reveal())->shouldBeCalled();
        $this->form->get('contact')->willReturn($this->contactForm->reveal());

        $this->systemCollectionManager->getSystemCollection('sulu_contact.contact')->willReturn(2)->shouldBeCalled();

        $this->mediaManager->save(
            $uploadedFile,
            [
                'id' => null,
                'locale' => 'de',
                'title' => 'test.jpg',
                'collection' => 2,
            ],
            1
        )->shouldBeCalled()->willReturn($this->apiMedia->reveal());

        $this->saveMediaFields($this->form->reveal(), $this->user->reveal(), $this->locale);
    }

    public function testNoAvatarAndSingleMedia()
    {
        $this->contact->addMedia($this->media->reveal())->shouldBeCalled();

        $this->tempFilePaths[] = tempnam(sys_get_temp_dir(), 'sulu_community_test_media');
        $uploadedFile = new UploadedFile($this->tempFilePaths[0], 'test.jpg');

        $this->user->getId()->willReturn(1)->shouldBeCalled();
        $this->contact->getAvatar()->shouldNotBeCalled();

        $this->form->has('contact')->willReturn(true)->shouldBeCalled();
        $this->contactForm->has('medias')->willReturn(true)->shouldBeCalled();
        $this->contactForm->get('medias')->willReturn($this->mediasForm->reveal());
        $this->mediasForm->getData()->willReturn($uploadedFile)->shouldBeCalled();
        $this->contactForm->has('avatar')->willReturn(false)->shouldBeCalled();
        $this->contactForm->get('avatar')->shouldNotBeCalled();
        $this->form->get('contact')->willReturn($this->contactForm->reveal());

        $this->systemCollectionManager->getSystemCollection('sulu_contact.contact')->willReturn(2)->shouldBeCalled();

        $this->mediaManager->save(
            $uploadedFile,
            [
                'id' => null,
                'locale' => 'de',
                'title' => 'test.jpg',
                'collection' => 2,
            ],
            1
        )->shouldBeCalled()->willReturn($this->apiMedia->reveal());

        $this->saveMediaFields($this->form->reveal(), $this->user->reveal(), $this->locale);
    }

    public function testNoAvatarAndMultipleMedias()
    {
        $this->contact->addMedia($this->media->reveal())->shouldBeCalled();

        $this->tempFilePaths[] = tempnam(sys_get_temp_dir(), 'sulu_community_test_media');
        $this->tempFilePaths[] = tempnam(sys_get_temp_dir(), 'sulu_community_test_media');
        $uploadedFile = new UploadedFile($this->tempFilePaths[0], 'test.jpg');
        $uploadedFile2 = new UploadedFile($this->tempFilePaths[1], 'test2.jpg');

        $this->user->getId()->willReturn(1)->shouldBeCalled();
        $this->contact->getAvatar()->shouldNotBeCalled();

        $this->form->has('contact')->willReturn(true)->shouldBeCalled();
        $this->contactForm->has('medias')->willReturn(true)->shouldBeCalled();
        $this->contactForm->get('medias')->willReturn($this->mediasForm->reveal());
        $this->mediasForm->getData()->willReturn([$uploadedFile, $uploadedFile2])->shouldBeCalled();
        $this->contactForm->has('avatar')->willReturn(false)->shouldBeCalled();
        $this->contactForm->get('avatar')->shouldNotBeCalled();
        $this->form->get('contact')->willReturn($this->contactForm->reveal());

        $this->systemCollectionManager->getSystemCollection('sulu_contact.contact')->willReturn(2)->shouldBeCalled();

        $this->mediaManager->save(
            $uploadedFile,
            [
                'id' => null,
                'locale' => 'de',
                'title' => 'test.jpg',
                'collection' => 2,
            ],
            1
        )->shouldBeCalled()->willReturn($this->apiMedia->reveal());

        $this->mediaManager->save(
            $uploadedFile2,
            [
                'id' => null,
                'locale' => 'de',
                'title' => 'test2.jpg',
                'collection' => 2,
            ],
            1
        )->shouldBeCalled()->willReturn($this->apiMedia->reveal());

        $this->saveMediaFields($this->form->reveal(), $this->user->reveal(), $this->locale);
    }

    public function testAvatarAndMultipleMedias()
    {
        $this->contact->setAvatar($this->media->reveal())->shouldBeCalled();
        $this->contact->addMedia($this->media->reveal())->shouldBeCalled();

        $this->tempFilePaths[] = tempnam(sys_get_temp_dir(), 'sulu_community_test_media');
        $this->tempFilePaths[] = tempnam(sys_get_temp_dir(), 'sulu_community_test_media');
        $this->tempFilePaths[] = tempnam(sys_get_temp_dir(), 'sulu_community_test_media');
        $uploadedFile = new UploadedFile($this->tempFilePaths[0], 'test.jpg');
        $uploadedFile2 = new UploadedFile($this->tempFilePaths[1], 'test2.jpg');
        $uploadedFile3 = new UploadedFile($this->tempFilePaths[1], 'test3.jpg');

        $this->user->getId()->willReturn(1)->shouldBeCalled();
        $this->contact->getAvatar()->willReturn($this->media->reveal())->shouldBeCalled();

        $this->form->has('contact')->willReturn(true)->shouldBeCalled();
        $this->contactForm->has('medias')->willReturn(true)->shouldBeCalled();
        $this->contactForm->get('medias')->willReturn($this->mediasForm->reveal());
        $this->mediasForm->getData()->willReturn([$uploadedFile, $uploadedFile2])->shouldBeCalled();
        $this->contactForm->has('avatar')->willReturn(true)->shouldBeCalled();
        $this->avatarForm->getData()->willReturn($uploadedFile3)->shouldBeCalled();
        $this->contactForm->get('avatar')->willReturn($this->avatarForm->reveal())->shouldBeCalled();
        $this->form->get('contact')->willReturn($this->contactForm->reveal());

        $this->systemCollectionManager->getSystemCollection('sulu_contact.contact')->willReturn(2)->shouldBeCalled();

        $this->mediaManager->save(
            $uploadedFile,
            [
                'id' => null,
                'locale' => 'de',
                'title' => 'test.jpg',
                'collection' => 2,
            ],
            1
        )->shouldBeCalled()->willReturn($this->apiMedia->reveal());

        $this->mediaManager->save(
            $uploadedFile2,
            [
                'id' => null,
                'locale' => 'de',
                'title' => 'test2.jpg',
                'collection' => 2,
            ],
            1
        )->shouldBeCalled()->willReturn($this->apiMedia->reveal());

        $this->mediaManager->save(
            $uploadedFile3,
            [
                'id' => 3,
                'locale' => 'de',
                'title' => 'test3.jpg',
                'collection' => 2,
            ],
            1
        )->shouldBeCalled()->willReturn($this->apiMedia->reveal());

        $this->saveMediaFields($this->form->reveal(), $this->user->reveal(), $this->locale);
    }

    private function getMediaManager()
    {
        return $this->mediaManager->reveal();
    }

    private function getSystemCollectionManager()
    {
        return $this->systemCollectionManager->reveal();
    }
}
