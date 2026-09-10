# OAuth and SAML Support

## Introduction

LibreNMS has support for [Laravel Socialite](https://github.com/laravel/socialite) to try and simplify the use of OAuth 1 or 2 providers such as using GitHub, Microsoft, Twitter + many more and SAML.

[Socialite Providers](https://socialiteproviders.com) supports more
than 100 third parties. Your SAML provider or OAuth provider is
therefore usually in the list.

Please do note however, these providers are not maintained by LibreNMS so we cannot add support for new ones and we can only provide you basic help with general configuration.
See the Socialite Providers website for more information on adding a new OAuth provider.

The sections below describe the installation of SAML and of some OAuth
providers. Use them as a guide for any other provider. **Read the
Socialite Providers documentation with care.**

[GitHub Provider](https://socialiteproviders.com/GitHub/)
[Microsoft Provider](https://socialiteproviders.com/Microsoft/)
[Okta Provider](https://socialiteproviders.com/Okta)
[SAML2](https://socialiteproviders.com/Saml2/)

## Requirements

LibreNMS version 22.3.0 or later.

Please ensure you set `APP_URL` within your `.env` file so that callback URLs work correctly with the identify provider.

!!! note
    Once you have configured your OAuth or SAML2 provider, please ensure you check the [Post configuration settings](#post-config) section at the end.

## GitHub and Microsoft Examples

### Install plugin

!!! note
    First install the plugin. The plugin name can differ. Read the
    Socialite Providers documentation and find the line
    `composer require socialiteproviders/github`. This line gives the
    name for the command, that is `socialiteproviders/github`.

=== "GitHub"

    `lnms plugin:add socialiteproviders/github`

=== "Microsoft"

    `lnms plugin:add socialiteproviders/microsoft`

=== "Okta"

    `lnms plugin:add socialiteproviders/okta`

### Find the provider name

Next we need to find the provider name and writing it down

!!! note
    The name is usually the provider name in lower case. It can differ.
    Read the Socialite Providers documentation and find the line
    `github => [`. This line gives the name for the command above:
    `github`.

=== "GitHub"

    For GitHub we can find the line:
    ```php
    'github' => [
      'client_id' => env('GITHUB_CLIENT_ID'),
      'client_secret' => env('GITHUB_CLIENT_SECRET'),
      'redirect' => env('GITHUB_REDIRECT_URI')
    ],
    ```
    So our provider name is `github`, write this down.


=== "Microsoft"

    For Microsoft we can find the line:
    ```php
    'microsoft' => [
      'client_id' => env('MICROSOFT_CLIENT_ID'),
      'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
      'redirect' => env('MICROSOFT_REDIRECT_URI')
    ],
    ```
    So our provider name is `microsoft`, write this down.


=== "Okta"

    For Okta we can find the line:
    ```php
    'okta' => [
      'base_url' => env('OKTA_BASE_URL'),
      'client_id' => env('OKTA_CLIENT_ID'),
      'client_secret' => env('OKTA_CLIENT_SECRET'),
      'redirect' => env('OKTA_REDIRECT_URI')
    ],
    ```
    So our provider name is `okta`, write this down.


### Register OAuth application

#### Register a new application

You now need some values from the OAuth provider. Usually, you register
a new "OAuth application" on the site of the provider. The steps differ
between providers. The process is similar to the examples below.

!!! note
    The callback URL is always: https://*your-librenms-url*/auth/*provider*/callback
    The site does not need public access. It almost always needs TLS
    (https).

=== "GitHub"
    For our example with GitHub we go to [GitHub Developer Settings](https://github.com/settings/developers) and press "Register a new application":

    ![socialite-github-1](../img/socialite-github-1.png)

    Fill out the form accordingly (with your own values):
    ![socialite-github-2](../img/socialite-github-2.png)

=== "Microsoft"
    For our example with Microsoft we go to ["Azure Active Directory" > "App registrations"](https://aad.portal.azure.com/#blade/Microsoft_AAD_IAM/ActiveDirectoryMenuBlade/RegisteredApps) and press "New registration"

    ![socialite-1](../img/socialite-microsoft-1.png)

    Fill out the form accordingly using your own values):
    ![socialite-2](../img/socialite-microsoft-2.png)

    Copy and save the **Application (client) ID** and the **Directory
    (tenant) ID**. The next step needs them.
    ![socialite-2](../img/socialite-microsoft-3.png)

=== "Okta"
    For our example with Okta, we go to `Applications>Create App Integration`, Select `OIDC - OpenID Connect`, then `Web Application`.

    ![socialite-okta-1](../img/socialite-okta-1.png)

    Enter the Name, the Logo, and the Assignments for your setup. Leave
    the `Sign-In Redirect URI` field. You edit this field later:
    ![socialite-okta-2](../img/socialite-okta-2.png)

    Note your Okta domain or login URL. It is a vanity URL such as
    `login.company.com`, or the standard form `company.okta.com`.

    Click save.

#### Generate a new client secret

=== "GitHub"

    Press 'Generate a new client secret' to get a new client secret.

    ![socialite-github-3](../img/socialite-github-3.png)

    Copy the **Client ID** and **Client secret**

    In the example above it is:

    **Client ID**: 7a41f1d8215640ca6b00
    **Client secret**: ea03957288edd0e590be202b239e4f0ff26b8047

=== "Microsoft"

    Select Certificates & secrets under Manage.
    Select the 'New client secret' button.
    Enter a value in Description and select one of the options for Expires and select 'Add'.

    ![socialite-2](../img/socialite-microsoft-6.png)

    Copy the client secret **Value**, not the Secret ID, before you
    leave this page. The next step needs it.

    ![socialite-2](../img/socialite-microsoft-5.png)

=== "Okta"

    The creation of the app does this step. Copy the client secret. The
    next step needs it.

    ![socialite-okta-3](../img/socialite-okta-3.png)


### Saving configuration

Now we need to set the configuration options for your provider within LibreNMS itself. Please replace the values in the examples below with the values you collected earlier:

The format of the configuration string is `auth.socialite.configs.*provider name*.*value*`

=== "GitHub"

    !!! setting "settings/auth/socialite"
        ```bash
        lnms config:set auth.socialite.configs.github.client_id 7a41f1d8215640ca6b00
        lnms config:set auth.socialite.configs.github.client_secret ea03957288edd0e590be202b239e4f0ff26b8047
        ```

=== "Microsoft"

    !!! setting "settings/auth/socialite"
        ```bash
        lnms config:set auth.socialite.configs.microsoft.client_id 7983ac13-c955-40e9-9b85-5ba27be52a52
        lnms config:set auth.socialite.configs.microsoft.client_secret J9P7Q~K2F5C.L243sqzbGj.cOOcjTBgAPak_l
        lnms config:set auth.socialite.configs.microsoft.tenant a15edc05-152d-4eb4-973c-14f1fdc57d8b
        ```

=== "Okta"

    !!! setting "settings/auth/socialite"
        ```bash
        lnms config:set auth.socialite.configs.okta.client_id 0oa1c08tti8D7xgXb697
        lnms config:set auth.socialite.configs.okta.client_secret sWew90IKqKDmURj1XLsCPjXjre0U3zmJuFR6SzsG
        lnms config:set auth.socialite.configs.okta.base_url "https://<okta_login_url>"
        ```

### Add provider event listener

The final step is to now add an event listener.

!!! note
    Copy the exact value here,
    It starts with a `\` and ends before `::class.'@handle'`

=== "GitHub"

    Find the section looking like:
    ```php
    protected $listen = [
        \SocialiteProviders\Manager\SocialiteWasCalled::class => [
            // ... other providers
            \SocialiteProviders\GitHub\GitHubExtendSocialite::class.'@handle',
        ],
    ];
    ```

    Copy the part: `\SocialiteProviders\GitHub\GitHubExtendSocialite` and run;
    !!! setting "settings/auth/socialite"
        ```bash
        lnms config:set auth.socialite.configs.github.listener "\SocialiteProviders\GitHub\GitHubExtendSocialite"
        ```
    Use the backslash (\) at the start.

=== "Microsoft"

    Find the section looking like:
    ```php
    protected $listen = [
        \SocialiteProviders\Manager\SocialiteWasCalled::class => [
            // ... other providers
            \SocialiteProviders\Microsoft\MicrosoftExtendSocialite::class.'@handle',
        ],
    ];
    ```

    Copy the part: `\SocialiteProviders\Microsoft\MicrosoftExtendSocialite` and run;
    !!! setting "settings/auth/socialite"
        ```bash
        lnms config:set auth.socialite.configs.microsoft.listener "\SocialiteProviders\Microsoft\MicrosoftExtendSocialite"
        ```
    Use the backslash (\) at the start.

=== "Okta"

    Find the section looking like:
    ```php
    protected $listen = [
    \SocialiteProviders\Manager\SocialiteWasCalled::class => [
        // ... other providers
        \SocialiteProviders\Okta\OktaExtendSocialite::class.'@handle',
    ],
    ];
    ```

    Copy the part: `\SocialiteProviders\Okta\OktaExtendSocialite` and run;
    !!! setting "settings/auth/socialite"
        ```bash
        lnms config:set auth.socialite.configs.okta.listener "\SocialiteProviders\Okta\OktaExtendSocialite"
        ```
    Use the backslash (\) at the start.

Now you are done with setting up the OAuth provider!
If it fails, read your configuration values again with the `config:get`
command below.

!!! setting "settings/auth/socialite"
    ```bash
    lnms config:get auth.socialite
    ```

### Default Role

Most Socialite Providers give only authentication, not authorization.
You can therefore set the default user role of the authorized users.
Take care with this setting.

- none: **No Permissions**: User has no permissions assigned

- normal: **Normal User**: you must assign the device permissions or
  the port
      permissions for users at this level.

- global-read: **Global Read**: Read only Administrator.

- admin: **Administrator**: This is a global read/write admin account.

!!! setting "settings/auth/socialite"
    ```bash
    lnms config:set auth.socialite.default_role global-read
    ```

###  Claims / Access Scopes

Socialite can give the scopes of the authentication request.
(see [Larvel docs](https://laravel.com/docs/10.x/socialite#access-scopes) )

=== "Okta"

    For example, if Okta is configured to expose group information it is possible to use these group
    names to configure User Roles.

    This requires configuration in Okta.  You can set the 'Groups claim type' to 'Filter' and supply
    a regular expression of the groups to return. The map below uses them.

    ![socialite-okta-1](../img/socialite-okta-4.png)

    First enable sending the 'groups' claim (along with the normal openid, profile, and email claims).
    Be aware that the scope name must match the claim name. For identity providers where the scope does
    do not match, for example Keycloak roles against groups, configure your own scope.

    !!! setting "settings/auth/socialite"
        ```bash
        lnms config:set auth.socialite.scopes.+ groups
        ```

    Then setup mappings from the returned claim arrays to the User levels you want
    !!! setting "settings/auth/socialite"
        ```bash
        lnms config:set auth.socialite.claims.RETURN_FROM_CLAIM.roles '["admin"]'
        lnms config:set auth.socialite.claims.OTHER_RETURN_FROM_CLAIM.roles '["global-read","cleaner"]'
        ```

=== "Microsoft"

    For example in Microsoft EntraID you need to configure roles that are sent back to LibreNMS

    This requires configuration in EntraID.
    
    create roles.
    
    ![socialite-microsoft-8](../img/socialite-microsoft-8.png)

    assign roles to groups
    
    ![socialite-microsoft-9](../img/socialite-microsoft-9.png)

    Then setup mappings from the returned claim arrays to the User levels you want
    !!! setting "settings/auth/socialite"
        ```bash
        lnms config:set auth.socialite.claims.RETURN_FROM_CLAIM.roles '["admin"]'
        lnms config:set auth.socialite.claims.OTHER_RETURN_FROM_CLAIM.roles '["global-read","cleaner"]'
        ```
        
    !!! full config example
        ```bash
        lnms config:get auth.socialite.default_role none
        lnms config:set auth.socialite.configs.microsoft.claim_field roles
        lnms config:set auth.socialite.scopes '["profile"]'
        lnms config:set auth.socialite.claims.admin.roles '["admin"]'
        lnms config:set auth.socialite.claims.globalread.roles '["global-read"]'
        lnms config:set auth.socialite.claims.user.roles '["user"]'
        ```

    it is also possible to add groups claims and use groupids but this is the recommended microsoft way.
    
## Claim Field (advanced)

Some providers deliver role or group membership under a token claim field that
is **not** a valid OAuth scope name.  Microsoft is a common example: app-role
assignments arrive in the `roles` claim, but adding `roles` to
`auth.socialite.scopes` causes Microsoft to return:

```
AADSTS650053: The application asked for scope 'roles' that doesn't exist
```

The `claim_field` option solves this by separating *what is requested from the
IdP* (scopes) from *what key is read in the returned token* (claim_field).  It
is configured per provider under `auth.socialite.configs.<provider>.claim_field`
and accepts an string.

```bash
lnms config:set auth.socialite.configs.microsoft.claim_field roles
```

## SAML2 Example

### Install plugin

The first step is to install the plugin itself.

```bash
lnms plugin:add socialiteproviders/saml2
```

### Add configuration

The configuration depends on your identity provider, such as Google or
Azure. Your configuration can therefore differ from the example below.
Use the example as a guide.
The IdP supplies the details of the configuration.

=== "Google"

    Go to [https://admin.google.com/ac/apps/unified](https://admin.google.com/ac/apps/unified)

    ![socialite-saml-google-1](../img/socialite-saml-google-1.png)
    ![socialite-saml-google-2](../img/socialite-saml-google-2.png)

    Press "DOWNLOAD METADATA" and save the file somewhere accessible by your LibreNMS server

    ![socialite-saml-google-3](../img/socialite-saml-google-3.png)

    ACS URL = https://*your-librenms-url*/auth/saml2/callback
    Entity ID = https://*your-librenms-url*/auth/saml2
    Name ID format = PERSISTENT
    Name ID = Basic Information > Primary email

    ![socialite-saml-google-4](../img/socialite-saml-google-4.png)


    First name = http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname
    Last name = http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname
    Primary email = http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress


    ![socialite-saml-google-5](../img/socialite-saml-google-5.png)


    ![socialite-saml-google-6](../img/socialite-saml-google-6.png)


    !!! setting "settings/auth/socialite"
    ```bash
    lnms config:set auth.socialite.configs.saml2.metadata "$(cat /tmp/GoogleIDPMetadata.xml)"
    ```

    You can also copy the content of the file and run it in this way. The
    result is the same.
    !!! setting "settings/auth/socialite"
    ```bash
    lnms config:set auth.socialite.configs.saml2.metadata '''<?xml version="1.0" encoding
    ...
    ...
    </md:EntityDescriptor>'''
    ```

=== "Azure"

    ![LibreNMS-SAML-Azure](../img/socialite-azure-1.png)
    ```bash
    echo "SESSION_SAME_SITE=none" >> .env
    lnms plugin:add socialiteproviders/saml2
    lnms config:set auth.socialite.redirect true
    lnms config:set auth.socialite.register true
    lnms config:set auth.socialite.configs.saml2.acs https://login.microsoftonline.com/xxxidfromazurexxx/saml2
    lnms config:set auth.socialite.configs.saml2.entityid https://sts.windows.net/xxxidfromazurexxx/
    lnms config:set auth.socialite.configs.saml2.certificate xxxcertinonelinexxx
    lnms config:set auth.socialite.configs.saml2.listener "\SocialiteProviders\Saml2\Saml2ExtendSocialite"
    lnms config:set auth.socialite.configs.saml2.metadata https://nexus.microsoftonline-p.com/federationmetadata/saml20/federationmetadata.xml
    lnms config:set auth.socialite.configs.saml2.sp_default_binding_method urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST
    lnms config:clear
    ```

#### Using an Identity Provider metadata URL

!!! note
    This is the preferred and easiest way, if your IdP supports it!

!!! setting "settings/auth/socialite"
    ```bash
    lnms config:set auth.socialite.configs.saml2.metadata https://idp.co/metadata/xml
    ```

#### Using an Identity Provider metadata XML file

!!! setting "settings/auth/socialite"
    ```bash
    lnms config:set auth.socialite.configs.saml2.metadata "$(cat GoogleIDPMetadata.xml)"
    ```

#### Manually configuring the Identity Provider with a certificate string

!!! setting "settings/auth/socialite"
    ```bash
    lnms config:set auth.socialite.configs.saml2.acs https://idp.co/auth/acs
    lnms config:set auth.socialite.configs.saml2.entityid http://saml.to/trust
    lnms config:set auth.socialite.configs.saml2.certificate MIIC4jCCAcqgAwIBAgIQbDO5YO....
    ```

#### Manually configuring the Identity Provider with a certificate file

!!! setting "settings/auth/socialite"
    ```bash
    lnms config:set auth.socialite.configs.saml2.acs https://idp.co/auth/acs
    lnms config:set auth.socialite.configs.saml2.entityid http://saml.to/trust
    lnms config:set auth.socialite.configs.saml2.certificate "$(cat /path/to/certificate.pem)"
    ```

### Add provider event listener

Then define the listener service in LibreNMS:

!!! setting "settings/auth/socialite"
    ```bash
    lnms config:set auth.socialite.configs.saml2.listener "\SocialiteProviders\Saml2\Saml2ExtendSocialite"
    ```

### SESSION_SAME_SITE

With SAML2, set `SESSION_SAME_SITE=none` in `.env`.
At an error with HTTP code 419, remove `SESSION_SAME_SITE=none` from
your `.env` file.

!!! note
    After a change to `.env`, run `lnms config:clear`. This command
    clears the configuration cache

### Service provider metadata

Your identity provider can ask for your Service Provider (SP) metadata.
LibreNMS exposes all of this information from your [LibreNMS install](https://*your-librenms-url*/auth/saml2/metadata)


## Troubleshooting
If it fails, read your configuration values again with the `config:get`
command below.

!!! setting "settings/auth/socialite"
    ```bash
    lnms config:get auth.socialite
    ```

### Redirect URL
If you have a need to, then you can override redirect url with the following commands:

=== "OAuth"
    Replace `github` and the relevant URL below with your identity provider details.
    `lnms config:set auth.socialite.configs.github.redirect https://demo.librenms.org/auth/github/callback`

=== "SAML2"
    `lnms config:set auth.socialite.configs.saml2.sp_acs auth/saml2/callback`

## <a name="post-config">Post configuration settings</a>

!!! setting "settings/auth/socialite"
    From here you can configure the settings for any identity providers you have configured along with some bespoke options.

    Redirect Login page: this setting skips the LibreNMS login. It sends
    the user to your first configured idP.

    Allow registration via provider: with this setting off, a new user
    from the idP gets no authentication. With this setting on, LibreNMS
    creates the local user automatically and permits the login.
