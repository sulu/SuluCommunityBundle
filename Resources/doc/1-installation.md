# Installation

## Download and install 

Run the following command to install:

```bash
composer require sulu/community-bundle
```

## Enable Bundle

Enable the required bundles in the kernel:

```php
<?php
// app/AbstractKernel.php

public function registerBundles()
{
    $bundles = [
        // ...
        new Symfony\Bundle\SecurityBundle\SecurityBundle(),
        new Sulu\Bundle\CommunityBundle\SuluCommunityBundle(),
    ];
}
```

To avoid the:

> Trying to register two bundles with the same name "SecurityBundle"

error. Remove the SecurityBundle from `app/AdminKernel.php`.

```diff
<?php
// app/AdminKernel.php

public function registerBundles()
{
     // ...
-    $bundles[] = new Symfony\Bundle\SecurityBundle\SecurityBundle(); // This line need to be removed!
```

## Register Routes

Register the website routes:

```yml
# app/config/website/routing.yml

sulu_community:
    type: portal
    resource: "@SuluCommunityBundle/Resources/config/routing_website.xml"
```

Register the admin routes:

```yml
#app/config/admin/routing.yml

sulu_community_api:
    type: rest
    resource: "@SuluCommunityBundle/Resources/config/routing_api.xml"
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

## Generate translations

Execute the following command to generate the new translations:

```bash
php bin/adminconsole sulu:translate:export
```
