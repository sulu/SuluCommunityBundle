# Maintenance

You can temporarily change the community bundle into a maintenace mode 
this will disable all form renderings.

## Config

```yml
# config/packages/sulu_community.yaml

sulu_community:
    webspaces:
        <webspace_key>: # Replace <webspace_key> with the key of your webspace
            maintenance:
                enabled: false
                template: community/maintenance.html.twig
```

The configured maintenance template will be rendered instead of configured 
login, registration, completion, password forget, password reset, ... template.

## Disable login embed

In the `login-embed.html.twig` a variable `maintenanceMode` is available so you can render
the login embed another text instead.

```twig
{# community/login-embed.html.twig #}

{% if maintenanceMode %}
    Maintenace mode active
{% else %}
    {# ... #}
{% endif %}
```
