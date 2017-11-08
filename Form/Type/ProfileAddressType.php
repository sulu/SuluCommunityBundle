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

use Sulu\Bundle\ContactBundle\Entity\Address;
use Sulu\Bundle\ContactBundle\Entity\Country;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Profile address form.
 */
class ProfileAddressType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('primaryAddress', HiddenType::class, ['data' => 1]);

        $builder->add('street', TextType::class, ['required' => false]);
        $builder->add('number', TextType::class, ['required' => false]);
        $builder->add('zip', TextType::class, ['required' => false]);
        $builder->add('city', TextType::class, ['required' => false]);
        $builder->add(
            'country',
            EntityType::class,
            [
                'class' => Country::class,
                'choice_label' => function (Country $country) {
                    return Intl::getRegionBundle()->getCountryName($country->getCode());
                },
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
                'data_class' => Address::class,
                'validation_groups' => ['profile'],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'address';
    }
}
