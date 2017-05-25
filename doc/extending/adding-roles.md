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

To add roles to Auth via your extension, you can add a listener to the
`AuthEvents::AUTH_ROLE` event.
 
You will need the following use statements at the top of your PHP class file:

```php
use Bolt\Extension\BoltAuth\Auth\AccessControl\Role;
use Bolt\Extension\BoltAuth\Auth\Event\AuthEvents;
use Bolt\Extension\BoltAuth\Auth\Event\AuthRolesEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
```

In your extension's `subscribe()` function you can define the listener and
pass in the callback that will be called when the event is triggered, e.g.

```php
    protected function subscribe(EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addListener(AuthEvents::AUTH_ROLE, [$this, 'myCallbackRunction']);
    }
```

### Callback Function

The callback function will be passed a `AuthRolesEvent` object, you can
then use the events `addRole()` function to add `\Bolt\Extension\BoltAuth\Auth\AccessControl\Role` 
objects, e.g.

```php
    public function myCallbackRunction(AuthRolesEvent $event)
    {
        $event->addRole(new Role('koala', 'Friendly Koalas'));
        $event->addRole(new Role('dropbear', 'Deady Drop Bears'));
    }
```

The `Role` class takes two parameters, programmatic name and a display name
that will be used in the auth admin pages in Bolt's backend admin section.

