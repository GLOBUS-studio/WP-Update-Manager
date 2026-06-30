# AGENTS.md

## Repo overview

WordPress plugin -- **Complete Updates Manager**. Disables core/plugin/theme update checks, cronjobs, and notifications in WordPress.

## Source layout (non-obvious)

- **Plugin source lives in `dist/complete-updates-manager/`** -- not at the repo root. The repo root contains README, LICENSE, `.github/`, and dev config files.
- Main entrypoint: `dist/complete-updates-manager/complete-updates-manager.php`
- Classes: `dist/complete-updates-manager/includes/class-*.php`
- Helpers/freeze functions: `dist/complete-updates-manager/includes/helpers.php`
- Assets: `dist/complete-updates-manager/assets/admin.css` and `admin.js`
- Uninstall: `dist/complete-updates-manager/uninstall.php`
- Translations: `dist/complete-updates-manager/languages/` (POT + .po/.mo files for 5 languages)
- Test stubs: `.wordpress/wp-includes/version.php`

## Conventions

- PHP **7.4+** minimum, WordPress **3.8+** minimum
- Function prefix: `wum_` | Constant prefix: `WUM_` | Text domain: `complete-updates-manager`
- Classes follow WordPress naming: `Complete_Updates_Manager`, `Complete_Updates_Manager_Admin`, `Complete_Updates_Manager_Settings`
- GPL-2.0-or-later license (per plugin header), GPL-3.0 per repo LICENSE file
- POT file generated via [Loco Translate](https://localise.biz/) 2.7.2
- WordPress Coding Standards (WPCS) via `phpcs.xml.dist`
- Static analysis via `phpstan.neon` (level 5)

## Settings & options

- Plugin settings: `wum_settings` option (array)
- Version Freeze data: `wum_version_freeze` option (array with `core`, `plugin`, `theme` sub-keys)
- Security issues cache: `wum_security_issues` option, `wum_security_check` transient
- First activation: `wum_first_activation_done`, `wum_show_activation_notice`
- Settings page at **Settings > Updates Manager** (`options-general.php?page=complete-updates-manager`), with three tabs: General, Security Monitoring, Version Freeze

## Build / test / lint

```
composer install     # Install PHP dependencies (PHPCS, PHPStan, PHPUnit, Brain Monkey)
npm install          # Install Node dependencies (CSS/JS minification)
```

| Command | What it does |
|---------|-------------|
| `composer phpcs` | WordPress Coding Standards check |
| `composer phpcs:fix` | Auto-fix WPCS issues |
| `composer phpstan` | Static analysis (level 5, optional) |
| `composer test` | PHPUnit tests (43 tests, brain/monkey) |
| `composer lint` | Run phpcs |
| `npm run build` | Minify CSS + JS to `dist/.../assets/` |

CI runs on push/PR via `.github/workflows/ci.yml` -- PHP lint (PHPCS), static analysis (PHPStan), unit tests (PHPUnit) across PHP 7.4 -- 8.3.
