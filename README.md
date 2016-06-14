# Sulu Community Bundle

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
    resource: "@SuluCommunityBundle/Resources/config/routing.xml"
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

## Setup

```bash
app/console sulu:community:init
```
