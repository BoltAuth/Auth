Access Control
--------------

## Index

  * [Roles](#roles)
  * [Template Elements](#template-elements)
  * [Routes](#routes)


## Guide


### Template Elements

A member's login status can be checked with the `is_member()` function, and the
profile data can be returned via the `member()` function, e.g.:


```twig
    {% if is_member() %}
        {% set member = member() %}

        <p>Hello, {{ member.displayname }}. You were last seen at {{ member.lastseen }},
        logging on from {{ member.lastip }}, and your email is {{ member.email }}</p>
    {% else %}
        <p>Welcome visitor from the Internet!</p>
    {% endif %}
```

### Roles

Roles can be added in one of two ways, either the `roles:` key in the
configuration file, or [via events](extending-adding-roles.md).

To check if a member account has a role, you can use the Twig function
`member_has_role()`. This function takes a string as the parameter.

```twig
    {% if member_has_role('admin') %}
        <h1>Greetings, master!</h1>
    {% endif %}
```


#### Configuration

The `roles:` subkey `member:` takes an associative array of role names
and human readable labels as values, e.g.:

```yaml
roles:
    member:
        admin: Administrator
        participant: Participant
```


### Routes

> *Not currently implemented*
