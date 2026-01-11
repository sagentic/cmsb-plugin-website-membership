# Changelog

All notable changes to the Website Membership Admin plugin will be documented in this file.

## Version 1.00 (2026-01-11)

This is the initial release of the Sagentic fork, based on websiteMembership v1.14 by InteractiveTools.com.

### Added

- **Admin UI Dashboard** - Overview page showing plugin status, login statistics, and recent activity
- **Settings Page** - Configure all plugin options through the admin interface:
  - URL settings (login, signup, password reset, profile, post-login/logoff redirects)
  - Accounts table selection (supports custom user tables)
  - Separate login toggle (allow simultaneous website/CMS logins)
  - Required profile fields selection with checkbox interface
  - Login audit enable/disable toggle
- **Login History Page** - View paginated login audit records with user, date, and IP address
- **Help Page** - Comprehensive documentation including:
  - Getting started guide
  - URL settings reference
  - Account configuration options
  - Page protection code examples
  - Required fields explanation
  - Email template customization
  - Troubleshooting guide
- **Settings-based Configuration** - All settings stored in JSON file, no code editing required
- **Login Audit Integration** - Check settings before creating audit records
- **Navigation Menu** - Button group navigation across all plugin pages

### Changed

- Settings now loaded from `websiteMembership_settings.json` instead of hardcoded in PHP
- Version numbering reset to 1.00 for this fork
- Plugin display name changed to "Website Membership Admin"

### Technical Notes

- Based on websiteMembership v1.14 by InteractiveTools.com
- Maintains full backward compatibility with existing installations
- Uses prefixed function names (no PHP namespaces) per CMSB standards
- Follows CMSB Plugin Developer Manual guidelines

---

## Original Plugin History (InteractiveTools.com)

The original websiteMembership plugin by InteractiveTools.com:

- v1.14 - Base version this fork is derived from
