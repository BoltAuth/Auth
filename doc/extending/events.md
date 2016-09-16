Extending: Events
-----------------


## Index

  * [Dispatched Events](#dispatched-events)
    * [Login](#login)
    * [Login completion event](#login-completion-event)
    * [Membership Role Data](#membership-role-data)
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
    * [MembersLoginEvent](#membersloginevent)
    * [MembersNotificationEvent](#membersnotificationevent)
    * [MembersNotificationFailureEvent](#membersnotificationfailureevent)
    * [MembersProfileEvent](#membersprofileevent)
    * [MembersRolesEvent](#membersrolesevent)
    * [MembersLoginEvent](#membersloginevent-1)
    * [FormBuilderEvent](#formbuilderevent)
    * [ExceptionEvent](#exceptionevent)


## Guide

### Dispatched Events

#### Login

#### Login completion event 

Dispatched after all checks are validated.

| Event  |     |
| -------| --- |
| Name   | `Bolt\Extension\Bolt\Members\Event\MembersEvents::MEMBER_LOGIN`
| Object | `Bolt\Extension\Bolt\Members\Event\MembersLoginEvent`


#### Membership Role Data

##### Role set up

Membership access level roles.

| Event  |     |
| -------| --- |
| Name   | `Bolt\Extension\Bolt\Members\Event\MembersEvents::MEMBER_ROLE`
| Object | `Bolt\Extension\Bolt\Members\Event\MembersRolesEvent`


#### Profile

##### New profile registration

| Event  |     |
| -------| --- |
| Name   | `Bolt\Extension\Bolt\Members\Event\MembersEvents::MEMBER_PROFILE_REGISTER`
| Object | `Bolt\Extension\Bolt\Members\Event\MembersProfileEvent`


##### New profile verification

Dispatched at successful verification of a new account.

| Event  |     |
| -------| --- |
| Name   | `Bolt\Extension\Bolt\Members\Event\MembersEvents::MEMBER_PROFILE_VERIFY`
| Object | `Bolt\Extension\Bolt\Members\Event\MembersProfileEvent`


##### Save to storage

Prior to save of a profile form.

| Event  |     |
| -------| --- |
| Name   | `Bolt\Extension\Bolt\Members\Event\MembersEvents::MEMBER_PROFILE_PRE_SAVE`
| Object | `Bolt\Extension\Bolt\Members\Event\MembersProfileEvent`


Post-save of a profile form.

| Event  |     |
| -------| --- |
| Name   | `Bolt\Extension\Bolt\Members\Event\MembersEvents::MEMBER_PROFILE_POST_SAVE`
| Object | `Bolt\Extension\Bolt\Members\Event\MembersProfileEvent`


#### Notifications

##### Notification email pre-send.

| Event  |     |
| -------| --- |
| Name   | `Bolt\Extension\Bolt\Members\Event\MembersEvents::MEMBER_NOTIFICATION_PRE_SEND`
| Object | `Bolt\Extension\Bolt\Members\Event\MembersNotificationEvent`


##### Notification email send failure.

| Event  |     |
| -------| --- |
| Name   | `Bolt\Extension\Bolt\Members\Event\MembersEvents::MEMBER_NOTIFICATION_FAILURE`
| Object | `Bolt\Extension\Bolt\Members\Event\MembersNotificationFailureEvent`


##### Password Reset

Account password reset request.

| Event  |     |
| -------| --- |
| Name   | `Bolt\Extension\Bolt\Members\Event\MembersEvents::MEMBER_PROFILE_RESET`
| Object | `Bolt\Extension\Bolt\Members\Event\MembersNotificationEvent`


### Form Builder

Dispatched when a builder is created for a Members form.

| Event  |     |
| -------| --- |
| Name   | `Bolt\Extension\Bolt\Members\Event\FormBuilderEvent::BUILD`
| Object | `Bolt\Extension\Bolt\Members\Event\FormBuilderEvent`




### Controller Exceptions

- src/Controller/Authentication.php
ExceptionEvent::ERROR, new ExceptionEvent($e));

| Event  |     |
| -------| --- |
| Name   | `Bolt\Extension\Bolt\Members\Event\ExceptionEvent::ERROR`
| Object | `Bolt\Extension\Bolt\Members\Event\ExceptionEvent`



## Event Classes 

### MembersLoginEvent

| Property           | Type |
| ------------------ | ---- |
| `$account`         | `Bolt\Extension\Bolt\Members\Storage\Entity\Account`


### MembersNotificationEvent

| Property           | Type |
| ------------------ | ---- |
| `$message`         | `Swift_Mime_Message`


### MembersNotificationFailureEvent

| Property           | Type |
| ------------------ | ---- |
| `$message`         | `Swift_Mime_Message`
| `$exception`       | `Swift_SwiftException`


### MembersProfileEvent

| Property           | Type |
| ------------------ | ---- |
| `$account`         | `Bolt\Extension\Bolt\Members\Storage\Entity\Account`
| `$metaEntities`    | `Bolt\Extension\Bolt\Members\Storage\Entity\AccountMeta[]`
| `$metaEntityNames` | `string[]`


### MembersRolesEvent

| Property           | Type |
| ------------------ | ---- |
| `$roles`           | `Bolt\Extension\Bolt\Members\AccessControl\Role[]`


### MembersLoginEvent

| Property           | Type |
| ------------------ | ---- |
| `$account`         | `Bolt\Extension\Bolt\Members\Storage\Entity\Account`


### FormBuilderEvent

| Property           | Type |
| ------------------ | ---- |
| `$name`            | `string`
| `$type`            | `Symfony\Component\Form\FormTypeInterface`
| `$entity`          | `Bolt\Extension\Bolt\Members\Form\Entity\EntityInterface`
| `$entityClass`     | `string`


### ExceptionEvent

| Property           | Type |
| ------------------ | ---- |
| `$exception`       | `\Exception`
