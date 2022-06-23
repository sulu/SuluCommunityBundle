# Profile

## Config

```yml
# config/packages/sulu_community.yaml

sulu_community:
    webspaces:
        <webspace_key>: # Replace <webspace_key> with the key of your webspace
            # Profile
            profile:
                email:
                    subject: Profile Updated
                    admin_template: ~
                    user_template: community/profile-email.html.twig
                redirect_to: ~
                template: community/profile-form.html.twig
                type: App\Form\ProfileType
```

## email

It is possible that an email is also sent when a profile is changed.

**Example Template**:

```twig
{# community/profile-email.html.twig #}

{% extends 'base-email.html.twig' %}

{% block content %}
    Your profile was updated.
{% endblock %}
```

## template

The profile edit template.

```twig
{# community/profile-form.html.twig #}

{% extends 'base.html.twig' %}

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
//  src/Form/ProfileType.php

namespace App\Form;

use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextAreaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('plainPassword', PasswordType::class, [
            'mapped' => false,
            'required' => false,
        ]);

        $builder->add('formOfAddress', ChoiceType::class, [
            'property_path' => 'contact.formOfAddress',
            'choices' => [
                'sulu_contact.male_form_of_address' => 0,
                'sulu_contact.female_form_of_address' => 1,
            ],
            'translation_domain' => 'admin',
            'expanded' => true,
        ]);

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
            'mapped' => false,
            'property_path' => 'contact.avatar',
            'required' => false,
        ]);

        $builder->add('street', TextType::class, [
            'property_path' => 'contact.mainAddress.avatar',
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

        $builder->add('countryCode', CountryType::class, [
            'property_path' => 'contact.mainAddress.countryCode',
        ]);
        
        $builder->add('note', TextAreaType::class, [
            'property_path' => 'contact.note',
            'required' => false,
        ]);
        
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
                'validation_groups' => ['profile'],
            ]
        );
    }
}
```
