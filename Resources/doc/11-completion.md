# Completion

You can add an additional form to complete the user registration after confirmation.

## Config

```yml
# config/packages/sulu_community.yaml

sulu_community:
    webspaces:
        <webspace_key>: # Replace <webspace_key> with the key of your webspace
            completion:
                email:
                    subject: Completion
                    user_template: ~
                    admin_template: ~
                redirect_to: /
                service: App\Community\CompletionValidator
                template: community/completion-form.html.twig
                type: App\Form\CompletionType
```

## email

It is possible that an email is also sent after the completion.

```twig
{# community/completion-email.html.twig #}

{% extends 'base-email.html.twig' %}

{% block content %}
    You successfully completed your data.
{% endblock %}
```

## service

The service which validates the user data and checks if a completion form should be displayed.

```php
// src/Community/CompletionValidator.php

namespace App\Community;

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

When using autowiring you can use the FQCN (`AppBundle\Validator\CompletionValidator`)
as service configuration.

## template

```twig
{# community/completion-form.html.twig #}

{% extends 'base.html.twig' %}

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
// src/Form/CompletionType.php

namespace App\Form;

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
        
        if (!$user->getContact()->getFirstName()) {
            $builder->add('firstName', TextType::class, [
                'property_path' => 'contact.lastName',
            ]);
        }

        if (!$user->getContact()->getLastName()) {
            $builder->add('lastName', TextType::class, [
                'property_path' => 'contact.lastName',
            ]);
        }

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
                'validation_groups' => ['completion'],
            ]
        );
    }
}
```
