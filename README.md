# Website Membership Admin

Enhanced website membership plugin for CMS Builder with a full Admin UI.

> **Note:** This plugin only works with CMS Builder, available for download at https://www.interactivetools.com/download/

## About This Plugin

This is an enhanced fork of the websiteMembership plugin (v1.14) by InteractiveTools.com. The original plugin required editing PHP code to change settings. This version adds a complete Admin UI so all settings can be configured through the CMS admin interface.

**Original Plugin:** websiteMembership v1.14 by [InteractiveTools.com](https://www.interactivetools.com)
**Enhanced By:** [Sagentic Web Design](https://www.sagentic.com)

---

## Features

### Core Membership Features (from original plugin)

-   User login with username or email
-   User signup/registration
-   Password reset via email
-   Profile management
-   Content protection (login-required pages)
-   Custom accounts table support
-   Separate website/CMS login sessions

### New in This Fork

-   **Admin UI Dashboard** - Overview of plugin status
-   **Settings Page** - Configure all options without editing code
-   **Help Documentation** - Built-in comprehensive documentation
-   **Required Fields UI** - Select required profile fields with checkboxes

---

## Requirements

-   CMS Builder 3.50 or higher
-   PHP 8.0 or higher

---

## Installation

1. Copy the `websiteMembership` folder to your CMS Builder plugins directory:

    ```
    /path/to/cmsb/plugins/websiteMembership/
    ```

2. The plugin will automatically activate (it's a required system plugin)

3. Navigate to **Plugins > Website Membership Admin > Settings** to configure

---

## Getting Started

Please be aware that this is a fairly advanced plugin. It will take care of the basics and save you a lot of time implementing website membership functions, but adding functionality beyond the basics may require more advanced PHP/MySQL knowledge.

### Step 1: Configure Settings

Go to **Plugins > Website Membership Admin > Settings** and configure your URL paths. The defaults are:

| Setting            | Default Value       |
| ------------------ | ------------------- |
| Login Page URL     | `/entry/`           |
| Signup Page URL    | `/entry/signup.php` |
| Password Reset URL | `/entry/reset.php`  |
| Profile Page URL   | `/profile/`         |

### Step 2: Generate Page Code

Go to **Admin > Code Generator > Website Membership** and create each of the membership pages.

---

## Configuration

All settings are configured through the Admin UI at **Plugins > Website Membership Admin > Settings**.

### URL Settings

| Setting            | Description               | Default             |
| ------------------ | ------------------------- | ------------------- |
| Login Page URL     | Path to login form        | `/entry/`           |
| Signup Page URL    | Path to registration page | `/entry/signup.php` |
| Password Reset URL | Path to password recovery | `/entry/reset.php`  |
| Profile Page URL   | Path to user profile      | `/profile/`         |
| Post-Login URL     | Redirect after login      | `/`                 |
| Post-Logoff URL    | Redirect after logout     | `/`                 |

### Account Settings

| Setting        | Description                             | Default    |
| -------------- | --------------------------------------- | ---------- |
| Accounts Table | Database table for user accounts        | `accounts` |
| Separate Login | Allow simultaneous website/CMS sessions | Disabled   |

### Required Profile Fields

Select which fields users must complete. Users with missing required fields are redirected to the profile page.

---

## Creating Membership Pages

### Creating a Sign-Up Form

1. Create a "Signup / Create an account" page in the Code Generator and save it with the recommended filename
2. Access the page through your web browser and try creating a user
3. Confirm that the new user shows up under "User Accounts" in the CMS
4. To add additional fields, copy the existing code and insert your new fieldname in: error checking, add user (mysql insert code), and HTML form fields
5. If you want newly created users to have access to the CMS, set `$setAccessRights` to `true` and update the code under that line

### Creating a Login Page

1. Create a "Login Page" in the Code Generator and save it with the recommended filename
2. Test logging in with an invalid username to ensure error messages display correctly
3. Test the sign-up link goes to the sign-up page as expected
4. Test that after logging in with valid credentials, you are redirected to the post-login URL

### Creating a Password Reset Page

1. Create a "Password Request" and "Password Reset" page in the Code Generator
2. Test with a non-existent username or email to ensure error messages display correctly
3. Test with a valid username or email to ensure a password reminder email gets sent

**Tip:** For easier testing of outgoing email, set **Admin > General Settings > Outgoing Mail** to "Send & Log" and you will be able to review a log of outgoing emails under **Admin > Outgoing Email**.

### Creating an Edit Profile Page

1. Create an "Edit Profile" page in the Code Generator
2. Create a login page if you haven't already and login to the website
3. Access the page and try updating user fields
4. Confirm that the new field values show up under "User Accounts" in the CMS
5. To add additional fields, copy the existing code and insert your new fieldname in: error checking, update user (mysql update code), and HTML form fields

**Note:** If you don't need the "Delete Account" feature, feel free to remove that HTML.

---

## Usage

### Making Pages Login-Only

Add this code at the top of any PHP page (after `viewer_functions.php` is loaded):

```php
<?php if (!$CURRENT_USER) { websiteLogin_redirectToLogin(); } ?>
```

Test by:

1. Logging off and then accessing the page - you should be redirected to login
2. Accessing the page after logging in - you should see the page without issues

### Showing Different Content Based on User

**Content for logged-in users only:**

```php
<?php if ($CURRENT_USER): ?>
  Only logged in users will be able to see this.
<?php endif ?>
```

**Content for users who are NOT logged in:**

```php
<?php if (!$CURRENT_USER): ?>
  Only users who are NOT logged in will see this.
<?php endif ?>
```

**Header with username or login link:**

```php
<?php if ($CURRENT_USER): ?>
  Welcome, <?php echo htmlencode($CURRENT_USER['username']); ?>!
  <a href="<?php echo $GLOBALS['WEBSITE_LOGIN_PROFILE_URL'] ?>">Edit Profile</a> |
  <a href="?action=logoff">Logoff</a>
<?php else: ?>
  <a href="<?php echo $GLOBALS['WEBSITE_LOGIN_LOGIN_FORM_URL'] ?>">Login</a>
<?php endif ?>
```

### Using Email as Username

To disable usernames and have users use their email to login:

In both the signup and profile pages, set:

```php
$useUsernames = false;
```

---

## Adding Custom Fields

### Adding a Text Field

1. Add a field in the CMS field editor under User Accounts (example: `city`)

2. Add error checking in your form:

```php
if (!@$_REQUEST['city']) { $errorsAndAlerts .= "You must enter your city!<br/>\n"; }
```

3. Add the mysql update line:

```php
$colsToValues['city'] = $_REQUEST['city'];
```

4. Add the HTML input field:

```html
<tr>
	<td>City</td>
	<td>
		<input
			type="text"
			name="city"
			value="<?php echo htmlencode(@$_REQUEST['city']); ?>"
			size="50"
		/>
	</td>
</tr>
```

### Adding Radio Fields from CMS List Options

1. Add a list field in the CMS field editor (example: `interest`)

2. Add error checking:

```php
if (!@$_REQUEST['interest']) { $errorsAndAlerts .= "You must select your interest!<br/>\n"; }
```

3. Add the mysql update line:

```php
$colsToValues['interest'] = $_REQUEST['interest'];
```

4. Add the HTML radio buttons:

```php
<tr>
  <td valign="top">Interest</td>
  <td>
    <?php $fieldname = 'interest'; ?>
    <?php $idCounter = 0; ?>
    <?php foreach (getListOptions(accountsTable(), $fieldname) as $value => $label): ?>
      <?php $id = "$fieldname." . ++$idCounter; ?>
      <input type="radio" name="<?php echo $fieldname ?>" id="<?php echo $id ?>"
            value="<?php echo htmlencode($value) ?>" <?php checkedIf(@$_REQUEST[$fieldname], $value) ?> />
      <label for="<?php echo $id ?>"><?php echo htmlencode($value) ?></label><br/>
    <?php endforeach ?>
  </td>
</tr>
```

---

## Advanced Topics

### Accessing User Data

The `$CURRENT_USER` variable is available on any page that loads `viewer_functions.php`. To see available fields:

```php
<?php showme($CURRENT_USER); ?>
```

### Showing Content Based on User Fields

If you have a user field called `premium`, you can show different content:

```php
<?php if ($CURRENT_USER['premium']): ?>
  Only premium members would see this.
<?php endif ?>
```

---

## Email Templates

The plugin uses these email templates (customizable at **Admin > Email Templates**):

| Template ID           | Purpose                              |
| --------------------- | ------------------------------------ |
| `USER-SIGNUP`         | Sent to new users after registration |
| `USER-PASSWORD-RESET` | Sent when requesting password reset  |

---

## Technical Notes

-   **Session Separation:** By default, user login sessions are separate between the CMS and the website. Enable "Separate Login" in settings to allow simultaneous logins as different users.

-   **Accounts Table:** User accounts for the CMS and website are stored in the same `accounts` table by default. You can use a separate table for website accounts by changing the "Accounts Table" setting.

-   **CMS Access:** Users created through the website won't have access to any CMS section unless you explicitly grant it in the signup form code.

-   **Disabled Users:** Users who are "disabled" in the CMS will not be able to login to the website.

-   **Last Login Tracking:** Users with a `lastLoginDate` field will have that value updated every minute when logged into the website and accessing pages that include `viewer_functions.php`.

---

## File Structure

```
websiteMembership/
├── websiteMembership.php           # Main plugin file
├── websiteMembership_admin.php     # Admin UI pages
├── websiteMembership_functions.php # Helper functions
├── websiteMembership_settings.json # Settings storage
├── wsm_codeGenerator.php           # Code generator
├── languages/                      # Translation files
├── LICENSE                         # MIT License
├── CHANGELOG.md                    # Version history
└── README.md                       # This file
```

---

## Language Files & Translations

The plugin includes translation files for multiple languages in the `languages/` folder:

| File     | Language               |
| -------- | ---------------------- |
| `en.php` | English (default)      |
| `es.php` | Spanish (Español)      |
| `fr.php` | French (Français)      |
| `he.php` | Hebrew (עברית)         |
| `pt.php` | Portuguese (Português) |

### Adding New Languages

To add support for a new language:

1. Copy `en.php` to a new file named with the language code (e.g., `de.php` for German)
2. Translate each string value to the target language
3. The CMS will automatically use the appropriate language file based on your language settings

Example language file structure:

```php
return array (
  'Invalid username or password!' => 'Your translation here',
  'Please enter a password!' => 'Your translation here',
  // ... more strings
);
```

### Upgrade Warning

> **Important:** When upgrading the plugin, **do not include the `languages/` folder** in your upgrade files. Language files may be overwritten during upgrades, causing you to lose your translations.

**Best practices for preserving translations:**

1. **Exclude language files from upgrades** - When copying new plugin files, skip the `languages/` folder entirely
2. **Back up your language files** - Before any upgrade, make a backup copy of your `languages/` folder
3. **Use custom language files** - Create language files with custom names (e.g., `custom_es.php`) that won't be overwritten

---

## Troubleshooting

### Login not working

-   Ensure the accounts table has username, password, and email fields
-   Verify the login form posts to `action=login`
-   Check that sessions are enabled

### User keeps getting logged out

-   Check cookie settings and session configuration
-   Ensure the website domain matches the cookie domain

### Password reset emails not sending

-   Verify email settings in **Admin > General Settings**
-   Check the USER-PASSWORD-RESET email template
-   Test email sending with **Admin > Email Settings > Send Test Email**

### Required fields redirect loop

-   Ensure the profile page sets `$GLOBALS['WEBSITE_MEMBERSHIP_PROFILE_PAGE'] = true;` at the top

---

## Version History

See [CHANGELOG.md](CHANGELOG.md) for detailed version history.

## Author

Sagentic Web Design
https://www.sagentic.com

## License

MIT License - See [LICENSE](LICENSE) file for details.
