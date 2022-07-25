<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\CommunityBundle\Controller\SaveMediaTrait;
use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\MediaBundle\Api\Media as ApiMedia;
use Sulu\Bundle\MediaBundle\Entity\Media;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Component\Media\SystemCollections\SystemCollectionManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SaveMediaTraitTest extends TestCase
{
    use SaveMediaTrait {
        getMediaManager as mockedGetMediaManager;
        getSystemCollectionManager as mockedGetSystemCollectionManager;
    }

    /**
     * @var ObjectProphecy<MediaManagerInterface>
     */
    private $mediaManager;

    /**
     * @var ObjectProphecy<SystemCollectionManagerInterface>
     */
    private $systemCollectionManager;

    /**
     * @var ObjectProphecy<User>
     */
    private $user;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var ObjectProphecy<FormInterface>
     */
    private $form;

    /**
     * @var ObjectProphecy<FormInterface>
     */
    private $avatarForm;

    /**
     * @var ObjectProphecy<FormInterface>
     */
    private $mediasForm;

    /**
     * @var string[]
     */
    private $tempFilePaths = [];

    /**
     * @var ObjectProphecy<Contact>
     */
    private $contact;

    /**
     * @var ObjectProphecy<Media>
     */
    private $media;

    /**
     * @var ObjectProphecy<ApiMedia>
     */
    private $apiMedia;

    protected function setUp(): void
    {
        $this->form = $this->prophesize(FormInterface::class);
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

    protected function tearDown(): void
    {
        foreach ($this->tempFilePaths as $tempFilePath) {
            \unlink($tempFilePath);
        }
    }

    public function testNoAvatarAndNoMedias(): void
    {
        $this->form->has('avatar')->willReturn(false)->shouldBeCalled();
        $this->form->get('avatar')->shouldNotBeCalled();
        $this->form->has('medias')->willReturn(false)->shouldBeCalled();
        $this->form->get('medias')->shouldNotBeCalled();

        $this->saveMediaFields($this->form->reveal(), $this->user->reveal(), $this->locale);
    }

    public function testAvatarAndNoMedias(): void
    {
        $this->contact->setAvatar($this->media->reveal())->shouldBeCalled();

        $fileName = $this->createTempnam();
        $uploadedFile = new UploadedFile($fileName, 'test.jpg');

        $this->user->getId()->willReturn(1)->shouldBeCalled();
        $this->contact->getAvatar()->willReturn(null)->shouldBeCalled();

        $this->form->has('medias')->willReturn(false)->shouldBeCalled();
        $this->form->get('medias')->shouldNotBeCalled();
        $this->form->has('avatar')->willReturn(true)->shouldBeCalled();
        $this->avatarForm->getData()->willReturn($uploadedFile)->shouldBeCalled();
        $this->form->get('avatar')->willReturn($this->avatarForm->reveal())->shouldBeCalled();

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

    public function testNoAvatarAndSingleMedia(): void
    {
        $this->contact->addMedia($this->media->reveal())->shouldBeCalled();

        $tempFileName = $this->createTempnam();
        $uploadedFile = new UploadedFile($tempFileName, 'test.jpg');

        $this->user->getId()->willReturn(1)->shouldBeCalled();
        $this->contact->getAvatar()->shouldNotBeCalled();

        $this->form->has('medias')->willReturn(true)->shouldBeCalled();
        $this->form->get('medias')->willReturn($this->mediasForm->reveal());
        $this->mediasForm->getData()->willReturn($uploadedFile)->shouldBeCalled();
        $this->form->has('avatar')->willReturn(false)->shouldBeCalled();
        $this->form->get('avatar')->shouldNotBeCalled();

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

    public function testNoAvatarAndMultipleMedias(): void
    {
        $this->contact->addMedia($this->media->reveal())->shouldBeCalled();

        $tempFileName1 = $this->createTempnam();
        $tempFileName2 = $this->createTempnam();

        $uploadedFile = new UploadedFile($tempFileName1, 'test.jpg');
        $uploadedFile2 = new UploadedFile($tempFileName2, 'test2.jpg');

        $this->user->getId()->willReturn(1)->shouldBeCalled();
        $this->contact->getAvatar()->shouldNotBeCalled();

        $this->form->has('medias')->willReturn(true)->shouldBeCalled();
        $this->form->get('medias')->willReturn($this->mediasForm->reveal());
        $this->mediasForm->getData()->willReturn([$uploadedFile, $uploadedFile2])->shouldBeCalled();
        $this->form->has('avatar')->willReturn(false)->shouldBeCalled();
        $this->form->get('avatar')->shouldNotBeCalled();

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

    public function testAvatarAndMultipleMedias(): void
    {
        $this->contact->setAvatar($this->media->reveal())->shouldBeCalled();
        $this->contact->addMedia($this->media->reveal())->shouldBeCalled();

        $tempFileName1 = $this->createTempnam();
        $tempFileName2 = $this->createTempnam();
        $tempFileName3 = $this->createTempnam();

        $uploadedFile = new UploadedFile($tempFileName1, 'test.jpg');
        $uploadedFile2 = new UploadedFile($tempFileName2, 'test2.jpg');
        $uploadedFile3 = new UploadedFile($tempFileName3, 'test3.jpg');

        $this->user->getId()->willReturn(1)->shouldBeCalled();
        $this->contact->getAvatar()->willReturn($this->media->reveal())->shouldBeCalled();

        $this->form->has('medias')->willReturn(true)->shouldBeCalled();
        $this->form->get('medias')->willReturn($this->mediasForm->reveal());
        $this->mediasForm->getData()->willReturn([$uploadedFile, $uploadedFile2])->shouldBeCalled();
        $this->form->has('avatar')->willReturn(true)->shouldBeCalled();
        $this->avatarForm->getData()->willReturn($uploadedFile3)->shouldBeCalled();
        $this->form->get('avatar')->willReturn($this->avatarForm->reveal())->shouldBeCalled();

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

    private function getMediaManager(): MediaManagerInterface
    {
        return $this->mediaManager->reveal();
    }

    private function getSystemCollectionManager(): SystemCollectionManagerInterface
    {
        return $this->systemCollectionManager->reveal();
    }

    private function createTempnam(): string
    {
        $filename = \tempnam(\sys_get_temp_dir(), 'sulu_community_test_media');

        if (false === $filename) {
            throw new \RuntimeException('Could not create tempnam in: ' . \sys_get_temp_dir());
        }

        $this->tempFilePaths[] = $filename;

        return $filename;
    }
}
