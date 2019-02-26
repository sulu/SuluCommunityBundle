# Email Confirmation

## Config

```yml
# config/packages/sulu_community.yaml

sulu_community:
    webspaces:
        <webspace_key>: # Replace <webspace_key> with the key of your webspace
            # Email Confirmation
            email_confirmation:
                email:
                    subject: Email Changed
                    user_template: community/email-confirmation-email.html.twig
                    admin_template: ~
                template: community/email-confirmation-success.html.twig
```

## email

The email with the url to confirm the new email address.

**Example Template**:

```twig
{# community/email-confirmation-email.html.twig #}

{% extends 'base-email.html.twig' %}

{% block content %}
    {% set confirmUrl = url('sulu_community.email_confirmation', { token: token }) %}
    <a href="{{ confirmUrl }}">{{ confirmUrl }}</a>
{% endblock %}
```

## template

Template which is rendered after the email address has been successfully confirmed.

**Example Template**:

```twig
{# community/email-confirmation-success.html.twig #}

{% extends 'base.html.twig' %}

{% block content %}
    <h1>Profile</h1>

    {% if success %}
        <p class="success">
            Email was successfully changed.
        </p>
    {% else %}
        <p class="fail">
            Invalid token.
        </p>
    {% endif %}
{% endblock %}
```
