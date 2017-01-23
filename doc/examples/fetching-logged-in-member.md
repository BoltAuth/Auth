Example: Fetching logged in member
----------------------------------


```php
        // Get $app

        /** @var \Bolt\Extension\Bolt\Members\AccessControl\Session $membersSession */
        $membersSession = $app['members.session'];
        if ($membersSession->hasAuthorisation()) {
            /** @var \Bolt\Extension\Bolt\Members\AccessControl\Authorisation $auth */
            $auth = $membersSession->getAuthorisation();
            /** @var \Bolt\Extension\Bolt\Members\Storage\Entity\Account $account */
            $account = $auth->getAccount();
            /** @var string $memberId */
            $memberId = $account->getGuid();
        }
```
