=== Plugin Name ===
Contributors: kohactivellc
Tags: slurv, sports
Requires at least: 3.0.1
Tested up to: 4.4.2
Stable tag: 1.2.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Sync all of your Wordpress users to your Slurv league. Use the slurv_iframe shortcode to embed Slurv in a page or post.

== Description ==

This plugin will add a settings page to your Wordpress admin that will allow you
to import your existing users to Slurv. It also hooks into user registration and,
once installed, will add new users to your Slurv league.

You can use the `[slurv_iframe]` shortcode anywhere and if a user
is logged in then your Slurv league will render in the page or post.

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings->Plugin Name screen to configure the plugin
1. Go to Settings->Slurv and add your API Partner Token
1. Click "Save"
1. If your API Partner Token is valid, import your existing users by clicking "Sync"


== Frequently Asked Questions ==

= How do I get an API Partner Token? =

You must be a Slurv partner to receive a partner token. To inquire about becoming a
Slurv partner, email info@slurv.com

== Screenshots ==

1. The Slurv settings page

== Changelog ==

= 1.1.0 =
* First public release.

= 1.1.1 =
* Add notice for IE users to open Slurv in new window when shortcode is used.

= 1.1.6 =
* Add custom colors and logo URL to settings

= 1.2.0 =
* Add ability for users to login by email/password through shortcode

= 1.2.1 =
* SVN fix

= 1.2.2 =
* Tagging fix

= 1.2.3 =
* Add some login form markup

= 1.2.4 =
* Hide elements with data attribute after authentication

= 1.2.5 =
* jQuery syntax fix

= 1.2.6 =
* Update Slurv API endpoint

= 1.2.7 =
* PHP fix
