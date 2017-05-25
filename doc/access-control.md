Access Control
--------------

## Index

  * [Roles](#roles)
  * [Template Elements](#template-elements)
  * [Routes](#routes)


## Guide


### Template Elements

A auth's login status can be checked with the `is_auth()` function, and the
profile data can be returned via the `auth()` function, e.g.:


```twig
    {% if is_auth() %}
        {% set auth = auth() %}

        <p>Hello, {{ auth.displayname }}. You were last seen at {{ auth.lastseen }},
        logging on from {{ auth.lastip }}, and your email is {{ auth.email }}</p>
    {% else %}
        <p>Welcome visitor from the Internet!</p>
    {% endif %}
```

### Roles

Roles can be added in one of two ways, either the `roles:` key in the
configuration file, or [via events](extending-adding-roles.md).

To check if a auth account has a role, you can use the Twig function
`auth_has_role()`. This function takes a string as the parameter.

```twig
    {% if auth_has_role('admin') %}
        <h1>Greetings, master!</h1>
    {% endif %}
```


#### Configuration

The `roles:` subkey `auth:` takes an associative array of role names
and human readable labels as values, e.g.:

```yaml
roles:
    auth:
        admin: Administrator
        participant: Participant
```


### Routes

> *Not currently implemented*
