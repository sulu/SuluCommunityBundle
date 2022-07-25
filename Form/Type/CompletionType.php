<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Form\Type;

use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Create the registration form type.
 */
class CompletionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $builder->getData();

        if (!$user->getUsername()) {
            $builder->add('username', TextType::class);
        }

        if (!$user->getEmail()) {
            $builder->add('email', EmailType::class);
        }

        if (!$user->getContact()->getFirstName()) {
            $builder->add('firstName', TextType::class, [
                'property_path' => 'contact.firstName',
            ]);
        }

        if (!$user->getContact()->getLastName()) {
            $builder->add('lastName', TextType::class, [
                'property_path' => 'contact.lastName',
            ]);
        }

        $builder->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
                'validation_groups' => ['completion'],
            ]
        );
    }
}
