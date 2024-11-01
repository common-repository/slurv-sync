<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once( dirname( __FILE__ ) . '/../user/class-slurvsync-user.php' );

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    slurvsync
 * @subpackage slurvsync/admin
 * @author     Lawrence Davis <lawrence@kohactive.com>
 */
class SlurvSync_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	public function add_plugin_admin_page() {
		global $slurv_page_settings;
		$slurv_page_settings = add_menu_page( 'Slurv Settings', 'Slurv', 'manage_options', 'slurv-settings', array( $this, 'render_slurv_settings' ) );
	}

	public function render_slurv_settings() {
		require_once( 'partials/slurvsync-admin-display.php' );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook ) {
		global $slurv_page_settings;

		if ( $hook == $slurv_page_settings ) {
			wp_register_style( 'slurvsync-admin', plugin_dir_url( __FILE__ ) . 'styles/admin.css' );
			wp_enqueue_style( 'slurvsync-admin' );
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {

		global $slurv_page_settings;

		if ( $hook == $slurv_page_settings ) {
			wp_enqueue_script( 'slurvsync-admin', plugin_dir_url( __FILE__ ) . 'scripts/async.js' );
			wp_enqueue_script( 'slurvsync-admin-colors', plugin_dir_url( __FILE__ ) . 'scripts/jqColorPicker.js' );

		}

	}

}
