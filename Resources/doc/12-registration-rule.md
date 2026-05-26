# Registration rules

## Config

```yml
# config/packages/sulu_community.yaml

sulu_community:
    webspaces:
        <webspace_key>: # Replace <webspace_key> with the key of your webspace
            registration_rule:
                email:
                    subject: Registration rule
                    admin_template: community/registration-rule-email.html.twig
                    user_template: ~
            registration_rule_denied:
                email:
                    subject: Denied
                    admin_template: ~
                    user_template: ~
                template: community/registration-rule-denied.html.twig
            registration_rule_confirmed:
                email:
                    subject: Registration
                    admin_template: ~
                    user_template: community/registration-email.html.twig
                template: community/registration-rule-confirmed.html.twig
```

## Backend Config

To enable the registration rule feature add the permissions for registration rules to your role.
For this go to `Settings -> Roles -> YourRole` in the sulu backend and add the permissions.

When you successfully enabled it email addresses can be set to `block` or on `request` under `Settings -> Registration rules`.
It is possible to use wildcards e.g. `*@test.com` to set the state for a whole domain.

## registration_rule.email

The admin of the page will receive the registration rule email when a user email address is set to `request`.

**Example Template**:

```twig
{# community/registration-rule-email.html.twig #}

{% extends 'base-email.html.twig' %}

{% block content %}
    <p>E-Mail: {{ user.email }}</p>

    {% set confirmUrl = url('sulu_community.user_confirm', { token: token }) %}
    <p>Confirm: <a href="{{ confirmUrl }}">{{ confirmUrl }}</a></p>

    {% set denyUrl = url('sulu_community.user_deny', { token: token }) %}
    <p>Deny: <a href="{{ denyUrl }}">{{ denyUrl }}</a></p>
{% endblock %}
```

## registration_rule_denied.template / registration_rule_confirmed.template

When the admin clicks on the link a template is rendered which can show specific content:

**Example Template**:

```twig
{# community/registration-rule-denied.html.twig / community/registration-rule-confirmed.html.twig #}

{% extends 'base.html.twig' %}

{% block content %}
    User "{{ user.email }}" denied/confirmed.
{% endblock %}
```

## registration_rule_confirmed.email

If the user is confirmed he will receive an email with the confirmation link:

```twig
{# community/registration-form.html.twig #}

{% extends 'base.html.twig' %}

{% block content %}
    <h1>Registration</h1>

    {% if app.request.get('send') == 'true' %}
        <p>
            To complete the registration click on the link in the received email.
        </p>
    {% else %}
        {{ form(form) }}
    {% endif %}
{% endblock %}
```
