# Changelog

All notable changes to the Force Sensitivity Detector extension will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Planned for v1.1.0
- REST API endpoints for external integrations
- Webhook notifications for FS events
- Unit test suite with PHPUnit
- Achievement system integration
- User self-reroll option
- Database migration/upgrade system

---

## [1.0.0] - 2026-01-17

### Added
- Initial release of Force Sensitivity Detector
- Automatic detection on user registration via memberCreate hook
- Configurable base probability (default 5%)
- Ratio-based probability adjustment with soft/hard enforcement modes
- Admin manual trigger and override functionality
- Probability modifiers (member, group, global, event types)
- Profile field integration with Profile extension
- Badge display on profiles with three styles (simple, glow, animated)
- Post indicator display support
- Complete audit logging with export (CSV/JSON)
- Bulk member operations (reroll, set status)
- Import/Export settings functionality
- Member filter extension for ACP
- 150+ language strings for full localization
- Dark mode CSS support
- Comprehensive documentation (README, Admin Guide, Technical Spec, FAQ)
- GitHub issue templates (bug report, feature request, dev task)
- Pull request template

### Technical
- ICS v4.7.20 compatibility
- PHP 7.4+ / 8.0+ support
- MySQL 5.7+ / MariaDB 10.2+ support
- Secure random_int() for fair probability rolling
- CSRF protection on all admin actions
- Proper database indexes for performance

---

## Version History Template

### [X.Y.Z] - YYYY-MM-DD

### Added
- New features

### Changed
- Changes in existing functionality

### Deprecated
- Soon-to-be removed features

### Removed
- Removed features

### Fixed
- Bug fixes

### Security
- Vulnerability fixes
