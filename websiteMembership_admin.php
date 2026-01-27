<?php

/**
 * Website Membership Plugin - Admin UI Pages
 *
 * @package WebsiteMembership
 */

/**
 * Generate plugin navigation bar
 *
 * @param string $currentPage Current page identifier
 * @return string HTML for navigation bar
 */
function wsm_getPluginNav(string $currentPage): string
{
	$pages = [
		'dashboard' => ['label' => t('Dashboard'), 'action' => 'wsm_adminDashboard'],
		'settings'  => ['label' => t('Settings'), 'action' => 'wsm_adminSettings'],
		'help'      => ['label' => t('Help'), 'action' => 'wsm_adminHelp'],
	];

	$html = '<nav aria-label="' . t('Website Membership plugin navigation') . '"><div class="btn-group" role="group" style="margin-bottom:20px">';
	foreach ($pages as $key => $page) {
		$isActive = ($key === $currentPage);
		$btnClass = $isActive ? 'btn btn-primary' : 'btn btn-default';
		$ariaCurrent = $isActive ? ' aria-current="page"' : '';
		$html .= '<a href="?_pluginAction=' . $page['action'] . '" class="' . $btnClass . '"' . $ariaCurrent . '>' . $page['label'] . '</a>';
	}
	$html .= '</div></nav>';

	return $html;
}

/**
 * Dashboard page - Main plugin overview
 */
function wsm_adminDashboard(): void
{
	$settings = wsm_loadSettings();

	$adminUI = [];
	$adminUI['PAGE_TITLE'] = [
		t("Plugins") => '?menu=admin&action=plugins',
		t("Website Membership"),
	];

	$content = '';

	// Plugin navigation
	$content .= wsm_getPluginNav('dashboard');

	// Plugin Status Section
	$content .= '<div class="separator"><div>' . t('Plugin Status') . '</div></div>';

	$content .= '<p class="help-block" style="margin-top:0;margin-bottom:15px">' . t('Current configuration and status overview.') . '</p>';

	$content .= '<div class="form-horizontal">';

	// Version row
	$content .= '<div class="form-group" style="margin-bottom:8px">';
	$content .= '<label class="col-sm-2 control-label" style="padding-top:0">' . t('Version') . '</label>';
	$content .= '<div class="col-sm-10">';
	$content .= '<p class="form-control-static" style="padding-top:0;padding-bottom:0;margin-bottom:0;min-height:0">';
	$content .= '<strong>' . htmlencode($GLOBALS['WEBSITE_MEMBERSHIP_VERSION'] ?? '1.00') . '</strong>';
	$content .= '</p></div></div>';

	// Accounts Table row
	$content .= '<div class="form-group" style="margin-bottom:8px">';
	$content .= '<label class="col-sm-2 control-label" style="padding-top:0">' . t('Accounts Table') . '</label>';
	$content .= '<div class="col-sm-10">';
	$content .= '<p class="form-control-static" style="padding-top:0;padding-bottom:0;margin-bottom:0;min-height:0">';
	$content .= '<code>' . htmlencode($settings['accountsTable']) . '</code>';
	$content .= '</p></div></div>';

	// Login Form URL row
	$content .= '<div class="form-group" style="margin-bottom:8px">';
	$content .= '<label class="col-sm-2 control-label" style="padding-top:0">' . t('Login Page') . '</label>';
	$content .= '<div class="col-sm-10">';
	$content .= '<p class="form-control-static" style="padding-top:0;padding-bottom:0;margin-bottom:0;min-height:0">';
	$content .= '<code>' . htmlencode($settings['loginFormUrl']) . '</code>';
	$content .= '</p></div></div>';

	// Separate Login row
	$content .= '<div class="form-group" style="margin-bottom:8px">';
	$content .= '<label class="col-sm-2 control-label" style="padding-top:0">' . t('Separate Login') . '</label>';
	$content .= '<div class="col-sm-10">';
	$content .= '<p class="form-control-static" style="padding-top:0;padding-bottom:0;margin-bottom:0;min-height:0">';
	if ($settings['separateLogin']) {
		$content .= '<strong style="color:#28a745"><i class="fa-duotone fa-solid fa-check" aria-hidden="true"></i> ' . t('Enabled') . '</strong>';
		$content .= ' <span class="text-muted">' . t('(Website and CMS logins are separate)') . '</span>';
	} else {
		$content .= '<strong style="color:#6c757d"><i class="fa-duotone fa-solid fa-xmark" aria-hidden="true"></i> ' . t('Disabled') . '</strong>';
		$content .= ' <span class="text-muted">' . t('(Shared login between website and CMS)') . '</span>';
	}
	$content .= '</p></div></div>';

	$content .= '</div>'; // end form-horizontal

	// Quick Actions Section
	$content .= '<div class="separator" style="margin-top:20px"><div>' . t('Quick Actions') . '</div></div>';

	$content .= '<p class="help-block" style="margin-top:0;margin-bottom:15px">' . t('Common actions and links.') . '</p>';

	$content .= '<div style="margin-bottom:20px">';
	$content .= '<a href="?_pluginAction=wsm_adminSettings" class="btn btn-primary"><i class="fa-duotone fa-solid fa-gear" aria-hidden="true"></i> ' . t('Configure Settings') . '</a> ';
	$content .= '<a href="?menu=' . htmlencode($settings['accountsTable']) . '" class="btn btn-default"><i class="fa-duotone fa-solid fa-users" aria-hidden="true"></i> ' . t('Manage Accounts') . '</a> ';
	$content .= '<a href="?menu=_email_templates" class="btn btn-default"><i class="fa-duotone fa-solid fa-envelope" aria-hidden="true"></i> ' . t('Email Templates') . '</a> ';
	$content .= '<a href="?menu=_codeGenerator&_generator=wsm_codeGenerator" class="btn btn-default"><i class="fa-duotone fa-solid fa-code" aria-hidden="true"></i> ' . t('Code Generator') . '</a>';
	$content .= '</div>';

	$adminUI['CONTENT'] = $content;
	adminUI($adminUI);
}

