# Email Confirmation

## Config

```yml
# app/config/config.yml

sulu_community:
    webspaces:
        <webspace_key>:
            # Email Confirmation
            email_confirmation:
                email:
                    subject: Email Changed
                    user_template: AppBundle:templates:community/EmailConfirmation/email-confirmation-email.html.twig
                    admin_template: ~
                template: AppBundle:templates:community/EmailConfirmation/email-confirmation-success.html.twig
```

## email

The email with the url to confirm the new email address.

**Example Template**:

```twig
{# AppBundle:templates:community/EmailConfirmation/email-confirmation-email.html.twig #}

{% extends "AppBundle::master-email.html.twig" %}

{% block content %}
    {% set confirmUrl = url('sulu_community.email_confirmation', { token: token }) %}
    <a href="{{ confirmUrl }}">{{ confirmUrl }}</a>
{% endblock %}
```

## template

Template which is rendered after the email address has been successfully confirmed.

**Example Template**:

```twig
{# AppBundle:templates:community/EmailConfirmation/email-confirmation-success.html.twig #}
{% extends "AppBundle:website:master.html.twig" %}

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
