# AGENTS.md

## Repo overview

WordPress plugin — **Complete Updates Manager**. Disables core/plugin/theme update checks, cronjobs, and notifications in WordPress.

## Source layout (non-obvious)

- **Plugin source lives in `dist/complete-updates-manager/`** — not at the repo root. The repo root contains only README, LICENSE, and `.github/`.
- Main entrypoint: `dist/complete-updates-manager/complete-updates-manager.php`
- Classes: `dist/complete-updates-manager/includes/class-*.php`
- Helpers/freeze functions: `dist/complete-updates-manager/includes/helpers.php`
- Translations: `dist/complete-updates-manager/languages/` (POT + .po/.mo files for 5 languages)

## Conventions

- PHP **7.4+** minimum, WordPress **3.8+** minimum
- Function prefix: `wum_` | Constant prefix: `WUM_` | Text domain: `complete-updates-manager`
- Classes follow WordPress naming: `Complete_Updates_Manager`, `Complete_Updates_Manager_Admin`, `Complete_Updates_Manager_Settings`
- GPL-2.0-or-later license (per plugin header), GPL-3.0 per repo LICENSE file — clarify if needed
- POT file generated via [Loco Translate](https://localise.biz/) 2.7.2

## Settings & options

- Plugin settings: `wum_settings` option (array)
- Version Freeze data: `wum_version_freeze` option (array with `core`, `plugin`, `theme` sub-keys)
- Security issues cache: `wum_security_issues` option, `wum_security_check` transient
- Settings page at **Settings → Updates Manager** (`options-general.php?page=complete-updates-manager`), with three tabs: General, Security Monitoring, Version Freeze

## Build / test / lint

- **No build system, no tests, no CI workflows, no linting config.**
- Editing PHP files directly in `dist/` is the workflow.
