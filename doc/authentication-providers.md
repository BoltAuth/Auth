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

| Key        | Sub key          | Type    | Description |
|------------|------------------|---------|-------------|
| `enabled:` |                  | boolean | Setting to true enables provider
| `label:`   | `sign_in:`       | string  | Default value for this provider's login button
|            | `associate:`     | string  | Default value for this provider's account association button
| `keys:`    | `client_id:`     | string  | Public key from OAuth2 provider
|            | `client_secret:` | string  | Private key from OAuth2 provider
| `scopes:`  |                  | array   | OAUth2 scopes to request


##### Local (valid email address & password)

```yaml
providers:
    local:
        enabled:
        keys:
            client_id: --- set to random string ---
            client_secret: --- set to random string ---
        scopes: [ user ]
```

##### Google

```yaml
providers:
    google:
        enabled: true
        keys:
            client_id:
            client_secret:
        scopes: [ openid, profile, email ]
```

##### Facebook

```yaml
providers:
    facebook:
        enabled: true
        keys:
            client_id:
            client_secret:
        scopes: [ email ]
```

##### GitHub

```yaml
providers:
        enabled: true
        keys:
            client_id:
            client_secret:
        scopes: [ user ]
```
