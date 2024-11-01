<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.

 * @link              http://slurv.com
 * @since             1.1.6
 * @package           slurv.com
 *
 * @wordpress-plugin
 * Plugin Name:       SlurvSync
 * Plugin URI:        http://slurv.com
 * Description:       Sync your WP users with Slurv.
 * Version:           1.2.7
 * Author:            slurv.com
 * Author URI:        http://slurv.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       slurvsync
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-waitlisted-activator.php
 */
function activate_slurvsync() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-slurvsync-activator.php';
	SlurvSync_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-waitlisted-deactivator.php
 */
function deactivate_slurvsync() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-slurvsync-deactivator.php';
	SlurvSync_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_slurvsync' );
register_deactivation_hook( __FILE__, 'deactivate_slurvsync' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-slurvsync.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.1.6
 */
function run_slurvsync() {

	$plugin = new SlurvSync();
	$plugin->run();

}
run_slurvsync();
