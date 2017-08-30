<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Form\Type;

use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Create the registration form type.
 */
class RegistrationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
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

        $builder->add(
            'contact',
            $options['contact_type'],
            $options['contact_type_options']
        );

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

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
                'contact_type' => RegistrationContactType::class,
                'contact_type_options' => ['label' => false],
                'validation_groups' => ['registration'],
            ]
        );
    }
}
