# Changelog

All notable changes to Complete Updates Manager will be documented in this file.

## [1.0.2] — Unreleased

### Added
- **Uninstall handler** (`uninstall.php`) — Cleans all plugin options and transients on removal
- **External CSS file** (`assets/admin.css`) — Hidden update UI indicators via enqueued stylesheet instead of inline `<style>`
- **External JS file** (`assets/admin.js`) — Version freeze UI helpers (copy button, unfreeze, input validation) extracted from inline script
- **PHPUnit tests** — 43 tests across 4 test files covering helpers, settings, updates-manager, and uninstall
- **Development tooling** — `composer.json` (PHPCS, PHPStan, PHPUnit, Brain Monkey), `package.json` (CSS/JS minification), `phpcs.xml.dist`, `phpstan.neon`, `phpstan-bootstrap.php`
- **CI pipeline** — `.github/workflows/ci.yml` with PHPCS + PHPUnit matrix across PHP 7.4–8.3
- **`.gitignore`** — Comprehensive ignore rules for OS, IDE, PHP/Node dependencies, build artifacts, and coverage
- **`AGENTS.md`** — Agent instructions for AI-assisted development
- **`README.md`** — Overhauled with feature badges, dev setup instructions, and commands table

### Changed
- **Code formatting** — Full WPCS compliance reformat across all PHP files (tabs, proper spacing, array syntax)
- **`render_freeze_version_field()`** — Refactored from `Complete_Updates_Manager_Admin` class method to global helper `wum_render_freeze_version_field()` in `helpers.php`
- **`hide_update_ui()`** — Now enqueues external CSS via `enqueue_admin_assets()` instead of echoing inline styles
- **`enqueue_admin_styles()`** — JS moved to external file; admin bar inline CSS preserved via `wp_add_inline_style()`
- **Plugin action links** — `Documentation` link added alongside `Settings` and `Updates Disabled` status

### Fixed
- Corrected missing `disable_plugins_api_filter` key in activation defaults
- Added proper `esc_attr()` escaping in `render_freeze_version_field()` field attributes

---

## [1.0.1] — 2025-05-31

### Added
- Settings page under **Settings > Updates Manager** with configurable options
- **Security Monitoring** — Optional critical update check with email notifications
- **Version Freeze** — Freeze core/plugin/theme versions in a dedicated tab
- First activation warning notice with security recommendations
- Admin bar notification showing plugin is active
- Plugin action links (Settings, Status)
- Localization support with 5 languages (German, Spanish, French, Japanese, Russian)
- `wum_` prefixed global helper functions
- `@since` docblocks on all methods

### Changed
- `override_version_check()` — Integrated Version Freeze logic into transient filtering
- `block_update_requests()` — Allow version-check requests when security monitoring is enabled
- `filter_cron_events()` — Skip `wp_version_check` removal when security monitoring is enabled

---

## [1.0.0] — 2025-05-01

### Added
- Initial release
- Core update disabling (`pre_site_transient_update_core`, `wp_version_check`, health checks)
- Plugin update disabling (`pre_site_transient_update_plugins`, `wp_update_plugins`)
- Theme update disabling (`pre_site_transient_update_themes`, `wp_update_themes`)
- Automatic update blocking (all `auto_update_*` and `automatic_updater_*` filters)
- Cron event filtering (`schedule_event` hook)
- HTTP API request blocking to `api.wordpress.org`
- Update capability removal via `user_has_cap` filter
- Admin bar notification (basic)
- Hidden update UI elements (inline CSS)
