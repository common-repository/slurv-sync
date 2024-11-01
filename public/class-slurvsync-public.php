<?php
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * The public-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    slurvsync
 * @subpackage slurvsync/public
 * @author     Lawrence Davis <lawrence@kohactive.com>
 */
class SlurvSync_Public {
  
  public function load_auth_javascripts( $post ) {
    if ( !is_admin() && has_shortcode( $post->post_content, 'slurv_auth_iframe' ) ) {
      wp_enqueue_script( 'slurvsync-auth', plugin_dir_url( __FILE__ ) . 'scripts/auth.js' );
    }
  }
}

add_action( 'the_post', array( 'SlurvSync_Public', 'load_auth_javascripts' ) );


?>
