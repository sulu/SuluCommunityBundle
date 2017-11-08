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

use Sulu\Bundle\ContactBundle\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Profile contact form.
 */
class ProfileContactType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'formOfAddress',
            ChoiceType::class,
            [
                'choices' => [
                    'contact.contacts.formOfAddress.male',
                    'contact.contacts.formOfAddress.female',
                ],
                'translation_domain' => 'backend',
                'expanded' => true,
            ]
        );

        $builder->add('firstName', TextType::class);
        $builder->add('lastName', TextType::class);
        $builder->add('mainEmail', EmailType::class);
        $builder->add('avatar', FileType::class, ['mapped' => false, 'required' => false]);

        $builder->add(
            'contactAddresses',
            CollectionType::class,
            [
                'label' => false,
                'entry_type' => $options['contact_address_type'],
                'entry_options' => $options['contact_address_type_options'],
            ]
        );
        $builder->add(
            'notes',
            CollectionType::class,
            [
                'label' => false,
                'entry_type' => $options['note_type'],
                'entry_options' => $options['note_type_options'],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Contact::class,
                'contact_address_type' => ProfileContactAddressType::class,
                'contact_address_type_options' => ['label' => false],
                'note_type' => ProfileNoteType::class,
                'note_type_options' => ['label' => false],
                'validation_groups' => ['profile'],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'contact';
    }
}
