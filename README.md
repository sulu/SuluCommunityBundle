# SuluCommunityBundle

## Installation

```
composer require sulu/community-bundle
```

**Add to `app/AbstractKernel`**

```php
new Sulu\Bundle\CommunityBundle\SuluCommunityBundle(),
```

## Configuration

**`app/config/config.yml`**:

```yml
sulu_community:
    webspaces:
        example:
            from: %from_email%
```

**`app/config/routing.yml`**:

```yml
sulu_community:
    type: portal
    resource: "@SuluCommunityBundle/Resources/config/routing.xml"
```
