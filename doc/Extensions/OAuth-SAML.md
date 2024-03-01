# OAuth and SAML Support

## Introduction

LibreNMS has support for [Laravel Socialite](https://github.com/laravel/socialite) to try and simplify the use of OAuth 1 or 2 providers such as using GitHub, Microsoft, Twitter + many more and SAML.

[Socialite Providers](https://socialiteproviders.com) supports more than 100+ 3rd parties so you will most likely find support for the SAML or OAuth provider you need without too much trouble.

Please do note however, these providers are not maintained by LibreNMS so we cannot add support for new ones and we can only provide you basic help with general configuration.
See the Socialite Providers website for more information on adding a new OAuth provider.

Below we will guide you on how to install SAML or some of these OAth providers, you should be able to use these as a guide on how to install any others you may need but **please, please, ensure you read the Socialite Providers documentation carefully**.

[GitHub Provider](https://socialiteproviders.com/GitHub/)
[Microsoft Provider](https://socialiteproviders.com/Microsoft/)
[Okta Provider](https://socialiteproviders.com/Okta)
[SAML2](https://socialiteproviders.com/Saml2/)

## Requirements

LibreNMS version 22.3.0 or later.

Please ensure you set `APP_URL` within your `.env` file so that callback URLs work correctly with the identify provider.

!!! note
    Once you have configured your OAuth or SAML2 provider, please ensure you check the [Post configuration settings](#post-configration-settings) section at the end.

## GitHub and Microsoft Examples

### Install plugin

!!! note
    First we need to install the plugin itself. The plugin name can be slightly different so be sure to check the Socialite Providers documentation and look for this line, `composer require socialiteproviders/github` which will give you the name you need for the command, i.e: `socialiteproviders/github`.

=== "GitHub"

    `lnms plugin:add socialiteproviders/github`

=== "Microsoft"

    `lnms plugin:add socialiteproviders/microsoft`

=== "Okta"

    `lnms plugin:add socialiteproviders/okta`

### Find the provider name

Next we need to find the provider name and writing it down

!!! note
    It's almost always the name of the provider in lowercase but can be different so check the Socialite Providers documentation and look for this line, `github => [` which will give you the name you need for the above command: `github`.

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

Now we need some values from the OAuth provider itself, in most cases you need to register a new "OAuth application" at the providers site. This will vary from provider to provider but the process itself should be similar to the examples below.

!!! note
    The callback URL is always: https://*your-librenms-url*/auth/*provider*/callback
    It doesn't need to be a public available site, but it almost always needs to support TLS (https)!

=== "GitHub"
    For our example with GitHub we go to [GitHub Developer Settings](https://github.com/settings/developers) and press "Register a new application":

    ![socialite-github-1](/img/socialite-github-1.png)

    Fill out the form accordingly (with your own values):
    ![socialite-github-2](/img/socialite-github-2.png)

=== "Microsoft"
    For our example with Microsoft we go to ["Azure Active Directory" > "App registrations"](https://aad.portal.azure.com/#blade/Microsoft_AAD_IAM/ActiveDirectoryMenuBlade/RegisteredApps) and press "New registration"

    ![socialite-1](/img/socialite-microsoft-1.png)

    Fill out the form accordingly using your own values):
    ![socialite-2](/img/socialite-microsoft-2.png)

    Copy the value of the **Application (client) ID** and **Directory (tenant) ID** and save them, you will need them in the next step.
    ![socialite-2](/img/socialite-microsoft-3.png)

=== "Okta"
    For our example with Okta, we go to `Applications>Create App Integration`, Select `OIDC - OpenID Connect`, then `Web Application`.

    ![socialite-okta-1](/img/socialite-okta-1.png)

    Fill in the Name, Logo, and Assignments based on your preferred settings. Leave the `Sign-In Redirect URI` field, this is where you will edit this later:
    ![socialite-okta-2](/img/socialite-okta-2.png)

    Note your Okta domain or login url. Sometimes this can be a vanity url like `login.company.com`, or sometimes just `company.okta.com`.

    Click save.

#### Generate a new client secret

=== "GitHub"

    Press 'Generate a new client secret' to get a new client secret.

    ![socialite-github-3](/img/socialite-github-3.png)

    Copy the **Client ID** and **Client secret**

    In the example above it is:

    **Client ID**: 7a41f1d8215640ca6b00
    **Client secret**: ea03957288edd0e590be202b239e4f0ff26b8047

=== "Microsoft"

    Select Certificates & secrets under Manage.
    Select the 'New client secret' button.
    Enter a value in Description and select one of the options for Expires and select 'Add'.

    ![socialite-2](/img/socialite-microsoft-6.png)

    Copy the client secret **Value** (not Secret ID!) before you leave this page. You will need it in the next step.

    ![socialite-2](/img/socialite-microsoft-5.png)

=== "Okta"

    This step is done for you when creating the app. All you have to do is copy down the client secret. You will need it in the next step.

    ![socialite-okta-3](/img/socialite-okta-3.png)


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
    It's important to copy exactly the right value here,
    It should begin with a `\` and end before the `::class.'@handle'`

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
    Don't forget the initial backslash (\\) !

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
    Don't forget the initial backslash (\\) !

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
    Don't forget the initial backslack (\\) !

Now you are done with setting up the OAuth provider!
If it doesn't work, please double check your configuration values by using the `config:get` command below.

!!! setting "settings/auth/socialite"
    ```bash
    lnms config:get auth.socialite
    ```

### Default Role

Since most Socialite Providers don't provide Authorization only Authentication it is possible to set
the default User Role for Authorized users.   Appropriate care should be taken.

- none: **No Access**: User has no access

- normal: **Normal User**: You will need to assign device / port
      permissions for users at this level.

- global-read: **Global Read**: Read only Administrator.

- admin: **Administrator**: This is a global read/write admin account.

!!! setting "settings/auth/socialite"
    ```bash
    lnms config:set auth.socialite.default_role global-read
    ```

###  Claims / Access Scopes

Socialite can specifiy scopes that should be included with in the authentication request.
(see [Larvel docs](https://laravel.com/docs/10.x/socialite#access-scopes) )

For example, if Okta is configured to expose group information it is possible to use these group
names to configure User Roles.

First enable sending the 'groups' claim (along with the normal openid, profile, and email claims).
Be aware that the scope name must match the claim name. For identity providers where the scope does
not match (e.g. Keycloak: roles -> groups) you need to configure a custom scope.

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


## SAML2 Example

### Install plugin

The first step is to install the plugin itself.

```bash
lnms plugin:add socialiteproviders/saml2
```

### Add configuration

Depending on what your identity provider (Google, Azure, ...) supports, the configuration could look different from what you see next so please use this as a rough guide.
It is up the IdP to provide the relevant details that you will need for configuration.

=== "Google"

    Go to [https://admin.google.com/ac/apps/unified](https://admin.google.com/ac/apps/unified)

    ![socialite-saml-google-1](/img/socialite-saml-google-1.png)
    ![socialite-saml-google-2](/img/socialite-saml-google-2.png)

    Press "DOWNLOAD METADATA" and save the file somewhere accessible by your LibreNMS server

    ![socialite-saml-google-3](/img/socialite-saml-google-3.png)

    ACS URL = https://*your-librenms-url*/auth/saml2/callback
    Entity ID = https://*your-librenms-url*/auth/saml2
    Name ID format = PERSISTANT
    Name ID = Basic Information > Primary email

    ![socialite-saml-google-4](/img/socialite-saml-google-4.png)


    First name = http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname
    Last name = http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname
    Primary email = http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress


    ![socialite-saml-google-5](/img/socialite-saml-google-5.png)


    ![socialite-saml-google-6](/img/socialite-saml-google-6.png)


    !!! setting "settings/auth/socialite"
    ```bash
    lnms config:set auth.socialite.configs.saml2.metadata "$(cat /tmp/GoogleIDPMetadata.xml)"
    ```

    Alternatively, you can copy the content of the file and run it like so, this will result in the exact same result as above.
    !!! setting "settings/auth/socialite"
    ```bash
    lnms config:set auth.socialite.configs.saml2.metadata '''<?xml version="1.0" encoding
    ...
    ...
    </md:EntityDescriptor>'''
    ```

=== "Azure"

    ![LibreNMS-SAML-Azure](https://user-images.githubusercontent.com/8980985/222431219-af2369dc-1abd-4943-8dfb-5a21d8b9976c.png)
    ```bash
    echo "SESSION_SAME_SITE_COOKIE=none" >> .env
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
    This is the prefered and easiest way, if your IdP supports it!

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

Now we just need to define the listener service within LibreNMS:

!!! setting "settings/auth/socialite"
    ```bash
    lnms config:set auth.socialite.configs.saml2.listener "\SocialiteProviders\Saml2\Saml2ExtendSocialite"
    ```

### SESSION_SAME_SITE_COOKIE

You most likely will need to set `SESSION_SAME_SITE_COOKIE=none` in `.env` if you use SAML2!
If you get an error with http code 419, you should try to remove `SESSION_SAME_SITE_COOKIE=none` from your `.env`.

!!! note
    Don't forget to run `lnms config:clear` after you modify `.env` to flush the config cache

### Service provider metadata

Your identify provider might ask you for your Service Provider (SP) metadata.
LibreNMS exposes all of this information from your [LibreNMS install](https://*your-librenms-url*/auth/saml2/metadata)


## Troubleshooting
If it doesn't work, please double check your configuration values by using the `config:get` command below.

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

## Post configuration settings

!!! setting "settings/auth/socialite"
    From here you can configure the settings for any identity providers you have configured along with some bespoke options.

    Redirect Login page: This setting will skip your LibreNMS login and take the end user straight to the first idP you configured.

    Allow registration via provider: If this setting is disabled, new users signing in via the idP will not be authenticated. This setting allows a local user to be automatically created which permits their login.
