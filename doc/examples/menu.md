Example: Menu Entries
---------------------

Below is an example of a menu named "`auth`" that you can use as a base to
add to your `app/config/menu.yml` file for your site.

```yaml
auth:
  -
    label: Authentication
    link: "#"
    submenu:
      -
        label: Login
        path: authentication/login
      -
        label: Reset your password
        path: authentication/reset
      -
        label: Logout
        path: authentication/logout
  -
    label: Auth Profiles
    link: "#"
    submenu:
      -
        label: Registration
        path: auth/profile/register
      -
        label: View your profile
        path: auth/profile/view
      -
        label: Edit your profile
        path: auth/profile/edit
```
