Example: Menu Entries
---------------------

Below is an example of a menu named "`members`" that you can use as a base to
add to your `app/config/menu.yml` file for your site.

```yaml
members:
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
    label: Membership Profiles
    link: "#"
    submenu:
      -
        label: Registration
        path: membership/profile/register
      -
        label: View your profile
        path: membership/profile/view
      -
        label: Edit your profile
        path: membership/profile/edit
```
