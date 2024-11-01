<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://slurv.com
 * @since      1.0.0
 *
 * @package    slurvsync
 * @subpackage slurvsync/validation
 */

/**
 * The validation for admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    slurvsync
 * @subpackage slurvsync/validation
 * @author     Lawrence Davis <lawrence@kohactive.com>
 */

class SlurvSync_Admin_Validate {

  /**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
  public function __construct( $endpoint ) {
    $this->plugin_name          = "slurvsync";
    $this->endpoint             = $endpoint;
  }

  /**
	 * Validates the partner token and API endpoint provided, returns HTML for
	 * success or error messages.
   *
	 * @since    1.0.0
   * @param    string     $token      The site's partner token
   * @param    string     $endpoint   The base to use for API calls
	 */
  public function validate( $token ) {
    $validation = $this->validate_token( $token );
    
    if ( $validation ) {
      update_option( 'slurv_partner_token', $token );
      return "<div class='slurv-validation-success'><p>Successfully updated!</p></div>";
    } else {
      $message = "<div class='slurv-validation-error'>";
      $message .= "<p>Invalid API token.</p>";
      $message .= "</div>";
      return $message;
    }
  }

  public function validate_custom( $sidebar, $link, $logo ) {
    update_option( 'slurv_partner_custom_sidebar_bg', $sidebar );
    update_option( 'slurv_partner_custom_link_color', $link );
    update_option( 'slurv_partner_custom_logo', $logo );

    $message = "<div class='slurv-validation-warning'>
      <p>Successfully updated custom colors / logo.</p>
    </div>";

    return $message;
  }

  public function import( $validation ) {
    $success = $validation['success'];
    $failure = $validation['failure'];
    $message = "<div class='slurv-validation-warning'>
      <p>Successfully imported " . $success . " users.</p>";

    if ( $failure > 0) {
      $message .= "<p>Could not import " . $failure . " users.</p>";
    }

    $message .= "</div>";
    return $message;
  }

  public function disable_new_user_sync( $value ) {
    update_option( 'slurv_disable_new_user_imports', $value );
    $message = "<div class='slurv-validation-warning'><p>Syncing new WP users is now ";
    $message .= $value ? "disabled" : "enabled";
    $message .= "</p></div>";
    return $message;
  }

  /**
	 * Hits the leagues validation endpoint which will validate that the partner
   * token is valid as well as set the site's subdomain in WP options for
   * rendering the iframe shortcode.
   *
	 * @since    1.0.0
   * @param    string     $token      The site's partner token
   * @param    string     $endpoint   The base to use for API calls
	 */
  private function validate_token( $token ) {
    $endpoint = $this->endpoint . '/leagues/validate';
    $request  = wp_remote_post( $endpoint, array(
      'body' => array( "partner_token" => $token )
    ));

    if ( is_wp_error( $request ) || $request['response']['code'] == 401 ) {
      return false;
    }

    $league     = json_decode( $request['body'] );
    $subdomain  = $league->leagues->domain_name;

    update_option( 'slurv_subdomain', $subdomain );

    return true;
  }
}

?>
