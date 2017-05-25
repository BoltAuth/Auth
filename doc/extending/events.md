Extending: Events
-----------------


## Index

  * [Dispatched Events](#dispatched-events)
    * [Login](#login)
    * [Login completion event](#login-completion-event)
    * [Auth Role Data](#auth-role-data)
      * [Role set up](#role-set-up)
    * [Profile](#profile)
      * [New profile registration](#new-profile-registration)
      * [New profile verification](#new-profile-verification)
      * [Save to storage](#save-to-storage)
    * [Notifications](#notifications)
      * [Notification email pre-send.](#notification-email-pre-send)
      * [Notification email send failure.](#notification-email-send-failure)
      * [Password Reset](#password-reset)
  * [Form Builder](#form-builder)
  * [Controller Exceptions](#controller-exceptions)
  * [Event Classes](#event-classes)
    * [AuthLoginEvent](#authloginevent)
    * [AuthNotificationEvent](#authnotificationevent)
    * [AuthNotificationFailureEvent](#authnotificationfailureevent)
    * [AuthProfileEvent](#authprofileevent)
    * [AuthRolesEvent](#authrolesevent)
    * [AuthLoginEvent](#authloginevent-1)
    * [FormBuilderEvent](#formbuilderevent)
    * [ExceptionEvent](#exceptionevent)


## Guide

### Dispatched Events

#### Login

#### Login completion event 

Dispatched after all checks are validated.

| Event  |     |
| -------| --- |
| Name   | `Bolt\Extension\BoltAuth\Auth\Event\AuthEvents::AUTH_LOGIN`
| Object | `Bolt\Extension\BoltAuth\Auth\Event\AuthLoginEvent`


#### Auth Role Data

##### Role set up

Auth access level roles.

| Event  |     |
| -------| --- |
| Name   | `Bolt\Extension\BoltAuth\Auth\Event\AuthEvents::AUTH_ROLE`
| Object | `Bolt\Extension\BoltAuth\Auth\Event\AuthRolesEvent`


#### Profile

##### New profile registration

| Event  |     |
| -------| --- |
| Name   | `Bolt\Extension\BoltAuth\Auth\Event\AuthEvents::AUTH_PROFILE_REGISTER`
| Object | `Bolt\Extension\BoltAuth\Auth\Event\AuthProfileEvent`


##### New profile verification

Dispatched at successful verification of a new account.

| Event  |     |
| -------| --- |
| Name   | `Bolt\Extension\BoltAuth\Auth\Event\AuthEvents::AUTH_PROFILE_VERIFY`
| Object | `Bolt\Extension\BoltAuth\Auth\Event\AuthProfileEvent`


##### Save to storage

Prior to save of a profile form.

| Event  |     |
| -------| --- |
| Name   | `Bolt\Extension\BoltAuth\Auth\Event\AuthEvents::AUTH_PROFILE_PRE_SAVE`
| Object | `Bolt\Extension\BoltAuth\Auth\Event\AuthProfileEvent`


Post-save of a profile form.

| Event  |     |
| -------| --- |
| Name   | `Bolt\Extension\BoltAuth\Auth\Event\AuthEvents::AUTH_PROFILE_POST_SAVE`
| Object | `Bolt\Extension\BoltAuth\Auth\Event\AuthProfileEvent`


#### Notifications

##### Notification email pre-send.

| Event  |     |
| -------| --- |
| Name   | `Bolt\Extension\BoltAuth\Auth\Event\AuthEvents::AUTH_NOTIFICATION_PRE_SEND`
| Object | `Bolt\Extension\BoltAuth\Auth\Event\AuthNotificationEvent`


##### Notification email send failure.

| Event  |     |
| -------| --- |
| Name   | `Bolt\Extension\BoltAuth\Auth\Event\AuthEvents::AUTH_NOTIFICATION_FAILURE`
| Object | `Bolt\Extension\BoltAuth\Auth\Event\AuthNotificationFailureEvent`


##### Password Reset

Account password reset request.

| Event  |     |
| -------| --- |
| Name   | `Bolt\Extension\BoltAuth\Auth\Event\AuthEvents::AUTH_PROFILE_RESET`
| Object | `Bolt\Extension\BoltAuth\Auth\Event\AuthNotificationEvent`


### Form Builder

Dispatched when a builder is created for a Auth form.

| Event  |     |
| -------| --- |
| Name   | `Bolt\Extension\BoltAuth\Auth\Event\FormBuilderEvent::BUILD`
| Object | `Bolt\Extension\BoltAuth\Auth\Event\FormBuilderEvent`




### Controller Exceptions

- src/Controller/Authentication.php
ExceptionEvent::ERROR, new ExceptionEvent($e));

| Event  |     |
| -------| --- |
| Name   | `Bolt\Extension\BoltAuth\Auth\Event\ExceptionEvent::ERROR`
| Object | `Bolt\Extension\BoltAuth\Auth\Event\ExceptionEvent`



## Event Classes 

### AuthLoginEvent

| Property           | Type |
| ------------------ | ---- |
| `$account`         | `Bolt\Extension\BoltAuth\Auth\Storage\Entity\Account`


### AuthNotificationEvent

| Property           | Type |
| ------------------ | ---- |
| `$message`         | `Swift_Mime_Message`


### AuthNotificationFailureEvent

| Property           | Type |
| ------------------ | ---- |
| `$message`         | `Swift_Mime_Message`
| `$exception`       | `Swift_SwiftException`


### AuthProfileEvent

| Property           | Type |
| ------------------ | ---- |
| `$account`         | `Bolt\Extension\BoltAuth\Auth\Storage\Entity\Account`
| `$metaEntities`    | `Bolt\Extension\BoltAuth\Auth\Storage\Entity\AccountMeta[]`
| `$metaEntityNames` | `string[]`


### AuthRolesEvent

| Property           | Type |
| ------------------ | ---- |
| `$roles`           | `Bolt\Extension\BoltAuth\Auth\AccessControl\Role[]`


### AuthLoginEvent

| Property           | Type |
| ------------------ | ---- |
| `$account`         | `Bolt\Extension\BoltAuth\Auth\Storage\Entity\Account`


### FormBuilderEvent

| Property           | Type |
| ------------------ | ---- |
| `$name`            | `string`
| `$type`            | `Symfony\Component\Form\FormTypeInterface`
| `$entity`          | `Bolt\Extension\BoltAuth\Auth\Form\Entity\EntityInterface`
| `$entityClass`     | `string`


### ExceptionEvent

| Property           | Type |
| ------------------ | ---- |
| `$exception`       | `\Exception`
