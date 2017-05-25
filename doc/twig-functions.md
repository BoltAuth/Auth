Twig Functions
--------------

## Index

  * [Authentication](#authentication)
    * [Renderers](#renderers)
    * [URL Generators](#url-generators)
  * [Profiles](#profiles)
    * [Renderers](#renderers-1)
    * [URL Generators](#url-generators-1)
  * [Restricting Access](#restricting-access)
  * [Auth Data](#auth-data)

## Guide


### Authentication

#### Renderers 

| Function                                 | Parameter | Description |
|------------------------------------------|-----------|-------------|
| `{{ auth_auth_login() }}`             | string    | Render the login template
| `{{ auth_auth_logout() }}`            | string    | Render the logout template
| `{{ auth_auth_switcher() }}`          | string    | Render either the login or logout template depending on login state
| `{{ auth_auth_associate() }}`         | string    | Render the OAuth account association template


#### URL Generators 

| Function                                 | Parameter | Description |
|------------------------------------------|-----------|-------------|
| `{{ auth_link_auth_login() }}`        | integer   | Link to login
| `{{ auth_link_auth_logout() }}`       | integer   | Link to logout
| `{{ auth_link_auth_reset() }}`        | integer   | Link to password reset

All functions default to `2` if nothing supplied.

Valid parameter values:
 * `0` — Generates an absolute URL, e.g. "http://example.com/dir/file".
 * `1` — Generates an absolute path, e.g. "/dir/file".
 * `2` — Generates a relative path based on the current request path, e.g. "../parent-file".
 * `4` — Generates a network path, e.g. "//example.com/dir/file". Such reference reuses the current scheme but specifies the host.

### Profiles

#### Renderers 

| Function                                 | Parameter | Description |
|------------------------------------------|-----------|-------------|
| `{{ auth_profile_edit() }}`           | string    | Render the profile edit template
| `{{ auth_profile_register() }}`       | string    | Render the profile registration template


#### URL Generators

| Function                                 | Parameter | Description |
|------------------------------------------|-----------|-------------|
| `{{ auth_link_profile_edit() }}`      | integer   | Link to profile edit
| `{{ auth_link_profile_register() }}`  | integer   | Link to profile registration
                                            
All functions default to `2` if nothing supplied.

Valid parameter values:
 * `0` — Generates an absolute URL, e.g. "http://example.com/dir/file".
 * `1` — Generates an absolute path, e.g. "/dir/file".
 * `2` — Generates a relative path based on the current request path, e.g. "../parent-file".
 * `4` — Generates a network path, e.g. "//example.com/dir/file". Such reference reuses the current scheme but specifies the host.



### Restricting Access

| Function                             | Description |
|--------------------------------------|-------------|
| `{{ is_auth() }}`                  | Returns `true` if the broswer session is logged in


### Auth Data


| Function                             | Parameter | Description |
|--------------------------------------|-----------|-------------|
| `{{ auth() }}`                     |           | Returns an entity array of the auth's account information
| `{{ auth_meta() }}`                |           | Returns an entity array of the auth's meta data (if any) 
| `{{ auth_has_role() }}`            | string    | Returns `true` if the logged in user has the given Auth roles  
| `{{ auth_providers() }}`           |           | Returns an array of providers connected to the logged in user account

