<?php

namespace Sulu\Bundle\CommunityBundle\Form\Type;

use Sulu\Bundle\CommunityBundle\Validator\Constraints\Exist;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PasswordForgetType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email_username', TextType::class, [
            'constraints' => new Exist([
                'columns' => ['email', 'username'],
                'entity' => $options['user_class'],
                'groups' => 'password_forget',
            ]),
        ]);

        $builder->add('submit', SubmitType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'user_class' => User::class,
            'validation_groups' => ['password_forget'],
        ]);
    }
}
