[BETA] Members Extension for Bolt
=================================

Members enables a front-end client membership management interface and API for Bolt.

Dependencies
------------

Members requires the following Bolt extensions to be installed and configured:
  * Client Login - `bolt/clientlogin`
  
Please see the Client Login documentation for more information on configuring this for your site.

Initial Set Up
--------------

#### Tables

Now you need to update Bolt's database to add missing tables for Members to function.

Navigate to **Configuration/Check database** and Bolt will tell you what tables are missing, you just 
need to do a normal update.

#### Base Template

Members will work out-of-the-box at this point if you're using the `base-2014` theme, or one that has 
a `_header.twig`, `_footer.twig` and `_aside.twig`.

To override this, you simply need to create a file called `members.twig` in your theme's directory and 
include the following block statement where you want the forums rendered:

```
{% block members %}
{% endblock members %}
```

**NOTE:** The file name for the base template can be set in your `Members.yml` file:

```
templates:
  parent: members.twig
```

#### Individual Templates (optional)

You can override the individual Twig templates for the following:
  * New user registration (defaults to `members_register.twig`)
  * Existing user profile editing (defaults to `members_profile_edit.twig`)
  * Existing user profile viewing (defaults to `members_profile_view.twig`)

You can copy these files to your theme directory and edit them to better match your desired layout.

For the forums templates, at a minimum you should ensure the following exists in any new custom twig 
files you create:

  * To inherit the `members.twig` template 
```
{% extends twigparent %}
```

  * The block that layout will be rendered in by Twig
```
{% block members %}
   <!-- Your HTML & Twig here -->
{% endblock members %}
```

**NOTE:** To use different names, and/or subdirectories in your theme directory, see the `templates:` 
section in your `Members.yml` file.

Authentication & Twig Functions
-------------------------------

Members supplies some Twig functions for use in your templates:
  * `member(id, meta)`
  * `memberauth()` 
  * `hasRole(role)`

The `memberauth()` function returns a boolean `FALSE` is the current session is unauthenticated/invalid, 
or a members ID if they are logged in.

An example of how can be used:

```
{% if memberauth() %}
    {{ displaylogout(true) }}
{% else %}
    {{ displaylogin(true) }}
{% endif %}
```

The `member()` function takes two **optional** parameters:
 * `id`   - [integer] ID of member to look up
 * `meta` - [boolean] Return user metadata

Members allows, mostly via the API, to store additional metadata about a member that, if exists, will
be returned by the `meta` parameters being set to `true`

Member Pages
------------

#### Logged in user

```
http://example.com/members/profile
```

### User profile by ID

```
http://example.com/members/profile/{id}
```

API
---

Members registers its API as a Symfony Application service and can be accessed via  
`$this->app['members']`
 

```php
// Check to see if the current session is an active member and retrive their ID
$id = $this->app['members']->isAuth();

// Get a member record by ID
$record = $this->app['members']->getMember('id', $value);

// Get a member record by email
$record = $this->app['members']->getMember('email', $value);

// Get all of a members metadata records
$meta = $this->app['members']->getMemberMeta($id)

// Get a members avatar metadata record
$avatar = $this->app['members']->getMemberMeta($id, 'avatar')

// Create/update a meta value called 'something'
$this->app['members']->updateMemberMeta($id, 'something', $value)
 
```
