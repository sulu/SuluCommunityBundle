# Login

## Config

```yml
# app/config/config.yml

sulu_community:
    webspaces:
        <webspace_key>:
            login:
                embed_template: AppBundle:templates:community/Login/login-embed.html.twig
                template: AppBundle:templates:community/Login/login.html.twig
```

## embed_template

The embed template can be used to display on every page a login or logout 
button based on the login state of the user.  
  
When using the embed template make sure esi is enabled:

```yml
# app/config/config.yml

framework:
    esi: { enabled: true }
```

**Insert following in your twig file**:

```twig
{{ render_esi(controller('SuluCommunityBundle:Login:embed', {
    '_portal' : request.portalKey|default('default_portal_key'),
    '_locale' : app.request.locale
})) }}
```

Sulu sets by default the cache control header to 240 seconds. So it could happen
that the browser cache the page and show an incorrect status. For this you can
decrease or deactivate the browser cache lifetime in `app/config/config.yml`:

```yml
sulu_http_cache:
    handlers:
        public:
            max_age: 0
            shared_max_age: 0
```

**Example Template**:

```twig
{# AppBundle:templates:community/Login/login-embed.html.twig #}

{% if app.user %}
    {% set media = null %}
    {% if app.user.contact.avatar is not null %}
        {% set media = sulu_resolve_media(app.user.contact.avatar, request.locale) %}
    {% endif %}

    <a href="{{ path('sulu_community.profile') }}">
        {% if media is not null %}
            <img src="{{ media.thumbnails['50x50'] }}"/>
        {% endif %}

        {{ app.user.username|default('Profile') }}
    </a>

    <a href="{{ path('sulu_community.logout') }}">
        Logout
    </a>
{% else %}
    <a href="{{ path('sulu_community.login') }}">
        Login
    </a>
{% endif %}
```

## template

**Example Template**:

```twig
{% extends "SuluCommunityBundle::master.html.twig" %}

{% block content %}
    {% if error %}
        <div>{{ error.messageKey|trans(error.messageData, 'security') }}</div>
    {% endif %}

    <form action="{{ path('sulu_community.login') }}" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="_username" value="{{ last_username }}" />

        <label for="password">Password:</label>
        <input type="password" id="password" name="_password" />

        <input type="checkbox" id="remember_me" name="_remember_me" checked />
        <label for="remember_me">Keep me logged in</label>

        {#
            If you want to control the URL the user
            <input type="hidden" name="_target_path" value="/account" />
        #}

        <button type="submit">login</button>
    </form>

    <a href="{{ path('sulu_community.password_forget') }}">
        Password Forget
    </a> <br/>

    <a href="{{ path('sulu_community.registration') }}">
        Registration
    </a>
{% endblock %}

```
