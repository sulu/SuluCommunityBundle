# Completion

You can add an additional form to complete the user registration after confirmation.

## Config

```yml
# app/config/config.yml

sulu_community:
    webspaces:
        <webspace_key>:
            completion:
                email:
                    subject: Completion
                    user_template: ~
                    admin_template: ~
                redirect_to: /
                service: app.completion_validator
                template: AppBundle:templates:community/Completion/completion-form.html.twig
                type: AppBundle\Form\Type\CompletionType
```

## email

It is possible that an email is also sent after the completion.

```
{# AppBundle:templates:community/Completion/completion-email.html.twig #}

{% extends "AppBundle::master-email.html.twig" %}

{% block content %}
    You successfully completed your data.
{% endblock %}
```

## service

The service which validates the user data and checks if a completion form should be displayed.

```php
// src/AppBundle/Validator/CompletionValidator.php

namespace AppBundle\Validator;

use Sulu\Bundle\CommunityBundle\Validator\User\CompletionInterface;
use Sulu\Bundle\SecurityBundle\Entity\User;

class CompletionValidator implements CompletionInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate(User $user, $webspaceKey)
    {
        $contact = $user->getContact();

        if (!$contact
            || !$contact->getFirstName()
            || !$contact->getLastName()
            || !$user->getUsername()
            || !$user->getEmail()
        ) {
            return false;
        }

        return true;
    }
}
```

**Register Service**

```xml
<service id="app.completion_validator" class="AppBundle\Validator\CompletionValidator" />
```

## template

```twig
{# AppBundle:templates:community/Completion/completion-form.html.twig #}

{% extends "AppBundle:website:master.html.twig" %}

{% block content %}
    <h1>Registration</h1>

    {% if app.request.get('send') == 'true' %}
        <p>
            Registration completed.
        </p>
    {% else %}
        {{ form(form) }}
    {% endif %}
{% endblock %}

```

## type

```php
// src/AppBundle/Form/Type/CompletionType.php

namespace AppBundle\Form\Type;

use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            $options['contact_type'],
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
```

```php
// src/AppBundle/Form/Type/CompletionContactType.php

namespace AppBundle\Form\Type;

use Sulu\Bundle\ContactBundle\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompletionContactType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $contact = $builder->getData();

        if (!$contact->getFirstName()) {
            $builder->add('firstName', 'text');
        }

        if (!$contact->getLastName()) {
            $builder->add('lastName', 'text');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
            'validation_groups' => ['completion'],
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
