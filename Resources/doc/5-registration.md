# Registration

## Config

```yml
# config/packages/sulu_community.yaml
sulu_community:
    webspaces:
        <webspace_key>: # Replace <webspace_key> with the key of your webspace
            registration:
                activate_user: false
                auto_login: true # only available when activate_user is true
                email:
                    subject: Registration
                    user_template: community/registration-email.html.twig
                    admin_template: ~
                template: community/registration-form.html.twig
                type: App\Form\RegistrationType
```

## email

The registration email contains the confirmation link.

**Example Template**:

```twig
{# community/registration-email.html.twig #}

{% extends 'base-email.html.twig' %}

{% block content %}
    {% set url = url('sulu_community.confirmation', { token: user.confirmationKey }) %}

    <a href="{{ url }}">
        {{ url }}
    </a>
{% endblock %}
```

## template

The template contains the form and the success message.

**Example Template**:

```twig
{# community/registration-form.html.twig #}

{% extends 'base.html.twig' %}

{% block content %}
    <h1>Registration</h1>

    {% if app.request.get('send') == 'true' %}
        <p>
            To complete the registration click on the link in the received email.
        </p>
    {% else %}
        {{ form(form) }}
    {% endif %}
{% endblock %}
```

## type

You can create your own form by setting your own RegistrationType.

**Example Class**:

```php
// src/Form/RegistrationType.php

namespace App\Form;

use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', TextType::class);
    
        $builder->add('email', EmailType::class);

        $builder->add('plainPassword', PasswordType::class, [
            'mapped' => false,
        ]);

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
                'validation_groups' => ['registration'],
                'empty_data' => function(FormInterface $form) {
                    $user = new User();
                    $user->setContact(new Contact());

                    return $user;
                }
            ]
        );
    }
}
```
