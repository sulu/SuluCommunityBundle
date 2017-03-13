# Upgrade

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

