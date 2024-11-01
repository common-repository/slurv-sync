<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class SlurvSync_Shortcodes {

  public static function render_slurv_auth( $attrs, $content = null ) {
    $a = shortcode_atts( array(
      'width'   => '100%',
      'height'  => '1000'
    ), $attrs );

    $user_token = get_user_meta( $user->ID, 'slurv_login_token', true );
    if ( $user_token ) {
      return self::render_iframe_code( $attrs, $content, $user_token);
    }

    $endpoint = defined( 'SLURV_ENDPOINT' ) ? SLURV_ENDPOINT : 'http://chat.slurv.com/api/v1';
    $endpoint .= '/token_auth';

    $subdomain  = get_option( 'slurv_subdomain', '' );
    $sidebar    = get_option( 'slurv_partner_custom_sidebar_bg', '' );
    $link_color = get_option( 'slurv_partner_custom_link_color', '' );
    $logo_url   = get_option( 'slurv_partner_custom_logo', '' );

    $returnedHTML = '<div id="slurv-auth-container">'
      . '<div class="errors"></div>'
      . '<form action="" class="slurv-auth-form">'
      . '<label>Log in to Slurv</label>'
      . '<input name="email" type="text" placeholder="Email">'
      . '<input name="password" type="password" placeholder="Password">'
      . '<input name="subdomain" type="hidden" value=' . $subdomain . '>'
      . '<input name="endpoint" type="hidden" value=' . $endpoint . '>'
      . '<input name="height" type="hidden" value=' . $a['height'] . '>'
      . '<input name="width" type="hidden" value=' . $a['width'] . '>';

    if ( !empty( $sidebar ) ) {
      $returnedHTML .= '<input name="sidebar" type="hidden" value=' . $sidebar . '>';
    }

    if ( !empty( $link_color ) ) {
      $returnedHTML .= '<input name="link_color" type="hidden" value=' . $link_color . '>';
    }

    if ( !empty( $logo_url ) ) {
      $returnedHTML .= '<input name="logo_url" type="hidden" value=' . $logo_url . '>';
    }

    $returnedHTML .= '<button type="submit">Login</button>'
      . '</form>'
      . '</div>';

    return $returnedHTML;
  }

  public static function render_slurv_iframe( $attrs, $content = null ) {
    return self::render_iframe_code( $attrs, $content, null );
  }

  private static function render_iframe_code( $attrs, $content, $token ) {

    $a = shortcode_atts( array(
      'width'   => '100%',
      'height'  => '1000'
    ), $attrs );

    $user       = wp_get_current_user();
    $subdomain  = get_option( 'slurv_subdomain', '' );
    $sidebar    = get_option( 'slurv_partner_custom_sidebar_bg', '' );
    $link_color = get_option( 'slurv_partner_custom_link_color', '' );
    $logo_url   = get_option( 'slurv_partner_custom_logo', '' );

    if ( !$token ) {
      $token = get_user_meta( $user->ID, 'slurv_login_token', true );
    }

    $width      = $a['width'];
    $height     = $a['height'];

    $returnedHTML = '';

    if ( $user == false || empty( $token ) || empty( $subdomain ) ) {
      return "<p>Cannot authenticate with Slurv.</p>";
    }

    if ( eregi("MSIE", getenv( "HTTP_USER_AGENT" ) ) || eregi("Internet Explorer", getenv("HTTP_USER_AGENT" ) ) ) {
      $newWindowHTML = 'http://' . $subdomain . '/login?token=' . $token;
      $returnedHTML = 'Internet Explorer users: If you are having trouble'
       . ' loading chat, please <a target="_blank" href="'
       . $newWindowHTML
       . '">click here to open in a new window.</a>';
    }

    $returnedHTML .= "<iframe frameborder='no' width='"
      . $width
      . "' height='"
      . $height
      . "' src='http://"
      . $subdomain
      . "/login?token="
      . $token
      . "&showLeagues=false";

    if ( !empty( $sidebar ) ) {
      $sidebar_param = substr( $sidebar, 1, strlen( $sidebar ) );
      $returnedHTML .= "&sidebarBg=" . $sidebar_param;
    }

    if ( !empty( $link_color ) ) {
      $link_color_param = substr( $link_color, 1, strlen( $link_color ) );
      $returnedHTML .= "&linkColor=" . $link_color_param;
    }

    if ( !empty( $logo_url ) ) {
      $logo_url_param = urlencode( $logo_url );
      $returnedHTML .= "&logoUrl=" . $logo_url_param;
    }

    $returnedHTML .= "'></iframe>";
    return $returnedHTML;
  }
}

add_shortcode( 'slurv_auth_iframe', array( 'SlurvSync_Shortcodes', 'render_slurv_auth' ) );
add_shortcode( 'slurv_iframe', array( 'SlurvSync_Shortcodes', 'render_slurv_iframe' ) );
