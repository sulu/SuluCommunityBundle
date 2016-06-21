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
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $builder->getData();

        if (!$user->getUsername()) {
            $builder->add('username', TextType::class);
        }

        if (!$user->getEmail()) {
            $builder->add('email', EmailType::class);
        }

        $builder->add(
            'contact',
            new $options['contact_type'](),
            array_merge(
                $options['contact_type_options'],
                [
                    'data' => $user->getContact(),
                ]
            )
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
                'contact_type' => CompletionContactType::class,
                'contact_type_options' => ['label' => false],
                'validation_groups' => ['completion'],
            ]
        );
    }
}
