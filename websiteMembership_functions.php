<?php

/**
 * Website Membership Plugin - Helper Functions
 *
 * @package WebsiteMembership
 */

/**
 * Get the path to the settings JSON file
 *
 * @return string Settings file path
 */
function wsm_getSettingsFilePath(): string
{
	return __DIR__ . '/websiteMembership_settings.json';
}

/**
 * Get default plugin settings
 *
 * @return array Default settings
 */
function wsm_getDefaultSettings(): array
{
	return [
		// URL Settings
		'loginFormUrl'    => '/entry/',
		'signupUrl'       => '/entry/signup.php',
		'reminderUrl'     => '/entry/reset.php',
		'profileUrl'      => '/profile/',
		'postLoginUrl'    => '/',
		'postLogoffUrl'   => '/',

		// Required Profile Fields
		'requiredFields'  => ['agree_tos', 'agree_legal'],

		// Account Settings
		'accountsTable'   => 'accounts',
		'separateLogin'   => false,
	];
}

/**
 * Load plugin settings from JSON file
 *
 * Merges saved settings with defaults to ensure all keys exist.
 *
 * @return array Settings array
 */
function wsm_loadSettings(): array
{
	$settingsFile = wsm_getSettingsFilePath();
	$defaults = wsm_getDefaultSettings();

	if (!file_exists($settingsFile) || !is_readable($settingsFile)) {
		return $defaults;
	}

	$content = @file_get_contents($settingsFile);
	if ($content === false) {
		return $defaults;
	}

	$settings = @json_decode($content, true);

	if (!is_array($settings)) {
		return $defaults;
	}

	return array_merge($defaults, $settings);
}

/**
 * Save plugin settings to JSON file
 *
 * @param array $settings Settings to save
 * @return bool True on success
 */
function wsm_saveSettings(array $settings): bool
{
	$settingsFile = wsm_getSettingsFilePath();
	$json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	return @file_put_contents($settingsFile, $json) !== false;
}

/**
 * Apply settings from JSON to global variables
 *
 * This function bridges the new settings system with the legacy global variables
 * that the plugin currently uses.
 */
function wsm_applySettings(): void
{
	$settings = wsm_loadSettings();

	// Apply URL settings with PREFIX_URL
	$GLOBALS['WEBSITE_LOGIN_LOGIN_FORM_URL']  = PREFIX_URL . $settings['loginFormUrl'];
	$GLOBALS['WEBSITE_LOGIN_SIGNUP_URL']      = PREFIX_URL . $settings['signupUrl'];
	$GLOBALS['WEBSITE_LOGIN_REMINDER_URL']    = PREFIX_URL . $settings['reminderUrl'];
	$GLOBALS['WEBSITE_LOGIN_PROFILE_URL']     = PREFIX_URL . $settings['profileUrl'];
	$GLOBALS['WEBSITE_LOGIN_POST_LOGIN_URL']  = PREFIX_URL . $settings['postLoginUrl'];
	$GLOBALS['WEBSITE_LOGIN_POST_LOGOFF_URL'] = PREFIX_URL . $settings['postLogoffUrl'];

	// Apply required fields
	$GLOBALS['WEBSITE_LOGIN_REQUIRED_FIELDS'] = $settings['requiredFields'];

	// Apply account settings
	$GLOBALS['WSM_ACCOUNTS_TABLE']   = $settings['accountsTable'];
	$GLOBALS['WSM_SEPARATE_LOGIN']   = $settings['separateLogin'];
}

/**
 * Get list of available tables that could be used as accounts table
 *
 * @return array Array of table names with their menu names
 */
function wsm_getAvailableAccountsTables(): array
{
	$tables = [];
	$schemaFiles = glob(DATA_DIR . '/schema/*.ini.php');

	foreach ($schemaFiles as $file) {
		$tableName = basename($file, '.ini.php');

		// Skip system tables
		if (str_starts_with($tableName, '_')) {
			continue;
		}

		$schema = loadSchema($tableName);
		$menuName = $schema['menuName'] ?? $tableName;

		// Check if table has required fields for accounts (username, password, email)
		$hasUsername = isset($schema['username']);
		$hasPassword = isset($schema['password']);
		$hasEmail = isset($schema['email']);

		// Always include 'accounts' table, and any table with username/password/email fields
		if ($tableName === 'accounts' || ($hasUsername && $hasPassword)) {
			$tables[$tableName] = $menuName;
		}
	}

	// Ensure 'accounts' is always an option
	if (!isset($tables['accounts'])) {
		$tables['accounts'] = 'CMS Accounts';
	}

	asort($tables);
	return $tables;
}

/**
 * Get list of fields from the current accounts table that could be required
 *
 * @return array Array of field names with their labels
 */
function wsm_getAccountsTableFields(): array
{
	$settings = wsm_loadSettings();
	$tableName = $settings['accountsTable'];

	$schema = loadSchema($tableName);
	if (!$schema) {
		return [];
	}

	$fields = [];
	foreach ($schema as $fieldName => $fieldConfig) {
		// Skip non-field entries (schema metadata)
		if (!is_array($fieldConfig)) {
			continue;
		}

		// Skip system fields
		if (!empty($fieldConfig['isSystemField'])) {
			continue;
		}

		// Skip tab groups and separators (field names containing tabGroup or separator)
		if (stripos($fieldName, 'tabgroup') !== false || stripos($fieldName, 'separator') !== false) {
			continue;
		}

		// Skip certain field types
		$skipTypes = ['none', 'separator', 'relatedRecords', 'accessList', 'tabgroup'];
		if (in_array($fieldConfig['type'] ?? '', $skipTypes)) {
			continue;
		}

		$label = $fieldConfig['label'] ?? $fieldName;
		$fields[$fieldName] = $label;
	}

	asort($fields);
	return $fields;
}