/**
 * Settings page - Configure plugin options
 */
function wsm_adminSettings(): void
{
	$message = '';
	$messageType = 'info';

	// Load current settings
	$settings = wsm_loadSettings();

	// Handle form submission
	if (($_REQUEST['saveSettings'] ?? '')) {
		security_dieOnInvalidCsrfToken();

		// Validate inputs
		$errors = [];

		// URL validations - ensure they start with /
		$urlFields = ['loginFormUrl', 'signupUrl', 'reminderUrl', 'profileUrl', 'postLoginUrl', 'postLogoffUrl'];
		foreach ($urlFields as $field) {
			$value = trim($_POST[$field] ?? '');
			if ($value !== '' && !str_starts_with($value, '/')) {
				$_POST[$field] = '/' . $value;
			}
		}

		// Validate accounts table
		$availableTables = wsm_getAvailableAccountsTables();
		$accountsTable = $_POST['accountsTable'] ?? 'accounts';
		if (!isset($availableTables[$accountsTable])) {
			$accountsTable = 'accounts';
		}

		// Get required fields as array
		$requiredFields = [];
		if (!empty($_POST['requiredFields']) && is_array($_POST['requiredFields'])) {
			$requiredFields = array_values(array_filter($_POST['requiredFields']));
		}

		if (empty($errors)) {
			// Update settings
			$settings['loginFormUrl']    = trim($_POST['loginFormUrl'] ?? '/entry/');
			$settings['signupUrl']       = trim($_POST['signupUrl'] ?? '/entry/signup.php');
			$settings['reminderUrl']     = trim($_POST['reminderUrl'] ?? '/entry/reset.php');
			$settings['profileUrl']      = trim($_POST['profileUrl'] ?? '/profile/');
			$settings['postLoginUrl']    = trim($_POST['postLoginUrl'] ?? '/');
			$settings['postLogoffUrl']   = trim($_POST['postLogoffUrl'] ?? '/');
			$settings['accountsTable']   = $accountsTable;
			$settings['separateLogin']   = !empty($_POST['separateLogin']);
			$settings['requiredFields']  = $requiredFields;

			if (wsm_saveSettings($settings)) {
				// Re-apply settings to globals
				wsm_applySettings();

				notice(t('Settings saved successfully.'));
				redirectBrowserToURL('?_pluginAction=wsm_adminSettings');
				exit;
			} else {
				$message = t('Error saving settings. Check file permissions.');
				$messageType = 'danger';
			}
		} else {
			$GLOBALS['wsm_errors'] = $errors;
		}
	}

	// Get available tables and fields
	$availableTables = wsm_getAvailableAccountsTables();
	$accountFields = wsm_getAccountsTableFields();

	$adminUI = [];
	$adminUI['PAGE_TITLE'] = [
		t("Plugins") => '?menu=admin&action=plugins',
		t("Website Membership") => '?_pluginAction=wsm_adminDashboard',
		t("Settings"),
	];

	$adminUI['FORM'] = ['name' => 'settingsForm', 'autocomplete' => 'off'];
	$adminUI['HIDDEN_FIELDS'] = [
		['name' => 'saveSettings', 'value' => '1'],
		['name' => '_pluginAction', 'value' => 'wsm_adminSettings'],
	];
	$adminUI['BUTTONS'] = [
		['name' => '_action=save', 'label' => t('Save Settings')],
	];

	$content = '';

	// Plugin navigation
	$content .= wsm_getPluginNav('settings');

	// Display message if any
	if ($message) {
		$content .= '<div class="alert alert-' . $messageType . '" role="alert">' . htmlencode($message) . '</div>';
	}

	// Display validation errors
	if (!empty($GLOBALS['wsm_errors'])) {
		$content .= '<div class="alert alert-danger" role="alert">';
		$content .= '<strong>' . t('Please fix the following errors:') . '</strong>';
		$content .= '<ul style="margin-bottom:0">';
		foreach ($GLOBALS['wsm_errors'] as $error) {
			$content .= '<li>' . htmlencode($error) . '</li>';
		}
		$content .= '</ul></div>';
	}

	// URL Settings Section
	$content .= '<div class="separator"><div>' . t('URL Settings') . '</div></div>';

	$content .= '<p class="help-block" style="margin-top:0;margin-bottom:15px">' . t('Configure the URLs for membership pages. These paths are relative to your website\'s PREFIX_URL.') . '</p>';

	$content .= '<div class="form-horizontal">';

	// Login Form URL
	$content .= '<div class="form-group">';
	$content .= '<label for="loginFormUrl" class="col-sm-3 control-label">' . t('Login Page URL') . '</label>';
	$content .= '<div class="col-sm-9">';
	$content .= '<input type="text" name="loginFormUrl" id="loginFormUrl" class="form-control" style="width:400px;display:inline-block" value="' . htmlencode($settings['loginFormUrl']) . '" placeholder="/entry/">';
	$content .= '<p class="help-block" style="margin-top:8px">' . t('URL to the login form page.') . '</p>';
	$content .= '</div></div>';

	// Signup URL
	$content .= '<div class="form-group">';
	$content .= '<label for="signupUrl" class="col-sm-3 control-label">' . t('Signup Page URL') . '</label>';
	$content .= '<div class="col-sm-9">';
	$content .= '<input type="text" name="signupUrl" id="signupUrl" class="form-control" style="width:400px;display:inline-block" value="' . htmlencode($settings['signupUrl']) . '" placeholder="/entry/signup.php">';
	$content .= '<p class="help-block" style="margin-top:8px">' . t('URL to the user signup/registration page.') . '</p>';
	$content .= '</div></div>';

	// Password Reset URL
	$content .= '<div class="form-group">';
	$content .= '<label for="reminderUrl" class="col-sm-3 control-label">' . t('Password Reset URL') . '</label>';
	$content .= '<div class="col-sm-9">';
	$content .= '<input type="text" name="reminderUrl" id="reminderUrl" class="form-control" style="width:400px;display:inline-block" value="' . htmlencode($settings['reminderUrl']) . '" placeholder="/entry/reset.php">';
	$content .= '<p class="help-block" style="margin-top:8px">' . t('URL to the password reset/reminder page.') . '</p>';
	$content .= '</div></div>';

	// Profile URL
	$content .= '<div class="form-group">';
	$content .= '<label for="profileUrl" class="col-sm-3 control-label">' . t('Profile Page URL') . '</label>';
	$content .= '<div class="col-sm-9">';
	$content .= '<input type="text" name="profileUrl" id="profileUrl" class="form-control" style="width:400px;display:inline-block" value="' . htmlencode($settings['profileUrl']) . '" placeholder="/profile/">';
	$content .= '<p class="help-block" style="margin-top:8px">' . t('URL to the user profile edit page. Users with missing required fields will be redirected here.') . '</p>';
	$content .= '</div></div>';

	// Post Login URL
	$content .= '<div class="form-group">';
	$content .= '<label for="postLoginUrl" class="col-sm-3 control-label">' . t('Post-Login URL') . '</label>';
	$content .= '<div class="col-sm-9">';
	$content .= '<input type="text" name="postLoginUrl" id="postLoginUrl" class="form-control" style="width:400px;display:inline-block" value="' . htmlencode($settings['postLoginUrl']) . '" placeholder="/">';
	$content .= '<p class="help-block" style="margin-top:8px">' . t('Default URL to redirect users after successful login (if no previous page was saved).') . '</p>';
	$content .= '</div></div>';

	// Post Logoff URL
	$content .= '<div class="form-group">';
	$content .= '<label for="postLogoffUrl" class="col-sm-3 control-label">' . t('Post-Logoff URL') . '</label>';
	$content .= '<div class="col-sm-9">';
	$content .= '<input type="text" name="postLogoffUrl" id="postLogoffUrl" class="form-control" style="width:400px;display:inline-block" value="' . htmlencode($settings['postLogoffUrl']) . '" placeholder="/">';
	$content .= '<p class="help-block" style="margin-top:8px">' . t('Default URL to redirect users after logging off.') . '</p>';
	$content .= '</div></div>';

	$content .= '</div>'; // end form-horizontal

	// Account Settings Section
	$content .= '<div class="separator" style="margin-top:20px"><div>' . t('Account Settings') . '</div></div>';

	$content .= '<div class="form-horizontal">';

	// Accounts Table
	$content .= '<div class="form-group">';
	$content .= '<label for="accountsTable" class="col-sm-3 control-label">' . t('Accounts Table') . '</label>';
	$content .= '<div class="col-sm-9">';
	$content .= '<select name="accountsTable" id="accountsTable" class="form-control" style="width:400px;display:inline-block">';
	foreach ($availableTables as $tableName => $menuName) {
		$selected = ($settings['accountsTable'] === $tableName) ? ' selected' : '';
		$content .= '<option value="' . htmlencode($tableName) . '"' . $selected . '>' . htmlencode($menuName) . ' (' . htmlencode($tableName) . ')</option>';
	}
	$content .= '</select>';
	$content .= '<p class="help-block" style="margin-top:8px">' . t('The database table used to store website user accounts. Use "accounts" to share with CMS admin accounts, or select a custom table for separate website users.') . '</p>';
	$content .= '</div></div>';

	// Separate Login
	$content .= '<div class="form-group">';
	$content .= '<div class="col-sm-3 control-label">' . t('Separate Login') . '</div>';
	$content .= '<div class="col-sm-9">';
	$content .= '<div class="checkbox"><label>';
	$content .= '<input type="hidden" name="separateLogin" value="0">';
	$checked = $settings['separateLogin'] ? ' checked' : '';
	$content .= '<input type="checkbox" name="separateLogin" value="1"' . $checked . '> ';
	$content .= t('Allow simultaneous website and CMS login as different users');
	$content .= '</label></div>';
	$content .= '<p class="help-block">' . t('When enabled, users can be logged into the website and CMS admin as different accounts at the same time. When disabled, logging into one logs out of the other.') . '</p>';
	$content .= '</div></div>';

	$content .= '</div>'; // end form-horizontal

	// Required Profile Fields Section
	$content .= '<div class="separator" style="margin-top:20px"><div>' . t('Required Profile Fields') . '</div></div>';

	$content .= '<p class="help-block" style="margin-top:0;margin-bottom:15px">' . t('Select fields that users must complete. Users with blank/unchecked required fields will be redirected to the profile page.') . '</p>';

	if (empty($accountFields)) {
		$content .= '<div class="alert alert-warning">' . t('No editable fields found in the accounts table. Add custom fields to the accounts schema to enable required field checking.') . '</div>';
	} else {
		$content .= '<div style="margin-left:15px">';
		$content .= '<div class="row">';

		$colCount = 0;
		foreach ($accountFields as $fieldName => $fieldLabel) {
			if ($colCount % 3 === 0 && $colCount > 0) {
				$content .= '</div><div class="row" style="margin-top:8px">';
			}

			$isChecked = in_array($fieldName, $settings['requiredFields']) ? ' checked' : '';
			$content .= '<div class="col-md-4">';
			$content .= '<div class="checkbox" style="margin-top:0;margin-bottom:5px"><label>';
			$content .= '<input type="checkbox" name="requiredFields[]" value="' . htmlencode($fieldName) . '"' . $isChecked . '> ';
			$content .= htmlencode($fieldLabel) . ' <small class="text-muted">(' . htmlencode($fieldName) . ')</small>';
			$content .= '</label></div>';
			$content .= '</div>';

			$colCount++;
		}

		$content .= '</div>'; // end row
		$content .= '</div>'; // end margin wrapper
	}

	$adminUI['CONTENT'] = $content;
	adminUI($adminUI);
}

