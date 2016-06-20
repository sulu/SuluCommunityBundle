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

## Embedded Login

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

