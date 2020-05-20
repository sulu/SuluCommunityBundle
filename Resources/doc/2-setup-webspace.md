# Setup Webspace

## Enable Security System

Add a security system to your webspace:

```xml
<!-- config/webspaces/<your_webspace>.xml -->

<security>
    <system>Website</system>
</security>
```

## Activate Community Features

Enable community features for your webspace:

```yml
# config/packages/sulu_community.yaml

sulu_community:
    webspaces:
        <webspace_key>: # Replace <webspace_key> with the key of your webspace
            from: "%env(SULU_ADMIN_EMAIL)%"
```

## Enable Security

```yml 
# config/packages/security_website.yml

security:
    encoders:
        Sulu\Bundle\SecurityBundle\Entity\User: bcrypt

    providers:
        sulu:
            id: sulu_security.user_provider

    access_control:
        # needed when firewall on ^/ is not anonymous
        # - { path: '/login', roles: IS_AUTHENTICATED_ANONYMOUSLY }
        # - { path: '/registration', roles: IS_AUTHENTICATED_ANONYMOUSLY }
        # - { path: '/password-reset', roles: IS_AUTHENTICATED_ANONYMOUSLY }
        # - { path: '/password-forget', roles: IS_AUTHENTICATED_ANONYMOUSLY }
        # - { path: '/_fragment', roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: '/profile', roles: ROLE_USER }
        - { path: '/completion', roles: ROLE_USER }

    firewalls:
        <webspace_key>: # Replace <webspace_key> with the key of your webspace
            pattern: ^/
            anonymous: lazy
            form_login:
                login_path: sulu_community.login
                check_path: sulu_community.login
            logout:
                path: sulu_community.logout
                target: /
            remember_me:
                secret:   "%kernel.secret%"
                lifetime: 604800 # 1 week in seconds
                path:     /

sulu_security:
    checker:
        enabled: true
```

For functional tests you need to activate the security in the website test configuration:

```yaml
# config/packages/test/security_website.yml

security:
    providers:
        testprovider:
            id: test_user_provider

    firewalls:
        <webspace_key>: # Replace <webspace_key> with the key of your webspace
             http_basic: ~
```

## Create Role

Create user roles with the following command:

```bash
php bin/console sulu:community:init
```

