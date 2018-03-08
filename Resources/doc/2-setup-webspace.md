# Setup Webspace

## Enable Security System

Add a security system to your webspace:

```xml
<!-- app/Resources/webspaces/<your_webspace>.xml -->

<security>
    <system>Website</system>
</security>
```

## Activate Community Features

Enable community features for your webspace:

```yml
# app/config/config.yml

sulu_community:
    webspaces:
        <webspace_key>:
            from:
                name: "Website"
                email: "%sulu_admin.email%"
```

## Enable Security

```yml 
# app/config/website/security.yml

security:
    session_fixation_strategy: none

    access_decision_manager:
        strategy: affirmative

    encoders:
        Sulu\Bundle\SecurityBundle\Entity\User:
            algorithm: sha512
            iterations: 5000
            encode_as_base64: false

    providers:
        sulu:
            id: sulu_security.user_provider

    access_control:
       # - { path: /login, roles: IS_AUTHENTICATED_ANONYMOUSLY } # needed when firewall on ^/ is used
       # - { path: /_fragment, roles: IS_AUTHENTICATED_ANONYMOUSLY } # needed when firewall on ^/ is used
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
                target: /
            remember_me:
                secret:   "%secret%"
                lifetime: 604800 # 1 week in seconds
                path:     /

sulu_security:
    checker:
        enabled: true
```

## Create Role

Create user roles with the following command:

```bash
php bin/console sulu:community:init
```

