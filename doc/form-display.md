Form Display
------------

## Index

  * [Template Overrides](#template-overrides)
  * [Labels](#labels)
  * [Placeholders](#placeholders)
  * [Add-ons](#add-ons)

## Guide

The `forms:` key has 4 parameter keys:

```yaml
forms:
    templates:
    labels:
    placeholders:
    addons:
```


| Key             | Description |
|-----------------|-------------|
| `templates:`    | Twig template paths & names
| `labels:`       | Default labels to be displayed for certain fields (also configurable in template)
| `placeholders:` | Placeholder text for certain fields 
| `addons:`       | UI add-ons. Currently only `bolt/zocial-icons` is supported


### Template Overrides

Twig template overrides can be specified via the `templates:` sub-key of the 
`forms:` key. These parameters allow the overriding of certain, or all,
templates use to render Auth forms.

#### Configuration

Each parameter should be a *relative path* to a Twig template file in your
theme directory.

**NOTE:** only the templates you want/need to override should be specified. 

```yaml
    templates:
        profile:
            parent: path/to/profile/_profile.twig
            associate: path/to/profile/register.twig
            edit: path/to/profile/edit.twig
            register: path/to/profile/register.twig
            verify: path/to/profile/verify.twig
            view: path/to/profile/view.twig
        authentication:
            parent: path/to/authentication/_authentication.twig
            associate: path/to/authentication/associate.twig
            login: path/to/authentication/login.twig
            logout: path/to/authentication/logout.twig
            recovery: path/to/authentication/recovery.twig
        error:
            parent: path/to/error/_auth_error.twig
            error: path/to/error/auth_error.twig
        feedback:
            feedback: path/to/feedback/feedback.twig
        verification:
            subject: path/to/verification/subject.twig
            html: path/to/verification/html.twig
            text: path/to/verification/text.twig
        recovery:
            subject: path/to/recovery/subject.twig
            html: path/to/recovery/html.twig
            text: path/to/recovery/text.twig
```

You can find the installed defaults in the Auth source directory:

```
{site root directory}/extensions/vendor/bolt/auth/
```

### Labels

HTML input field label *defaults* can be specified via the `labels:` sub-key of 
the `forms:` key. 

#### Configuration

```yaml
    labels:
        login: Login
        logout: Logout
        displayname: Public Name
        email: Email Address
        password_first: Password
        password_second: Repeat Password
        profile_save: Save & Continue
```


These labels can also be set in Twig your templates as an attribute to the 
Twig function `form_row()`, e.g. to set the label "Submit me" on the `submit`
button on the form called `form_name`:

```
{{ form_row(form_name.submit, { 'label': 'Submit me' }) }}
```


### Placeholders

HTML input field placeholder values can be specified via the `placeholders:` 
sub-key of the `forms:` key. 

Placeholder values are a hint to the user of what can be entered in the input
field, and must not contain carriage returns or line-feeds.

**NOTE:** Do not use the placeholder attribute instead of a <label> element, as
their purposes are different: the `<label>` attribute describes the role of the 
form element; that is, it indicates what kind of information is expected, the 
placeholder attribute is a hint about the format the content should take. 

**NOTE:** There are cases in which the `placeholder` attribute is never
displayed to the user, so the form must be understandable without it.

#### Configuration

```yaml
    placeholders:
        displayname: The name you would like to display publicly…
        email: Your email address…
        password_first: Enter your password…
        password_second: Repeat the above password…
```


### Add-ons

Auth has very limited ability to be extended visually by, currently it
supports the Zocial Icons extension that gives some additional CSS handling
to form buttons.

#### Configuration

```yaml
    addons:
        zocial: true
```

Requires the `bolt/zocial-icons` extension to be installed.
