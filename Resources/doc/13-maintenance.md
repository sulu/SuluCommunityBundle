# Maintenance

You can temporarily change the community bundle into a maintenace mode 
this will disable all form renderings.

## Config

```yml
# app/config/config.yml

sulu_community:
    webspaces:
        <webspace_key>:
            maintenance:
                enabled: false
                template: AppBundle:template:community/Maintenance/maintenance.html.twig
```

The configured maintenance template will be rendered instead of configured 
login, registration, completion, password forget, password reset, ... template.

## Disable login embed

In the `login-embed.html.twig` a variable `maintenanceMode` is available so you can render
in the login embed another text instead.

```twig
{% if maintenanceMode %}
    Maintenace mode active
{% else %}
    {# ... #}
{% endif %}
```
