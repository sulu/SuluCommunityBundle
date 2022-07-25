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

use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Create the registration form type.
 */
class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('username', TextType::class);
        $builder->add('email', EmailType::class);
        $builder->add(
            'plainPassword',
            PasswordType::class,
            [
                'mapped' => false,
                'constraints' => new NotBlank([
                    'groups' => ['registration'],
                ]),
            ]
        );

        $builder->add('firstName', TextType::class, [
            'property_path' => 'contact.firstName',
        ]);

        $builder->add('lastName', TextType::class, [
            'property_path' => 'contact.lastName',
        ]);

        $builder->add(
            'terms',
            CheckboxType::class,
            [
                'mapped' => false,
                'required' => true,
                'constraints' => new NotBlank([
                    'groups' => ['registration'],
                ]),
            ]
        );

        $builder->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
                'validation_groups' => ['registration'],
                'empty_data' => function (FormInterface $form) {
                    $user = new User();
                    $user->setContact(new Contact());

                    return $user;
                },
            ]
        );
    }
}
