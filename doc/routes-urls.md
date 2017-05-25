Routes & URLs
-------------

## Index

  * [Base Routes](#base-routes)
    * [Configuration](#configuration)
  * [Authentication Routes](#authentication-routes)
    * [Configuration](#configuration-1)
  * [Authentication Callback Route](#authentication-callback-route)
    * [Configuration](#configuration-2)
  * [Auth Routes](#auth-routes)
    * [Configuration](#configuration-3)

## Guide

### Base Routes

A default configuration will use the base URI:

  * `/authentication` — For login, logout, & password resets
  * `/auth` — For registration, editing, & viewing profiles

#### Configuration

The base `urls:` config key values each take a alpha string of characters. 

```yaml
urls:
    authenticate: authentication
    auth: auth
```

### Authentication Routes

Login, logout and password resets occur on the following routes:

| URI                      | Description                       |
|--------------------------|-----------------------------------|
| `/{authenticate}/login`  | Login (password & OAuth)          |
| `/{authenticate}/logout` | Logout                            |
| `/{authenticate}/reset`  | Password reset (request & submit) |

Where:
 * `{authenticate}` — value set for the `authenticate:` key in the `url:`
    config setting.

#### Configuration

None presently. 

```yaml
```
  
### Authentication Callback Route

OAuth2 authentication provider callback URI. 

| URI                                                       | Description    |
|-----------------------------------------------------------|----------------|
| `/{authenticate}/oauth2/callback?provider={providerName}` | Callback route |

Where:
 * `{authenticate}` — value set for the `authenticate:` key in the `url:`
    config setting.
 * `{providerName}` — Name of the provider making the callback


#### Configuration

This is not configurable as such. However, when setting up the OAuth2 keys your
provider will require you to provide a valid callback URL.

The `{providerName}` value must match the `provider_name` key for that
provider's callback. 

```yaml
providers:
    provider_name:
        …
```

An example URL would be:

```
https://example.com/authentication/oauth2/callback?provider=google
```

**NOTE:** Change the scheme (`http` or `https`), and domain, to match the site
deployed on. 


### Auth Routes

Auth profile editing, viewing, and registration, occurs on the following
routes:

| URI                           | Description     |
|-------------------------------|-----------------|
| `/{auth}/profile/register` | Registration    |
| `/{auth}/profile/edit`     | Profile editing |
| `/{auth}/profile/view`     | Profile viewing |

Where:
 * `{auth}` — value set for the `auth:` key in the `url:` config setting.

#### Configuration

None presently. 

```yaml
```
