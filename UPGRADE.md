# Upgrade

## 2.0.0 (unreleased)

### ListRepresentation relation name changed

The name of the relation inside of the `_embedded` field has been changed from `items` to `blacklist_items`.

### BlacklistUser and BlacklistItem repository service identification changed

 - BlacklistUserRepository has been changed from `sulu_community.blacklisting.user_repository` to `sulu.repository.blacklist_user`.

 - BlacklistItemRepository has been changed from `sulu_community.blacklisting.item_repository` to `sulu.repository.blacklist_item`.

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

