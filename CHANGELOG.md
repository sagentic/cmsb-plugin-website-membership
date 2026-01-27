# Changelog

All notable changes to the Website Membership Admin plugin will be documented in this file.

## Version 1.01 (2026-01-27)

### Changed

- **Removed Custom Login Audit System** - Plugin now uses CMSB's internal audit_log system instead of maintaining its own login_audit table
  - Removed `login_audit` table and all related functionality
  - Removed Login History page from admin navigation
  - Removed Login Audit settings (enableLoginAudit toggle)
  - Removed Login Statistics from dashboard
  - Removed all login audit helper functions (wsm_loginAuditTableExists, wsm_getLoginAuditStats, wsm_getLastLoginDate, wsm_getRecentLogins, wsm_getAccountUsername)
  - Login activity now tracked through CMSB's standard audit log at Admin > System > Audit Log

### Benefits

- Simplified plugin maintenance and reduced code complexity
- Unified audit logging across all CMSB systems
- Better performance with centralized audit system
- Consistent audit data format throughout CMSB

## Version 1.00 (2026-01-11)

This is the initial release of the Sagentic fork, based on websiteMembership v1.14 by InteractiveTools.com.

### Added

- **Admin UI Dashboard** - Overview page showing plugin status and recent activity
- **Settings Page** - Configure all plugin options through the admin interface:
  - URL settings (login, signup, password reset, profile, post-login/logoff redirects)
  - Accounts table selection (supports custom user tables)
  - Separate login toggle (allow simultaneous website/CMS logins)
  - Required profile fields selection with checkbox interface
- **Help Page** - Comprehensive documentation including:
  - Getting started guide
  - URL settings reference
  - Account configuration options
  - Page protection code examples
  - Required fields explanation
  - Email template customization
  - Troubleshooting guide
- **Settings-based Configuration** - All settings stored in JSON file, no code editing required
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
