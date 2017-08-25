# Profile

## Config

```yml
# app/config/config.yml

sulu_community:
    webspaces:
        <webspace_key>:
            # Profile
            profile:
                email:
                    subject: Profile Updated
                    user_template: ~
                    admin_template: ~
                redirect_to: ~
                template: AppBundle:templates:community/Profile/profile-form.html.twig
                type: AppBundle\Form\Type\ProfileType
```

## email

It is possible that an email is also sent when a profile is changed.

**Example Template**:

```twig
{# AppBundle:templates:community/Profile/profile-email.html.twig #}

{% extends "AppBundle::master-email.html.twig" %}

{% block content %}
    Your profile was updated.
{% endblock %}
```

## template

The profile edit template.

```twig
{# AppBundle:templates:community/Profile/profile-form.html.twig #}

{% extends "AppBundle::master.html.twig" %}

{% block content %}
    <h1>Profile</h1>

    {% if success %}
        <p class="success">
            Profile was saved!
        </p>
    {% endif %}

    {{ form(form) }}
{% endblock %}
```

## type

Set a new type to overwrite the existing form.

**Example Class**:

```php
//  src/AppBundle/Form/Type/ProfileType.php

namespace AppBundle\Form\Type;

use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('plainPassword', PasswordType::class, ['mapped' => false, 'required' => false]);
        $builder->add('contact', $options['contact_type'], $options['contact_type_options']);
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
                'contact_type' => ProfileContactType::class,
                'contact_type_options' => ['label' => false],
                'validation_groups' => ['profile'],
            ]
        );
    }
}
```

```php
//  src/AppBundle/Form/Type/ProfileContactType.php

namespace AppBundle\Form\Type;

use Sulu\Bundle\ContactBundle\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
        $builder->add('lastLame', TextType::class);
        $builder->add('mainEmail', EmailType::class);
        $builder->add('avatar', FileType::class, ['mapped' => false, 'required' => false]);

        $builder->add(
            'contactAddresses',
            CollectionType::class,
            [
                'label' => false,
                'type' => $options['contact_address_type'],
                'options' => $options['contact_address_type_options'],
            ]
        );
        $builder->add(
            'notes',
            CollectionType::class,
            [
                'label' => false,
                'type' => $options['note_type'],
                'options' => $options['note_type_options'],
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
```

```php
//  src/AppBundle/Form/Type/ProfileNoteType.php

namespace AppBundle\Form\Type;

use Sulu\Bundle\ContactBundle\Entity\Note;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileNoteType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('value', TextType::class, ['required' => false, 'label' => 'Note']);
        $builder->get('value')->addViewTransformer(
            new CallbackTransformer(
                function ($value) {
                    return $value;
                },
                function ($value) {
                    if ($value === null) {
                        return '';
                    }

                    return $value;
                }
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Note::class]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'note';
    }
}
```

```php
//  src/AppBundle/Form/Type/ProfileContactAddressType.php

namespace AppBundle\Form\Type;

use Sulu\Bundle\ContactBundle\Entity\ContactAddress;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileContactAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('address', $options['address_type'], $options['address_type_options']);
        $builder->add('main', 'hidden', [
            'required' => false,
            'data' => 1,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => ContactAddress::class,
                'address_type' => ProfileAddressType::class,
                'address_type_options' => ['label' => false],
                'validation_groups' => ['profile'],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'contact_address';
    }
}
```

```php
//  src/AppBundle/Form/Type/ProfileAddressType.php

namespace AppBundle\Form\Type;

use Sulu\Bundle\ContactBundle\Entity\Address;
use Sulu\Bundle\ContactBundle\Entity\Country;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileAddressType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('primaryAddress', 'hidden', ['data' => 1]);

        $builder->add('street', TextType::class, ['required' => false]);
        $builder->add('number', TextType::class, ['required' => false]);
        $builder->add('zip', TextType::class, ['required' => false]);
        $builder->add('city', TextType::class, ['required' => false]);
        $builder->add('country', EntityType::class, ['class' => Country::class, 'property' => 'name']);
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
```
