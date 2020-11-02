# Customization

The sulu community bundle can be configured in many ways to match your workflow.

## Full Configuration

This is just an example configuration with all options.
At first state you should just use the basic configuration from [here](2-setup-webspace.md#activate-community-features).

```yml
# config/packages/sulu_community.yaml

sulu_community:
    last_login:
        enabled: false
        refresh_interval: 600
    webspaces:
        <webspace_key>: # Replace <webspace_key> with the key of your webspace
            from:
                name: "Website"
                email: "%sulu_admin.email%"
            to:
                name: "Admin"
                email: "%sulu_admin.email%"
            role: CustomRoleName
            firewall: CustomFirewallName
            # Maintenance
            maintenance:
                enabled: false
                template: community/maintenance.html.twig
            # Login
            login:
                embed_template: community/login-embed.html.twig
                template: community/login.html.twig
                
            # Registration
            registration:
                activate_user: false
                auto_login: true # only available when activate_user is true
                email:
                    subject: Registration
                    user_template: community/registration-email.html.twig
                    admin_template: ~
                redirect_to: ?send=true
                template: community/registration-form.html.twig
                type: App\Form\RegistrationType
                    
            # Confirmation
            confirmation: 
                activate_user: true
                auto_login: true
                email:
                    subject: Confirmation
                    user_template: ~
                    admin_template: ~
                redirect_to: ~
                template: community/confirmation-message.html.twig
                    
            # Completion
            completion:
                email:
                    subject: Completion
                    user_template: ~
                    admin_template: ~
                redirect_to: /
                service: ~
                template: community/completion-form.html.twig
                type: App\Form\CompletionType
                    
            # Password Forget / Reset
            password_forget:
                email:
                    subject: Password Forget
                    admin_template: ~
                    user_template: community/password-forget-email.html.twig
                redirect_to: ?send=true
                template: community/password-forget-form.html.twig
                type: App\Form\PasswordForgetType
            password_reset:
                auto_login: true
                email:
                    subject: Password Reset
                    admin_template: ~
                    user_template: community/password-reset-email.html.twig
                redirect_to: ?send=true
                template: community/reset-form.html.twig
                type: App\Form\PasswordResetType
                    
            # Profile
            profile:
                email:
                    subject: Profile Updated
                    user_template: ~
                    admin_template: ~
                redirect_to: ~
                template: community/profile-form.html.twig
                type: App\Form\ProfileType
                
            # Email Confirmation
            email_confirmation:
                email:
                    subject: Email Changed
                    user_template: community/email-confirmation-email.html.twig
                    admin_template: ~
                template: community/email-confirmation-success.html.twig
                
            # Blacklist
            blacklisted:
                email:
                    subject: Blacklisted
                    admin_template: community/blacklist-email.html.twig
                    user_template: ~
            blacklist_denied:
                email:
                    subject: Denied
                    admin_template: ~
                    user_template: ~
                template: community/blacklist-denied.html.twig
            blacklist_confirmed:
                email:
                    subject: Registration
                    admin_template: ~
                    user_template: community/registration-email.html.twig
                template: community/blacklist-confirmed.html.twig
```

### Basic Options

#### last_login.refresh_interval

Type: `integer`
Example: 600

When used cookie based login the last login timestamp will never 
be refreshed you can activate the last login refresh interval
which will refresh every given seconds the last login timestamp
to show example active users in your application.

#### from

Type: `string`
Example: test@test.com

Will be used as sender of all emails in this webspace. 

#### to

Type: `string`
Example: test@test.com

Will be used as receiver of the admin emails.
If not configured it will fallback to the from configuration.

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
Example: community/login-embed.html.twig

The template which is used to render an uncached esi login form with `render_esi`.

#### email.subject

Type: `string`
Example: Registration

The subject of the emails.

#### email.admin_template

Type: `string`
Example: community/blacklist-email.html.twig

The template used to render the admin email, set to null to deactivate it.

#### email.user_template

Type: `string`
Example: community/registration-email.html.twig

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
Example: community/registration-form.html.twig

The template where the form should be rendered.

#### Type

Type: `string`
Example: App\Form\RegistrationType

The form type which is used to build the form.

### Example customizations

 - [Login](4-login.md)
 - [Registration](5-registration.md)
 - [Confirmation](6-confirmation.md)
 - [Password Forget](7-password-forget.md)
 - [Password Reset](8-password-reset.md)
 - [Profile](9-profile.md)
 - [Email Confirmation](10-email-confirmation.md)
 - [Completion](11-completion.md)
 - [Blacklisting](12-blacklisting.md)
 - [Maintenance](13-maintenance.md)
