# Upgrade

## 3.0.0

### Sulu 3.0 compatibility

This version adds support for Sulu 3.0. The bundle now requires:

- PHP 8.2 or higher
- Sulu 3.0 or higher
- Symfony 6.4 or 7.1 or higher
- Doctrine ORM 2.17.3 or 3.3 or higher

### Removing the rest routing

The Rest Routing bundle is no longer required. Remove `type: rest` from your
admin route import and rename `.yml` to `.yaml`:

```diff
# config/routes/sulu_community_admin.yaml
 sulu_community_api:
-    type: rest
     resource: "@SuluCommunityBundle/Resources/config/routing_api.yaml"
     prefix: /admin/api
```

### REST list response changed

The list endpoint of the registration rule items API now returns a
`PaginatedRepresentation` instead of the removed `ListRepresentation`. The
`_embedded.registration_rule_items` array, `page`, `limit` and `total` fields
are unchanged. The `_links` section no longer exposes the request route and
query parameters.

### MailFactory no longer supports SwiftMailer

`Sulu\Bundle\CommunityBundle\Mail\MailFactory::__construct()` now requires a
`Symfony\Component\Mailer\MailerInterface` and dropped the SwiftMailer code
path. Remove SwiftMailer from your project to use `symfony/mailer` instead.

### Strict-typed form fields

Some Sulu entity setters now require a non-null `string`
(`User::setUsername`, `Contact::setFirstName`, `Contact::setLastName`). If
you extend `RegistrationType`, `ProfileType`, `CompletionType` or
`CompletionContactType`, add `'empty_data' => ''` to any field that maps to
one of these setters.

### LastLoginListener constructor changed

`LastLoginListener::__construct()` now requires
`Doctrine\ORM\EntityManagerInterface` (was the concrete `EntityManager`).


## 2.0.0 (unreleased)

### Dropped support for older PHP and dependency versions

Minimum requirements have been raised to align with Sulu 2.6. Consumers on PHP 7
or older Sulu versions need to upgrade their environment before pulling in this
release.

```
php:                      ^7.2 || ^8.0          -> ^8.2 || ^8.3 || ^8.4 || ^8.5
doctrine/orm:             ^2.5.3                -> ^2.13
doctrine/persistence:     ^1.3 || ^2.0 || ^3.0  -> ^2.0 || ^3.0
doctrine/phpcr-bundle:    ^2 || ^3.0            -> ^2.2 || ^3.0
jms/serializer-bundle:    ^3.3 || ^4.0 || ^5.4  -> ^4.0 || ^5.4
massive/build-bundle:     ^0.3 || ^0.4 || ^0.5  -> ^0.5.7 || ^0.6.0
sulu/sulu:                ^2.4.0 || ^2.6@dev    -> ^2.6@dev
```

### Blacklist replaced with registration rules

For clearer naming all occurrences of "blacklist" have been replaced with "registration rule".

This also includes the renames of the following entities and tables in the database:

* BlacklistItem -> RegistrationRuleItem
* BlacklistItemRepository -> RegistrationRuleItemRepository
* BlacklistUser -> RegistrationRuleUser
* BlacklistUserRepository -> RegistrationRuleUserRepository
* BlacklistItemController -> RegistrationRuleItemController
* BlacklistConfirmationController -> RegistrationRuleConfirmationController
* BlacklistListener -> RegistrationRuleListener
* BlacklistItemManager -> RegistrationRuleItemManager
* BlacklistItemManagerInterface -> RegistrationRuleItemManagerInterface

Public constants on `CommunityAdmin`:

```
CommunityAdmin::BLACKLIST_ITEM_SECURITY_CONTEXT  -> CommunityAdmin::REGISTRATION_RULE_ITEM_SECURITY_CONTEXT
CommunityAdmin::BLACKLIST_ITEM_LIST_VIEW         -> CommunityAdmin::REGISTRATION_RULE_ITEM_LIST_VIEW
CommunityAdmin::BLACKLIST_ITEM_ADD_FORM_VIEW     -> CommunityAdmin::REGISTRATION_RULE_ITEM_ADD_FORM_VIEW
CommunityAdmin::BLACKLIST_ITEM_EDIT_FORM_VIEW    -> CommunityAdmin::REGISTRATION_RULE_ITEM_EDIT_FORM_VIEW
```

Public constants on `Configuration` (passed to `CommunityManager::sendEmails()`
and consumed by custom listeners):

