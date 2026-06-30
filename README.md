# Complete Updates Manager

[![License: GPL-3.0](https://img.shields.io/badge/license-GPL--3.0-blue.svg)](LICENSE)
[![WordPress](https://img.shields.io/badge/WordPress-plugin-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-%3E%3D7.4-777BB4.svg)](https://www.php.net/)

Advanced tool to fully disable WordPress theme, plugin and core update checking, related cronjobs and notifications with customization options.

The Complete Update Manager plugin provides a robust solution to disable the WordPress update checking system. It prevents WordPress from checking for updates including cronjobs, and suppresses all update-related notifications in the admin area.

## Why Use This Plugin

Some scenarios demand **fixed, stable environments** -- such as white-labeled projects, custom development, or legacy support.

This plugin disables all update mechanisms and suppresses admin notices, giving you a **cleaner dashboard**, reduced load on admin pages, and zero unexpected updates.

---

## Key Features

* **Completely disables WordPress core updates** -- Avoid major version changes without your approval
* **Prevents plugin update checks and notifications** -- No more plugin update prompts
* **Blocks theme update checks and notifications** -- Preserve custom themes from unintentional overwrites
* **Removes update-related items from Site Health screen** -- Clean Site Health interface
* **Blocks update requests to WordPress API servers** -- Reduce background requests
* **Disables all automatic update email notifications** -- No more update emails cluttering your inbox
* **Includes admin bar notification showing that updates are disabled** -- Quick visual status indicator
* **Configurable settings page to customize which updates to disable** -- Selectively control core, themes, plugins
* **Security monitoring option for critical updates** -- Stay informed about vulnerabilities even when updates are disabled
* **Version Freeze** -- Freeze WordPress core, plugin, or theme at a specific version. Updates above this version are blocked, even manual ones. Manage freeze settings in a dedicated tab on the plugin settings page

---

## Important Security Notice

It is *critical* to keep your WordPress theme, core and plugins up to date when not using this plugin. If you do not, your site could become **vulnerable to security issues** or performance problems.

We recommend using the security monitoring feature to stay informed about critical security updates even when regular updates are disabled. You can temporarily enable updates as needed.

---

## Who Should Use Complete Updates Manager

* Site owners who prefer **manual update control**
* Agencies managing **client websites** with locked-down configurations
* Developers maintaining **legacy or modified plugins/themes**
* Multisite administrators seeking update consistency across networks

---

## Plugin Highlights

* Compatible with **single-site and multisite**
* Lightweight, no bloat, no performance hit
* Fully translatable and **i18n-ready** (5 languages)
* Clean, PSR-compliant code structure
* WordPress Coding Standards compliant
* Static analysis with PHPStan
* Tested with PHP 7.4 -- 8.3

---

## Installation & Usage

1. Install and activate the plugin
2. Navigate to **Settings -> Updates Manager**
3. Choose which updates to disable (core, plugins, themes)
4. Optionally enable **security monitoring**
5. To check for updates, temporarily deactivate the plugin

---

## Development

### Requirements

* PHP 7.4+
* Composer
* Node.js & npm (for asset building)

### Setup

```
composer install
npm install
```

### Commands

| Command | Description |
|---------|-------------|
| `composer phpcs` | Run WordPress Coding Standards checks |
| `composer phpcs:fix` | Auto-fix coding standards issues |
| `composer phpstan` | Run static analysis |
| `composer test` | Run unit tests |
| `composer lint` | Run both phpcs and phpstan |
| `composer check` | Run lint + tests |
| `npm run build` | Minify CSS and JS assets |

### Plugin Source

The plugin source lives in `dist/complete-updates-manager/`. Files are edited directly in that directory.

---

## License

GPL-3.0. See [LICENSE](LICENSE).
