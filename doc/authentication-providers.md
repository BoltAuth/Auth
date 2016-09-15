Authentication Providers
------------------------

## Index 
  * [Supported Providers](#supported-providers)
    * [Configuration](#configuration)
      * [Local (valid email address & password)](#local-valid-email-address--password)
      * [Google](#google)
      * [Facebook](#facebook)
      * [GitHub](#github)


## Guide


### Supported Providers

| Config key | Description                            |
|------------|----------------------------------------|
| `local`    | Local (valid email address & password) |
| `google`   | Google                                 |
| `facebook` | Facebook                               |
| `github`   | GitHub                                 |


### Set up

#### Obtain Callback URL

The URL for each provider will depend on:
  * Your web site is running HTTP or HTTPS (HTTPS is highly recommended)
  * Authentication URI configured in Members
  * OAuth provider's name 

e.g. using default routes for the domain `example.com`, and configuring
GitHub as the OAuth provider:

```
https://example.com/authentication?provider=GitHub
```

#### Obtain Provider Keys

All required providers must be both configured, **and enabled**, under the `providers:`
configuration key.

OAuth2 providers will also fail to work if valid `client_id` and 
`client_secret` keys are not set.

This will vary from provider to provider, and the details and steps change too
often to reliably document here.

For more information see:
  * Google — https://console.developers.google.com/
  * Facebook — https://developers.facebook.com/apps/
  * GitHub — https://github.com/settings/developers

#### Configuration

Each provider under the `providers:` key should have the following 
configuration block, with `provider_name:` being one of the supported provider
config keys.


```yaml
providers:
    provider_name:             # [Required] Change to match provider name key 
        label:
            sign_in:           # string  — Default value for this provider's login button 
            associate:         # string  — Default value for this provider's account association button
        enabled:               # boolean — [Required] Setting to true enables provider 
        keys:
            client_id:         # string  — [Required] Public key from OAuth2 provider
            client_secret:     # string  — [Required] Private key from OAuth2 provider
        scopes: [ ]            # array   — [Required] OAUth2 copes that this site will require access to
```

##### Local (valid email address & password)

```yaml
providers:
    local:
        label:
            sign_in: Sign in with a local account
            associate: --not applicable--
        enabled: false
        keys:
            client_id: localdefault
            client_secret: localdefault
        scopes: [ user ]
```

##### Google

```yaml
providers:
    google:
        label:
            sign_in: Sign in with Google
            associate: Add your Google account
        enabled: false
        keys:
            client_id:
            client_secret:
        scopes: [ openid, profile, email ]
```

##### Facebook

```yaml
providers:
    facebook:
        label:
            sign_in: Sign in with Facebook
            associate: Add your Facebook account
        enabled: false
        keys:
            client_id:
            client_secret:
        scopes: [ email ]
```

##### GitHub

```yaml
providers:
    github:
        label:
            sign_in: Sign in with GitHub
            associate: Add your GitHub account
        enabled: false
        keys:
            client_id:
            client_secret:
        scopes: [ user ]
```
