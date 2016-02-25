Membership Extension for Bolt
-----------------------------

To be completed!

Twig Functions
==============

The following Twig functions are available:
  * `{{ is_member() }}` 
  * `{{ member_has_role() }}`
  * `{{ member_providers() }}`
  * `{{ members_auth_switcher([true | false]) }}`
  * `{{ members_auth_login([true | false]) }}`
  * `{{ members_auth_logout([true | false]) }}`


Extending Members
=================

Members is event driven and provides a number of dispatcher that your own extension can listen for, and affect the 
behaviour of different parts of the Members extension. 

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
        $event->setMetaFields($fields);
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

