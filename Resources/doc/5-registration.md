# Registration

## Config

```yml
# app/config/config.yml
sulu_community:
    webspaces:
        <webspace_key>:
            registration:
                activate_user: false
                auto_login: true # only available when activate_user is true
                email:
                    subject: Registration
                    user_template: AppBundle:templates:community/Registration/registration-email.html.twig
                    admin_template: ~
                template: AppBundle:templates:community/Registration/registration-form.html.twig
                type: AppBundle\Form\Type\RegistrationType
```

## email

The registration email contains the confirmation link.

**Example Template**:

```twig
{# AppBundle:templates:community/Registration/registration-email.html.twig #}

{% extends "AppBundle::master-email.html.twig" %}

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
{# AppBundle:templates:community/Registration/registration-form.html.twig #}

{% extends "AppBundle:website:master.html.twig" %}

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
// src/Appbundle/Type/RegistrationType.php

namespace AppBundle\Form\Type;

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
        $builder->add(
            'plainPassword',
            PasswordType::class,
            [
                'mapped' => false,
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
```

```php
// src/Appbundle/Type/RegistrationContactType.php

namespace AppBundle\Form\Type;

use Sulu\Bundle\ContactBundle\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationContactType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('firstName', TextType::class);
        $builder->add('lastLame', TextType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
            'validation_groups' => ['registration'],
        ]);
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
