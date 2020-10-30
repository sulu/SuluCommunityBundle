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

For all configuration options have a look at [Customization docs](3-customization.md).

## Create Role

Create user roles with the following command:

```bash
php bin/console sulu:community:init
```

