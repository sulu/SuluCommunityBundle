# Password Reset

## Config

```yml
# config/packages/sulu_community.yaml

sulu_community:
    webspaces:
        <webspace_key>: # Replace <webspace_key> with the key of your webspace
            password_reset:
                auto_login: true
                email:
                    subject: Password Reset
                    admin_template: ~
                    user_template: community/password-reset-email.html.twig
                redirect_to: ?send=true
                template: community/password-reset-form.html.twig
                type: App\Form\PasswordResetType
```

## email

After the user submitted the password reset form he will receive an email.

**Example Template**:

```twig
{# community/password-reset-email.html.twig #}

{% extends "base.html.twig" %}

{% block content %}
    Your password was changed.
{% endblock %}
```

## template

The password reset template.

**Example Template**:

```twig
{# community/password-reset-form.html.twig #}

{% extends 'base.html.twig' %}

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
//  src/Form/PasswordResetType.php

namespace App\Form;

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
