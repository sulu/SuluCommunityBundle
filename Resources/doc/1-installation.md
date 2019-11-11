# Installation

## Download and install 

Run the following command to install:

```bash
composer require sulu/community-bundle
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

## Create database tables

Execute the following command to get the sqls to update your database.

```bash
php bin/console doctrine:schema:update --dump-sql
``` 

You can use `--force` to run the sqls but be carefully which other sql statements are executed.

## Install assets

Execute the following command to install the community bundle assets:

```bash
php bin/adminconsole assets:install --symlink --relative
```
