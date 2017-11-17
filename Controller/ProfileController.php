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

use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Sulu\Bundle\ContactBundle\Entity\Address;
use Sulu\Bundle\ContactBundle\Entity\AddressType;
use Sulu\Bundle\ContactBundle\Entity\ContactAddress;
use Sulu\Bundle\ContactBundle\Entity\Note;
use Sulu\Bundle\MediaBundle\Api\Media;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handle profile page.
 */
class ProfileController extends AbstractController
{
    const TYPE = Configuration::TYPE_PROFILE;

    /**
     * Handle profile form.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $communityManager = $this->getCommunityManager($this->getWebspaceKey());

        $user = $this->getUser();

        // Create Form
        $form = $this->createForm(
            $communityManager->getConfigTypeProperty(self::TYPE, Configuration::FORM_TYPE),
            $user,
            $communityManager->getConfigTypeProperty(self::TYPE, Configuration::FORM_TYPE_OPTIONS)
        );

        $form->handleRequest($request);
        $success = false;

        // Handle Form Success
        if ($form->isSubmitted() && $form->isValid()) {
            // Set Password and Salt
            $user = $this->setUserPasswordAndSalt($form->getData(), $form);

            if (!$user->getLocale()) {
                $user->setLocale($request->getLocale());
            }

            $this->saveAvatar($form, $user, $request->getLocale());

            // Register User
            $communityManager->saveProfile($user);
            $this->saveEntities();

            // Redirect
            $redirectTo = $communityManager->getConfigTypeProperty(self::TYPE, Configuration::REDIRECT_TO);

            if ($redirectTo) {
                return $this->redirect($redirectTo);
            }

            $success = true;
        }

        return $this->renderTemplate(
            self::TYPE,
            [
                'form' => $form->createView(),
                'success' => $success,
            ]
        );
    }

    /**
     * Save media and set avatar on user.
     *
     * @param Form $form
     * @param User $user
     * @param string $locale
     *
     * @return Media|null
     */
    protected function saveAvatar(Form $form, User $user, $locale)
    {
        $uploadedFile = $form->get('contact')->get('avatar')->getData();
        if (null === $uploadedFile) {
            return null;
        }

        $systemCollectionManager = $this->get('sulu_media.system_collections.manager');
        $mediaManager = $this->get('sulu_media.media_manager');

        $collection = $systemCollectionManager->getSystemCollection('sulu_contact.contact');
        $avatar = $user->getContact()->getAvatar();

        $apiMedia = $mediaManager->save(
            $uploadedFile,
            [
                'id' => (null !== $avatar ? $avatar->getId() : null),
                'locale' => $locale,
                'title' => $user->getUsername(),
                'collection' => $collection,
            ],
            $user->getId()
        );

        $user->getContact()->setAvatar($apiMedia->getEntity());

        return $apiMedia;
    }

    /**
     * {@inheritdoc}
     *
     * @return User
     */
    public function getUser()
    {
        /** @var User $user */
        $user = parent::getUser();

        if (null === $user->getContact()->getMainAddress()) {
            $this->addAddress($user);
        }

        if (0 === count($user->getContact()->getNotes())) {
            $this->addNote($user);
        }

        return $user;
    }

    /**
     * Add address to user.
     *
     * @param User $user
     */
    private function addAddress(User $user)
    {
        $entityManager = $this->get('doctrine.orm.entity_manager');

        $address = new Address();
        $address->setPrimaryAddress(true);
        $address->setAddressType($entityManager->getRepository(AddressType::class)->find(1));
        $contactAddress = new ContactAddress();
        $contactAddress->setAddress($address);
        $contactAddress->setContact($user->getContact());

        $user->getContact()->addContactAddress($contactAddress);
    }

    /**
     * Add note to user.
     *
     * @param User $user
     */
    private function addNote(User $user)
    {
        $note = new Note();
        $user->getContact()->addNote($note);

        $this->get('doctrine.orm.entity_manager')->persist($note);
    }
}
