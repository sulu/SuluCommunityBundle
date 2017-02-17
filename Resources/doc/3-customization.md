# Customization

## Full Configuration

```yml
# app/config/config.yml

sulu_community:
    webspaces:
        <webspace_key>:
            from: %sulu_admin.email%
            to: %sulu_admin.email%
            role: CustomRoleName
            firewall: CustomFirewallName

            # Login
            login:
                embed_template: AppBundle:templates:community/Login/login-embed.html.twig
                template: AppBundle:templates:community/Login/login.html.twig
                
            # Registration
            registration:
                activate_user: false
                auto_login: true # only available when activate_user is true
                email:
                    subject: Registration
                    user_template: AppBundle:templates:community/Registration/registration-email.html.twig
                    admin_template: ~
                redirect_to: ?send=true
                template: AppBundle:templates:community/Registration/registration-form.html.twig
                type: AppBundle\Form\Type\RegistrationType
                    
            # Confirmation
            confirmation: 
                activate_user: true
                auto_login: true
                email:
                    subject: Confirmation
                    user_template: ~
                    admin_template: ~
                redirect_to: ~
                template: AppBundle:templates:community/Confirmation/confirmation-message.html.twig
                    
            # Completion
            completion:
                email:
                    subject: Completion
                    user_template: ~
                    admin_template: ~
                redirect_to: /
                service: ~
                template: AppBundle:templates:community/Completion/completion-form.html.twig
                type: AppBundle\Form\Type\CompletionType
                    
            # Password Forget / Reset
            password_forget:
                email:
                    subject: Password Forget
                    admin_template: ~
                    user_template: AppBundle:templates:community/Password/forget-email.html.twig
                redirect_to: ?send=true
                template: AppBundle:templates:community/Password/forget-form.html.twig
                type: AppBundle\Form\Type\PasswordForgetType
            password_reset:
                auto_login: true
                email:
                    subject: Password Reset
                    admin_template: ~
                    user_template: AppBundle:templates:community/Password/reset-email.html.twig
                redirect_to: ?send=true
                template: AppBundle:templates:community/Password/reset-form.html.twig
                type: AppBundle\Form\Type\PasswordResetType
                    
            # Profile
            profile:
                email:
                    subject: Profile Updated
                    user_template: ~
                    admin_template: ~
                redirect_to: ~
                template: AppBundle:templates:community/Profile/profile-form.html.twig
                type: AppBundle\Form\Type\ProfileType
                
            # Email Confirmation
            email_confirmation:
                email:
                    subject: Email Changed
                    user_template: AppBundle:templates:community/EmailConfirmation/email-confirmation-email.html.twig
                    admin_template: ~
                template: AppBundle:templates:community/EmailConfirmation/email-confirmation-success.html.twig
                
            # Blacklist
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

### Basic Options

#### from

Type: `string`
Example: test@test.com

Will be used as sender of all emails in this webspace. 

#### to

Type: `string`
Example: test@test.com

Will be used as receiver of the admin emails.

#### role

Type: `string`
Example: website

You can change the role name. 
If not set it will generate it by webspace key and add 'User' as postfix.

#### firewall

Type: `string`
Example: website

Set the firewall for the current webspace else the default value is the webspace key.

### Type Options

#### activate_user

Type: `bool`
Example: true

When set to true the user can login after successfully submitting the form.

#### auto_login

Type: `bool`
Example: true

The user will be automatically logged in after successfully submitting the form.

#### embed_template

Type: `string`
Example: AppBundle:templates:community/Login/login-embed.html.twig

The template which is used to render an uncached esi login form with `render_esi`.

#### email.subject

Type: `string`
Example: Registration

The subject of the emails.

#### email.admin_template

Type: `string`
Example: AppBundle:templates:community/Blacklist/blacklist-email.html.twig

The template used to render the admin email, set to null to deactivate it.

#### email.user_template

Type: `string`
Example: AppBundle:templates:community/Registration/registration-email.html.twig

The template used to render the user email, set to null to deactivate it.

#### redirect_to

Type: `string`
Example: ?send=true, app.redirect_route

redirect_to can be a url or a route_name. When containing `{localization}` 
it will be replaced with the current locale.

#### service

Type: `string`
Example: app.completion_validator

Service from interface `CompletionInterface` to validate if a user needs to add additional information before using your application. 

#### template

Type: `string`
Example: AppBundle:templates:community/Registration/registration-form.html.twig

The template where the form should be rendered.

#### Type

Type: `string`
Example: AppBundle\Form\Type\RegistrationType

The form type which is used to build the form.
