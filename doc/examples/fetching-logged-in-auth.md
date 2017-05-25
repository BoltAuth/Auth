Example: Fetching logged in auth
----------------------------------


```php
        // Get $app

        /** @var \Bolt\Extension\BoltAuth\Auth\AccessControl\Session $authSession */
        $authSession = $app['auth.session'];
        if ($authSession->hasAuthorisation()) {
            /** @var \Bolt\Extension\BoltAuth\Auth\AccessControl\Authorisation $auth */
            $auth = $authSession->getAuthorisation();
            /** @var \Bolt\Extension\BoltAuth\Auth\Storage\Entity\Account $account */
            $account = $auth->getAccount();
            /** @var string $authId */
            $authId = $account->getGuid();
        }
```
