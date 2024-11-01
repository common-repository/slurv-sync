<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://slurv.com
 * @since      1.0.0
 *
 * @package    slurvsync
 * @subpackage slurvsync/user
 * @author     Lawrence Davis <lawrence@kohactive.com>
 */
class SlurvSync_User {

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
	 * The API endpoint for this plugin.
	 *
	 * @since    1.0.1
	 * @access   private
	 * @var      string    $endpoint   The URL of the API endpoint.
	 */
	private $endpoint;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.1
	 * @param    string    $plugin_name   The name of the plugin.
	 * @param    string    $version    		The version of this plugin.
	 * @param    string    $endpoint    	The URL of the API endpoint.
	 */
	public function __construct( $plugin_name, $version, $endpoint ) {

		$this->plugin_name 	= $plugin_name;
		$this->version 		 	= $version;
		$this->endpoint		 	= $endpoint;
		$this->token			 	= get_option( 'slurv_partner_token', '' );
	}

	/**
	 * Register the hooks for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */

	public function sync_all_users() {
		$count_success = 0;
		$count_failure = 0;

		$users = $this->get_users_without_tokens();
		/**
		* Loop over each user, add them to slurv and set the login token on their
		* user metadata
		*/

		$users_request = array();

		foreach( $users as $user ) {
			$this_user  = array(
				"email" 		=> $user->user_email,
				"user_id" 	=> $user->ID,
				"username"	=> $this->sanitize_username( $user->user_login )
			);

			array_push( $users_request, $this_user );
		}

		$users_json = json_encode( $users_request );
		$this->create_user_import( $users_json );
	}

  public function sync_wordpress_user( $user_id ) {
		/**
		* Called when a new user is added. Double checks that a login token doesn't
		* already exist in metadata.
		*/

		$user = get_userdata( $user_id );

		if ( $this->has_login_token( $user ) ) {
			return;
		}

		$email    = $user->user_email;
		$username = $this->sanitize_username( $user->user_login );
		return $this->add_user_to_slurv( $username, $email, $user_id );
  }

	public function ajax_import_slurv_user() {
		/**
		* Callback from admin-ajax that passes the user ID to sync_wordpress_user
		* and returns a JSON response
		*/

		$form 		= $_POST['form'];
		$verified = wp_verify_nonce( $form[0]['value'], 'slurv-api-Sync' );

		if ( !$verified ) {
			wp_die();
		}

		$user_id 	= intval( $_POST['user'] );
		$status 	= $this->sync_wordpress_user( $user_id );

		$status_string = $status == true ? "ok" : "error";

		$response = array(
			"user" 		=> $user_id,
			"status"	=> $status_string
		);

		echo json_encode( $response );
		wp_die();
	}


	public function get_users_without_tokens() {
		/**
		* Get all users from WP database who don't have a slurv_login_token in
		* their user metadata
		*/
		$users_args = array(
			"meta_key" 			=> 'slurv_login_token',
			"meta_compare" 	=> 'NOT EXISTS'
		);

		$users 			= get_users( $users_args );
		return array_map( function( $user ) {
			return $user->ID;
		}, $users);
	}

	private function add_user_to_slurv( $username, $email, $user_id ) {
		/**
		* This method is called by both sync_wordpress_user and sync_all_users
		* and takes care of setting all necessary params on the user object and
		* sending it off to the API
		*/
		$password = $this->generate_password( $email );
		$request 	= $this->create_user_request( $email, $username, $password );
		if ( !is_wp_error( $request ) && $request['response']['code'] == 200 ) {
			$this->set_user_login_token( $user_id, $request['body'] );
			return true;
		}

		return false;
	}

	private function has_login_token( $user ) {
		/**
		* Utility function to see if user already has a slurv_login_token set in
		* their user metadata
		*/
		$token = get_user_meta( $user->ID, 'slurv_login_token', true );
		return !empty( $token );
	}

	private function set_user_login_token( $user, $response ) {
		/**
		* Sets the slurv_login_token on user metadata
		*/
		$json = json_decode( $response );
		$token = sanitize_key( $json->users->login_token );
		if ( $token && strlen( $token ) == 32 ) {
			update_user_meta( $user, 'slurv_login_token', $token );
		}
	}

  private function generate_password( $email ) {
		/**
		* Utility function that creates a random password based on an MD5 hash of
		* the user's email address.
		*/
    return substr( md5( $email ), -20 );
  }

	private function sanitize_username( $username ) {
		/**
		* Utility function that makes sure the username is allowed in Slurv.
		*/
		$split = explode( "@", $username )[0];
		return preg_replace( "/[^a-zA-Z0-9]+/", "", $split );
	}

  private function create_user_request( $email, $username, $pass ) {
		/**
		* This function sends the user object to the API and returns the request
		* using wp_remote_post
		*/
    $endpoint = $this->endpoint . '/users';
		$token		= $this->token;

		$request_args = array(
			"body" => array(
	      "partner_token" => $token,
	      "user" => array(
	        "username" 			=> $username,
					"display_name" 	=> $username,
	        "email"    			=> $email,
	        "password" 			=> $pass
	      )
			)
    );

	 	return wp_remote_post( $endpoint, $request_args );
  }


	private function create_user_import( $users ) {
		$endpoint = $this->endpoint . '/users/import';
		$token		= $this->token;

		$request_args = array(
			"headers" 	=> array( "Content-Type" => 'application/json' ),
			"body" 			=> $users
		);

		return wp_remote_post( $endpoint, $request_args );
	}
}
