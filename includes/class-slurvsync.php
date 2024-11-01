<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://slurv.com
 * @since      1.1.6
 *
 * @package    slurvsync
 * @subpackage slurvsync/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    slurvsync
 * @subpackage slurvsync/includes
 * @author     Lawrence Davis <lawrence@kohactive.com>
 */
class SlurvSync {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Waitlisted_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name 	= 'slurv-sync';
		$this->version 			= '1.2.7';
		$this->endpoint 		= defined( 'SLURV_ENDPOINT' ) ? SLURV_ENDPOINT : 'http://chat.slurv.com/api/v1';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_user_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - SlurvSync_Loader. Orchestrates the hooks of the plugin.
	 * - SlurvSync_Admin. Defines all hooks for the admin area.
	 * - SlurvSync_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-slurvsync-loader.php';

		/**
		 * The class responsible for defining all actions that occur in the public area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-slurvsync-public.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-slurvsync-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the user registration
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'user/class-slurvsync-user.php';

		/**
		 * The class responsible for defining all shortcodes handled by the plugin
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-slurvsync-shortcodes.php';


		$this->loader = new SlurvSync_Loader();

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new SlurvSync_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_page');
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
	}

	/**
	 * Register all of the hooks related to the user-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_user_hooks() {

		$plugin_admin = new SlurvSync_Admin( $this->get_plugin_name(), $this->get_version() );
		$plugin_user = new SlurvSync_User( $this->get_plugin_name(), $this->get_version(), $this->get_endpoint() );

		if ( !get_option( 'slurv_disable_new_user_imports', '' ) ) {
			$this->loader->add_action( 'user_register', $plugin_user, 'sync_wordpress_user' );
		}

		$this->loader->add_action( 'wp_ajax_import_slurv_user', $plugin_user, 'ajax_import_slurv_user' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    SlurvSync_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Retrieve the API endpoint for the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The url of the API endpoint.
	 */
	public function get_endpoint() {
		if ( defined( 'SLURV_ENDPOINT' ) ) {
			return SLURV_ENDPOINT;
		} else {
			return 'http://chat.slurv.com/api/v1';
		}
	}

}
