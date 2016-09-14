Extending: Adding Roles
-----------------------

## Index

  * [Extension Loader Class](#extension-loader-class)
  * [Callback Function](#callback-function)


## Guide

Roles can be added in one of two ways, either the `roles:` key in the
configuration file, or via events.

This guide explains the required steps in the process to creating an extension
that adds custom roles via event.


### Extension Loader Class

To add roles to Member via your extension, you can add a listener to the
`MembersEvents::MEMBER_ROLE` event.
 
You will need the following use statements at the top of your PHP class file:

```php
use Bolt\Extension\Bolt\Members\AccessControl\Role;
use Bolt\Extension\Bolt\Members\Event\MembersEvents;
use Bolt\Extension\Bolt\Members\Event\MembersRolesEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
```

In your extension's `subscribe()` function you can define the listener and
pass in the callback that will be called when the event is triggered, e.g.

```php
    protected function subscribe(EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addListener(MembersEvents::MEMBER_ROLE, [$this, 'myCallbackRunction']);
    }
```

### Callback Function

The callback function will be passed a `MembersRolesEvent` object, you can
then use the events `addRole()` function to add `\Bolt\Extension\Bolt\Members\AccessControl\Role` 
objects, e.g.

```php
    public function myCallbackRunction(MembersRolesEvent $event)
    {
        $event->addRole(new Role('koala', 'Friendly Koalas'));
        $event->addRole(new Role('dropbear', 'Deady Drop Bears'));
    }
```

The `Role` class takes two parameters, programmatic name and a display name
that will be used in the members admin pages in Bolt's backend admin section.

