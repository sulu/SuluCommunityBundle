# Confirmation

## Config

```yml
# app/config/config.yml

sulu_community:
    webspaces:
        <webspace_key>:
            confirmation: 
                activate_user: true
                auto_login: true
                email:
                    subject: Confirmation
                    user_template: ~
                    admin_template: ~
                redirect_to: ~
                template: AppBundle:templates:community/Confirmation/confirmation-message.html.twig
```

## email

After the user successfully confirmed his email address you can send him a welcome message or inform the admin
that a user has registered.

**Example Template**:

```twig
{# AppBundle:templates:community/Confirmation/confirmation-email.html.twig #}

{% extends "AppBundle::master-email.html.twig" %}

{% block content %}
    <h1>Welcome</h1>

    <a href="{{ url('sulu_community.login') }}">
        Login
    </a>
{% endblock %}
```

## template

When the user clicks on the confirmation link the following template will be rendered:

**Example Template**:

```twig
{# AppBundle:templates:community/Confirmation/confirmation-message.html.twig #}

{% extends "SuluCommunityBundle::master.html.twig" %}

{% block content %}
    <h1>Confirmation</h1>

    {% if success %}
        <p>
            You successfully activated your user.
        </p>
    {% else %}
        <p>
            Invalid confirmation token.
        </p>
    {% endif %}
{% endblock %}
```
