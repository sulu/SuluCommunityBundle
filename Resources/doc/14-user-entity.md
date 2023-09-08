# User entity

If you want to extend the user, that is used by the CommunityBundle, follow this guide.

## Config

Config for admin security:

```yml
# config/packages/admin_security.yaml

sulu_security:
    objects:
        user:
            model: <your-entity>
```

Config for website security:

```
# config/packages/website_security.yaml

sulu_security:
    objects:
        user:
            model: <your-entity>
```

**Replace `<your-entity>` with the the namespace plus name of your user entity (i.e. `App\Entity\User`).**

## User entity

In your own entity (i.e. `App\Entity\User`), which is supposed to act as your user entity throughout Sulu and the
CommunityBundle, the following is important:

You must override the default entity and therefore extend the `Sulu\Bundle\SecurityBundle\Entity\User` and make sure
to set the annotations accordingly: `@ORM\Table(name="se_users")`.

Follow the official Sulu docs for this:
[Sulu docs - Extend Entities](https://docs.sulu.io/en/2.5/cookbook/extend-entities.html#create-a-entity)

**Note: There are two ways to handle the configuration. The one mentioned above, which uses the default files
(`admin_security.yaml` and `website_security.yaml`) and alternatively, it is possible to use a seperate file
`config/packages/sulu_security.yaml` like in the
[Sulu docs - Extend Entities](https://docs.sulu.io/en/2.5/cookbook/extend-entities.html#configuration).
If the `sulu_security.yaml` is used, it overrides the configured parts of the configuration in `admin_security.yaml`
and `website_security.yaml`.**

## Finalizing

After making the changes to the configuration and creating the new entity, execute the following commands to make sure
the changes take effect.

1. Update the database schema:

```
$ php bin/adminconsole doctrine:schema:update --dump-sql
```

If the output of the command looks alright, execute the same command, but with `--force` instead of `--dump-sql`.

2. Clear cache:

```
$ php bin/adminconsole cache:clear
```
