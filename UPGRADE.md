# Upgrade

## 2.0.0 (unreleased)

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

```sql
ALTER TABLE com_blacklist_item RENAME com_registration_rule_item;
ALTER TABLE com_blacklist_user RENAME com_registration_rule_user;
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
