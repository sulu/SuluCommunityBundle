# Blacklisting

## Config

```yml
# app/config/config.yml

sulu_community:
    webspaces:
        <webspace_key>:
            blacklisted:
                email:
                    subject: Blacklisted
                    admin_template: AppBundle:templates:community/Blacklist/blacklist-email.html.twig
                    user_template: ~
            blacklist_denied:
                email:
                    subject: Denied
                    admin_template: ~
                    user_template: ~
                template: AppBundle:templates:community/Blacklist/blacklist-denied.html.twig
            blacklist_confirmed:
                email:
                    subject: Registration
                    admin_template: ~
                    user_template: AppBundle:templates:community/Registration/registration-email.html.twig
                template: AppBundle:templates:community/Blacklist/blacklist-confirmed.html.twig
```

## Backend Config

To enable the blacklisting feature add the permissions for blacklisting to your role.  
For this go to `Settings -> Roles -> YourRole` in the sulu backend and add the permissions.  
  
When you successfully enabled it email addresses can be set to `block` or on `request` under `Settings -> Blacklist`. 
It is possible to use wildcards e.g. `*@test.com` to set the state for a whole domain.

## blacklist.email

The admin of the page will receive the blacklisted email when a user email address is set to `request`.

**Example Template**:

```twig
{# AppBundle:templates:community/Blacklist/blacklist-email.html.twig #}

{% extends "AppBundle::master-email.html.twig" %}

{% block content %}
    <p>E-Mail: {{ user.email }}</p>

    {% set confirmUrl = url('sulu_community.user_confirm', { token: token }) %}
    <p>Confirm: <a href="{{ confirmUrl }}">{{ confirmUrl }}</a></p>

    {% set denyUrl = url('sulu_community.user_deny', { token: token }) %}
    <p>Deny: <a href="{{ denyUrl }}">{{ denyUrl }}</a></p>
{% endblock %}
```

## blacklist_denied.template / blacklist_confirmed.template

When the admin clicks on the link a template is rendered which can show specific content:

**Example Template**:

```twig
{# AppBundle:templates:community/Blacklist/blacklist-denied.html.twig / AppBundle:templates:community/Blacklist/blacklist-confirmed.html.twig #}

{% extends "AppBundle::master.html.twig" %}

{% block content %}
    User "{{ user.email }}" denied/confirmed.
{% endblock %}
```

## blacklist_confirmed.email

If the user is confirmed he will receive an email with the confirmation link:

```twig
{# AppBundle:templates:community/Registration/registration-form.html.twig #}

{% extends "AppBundle:website:master.html.twig" %}

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
