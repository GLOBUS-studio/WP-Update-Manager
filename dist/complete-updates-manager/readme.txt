=== Complete Updates Manager ===
Tags: disable updates, update control, security, wordpress updates, plugins updates, themes updates
Requires at least: 3.8
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Advanced tool to fully disable WordPress theme, plugin and core update checking, related cronjobs and notifications with customization options.

== Description ==

The Complete Updates Manager plugin provides a robust solution to disable the WordPress update checking system. It prevents WordPress from checking for updates including cronjobs, and suppresses all update-related notifications in the admin area.

**Key Features:**

* Completely disables WordPress core updates
* Prevents plugin update checks and notifications
* Blocks theme update checks and notifications
* Removes update-related items from Site Health screen
* Blocks update requests to WordPress API servers
* Disables all automatic update email notifications
* Includes admin bar notification showing that updates are disabled
* Configurable settings page to customize which updates to disable
* Security monitoring option for critical updates even when updates are disabled

**Important Security Notice:**

It's *critical* to keep your WordPress theme, core and plugins up to date when not using this plugin! If you don't, your site could become **vulnerable to security issues** or performance problems.

We recommend using the security monitoring feature to stay informed about critical security updates even when regular updates are disabled. You can temporarily enable updates as needed.

== Frequently Asked Questions ==

= How can I check for updates when using this plugin? =

1. You can use the built-in security monitoring feature to get notifications about critical security updates
2. Or temporarily deactivate the plugin for a short time to check for all updates

= Is this plugin compatible with multisite installations? =

Yes, this plugin works with both single site and multisite WordPress installations.

= Will this plugin block security updates? =

By default, yes, this plugin blocks ALL types of updates, including security updates. However, you can:

1. Enable the security monitoring option in settings to receive notifications about critical security updates
2. Customize which types of updates to disable (core, plugins, themes)
3. Temporarily check for updates by deactivating the plugin when needed

= How can I access the plugin settings? =

Go to Settings > Updates Manager in your WordPress admin panel to configure the plugin options.

== Installation ==

1. Download the plugin and unzip it.
2. Upload the folder complete-updates-manager/ to your /wp-content/plugins/ folder.
3. Activate the plugin from your WordPress admin panel.
4. Go to Settings > Updates Manager to configure which updates to disable.
5. Optional: Enable security monitoring to get notifications about critical updates.

To temporarily check for updates, simply deactivate the plugin, check for updates, and then reactivate it.

== Changelog ==

= 1.0.1 =
* Added settings page under Settings menu
* Added configurable options for disabling specific update types
* Added security monitoring for critical updates
* Improved admin interface with settings link
* Added proper localization for all text strings

= 1.0.0 =
* Initial release
* Complete code refactoring for better performance and maintainability
* Added admin bar notification
* Added helper functions
* PSR compliant code structure
