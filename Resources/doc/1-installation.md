# Installation

## Download and install 

Run the following command to install:

```bash
composer require sulu/community-bundle --no-scripts
```

## Enable Bundle

Enable the required bundles in the `config/bundles.php` of your project:

```diff
+    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
+    Sulu\Bundle\CommunityBundle\SuluCommunityBundle::class => ['all' => true],
-    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true, 'admin' => true], 
```

To avoid the:

> Trying to register two bundles with the same name "SecurityBundle"

## Register Routes

Register the website routes:

```yml
# config/routes/sulu_community_website.yaml

sulu_community:
    type: portal
    resource: "@SuluCommunityBundle/Resources/config/routing_website.yaml"
```

Register the admin routes:

```yml
# config/routes/sulu_community_admin.yaml

sulu_community_api:
    type: rest
    resource: "@SuluCommunityBundle/Resources/config/routing_api.yaml"
    prefix: /admin/api
```

## Configure security

The security needs now be configure in the `security_website.yaml`:

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
        sulu:
            id: test_user_provider

    firewalls:
        <webspace_key>: # Replace <webspace_key> with the key of your webspace
            http_basic: ~

sulu_test:
    enable_test_user_provider: true
```

## Clear cache

That the new config is loaded the caches need to be cleared:

```bash
php bin/adminconsole cache:clear
php bin/websiteconsole cache:clear
```

## Create database tables

Execute the following command to get the sqls to update your database.

```bash
php bin/adminconsole doctrine:schema:update --dump-sql
```

You can use `--force` to run the sqls but be carefully which other sql statements are maybe executed.

It's recommended to use [DoctrineMigrationsBundle](https://symfony.com/doc/current/bundles/DoctrineMigrationsBundle/index.html)
for this kind of database migrations.

## Install assets

Execute the following command to install the community bundle assets:

```bash
php bin/adminconsole assets:install --symlink --relative
```

## The next required step is to [Setup your Webspace](2-setup-webspace.md)