/**
 * Help page - Display plugin documentation
 */
function wsm_adminHelp(): void
{
	$adminUI = [];
	$adminUI['PAGE_TITLE'] = [
		t("Plugins") => '?menu=admin&action=plugins',
		t("Website Membership") => '?_pluginAction=wsm_adminDashboard',
		t("Help"),
	];

	$content = '';

	// Plugin navigation
	$content .= wsm_getPluginNav('help');

	// Quick action buttons
	$content .= '<div style="margin-bottom:20px">';
	$content .= '<a href="?menu=_codeGenerator&_generator=wsm_codeGenerator" class="btn btn-primary"><i class="fa-duotone fa-solid fa-code" aria-hidden="true"></i> ' . t('Code Generator') . '</a> ';
	$content .= '<a href="?menu=_email_templates" class="btn btn-default"><i class="fa-duotone fa-solid fa-envelope" aria-hidden="true"></i> ' . t('Email Templates') . '</a> ';
	$content .= '<a href="?_pluginAction=wsm_adminSettings" class="btn btn-default"><i class="fa-duotone fa-solid fa-gear" aria-hidden="true"></i> ' . t('Settings') . '</a>';
	$content .= '</div>';

	// Overview Section
	$content .= '<div class="separator"><div>' . t('Overview') . '</div></div>';
	$content .= '<p>' . t('The Website Membership plugin provides user authentication for your website\'s front-end pages. It enables login, signup, password reset, and profile management functionality separate from or integrated with the CMS admin area.') . '</p>';
	$content .= '<h4>' . t('Key Features') . '</h4>';
	$content .= '<ul>';
	$content .= '<li><strong>' . t('User Authentication') . '</strong> - ' . t('Login forms with username/email and password') . '</li>';
	$content .= '<li><strong>' . t('User Registration') . '</strong> - ' . t('Signup pages for new user accounts') . '</li>';
	$content .= '<li><strong>' . t('Password Reset') . '</strong> - ' . t('Email-based password recovery') . '</li>';
	$content .= '<li><strong>' . t('Profile Management') . '</strong> - ' . t('User profile editing with required field enforcement') . '</li>';
	$content .= '<li><strong>' . t('Content Protection') . '</strong> - ' . t('Restrict page access to logged-in users') . '</li>';
	$content .= '<li><strong>' . t('Access Levels') . '</strong> - ' . t('Control content visibility based on user permissions') . '</li>';
	$content .= '</ul>';

	// Getting Started Section
	$content .= '<div class="separator" style="margin-top:20px"><div>' . t('Getting Started') . '</div></div>';
	$content .= '<ol>';
	$content .= '<li><strong>' . t('Configure URLs') . '</strong> - ' . t('Set the paths to your login, signup, reset, and profile pages in Settings.') . '</li>';
	$content .= '<li><strong>' . t('Generate Page Code') . '</strong> - ' . t('Use the Code Generator to create PHP code for your membership pages.') . '</li>';
	$content .= '<li><strong>' . t('Create Pages') . '</strong> - ' . t('Create the actual PHP pages at the configured URLs.') . '</li>';
	$content .= '<li><strong>' . t('Configure Email Templates') . '</strong> - ' . t('Customize the USER-SIGNUP and USER-PASSWORD-RESET email templates.') . '</li>';
	$content .= '</ol>';
	$content .= '<p><a href="?menu=_codeGenerator&_generator=wsm_codeGenerator" class="btn btn-success"><i class="fa-duotone fa-solid fa-code" aria-hidden="true"></i> ' . t('Open Code Generator') . '</a></p>';

	// Suggested Page Structure Section
	$content .= '<div class="separator" style="margin-top:20px"><div>' . t('Suggested Page Structure') . '</div></div>';
	$content .= '<p>' . t('A common approach is to organize your membership pages in dedicated folders:') . '</p>';
	$content .= '<table class="table table-bordered table-striped">';
	$content .= '<thead><tr><th>' . t('Folder/File') . '</th><th>' . t('Purpose') . '</th><th>' . t('URL Setting') . '</th></tr></thead>';
	$content .= '<tbody>';
	$content .= '<tr><td><code>/entry/index.php</code></td><td>' . t('Login form - the main entry point for users') . '</td><td>' . t('Login Page URL') . '</td></tr>';
	$content .= '<tr><td><code>/entry/signup.php</code></td><td>' . t('New user registration form') . '</td><td>' . t('Signup Page URL') . '</td></tr>';
	$content .= '<tr><td><code>/entry/reset.php</code></td><td>' . t('Password reset request and confirmation') . '</td><td>' . t('Password Reset URL') . '</td></tr>';
	$content .= '<tr><td><code>/profile/index.php</code></td><td>' . t('User profile editing (login required)') . '</td><td>' . t('Profile Page URL') . '</td></tr>';
	$content .= '</tbody></table>';
	$content .= '<p class="text-muted"><small>' . t('Note: You can use any folder structure you prefer. Just update the URL Settings to match your chosen paths.') . '</small></p>';

	// URL Settings Section
	$content .= '<div class="separator" style="margin-top:20px"><div>' . t('URL Settings') . '</div></div>';
	$content .= '<table class="table table-bordered table-striped">';
	$content .= '<thead><tr><th>' . t('Setting') . '</th><th>' . t('Description') . '</th><th>' . t('Default') . '</th></tr></thead>';
	$content .= '<tbody>';
	$content .= '<tr><td>' . t('Login Page URL') . '</td><td>' . t('Path to your login form') . '</td><td><code>/entry/</code></td></tr>';
	$content .= '<tr><td>' . t('Signup Page URL') . '</td><td>' . t('Path to user registration') . '</td><td><code>/entry/signup.php</code></td></tr>';
	$content .= '<tr><td>' . t('Password Reset URL') . '</td><td>' . t('Path to password recovery') . '</td><td><code>/entry/reset.php</code></td></tr>';
	$content .= '<tr><td>' . t('Profile Page URL') . '</td><td>' . t('Path to profile editing') . '</td><td><code>/profile/</code></td></tr>';
	$content .= '<tr><td>' . t('Post-Login URL') . '</td><td>' . t('Where users go after logging in') . '</td><td><code>/</code></td></tr>';
	$content .= '<tr><td>' . t('Post-Logoff URL') . '</td><td>' . t('Where users go after logging out') . '</td><td><code>/</code></td></tr>';
	$content .= '</tbody></table>';

	// Creating Membership Pages Section
	$content .= '<div class="separator" style="margin-top:20px"><div>' . t('Creating Membership Pages') . '</div></div>';

	$content .= '<h4>' . t('Creating a Sign-Up Form') . '</h4>';
	$content .= '<ol>';
	$content .= '<li>' . t('Create a "Signup / Create an account" page in the Code Generator and save it with the recommended filename') . '</li>';
	$content .= '<li>' . t('Access the page through your web browser and try creating a user') . '</li>';
	$content .= '<li>' . t('Confirm that the new user shows up under "User Accounts" in the CMS') . '</li>';
	$content .= '<li>' . t('To add additional fields, copy the existing code and insert your new fieldname in: error checking, add user (mysql insert code), and HTML form fields') . '</li>';
	$content .= '<li>' . t('If you want newly created users to have access to the CMS, set <code>$setAccessRights</code> to <code>true</code> and update the code under that line') . '</li>';
	$content .= '</ol>';

	$content .= '<h4>' . t('Creating a Login Page') . '</h4>';
	$content .= '<ol>';
	$content .= '<li>' . t('Create a "Login Page" in the Code Generator and save it with the recommended filename') . '</li>';
	$content .= '<li>' . t('Test logging in with an invalid username to ensure error messages display correctly') . '</li>';
	$content .= '<li>' . t('Test the sign-up link goes to the sign-up page as expected') . '</li>';
	$content .= '<li>' . t('Test that after logging in with valid credentials, you are redirected to the post-login URL') . '</li>';
	$content .= '</ol>';

	$content .= '<h4>' . t('Creating a Password Reset Page') . '</h4>';
	$content .= '<ol>';
	$content .= '<li>' . t('Create a "Password Request" and "Password Reset" page in the Code Generator') . '</li>';
	$content .= '<li>' . t('Test with a non-existent username or email to ensure error messages display correctly') . '</li>';
	$content .= '<li>' . t('Test with a valid username or email to ensure a password reminder email gets sent') . '</li>';
	$content .= '</ol>';
	$content .= '<p class="alert alert-info"><i class="fa-duotone fa-solid fa-lightbulb" aria-hidden="true"></i> <strong>' . t('Tip:') . '</strong> ' . t('For easier testing of outgoing email, set <strong>Admin > General Settings > Outgoing Mail</strong> to "Send & Log" and you will be able to review a log of outgoing emails under <strong>Admin > Outgoing Email</strong>.') . '</p>';

	$content .= '<h4>' . t('Creating an Edit Profile Page') . '</h4>';
	$content .= '<ol>';
	$content .= '<li>' . t('Create an "Edit Profile" page in the Code Generator') . '</li>';
	$content .= '<li>' . t('Create a login page if you haven\'t already and login to the website') . '</li>';
	$content .= '<li>' . t('Access the page and try updating user fields') . '</li>';
	$content .= '<li>' . t('Confirm that the new field values show up under "User Accounts" in the CMS') . '</li>';
	$content .= '<li>' . t('To add additional fields, copy the existing code and insert your new fieldname in: error checking, update user (mysql update code), and HTML form fields') . '</li>';
	$content .= '</ol>';
	$content .= '<p><strong>' . t('Note:') . '</strong> ' . t('If you don\'t need the "Delete Account" feature, feel free to remove that HTML.') . '</p>';

	// The $CURRENT_USER Variable Section
	$content .= '<div class="separator" style="margin-top:20px"><div>' . t('The $CURRENT_USER Variable') . '</div></div>';
	$content .= '<p>' . t('When a user is logged in, the <code>$CURRENT_USER</code> variable is automatically populated with their full account record. This variable is available on any page that loads <code>viewer_functions.php</code>.') . '</p>';

	$content .= '<h4>' . t('Common Fields') . '</h4>';
	$content .= '<table class="table table-bordered table-striped">';
	$content .= '<thead><tr><th>' . t('Field') . '</th><th>' . t('Description') . '</th><th>' . t('Example Usage') . '</th></tr></thead>';
	$content .= '<tbody>';
	$content .= '<tr><td><code>$CURRENT_USER[\'num\']</code></td><td>' . t('The user\'s unique account number') . '</td><td><code>' . htmlencode('User #') . '</code></td></tr>';
	$content .= '<tr><td><code>$CURRENT_USER[\'username\']</code></td><td>' . t('The user\'s login username') . '</td><td><code>' . htmlencode('Welcome,') . '</code></td></tr>';
	$content .= '<tr><td><code>$CURRENT_USER[\'email\']</code></td><td>' . t('The user\'s email address') . '</td><td></td></tr>';
	$content .= '<tr><td><code>$CURRENT_USER[\'fullname\']</code></td><td>' . t('The user\'s full name (if stored)') . '</td><td><code>' . htmlencode('Hello, !') . '</code></td></tr>';
	$content .= '<tr><td><code>$CURRENT_USER[\'isAdmin\']</code></td><td>' . t('Whether user has CMS admin access') . '</td><td><code>' . htmlencode('if ($CURRENT_USER[\'isAdmin\']) { ... }') . '</code></td></tr>';
	$content .= '<tr><td><code>$CURRENT_USER[\'lastLoginDate\']</code></td><td>' . t('Timestamp of last login') . '</td><td><code>' . htmlencode('Last login:') . '</code></td></tr>';
	$content .= '</tbody></table>';
	$content .= '<p>' . t('To see all available fields for a user, add this debugging code to any page:') . '</p>';
	$content .= '<pre style="background:#f5f5f5;padding:15px;border-radius:4px;overflow-x:auto">' . htmlencode('<?php showme($CURRENT_USER); ?>') . '</pre>';

	// Protecting Pages Section
	$content .= '<div class="separator" style="margin-top:20px"><div>' . t('Protecting Pages') . '</div></div>';
	$content .= '<h4>' . t('Require Login') . '</h4>';
	$content .= '<p>' . t('To require login for a page, add this code after loading <code>viewer_functions.php</code>:') . '</p>';
	$content .= '<pre style="background:#f5f5f5;padding:15px;border-radius:4px;overflow-x:auto">' . htmlencode('<?php
// Load CMS Builder library
$libraryPath = \'cms/lib/viewer_functions.php\';
$dirsToCheck = array(\'/home/username/public_html/\', \'\', \'../\', \'../../\', \'../../../\');
foreach ($dirsToCheck as $dir) { if (@include_once("$dir$libraryPath")) { break; }}

// Require user to be logged in
if (!$CURRENT_USER) {
    websiteLogin_redirectToLogin();
}
?>') . '</pre>';

	$content .= '<h4>' . t('Show/Hide Content Based on Login Status') . '</h4>';
	$content .= '<pre style="background:#f5f5f5;padding:15px;border-radius:4px;overflow-x:auto">' . htmlencode('<?php if ($CURRENT_USER): ?>
    <!-- Content only logged-in users will see -->
    <p>Welcome back, <?php echo htmlencode($CURRENT_USER[\'username\']); ?>!</p>
    <a href="?action=logoff">Logout</a>
<?php else: ?>
    <!-- Content for visitors who are NOT logged in -->
    <p>Please <a href="<?php echo $GLOBALS[\'WEBSITE_LOGIN_LOGIN_FORM_URL\']; ?>">login</a> to continue.</p>
<?php endif ?>') . '</pre>';

	// Access Levels & Permissions Section
	$content .= '<div class="separator" style="margin-top:20px"><div>' . t('Access Levels & Permissions') . '</div></div>';
	$content .= '<p>' . t('You can create custom access levels by adding checkbox fields to your accounts table schema. These fields can then be used to control what content or features users can access.') . '</p>';

	$content .= '<h4>' . t('Adding Custom Permission Fields') . '</h4>';
	$content .= '<ol>';
	$content .= '<li>' . t('Go to <strong>Admin > Section Editors</strong> and edit the User Accounts section') . '</li>';
	$content .= '<li>' . t('Add a new <strong>checkbox</strong> field (e.g., "premium_member", "can_download", "is_moderator")') . '</li>';
	$content .= '<li>' . t('Save the schema - the field is now available on user accounts') . '</li>';
	$content .= '<li>' . t('Edit user accounts to enable/disable the permission for specific users') . '</li>';
	$content .= '</ol>';

	$content .= '<h4>' . t('Example: Premium Content') . '</h4>';
	$content .= '<p>' . t('After adding a "premium_member" checkbox field to accounts:') . '</p>';
	$content .= '<pre style="background:#f5f5f5;padding:15px;border-radius:4px;overflow-x:auto">' . htmlencode('<?php if ($CURRENT_USER && $CURRENT_USER[\'premium_member\']): ?>
    <!-- Premium content only premium members can see -->
    <div class="premium-content">
        <h2>Exclusive Premium Content</h2>
        <p>Thank you for being a premium member!</p>
    </div>
<?php else: ?>
    <div class="upgrade-prompt">
        <p>Upgrade to premium to access this content.</p>
    </div>
<?php endif ?>') . '</pre>';

	$content .= '<h4>' . t('Example: Admin-Only Section') . '</h4>';
	$content .= '<p>' . t('Using the built-in isAdmin field to show admin tools:') . '</p>';
	$content .= '<pre style="background:#f5f5f5;padding:15px;border-radius:4px;overflow-x:auto">' . htmlencode('<?php if ($CURRENT_USER && $CURRENT_USER[\'isAdmin\']): ?>
    <!-- Only CMS administrators will see this -->
    <div class="admin-tools">
        <h3>Admin Tools</h3>
        <a href="/cmsb/">Access CMS Admin</a>
    </div>
<?php endif ?>') . '</pre>';

	$content .= '<h4>' . t('Example: Page-Level Permission Check') . '</h4>';
	$content .= '<p>' . t('Restrict an entire page to users with a specific permission:') . '</p>';
	$content .= '<pre style="background:#f5f5f5;padding:15px;border-radius:4px;overflow-x:auto">' . htmlencode('<?php
// Require login first
if (!$CURRENT_USER) {
    websiteLogin_redirectToLogin();
}

// Then check for specific permission
if (!$CURRENT_USER[\'can_access_reports\']) {
    die("Sorry, you don\'t have permission to access this page.");
}
?>') . '</pre>';

	// Required Profile Fields Section
	$content .= '<div class="separator" style="margin-top:20px"><div>' . t('Required Profile Fields') . '</div></div>';
	$content .= '<p>' . t('You can require users to complete certain profile fields (like agreeing to Terms of Service). When a logged-in user has blank required fields, they will be automatically redirected to the profile page with a "missing_fields" parameter.') . '</p>';
	$content .= '<p>' . t('For checkbox fields, users must have the box checked (value = 1). For other fields, any non-empty value satisfies the requirement.') . '</p>';

	$content .= '<h4>' . t('Handling Missing Fields on Profile Page') . '</h4>';
	$content .= '<pre style="background:#f5f5f5;padding:15px;border-radius:4px;overflow-x:auto">' . htmlencode('<?php
// At the very top of your profile page, prevent redirect loops
$GLOBALS[\'WEBSITE_MEMBERSHIP_PROFILE_PAGE\'] = true;

// Show a message if user was redirected due to missing fields
if (@$_REQUEST[\'missing_fields\']) {
    echo \'<div class="alert">Please complete all required fields below.</div>\';
}
?>') . '</pre>';

	// Email Templates Section
	$content .= '<div class="separator" style="margin-top:20px"><div>' . t('Email Templates') . '</div></div>';
	$content .= '<p>' . t('The plugin uses these email templates which can be customized in <strong>Admin > Email Templates</strong>:') . '</p>';
	$content .= '<table class="table table-bordered table-striped">';
	$content .= '<thead><tr><th>' . t('Template ID') . '</th><th>' . t('Purpose') . '</th><th>' . t('Available Placeholders') . '</th></tr></thead>';
	$content .= '<tbody>';
	$content .= '<tr><td><code>USER-SIGNUP</code></td><td>' . t('Sent to new users after registration') . '</td><td><code>#user.username#</code>, <code>#user.email#</code>, <code>#password#</code>, <code>#loginUrl#</code></td></tr>';
	$content .= '<tr><td><code>USER-PASSWORD-RESET</code></td><td>' . t('Sent when requesting password reset') . '</td><td><code>#user.username#</code>, <code>#user.email#</code>, <code>#resetUrl#</code>, <code>#loginUrl#</code></td></tr>';
	$content .= '</tbody></table>';
	$content .= '<p style="margin-top:15px"><a href="?menu=_email_templates" class="btn btn-default"><i class="fa-duotone fa-solid fa-envelope" aria-hidden="true"></i> ' . t('Manage Email Templates') . '</a></p>';

	// Global Variables Reference Section
	$content .= '<div class="separator" style="margin-top:20px"><div>' . t('Global Variables Reference') . '</div></div>';
	$content .= '<p>' . t('These global variables are available on any page after loading <code>viewer_functions.php</code>:') . '</p>';
	$content .= '<table class="table table-bordered table-striped">';
	$content .= '<thead><tr><th>' . t('Variable') . '</th><th>' . t('Description') . '</th></tr></thead>';
	$content .= '<tbody>';
	$content .= '<tr><td><code>$CURRENT_USER</code></td><td>' . t('Array containing the logged-in user\'s account record, or empty/false if not logged in') . '</td></tr>';
	$content .= '<tr><td><code>$GLOBALS[\'WEBSITE_LOGIN_LOGIN_FORM_URL\']</code></td><td>' . t('URL to the login page') . '</td></tr>';
	$content .= '<tr><td><code>$GLOBALS[\'WEBSITE_LOGIN_SIGNUP_URL\']</code></td><td>' . t('URL to the signup page') . '</td></tr>';
	$content .= '<tr><td><code>$GLOBALS[\'WEBSITE_LOGIN_REMINDER_URL\']</code></td><td>' . t('URL to the password reset page') . '</td></tr>';
	$content .= '<tr><td><code>$GLOBALS[\'WEBSITE_LOGIN_PROFILE_URL\']</code></td><td>' . t('URL to the profile editing page') . '</td></tr>';
	$content .= '<tr><td><code>$GLOBALS[\'WEBSITE_LOGIN_POST_LOGIN_URL\']</code></td><td>' . t('Default redirect after login') . '</td></tr>';
	$content .= '<tr><td><code>$GLOBALS[\'WEBSITE_MEMBERSHIP_PLUGIN\']</code></td><td>' . t('True if the plugin is active (useful for conditional code)') . '</td></tr>';
	$content .= '</tbody></table>';

	// Technical Notes Section (Account Configuration)
	$content .= '<div class="separator" style="margin-top:20px"><div>' . t('Account Configuration') . '</div></div>';
	$content .= '<ul>';
	$content .= '<li><strong>' . t('Session Separation:') . '</strong> ' . t('By default, user login sessions are separate between the CMS and the website. Enable "Separate Login" in settings to allow simultaneous logins as different users.') . '</li>';
	$content .= '<li><strong>' . t('Accounts Table:') . '</strong> ' . t('User accounts for the CMS and website are stored in the same <code>accounts</code> table by default. You can use a separate table for website accounts by changing the "Accounts Table" setting.') . '</li>';
	$content .= '<li><strong>' . t('CMS Access:') . '</strong> ' . t('Users created through the website won\'t have access to any CMS section unless you explicitly grant it in the signup form code.') . '</li>';
	$content .= '<li><strong>' . t('Disabled Users:') . '</strong> ' . t('Users who are "disabled" in the CMS will not be able to login to the website.') . '</li>';
	$content .= '<li><strong>' . t('Last Login Tracking:') . '</strong> ' . t('Users with a <code>lastLoginDate</code> field will have that value updated every minute when logged into the website and accessing pages that include <code>viewer_functions.php</code>.') . '</li>';
	$content .= '</ul>';

	// Language Files Section
	$content .= '<div class="separator" style="margin-top:20px"><div>' . t('Language Files & Translations') . '</div></div>';
	$content .= '<p>' . t('Translation files are located in the <code>languages/</code> folder:') . '</p>';
	$content .= '<table class="table table-bordered table-striped" style="max-width:400px">';
	$content .= '<thead><tr><th>' . t('File') . '</th><th>' . t('Language') . '</th></tr></thead>';
	$content .= '<tbody>';
	$content .= '<tr><td><code>en.php</code></td><td>' . t('English (default)') . '</td></tr>';
	$content .= '<tr><td><code>es.php</code></td><td>' . t('Spanish') . '</td></tr>';
	$content .= '<tr><td><code>fr.php</code></td><td>' . t('French') . '</td></tr>';
	$content .= '<tr><td><code>he.php</code></td><td>' . t('Hebrew') . '</td></tr>';
	$content .= '<tr><td><code>pt.php</code></td><td>' . t('Portuguese') . '</td></tr>';
	$content .= '</tbody></table>';

	$content .= '<h4>' . t('Adding a New Language') . '</h4>';
	$content .= '<ol>';
	$content .= '<li>' . t('Copy <code>en.php</code> to a new file (e.g., <code>de.php</code> for German)') . '</li>';
	$content .= '<li>' . t('Translate each string value to the target language') . '</li>';
	$content .= '<li>' . t('The CMS will automatically use the file based on language settings') . '</li>';
	$content .= '</ol>';

	$content .= '<div class="alert alert-warning" style="margin-top:15px">';
	$content .= '<strong><i class="fa-duotone fa-solid fa-triangle-exclamation" aria-hidden="true"></i> ' . t('Upgrade Warning') . '</strong><br>';
	$content .= t('When upgrading the plugin, <strong>do not include the <code>languages/</code> folder</strong> in your upgrade files. Language files may be overwritten, causing you to lose your translations.');
	$content .= '<br><br><strong>' . t('Best practices for preserving translations:') . '</strong>';
	$content .= '<ol style="margin-bottom:0">';
	$content .= '<li>' . t('Exclude language files from upgrades - skip the <code>languages/</code> folder entirely') . '</li>';
	$content .= '<li>' . t('Back up your language files before any upgrade') . '</li>';
	$content .= '<li>' . t('Use custom language files (e.g., <code>custom_es.php</code>) that won\'t be overwritten') . '</li>';
	$content .= '</ol>';
	$content .= '</div>';

	// Troubleshooting Section
	$content .= '<div class="separator" style="margin-top:20px"><div>' . t('Troubleshooting') . '</div></div>';

	$content .= '<h4>' . t('Login not working') . '</h4>';
	$content .= '<ul>';
	$content .= '<li>' . t('Ensure PHP sessions are enabled on your server') . '</li>';
	$content .= '<li>' . t('Check that the accounts table has username, password, and email fields') . '</li>';
	$content .= '<li>' . t('Verify the login form includes <code>&lt;input type="hidden" name="action" value="login"&gt;</code>') . '</li>';
	$content .= '<li>' . t('Make sure the user account is not disabled in the CMS') . '</li>';
	$content .= '</ul>';

	$content .= '<h4>' . t('User keeps getting logged out') . '</h4>';
	$content .= '<ul>';
	$content .= '<li>' . t('Check that your website domain matches the cookie domain in PHP settings') . '</li>';
	$content .= '<li>' . t('Verify session configuration in php.ini or .htaccess') . '</li>';
	$content .= '<li>' . t('Ensure cookies are not being blocked by browser security settings') . '</li>';
	$content .= '<li>' . t('Check for mixed HTTP/HTTPS content which can invalidate sessions') . '</li>';
	$content .= '</ul>';

	$content .= '<h4>' . t('Password reset emails not sending') . '</h4>';
	$content .= '<ul>';
	$content .= '<li>' . t('Verify email settings in <strong>Admin > General Settings</strong>') . '</li>';
	$content .= '<li>' . t('Check that the <code>USER-PASSWORD-RESET</code> email template exists in <strong>Admin > Email Templates</strong>') . '</li>';
	$content .= '<li>' . t('Test email sending with <strong>Admin > Email Settings > Send Test Email</strong>') . '</li>';
	$content .= '<li>' . t('Check your server\'s mail logs for delivery errors') . '</li>';
	$content .= '<li>' . t('Ensure the user has a valid email address in their account') . '</li>';
	$content .= '</ul>';

	$content .= '<h4>' . t('Required fields redirect loop') . '</h4>';
	$content .= '<p>' . t('If users are stuck in a redirect loop when required fields are configured, the profile page must identify itself to prevent the loop.') . '</p>';
	$content .= '<p>' . t('Add this line at the very top of your profile page (before loading viewer_functions.php):') . '</p>';
	$content .= '<pre style="background:#f5f5f5;padding:15px;border-radius:4px;overflow-x:auto">' . htmlencode('<?php $GLOBALS[\'WEBSITE_MEMBERSHIP_PROFILE_PAGE\'] = true; ?>') . '</pre>';

	$content .= '<h4>' . t('$CURRENT_USER is empty or not set') . '</h4>';
	$content .= '<ul>';
	$content .= '<li>' . t('Make sure <code>viewer_functions.php</code> is loaded before checking <code>$CURRENT_USER</code>') . '</li>';
	$content .= '<li>' . t('Verify the library path points to your CMS Builder installation') . '</li>';
	$content .= '</ul>';
	$content .= '<pre style="background:#f5f5f5;padding:15px;border-radius:4px;overflow-x:auto">' . htmlencode('$libraryPath = \'cmsb/lib/viewer_functions.php\';
$dirsToCheck = array(\'/home/username/public_html/\', \'\', \'../\', \'../../\', \'../../../\');
foreach ($dirsToCheck as $dir) {
    if (@include_once("$dir$libraryPath")) { break; }
}') . '</pre>';
	$content .= '<ul>';
	$content .= '<li>' . t('The user may not be logged in - use <code>if ($CURRENT_USER)</code> to check first') . '</li>';
	$content .= '<li>' . t('Try <code>showme($CURRENT_USER)</code> to debug what data is available') . '</li>';
	$content .= '</ul>';

	$content .= '<h4>' . t('Disabled users can still access pages') . '</h4>';
	$content .= '<p>' . t('Users marked as "disabled" in the CMS cannot log in, but if they were already logged in before being disabled, their session may still be active. To force a logout, add this check to protected pages:') . '</p>';
	$content .= '<pre style="background:#f5f5f5;padding:15px;border-radius:4px;overflow-x:auto">' . htmlencode('<?php
if ($CURRENT_USER && @$CURRENT_USER[\'disabled\']) {
    // Force logout disabled users
    unset($_SESSION[\'WEBSITE_LOGIN_USER\']);
    $CURRENT_USER = false;
    websiteLogin_redirectToLogin();
}
?>') . '</pre>';

	// Version Info
	$content .= '<div class="separator" style="margin-top:20px"><div>' . t('Version Information') . '</div></div>';
	$content .= '<p><strong>' . t('Version:') . '</strong> ' . ($GLOBALS['WEBSITE_MEMBERSHIP_VERSION'] ?? '1.00') . '</p>';
	$content .= '<p><strong>' . t('Full Documentation:') . '</strong> ' . t('See the <code>README.md</code> file in the plugin folder for complete documentation.') . '</p>';
	$content .= '<p><strong>' . t('CMS Builder Docs:') . '</strong> <a href="https://www.interactivetools.com/docs/" target="_blank" rel="noopener">' . t('CMS Builder Documentation') . ' <i class="fa-solid fa-external-link" aria-hidden="true"></i></a></p>';

	$adminUI['CONTENT'] = $content;
	adminUI($adminUI);
}
