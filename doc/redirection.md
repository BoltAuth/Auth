Redirection
-----------

## Index

  * [Setting Custom Redirects](#setting-custom-redirects)
    * [Configuration](#configuration)

## Guide

### Setting Custom Redirects

URLs that a member will be redirected to after login/logout can be set in
configuration via teh `redirects:` key.

If not set, members will be redirected to the referring page after login,
and the homepage after logout.

#### Configuration

The `redirects:` key has two parameters, `login:` & `logout`. Each take either
a relative or absolute URL. 

```yaml
redirects:
    login:  /membership/profile
    logout: /authentication/login
```