```
Configuration::TYPE_BLACKLIST           -> Configuration::TYPE_REGISTRATION_RULE
Configuration::TYPE_BLACKLIST_CONFIRMED -> Configuration::TYPE_REGISTRATION_RULE_CONFIRMED
Configuration::TYPE_BLACKLIST_DENIED    -> Configuration::TYPE_REGISTRATION_RULE_DENIED
```

```sql
RENAME TABLE
    com_blacklist_item TO com_registration_rule_item,
    com_blacklist_user TO com_registration_rule_user;
```

Routes:
```
/admin/api/blacklist-items/ -> /admin/api/registration-rule-items/
sulu_community.get_blacklist-items -> sulu_community.get_registration-rule-items
sulu_community.get_blacklist-item -> sulu_community.get_registration-rule-item
```

Admin permissions:
```
sulu.community.blacklist_items -> sulu.community.registration_rule_items
```

Webspace configuration:
```
blacklisted -> registration_rule
blacklist_denied -> registration_rule_denied
blacklist_confirmed -> registration_rule_confirmed
```

Example:

```yaml
# Before
sulu_community:
    webspaces:
        <webspace_key>:
            blacklisted:
                email:
                    admin_template: community/blacklist-email.html.twig
            blacklist_denied:
                template: community/blacklist-denied.html.twig
            blacklist_confirmed:
                template: community/blacklist-confirmed.html.twig

# After
sulu_community:
    webspaces:
        <webspace_key>:
            registration_rule:
                email:
                    admin_template: community/registration-rule-email.html.twig
            registration_rule_denied:
                template: community/registration-rule-denied.html.twig
            registration_rule_confirmed:
                template: community/registration-rule-confirmed.html.twig
```

Object configuration:
```
sulu_community.objects.blacklist_item -> sulu_community.objects.registration_rule_item
sulu_community.objects.blacklist_user -> sulu_community.objects.registration_rule_user
```

Example:

```yaml
# Before
sulu_community:
    objects:
        blacklist_item:
            model: App\Entity\BlacklistItem
            repository: App\Repository\BlacklistItemRepository
        blacklist_user:
            model: App\Entity\BlacklistUser
            repository: App\Repository\BlacklistUserRepository

# After
sulu_community:
    objects:
        registration_rule_item:
            model: App\Entity\RegistrationRuleItem
            repository: App\Repository\RegistrationRuleItemRepository
        registration_rule_user:
            model: App\Entity\RegistrationRuleUser
            repository: App\Repository\RegistrationRuleUserRepository
```

Admin metadata keys:
```
blacklist_items -> registration_rule_items
blacklist_item_details -> registration_rule_item_details
```

Template names:
```
@SuluCommunity/blacklist-email.html.twig -> @SuluCommunity/registration-rule-email.html.twig
@SuluCommunity/blacklist-denied.html.twig -> @SuluCommunity/registration-rule-denied.html.twig
@SuluCommunity/blacklist-confirmed.html.twig -> @SuluCommunity/registration-rule-confirmed.html.twig
```

Admin URL paths (impacts bookmarks, deep-links and any custom view overrides):

```
/blacklist      -> /registration-rule
/blacklist/add  -> /registration-rule/add
/blacklist/:id  -> /registration-rule/:id
```

Admin navigation item key (used in translations and when adding sub-items):

```
sulu_community.blacklist -> sulu_community.registration_rule
```

Edit-view title binding changed from the (removed) `name` property to `pattern`,
since registration rule items are identified by their pattern:

```
setTitleProperty('name') -> setTitleProperty('pattern')
```

### ListRepresentation relation name changed

The name of the relation inside of the `_embedded` field has been changed from `items` to `registration_rule_items`.

### RegistrationRule service identification changed

 - RegistrationRuleListener has been changed from `sulu_community.black_listener` to `sulu_community.registration_rule_listener`.

 - RegistrationRuleItemController has been changed from `sulu_community.controller.blacklist_item` to `sulu_community.controller.registration_rule_item`.

 - RegistrationRuleItemManager has been changed from `sulu_community.blacklisting.item_manager` to `sulu_community.registration_rule.item_manager`.

 - RegistrationRuleUserRepository has been changed from `sulu_community.blacklisting.user_repository` to `sulu_community.registration_rule.user_repository`.

 - RegistrationRuleItemRepository has been changed from `sulu_community.blacklisting.item_repository` to `sulu_community.registration_rule.item_repository`.

 - The persistence repository aliases have been changed from `sulu.repository.blacklist_user` and `sulu.repository.blacklist_item` to `sulu.repository.registration_rule_user` and `sulu.repository.registration_rule_item`.

 - The model parameters have been changed from `sulu.model.blacklist_user.class` and `sulu.model.blacklist_item.class` to `sulu.model.registration_rule_user.class` and `sulu.model.registration_rule_item.class`.

