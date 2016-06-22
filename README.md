# SuluCommunityBundle

## Installation

```
composer require sulu/community-bundle
```

**Add to `app/AbstractKernel.php`**

```php
new Symfony\Bundle\SecurityBundle\SecurityBundle(),
```

**Add to `app/WebsiteKernel.php`**

```php
new Sulu\Bundle\CommunityBundle\SuluCommunityBundle(),
```

## Configuration

### Routes

**`app/config/website/routing.yml`**:

```yml
sulu_community:
    type: portal
    resource: "@SuluCommunityBundle/Resources/config/routing_admin.xml"
```

**`app/config/admin/routing.yml`**:

```yml
sulu_community_api:
    type: rest
    resource: "@SuluCommunityBundle/Resources/config/routing_api.xml"
    prefix: /admin/api
```

### Webspace

**`app/Resources/webspaces/<your_webspace>.xml`**:

```xml
    <security>
        <system>Website</system>
    </security>
```

### Community

**`app/config/config.yml`**:

```yml
sulu_community:
    webspaces:
        example:
            from: %from_email%
```

### Security

**`app/config/website/security.yml`**:

```yml
security:
    session_fixation_strategy: none

    access_decision_manager:
        strategy: affirmative

    acl:
        connection: default

    encoders:
        Sulu\Bundle\SecurityBundle\Entity\User:
            algorithm: sha512
            iterations: 5000
            encode_as_base64: false

    providers:
        sulu:
            id: sulu_security.user_provider

    access_control:
       - { path: /profile, roles: ROLE_USER }
       - { path: /completion, roles: ROLE_USER }

    firewalls:
        <webspace_key>:
            pattern: ^/
            anonymous: ~
            form_login:
                login_path: sulu_community.login
                check_path: sulu_community.login
            logout:
                path: sulu_community.logout
                target: sulu_community.login
            remember_me:
                secret:   '%secret%'
                lifetime: 604800 # 1 week in seconds
                path:     /

sulu_security:
    checker:
        enabled: true
```

## Setup

```bash
app/console sulu:community:init
```

## Additional Features

### Embedded Login

Activate ESI to use the `login-embed.html.twig`.

**`app/config/config.yml`**:

```yml
framework:
    esi:             { enabled: true }
```

**Insert following in your twig file**:

```twig
{{ render_esi(controller('SuluCommunityBundle:Login:embed')) }}
```

### Completion Form

You can add an additional completion form to complete the user registration after confirmation.

**Create Service**

```php
<?php

namespace AppBundle\Validator;

use Sulu\Bundle\CommunityBundle\Validator\User\CompletionInterface;
use Sulu\Bundle\SecurityBundle\Entity\User;

/**
 * Validates the user before he can access pages.
 */
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

**Create Form**

```php
<?php

namespace AppBundle\Form\Type\CompletionType;

use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Create the registration form type.
 */
class CompletionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Create your form

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

**Configuration**

```yml
# Community Configuration
sulu_community:
    webspaces:
        <webspace_key>:
            completion:
                form: AppBundle\Form\Type\CompletionType
                service: app.completion_validator
```
