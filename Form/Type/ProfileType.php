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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Profile form.
 */
class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('plainPassword', PasswordType::class, ['mapped' => false, 'required' => false]);

        $builder->add(
            'formOfAddress',
            ChoiceType::class,
            [
                'property_path' => 'contact.formOfAddress',
                'choices' => [
                    'sulu_contact.male_form_of_address' => 0,
                    'sulu_contact.female_form_of_address' => 1,
                ],
                'translation_domain' => 'admin',
                'expanded' => true,
            ]
        );

        $builder->add('firstName', TextType::class, [
            'property_path' => 'contact.firstName',
        ]);

        $builder->add('lastName', TextType::class, [
            'property_path' => 'contact.lastName',
        ]);

        $builder->add('mainEmail', EmailType::class, [
            'property_path' => 'contact.mainEmail',
        ]);

        $builder->add('avatar', FileType::class, [
            'property_path' => 'contact.avatar',
            'mapped' => false,
            'required' => false,
        ]);

        $builder->add('street', TextType::class, [
            'property_path' => 'contact.mainAddress.street',
            'required' => false,
        ]);

        $builder->add('number', TextType::class, [
            'property_path' => 'contact.mainAddress.number',
            'required' => false,
        ]);

        $builder->add('zip', TextType::class, [
            'property_path' => 'contact.mainAddress.zip',
            'required' => false,
        ]);

        $builder->add('city', TextType::class, [
            'property_path' => 'contact.mainAddress.city',
            'required' => false,
        ]);

        $builder->add('countryCode', CountryType::class, [
            'property_path' => 'contact.mainAddress.countryCode',
            'required' => false,
        ]);

        $builder->add('note', TextareaType::class, [
            'label' => false,
            'property_path' => 'contact.note',
        ]);

        $builder->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
                'validation_groups' => ['profile'],
            ]
        );
    }
}