### Typehints added to the codebase

Everywhere were possible typehints were added to the classes and interfaces.
If you extend or implement something you need also add the typehints there.

### Events changed

The general `CommunityEvent` class was removed and replaced with:

 - `UserRegisteredEvent`
 - `UserCompletedEvent`
 - `UserPasswordForgotEvent`
 - `UserPasswordResetedEvent`
 - `UserRegisteredEvent`
 - `UserProfileSavedEvent`

which all extend from the new `AbstractCommunityEvent`.

### Address entity changed

If you implemented a custom ProfileType you need to change the country field to countryCode:

```php
// Before
$builder->add('country', EntityType::class, [
    'property_path' => 'contact.mainAddress.countryCode',
    'class' => Country::class,
    'choice_label' => function (Country $country) {
        return Intl::getRegionBundle()->getCountryName($country->getCode());
    },
]);

// After
use Symfony\Component\Form\Extension\Core\Type\CountryType;

$builder->add('countryCode', CountryType::class, [
	'property_path' => 'contact.mainAddress.countryCode',
]);
```

For database migration see [Sulu 2.0 Upgrade](https://github.com/sulu/sulu/blob/2.0.0/UPGRADE.md#country-table-co_countries-was-replace-with-symfony-intl-regionbundle).

### Routing files changed to yaml

```yaml
# Before sulu_community_website.yaml
sulu_community:
    type: portal
    resource: "@SuluCommunityBundle/Resources/config/routing_website.xml"

# Before sulu_community_admin.yaml
sulu_community_api:
    type: rest
    prefix:  /admin/api
    resource: "@SuluCommunityBundle/Resources/config/routing_api.xml"

# After sulu_community_website.yaml
sulu_community:
    type: portal
    resource: "@SuluCommunityBundle/Resources/config/routing_website.yaml"

# After sulu_community_admin.yaml
sulu_community_api:
    type: rest
    prefix:  /admin/api
    resource: "@SuluCommunityBundle/Resources/config/routing_api.yaml"
```

### BaseUser class references replaced with User class

The `BaseUser` class from sulu is not longered used and all function
where replaced using the `User` entity class directly.

### Child Form Types removed

The following form types are removed:

 - `ProfileAddressType`
 - `ProfileContactAddressType`
 - `ProfileContactType`
 - `ProfileNoteType`
 - `RegistrationContactType`

the fields are now mapped using `property_path` attribute.

### UTF8MB4 compatibility

To support utf8mb4 we needed to shorten the length of indexed fields
Run the following SQLs to upgrade your DB:

```sql
ALTER TABLE com_email_token CHANGE token token VARCHAR(191) NOT NULL;
ALTER TABLE com_blacklist_user CHANGE token token VARCHAR(191) DEFAULT NULL;
ALTER TABLE com_blacklist_item CHANGE regexpPattern regexpPattern VARCHAR(191) NOT NULL;
ALTER TABLE com_blacklist_item CHANGE pattern pattern VARCHAR(191) NOT NULL;
```

## 0.3.0

### Parameter `sulu_community.config` was removed

The whole config as parameter is not longer available the webspaces config
you can get over the `sulu_community.webspaces_config` parameter.

## 0.2.0

### Avatar title will use username instead of fullname

If you want this also for old uploaded profile images the username as
media title run the following sql statement. Attention this will 
overwrite all manual changed media titles of contact images.

```sql
UPDATE me_file_version_meta AS fvm
INNER JOIN me_file_versions AS fv ON 
    fvm.idFileVersions = fv.id
INNER JOIN me_files AS f ON
    fv.idFiles = f.id
INNER JOIN me_media AS m ON
    f.idMedia = m.id
INNER JOIN me_collections AS co ON
    m.idCollections = co.id
INNER JOIN co_contacts AS c ON
    m.id = c.avatar
INNER JOIN se_users AS u ON
    u.idContacts = c.id
SET fvm.title = u.username
WHERE co.collection_key = 'sulu_contact.contact' AND u.id IS NOT NULL;
```
