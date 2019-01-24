# Confirmation

## Config

```yml
# config/packages/sulu_community.yaml

sulu_community:
    webspaces:
        <webspace_key>: # Replace <webspace_key> with the key of your webspace
            confirmation: 
                activate_user: true
                auto_login: true
                email:
                    subject: Confirmation
                    user_template: ~
                    admin_template: ~
                redirect_to: ~
                template: community/confirmation-message.html.twig
```

## email

After the user successfully confirmed his email address you can send him a welcome message or inform the admin
that a user has registered.

**Example Template**:

```twig
{# community/confirmation-email.html.twig #}

{% extends 'base-email.html.twig' %}

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
{# community/confirmation-message.html.twig #}

{% extends 'base.html.twig' %}

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
