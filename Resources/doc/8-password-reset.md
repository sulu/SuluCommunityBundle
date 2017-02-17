# Password Reset

## Config

```yml
# app/config/config.yml

sulu_community:
    webspaces:
        <webspace_key>:
            password_reset:
                auto_login: true
                email:
                    subject: Password Reset
                    admin_template: ~
                    user_template: AppBundle:templates:community/Password/reset-email.html.twig
                redirect_to: ?send=true
                template: AppBundle:templates:community/Password/reset-form.html.twig
                type: AppBundle\Form\Type\PasswordResetType
```

## email

After the user submitted the password reset form he will receive an email.

**Example Template**:

```twig
{# AppBundle:templates:community/Password/reset-email.html.twig #}

{% extends "SuluCommunityBundle::master-email.html.twig" %}

{% block content %}
    Your password was changed.
{% endblock %}
```

## template

The password reset template.

**Example Template**:

```twig
{# AppBundle:templates:community/Password/reset-form.html.twig #}

{% extends "AppBundle::master.html.twig" %}

{% block content %}
    <h1>Password reset</h1>

    {% if app.request.get('send') == 'true' %}
        <p>
            Password successfully changed.
        </p>
    {% else %}
        {% if form %}
            {{ form(form) }}
        {% else %}
            The received token is invalid.
        {% endif %}
    {% endif %}
{% endblock %}
```

## type

Set a new type to overwrite the existing form.

**Example Class**:

```php
//  src/AppBundle/Form/Type/PasswordResetType.php

namespace AppBundle\Form\Type;

use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Create the password reset form type.
 */
class PasswordResetType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'plainPassword',
            PasswordType::class,
            [
                'mapped' => false,
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
                'validation_groups' => ['password_reset'],
            ]
        );
    }
}
```
