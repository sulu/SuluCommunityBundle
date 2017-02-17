# Installation

## Download and install 

Run the following command to install:

```bash
composer require sulu/community-bundle
```

## Enable Bundle

Enable the required bundles in the kernel:

```bash
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

To avoid the trying to register two bundles with the same name error remove
the SecurityBundle from `app/AdminKernel.php`.


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
