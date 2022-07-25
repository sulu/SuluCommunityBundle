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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Create the contact registration form type.
 */
class CompletionContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Contact $contact */
        $contact = $builder->getData();

        if (!$contact->getFirstName()) {
            $builder->add('firstName', TextType::class);
        }

        if (!$contact->getLastName()) {
            $builder->add('lastName', TextType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
            'validation_groups' => ['completion'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'contact';
    }
}
