# Password Forget

## Config 

```yml
# app/config/config.yml

sulu_community:
    webspaces:
        <webspace_key>:
            password_forget:
                email:
                    subject: Password Forget
                    admin_template: ~
                    user_template: AppBundle:templates:community/Password/forget-email.html.twig
                redirect_to: ?send=true
                template: AppBundle:templates:community/Password/forget-form.html.twig
                type: AppBundle\Form\Type\PasswordForgetType
```


## email

After the user submitted the password forget form he will receive a email with the link.

**Example Template**:

```twig
{# AppBundle:templates:community/Password/forget-email.html.twig #}

{% extends "SuluCommunityBundle::master-email.html.twig" %}

{% block content %}
    {% set url = url('sulu_community.password_reset', { token: user.passwordResetToken }) %}

    <a href="{{ url }}">
        {{ url }}
    </a>
{% endblock %}
```

## template

The password forget template.

**Example Template**:

```twig
{# AppBundle:templates:community/Confirmation/confirmation-message.html.twig #}

{% extends "AppBundle::master.html.twig" %}

{% block content %}
    <h1>Password_forget</h1>

    {% if app.request.get('send') == 'true' %}
        <p>
            Click on the link in your email to reset your password.
        </p>
    {% else %}
        {{ form(form) }}
    {% endif %}
{% endblock %}
```

## type

Set a new type to overwrite the existing form.

**Example Class**:

```php
//  src/AppBundle/Form/Type/PasswordForgetType.php

namespace AppBundle\Form\Type;

use Sulu\Bundle\CommunityBundle\Validator\Constraints\Exist;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PasswordForgetType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email_username', TextType::class, [
            'constraints' => new Exist([
                'columns' => ['email', 'username'],
                'entity' => $options['user_class'],
                'groups' => 'password_forget',
            ]),
        ]);

        $builder->add('submit', SubmitType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'user_class' => User::class,
            'validation_groups' => ['password_forget'],
        ]);
    }
}
```
