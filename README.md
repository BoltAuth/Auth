Membership Extension for Bolt
=============================

Members provides functionality to enable restricted access to parts of Bolt's 
front end.

There are a couple of things you need to take care of in order to set up Members 
and membership access:
  * Enable at least one provider (can be "Local" email address & password, or remote OAuth2 provider)
  * Edit your templates to secure sections of your site (there are a number of defaults templates you can use included)


Twig Templates
--------------

### Authentication & Registration

The fastest way to get moving is to implement parent templates that use Twig blocks. The included partials will render
their content in these blocks.

To do this, edit `app/config/extensions/members.bolt.yml` to specify relative paths to these files in your theme 
directory, e.g.:

```yaml
templates:
    profile:
        parent: profile.twig
    authentication:
        parent: authentication.twig
```

For profile registration, editing, and viewing, the parent template (shown as `profile.twig` above) just needs the 
following block where you would like to render content: 

```twig
    {% block members %}
    {% endblock members %}
```

For login, logout, and password recovery (shown as `authentication.twig` above) just needs the following block where 
you would like to render content:

```twig
    {% block login %}
    {% endblock login %}

    {% block logout %}
    {% endblock logout %}

    {% block recovery %}
    {% endblock recovery %}
```

For more precise control over partials, the following Twig functions give you shortcuts you can use for much of the 
functionality:

  * `{{ member_providers() }}`
  * `{{ members_auth_switcher([true | false]) }}`
  * `{{ members_auth_associate() }}`
  * `{{ members_auth_login([true | false]) }}`
  * `{{ members_auth_logout([true | false]) }}`
  * `{{ members_profile_edit() }}`
  * `{{ members_profile_register() }}`


### Member Only Site Access

The following Twig functions are available:
  * `{{ is_member() }}` — Returns `true` if the broswer session is logged in
  * `{{ member() }}` — Returns an entity array of the member's account information
  * `{{ member_meta() }}` —  Returns an entity array of the member's meta data (if any) 
  * `{{ member_has_role('role_name') }}` — Returns `true` if the logged in  

For example:

```twig
    {% set member = member() %}
    {% if is_member() and member_has_role('participant') %}
        Hello, {{ member.displayname }}. You were last seen at {{ member.lastseen }},
        logging on from {{ member.lastip }}, and your email is {{ member.email }}
    {% endif %}
```


### Roles

You can define roles to members in `app/config/extensions/members.bolt.yml` like so:

```yaml
roles:
    member:
        admin: Administrator
        participant: Participant
```

Other extensions can also add roles relevant to their functionality via events (see below).

Extending Members
-----------------

Members is event driven and provides a number of dispatcher that your own extension can listen for, and affect the 
behaviour of different parts of the Members extension. 

There is an example repository called [Members Addon Example](https://github.com/bolt/Members-Addon-Example) that shows 
some of the available behaviour.

### Adding Meta Fields

```php
use Bolt\Extension\Bolt\Members\Event\MembersEvents;
use Bolt\Extension\Bolt\Members\Event\MembersProfileEvent;
```

```php
    protected function subscribe(EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addListener(MembersEvents::MEMBER_PROFILE_PRE_SAVE, [$this, 'onProfileSave']);
    }

    /**
     * @param MembersProfileEvent $event
     */
    public function onProfileSave(MembersProfileEvent $event)
    {
        $fields = [
            'website',
            'address_street',
            'address_street_meta',
            'address_city',
            'address_state',
            'address_country',
            'phone_number',
        ];
        $event->addMetaFieldNames($fields);
    }
```

### Adding Roles 

To add roles to Member via your extension, you can add a listener to the `MembersEvents::MEMBER_ROLE` event.
 
You will need the following use statements at the top of your PHP class file:

```php
use Bolt\Extension\Bolt\Members\AccessControl\Role;
use Bolt\Extension\Bolt\Members\Event\MembersEvents;
use Bolt\Extension\Bolt\Members\Event\MembersRolesEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
```

In your extension's `subscribe()` function you can define the listener and pass in the callback that will be called when
the event is triggered, e.g.

```php
    protected function subscribe(EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addListener(MembersEvents::MEMBER_ROLE, [$this, 'myCallbackRunction']);
    }
```

The callback function will be passed a `MembersRolesEvent` object, you can then use the events `addRole()` function to
add `\Bolt\Extension\Bolt\Members\AccessControl\Role` objects, e.g.

```php
    public function myCallbackRunction(MembersRolesEvent $event)
    {
        $event->addRole(new Role('koala', 'Friendly Koalas'));
        $event->addRole(new Role('dropbear', 'Deady Drop Bears'));
    }
```

The `Role` class takes two parameters, programmatic name and a display name that will be used in the members admin pages
in Bolt's backend admin section.

