<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Controller;

use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Component\Media\SystemCollections\SystemCollectionManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait SaveMediaTrait
{
    private function saveMediaFields(FormInterface $form, User $user, $locale)
    {
        $this->saveAvatar($form, $user, $locale);
        $this->saveDocuments($form, $user, $locale);
    }

    private function saveDocuments(FormInterface $form, User $user, $locale)
    {
        if (!$form->has('contact') || !$form->get('contact')->has('medias')) {
            return;
        }

        $uploadedFiles = $form->get('contact')->get('medias')->getData();

        if (empty($uploadedFiles)) {
            return null;
        }

        if (!is_array($uploadedFiles)) {
            $uploadedFiles = [$uploadedFiles];
        }

        $contact = $user->getContact();
        $apiMedias = [];

        foreach ($uploadedFiles as $uploadedFile) {
            $apiMedia = $this->saveMedia($uploadedFile, null, $locale, $user->getId());
            $contact->addMedia($apiMedia->getEntity());
            $apiMedias[] = $apiMedia;
        }

        return $apiMedias;
    }

    protected function saveAvatar(FormInterface $form, User $user, $locale)
    {
        if (!$form->has('contact') || !$form->get('contact')->has('avatar')) {
            return;
        }

        $uploadedFile = $form->get('contact')->get('avatar')->getData();
        if (null === $uploadedFile) {
            return null;
        }

        $avatar = $user->getContact()->getAvatar();

        $apiMedia = $this->saveMedia($uploadedFile, (null !== $avatar ? $avatar->getId() : null), $locale, $user->getId());

        $user->getContact()->setAvatar($apiMedia->getEntity());

        return $apiMedia;
    }

    private function saveMedia(UploadedFile $uploadedFile, $id, $locale, $userId)
    {
        return $this->getMediaManager()->save(
            $uploadedFile,
            [
                'id' => $id,
                'locale' => $locale,
                'title' => $uploadedFile->getClientOriginalName(),
                'collection' => $this->getContactMediaCollection(),
            ],
            $userId
        );
    }

    /**
     * Get system collection manager.
     *
     * @return SystemCollectionManagerInterface
     */
    private function getSystemCollectionManager()
    {
        return $this->get('sulu_media.system_collections.manager');
    }

    /**
     * Get media manager.
     *
     * @return MediaManagerInterface
     */
    private function getMediaManager()
    {
        return $this->get('sulu_media.media_manager');
    }

    /**
     * Get contact media collection.
     *
     * @return int
     */
    private function getContactMediaCollection()
    {
        return $this->getSystemCollectionManager()->getSystemCollection('sulu_contact.contact');
    }
}
