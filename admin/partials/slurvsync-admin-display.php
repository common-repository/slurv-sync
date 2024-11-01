<?php
  if ( ! defined( 'ABSPATH' ) ) exit;

  require_once( dirname(__FILE__) . '/../class-slurvsync-validation.php' );
  require_once( dirname(__FILE__) . '/../../user/class-slurvsync-user.php' );

  $endpoint           = defined( 'SLURV_ENDPOINT' ) ? SLURV_ENDPOINT : 'http://chat.slurv.com/api/v1';
  $users              = new SlurvSync_User( 'slurvsync', '1.0.0', $endpoint );
  $validator          = new SlurvSync_Admin_Validate( $endpoint );

  // Create an array with form POST values
  $form = array(
    "empty" => $form['empty'],
    "token" => sanitize_key( $_POST['slurv_partner_token'] ),
    "nonce" => sanitize_text_field( $_POST['submit'] ),
    "form_submit" => sanitize_text_field( $_POST['slurv_form_type'] ),
    "sidebar_bg" => sanitize_text_field( $_POST['slurv_partner_color_sidebar_bg'] ),
    "link_color" => sanitize_text_field( $_POST['slurv_partner_color_link_color'] ),
    "logo_url" => sanitize_text_field( $_POST['slurv_partner_custom_logo'] ),
    "disable_sync" => sanitize_text_field( $_POST['slurv_disable_sync'] )
  );

  // Any form endpoint needs to be whitelisted in this array
  $whitelisted_forms  = array('partner_token', 'custom_colors', 'disable_sync');

  // Make sure the hidden field is in the whitelist array
  $valid_submit_val   = in_array($form['form_submit'], $whitelisted_forms);

  // An empty $_POST is a valid form, check for that or if the form is whitelisted
  $valid_submission   = $form['empty'] || $valid_submit_val;

  // Token is valid by default (assume it's empty) we will validate later if present
  $valid_token        = true;

  // All validation methods will return a message to show in an alert
  $validation_message   = '';

  // If there is a POST and it is the partner token form
  if ( !$form['empty'] && is_token_form( $form['form_submit'] ) ) {
    // Check the length to make sure it matches the length of tokens in db
    $valid_token = strlen( $form['token'] ) == 32;
    $valid       = $valid_token && $valid_submission;

    // Checks nonce against form endpoint
    validate_submission_nonce( $form['nonce'] );
    // Send data to the validator class
    $validation_message = sanitize_validate_and_submit( $users, $validator, $form, $endpoint, $valid );
  }

  // If it's the custom colors/logo form being submitted
  if ( !$form['empty'] && is_colors_form( $form['form_submit'] ) ) {
    // Use validate_hex_code method to check verification
    $valid_sidebar  = validate_hex_code( $form['sidebar_bg'] );
    $valid_link     = validate_hex_code( $form['link_color'] );
    $valid_logo     = validate_logo_url( $form['logo_url'] );

    // If all values are valid, check nonce and send to validator
    $valid = $valid_sidebar && $valid_link && $valid_logo && $valid_submission;
    validate_submission_nonce( $form['nonce'] );
    $validation_message = sanitize_validate_and_submit_colors( $validator, $form, $valid );
  }

  // If it's the form to disable new WP user sync
  if ( !$form['empty'] && is_user_sync_form( $form['form_submit'] ) ) {
    // Validate nonce and send to method that toggles site option
    validate_submission_nonce( $form['nonce'] );
    $is_disabled = $form['nonce'] == "Disable";
    $validation_message = $validator->disable_new_user_sync( $is_disabled );
  }

  function sanitize_validate_and_submit( $users, $validator, $form, $endpoint, $valid ) {
    if ( !$valid ) {
      return "<div class='slurv-validation-error'>
        <p>Invalid form submission.</p>
      </div>";
    }

    if ( $form['token'] && is_token_form( $form['form_submit'] ) ) {
      return $validator->validate( $form['token'], $endpoint );
    }
  }

  function sanitize_validate_and_submit_colors( $validator, $form, $valid ) {
    if ( !$valid ) {
      return "<div class='slurv-validation-error'>
        <p>Invalid custom colors or custom logo URL.</p>
      </div>";
    }

    if ( is_colors_form( $form['form_submit'] ) ) {
      return $validator->validate_custom( $form['sidebar_bg'], $form['link_color'], $form['logo_url'] );
    }
  }

  function is_token_form( $submit ) {
    return $submit == "partner_token";
  }

  function is_colors_form( $submit ) {
    return $submit == "custom_colors";
  }

  function is_user_sync_form( $submit ) {
    return $submit == "disable_sync";
  }

  function validate_submission_nonce( $val ) {
    check_admin_referer( "slurv-api-" . $val );
  }

  function validate_hex_code( $val ) {
    return strlen( $val ) == 7 && substr( $val, 0, 1 ) == "#";
  }

  function validate_logo_url( $val ) {
    return empty( $val ) || substr( $val, 0, 4 ) == "http";
  }

  // Gets default values for settings if this isn't a POST
  $syncable_users     = $users->get_users_without_tokens();
  $users_json         = json_encode( $syncable_users );
  $api_token          = get_option( 'slurv_partner_token', '' );
  $custom_sidebar_bg  = get_option( 'slurv_partner_custom_sidebar_bg', '' );
  $custom_link_color  = get_option( 'slurv_partner_custom_link_color', '' );
  $custom_logo_url    = get_option( 'slurv_partner_custom_logo', '' );
  $disable_new_users  = get_option( 'slurv_disable_new_user_imports', '' );
?>

<div class="wrap">
  <div class="card">
    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

    <?php
      if ( !empty( $validation_message ) ) {
        echo $validation_message;
      }
    ?>

    <form id="slurv-api-token-save" action="" method="POST">
      <p>API Partner Token</p>
      <input type="text" name="slurv_partner_token" size="60" value="<?php echo $api_token; ?>" />
      <input type="hidden" name="slurv_form_type" value="partner_token" />
      <?php wp_nonce_field( 'slurv-api-Save' ); ?>
      <?php submit_button( __('Save', "slurv-settings" ) ); ?>
    </form>

    <form id="slurv-custom-colors-save" action="" method="POST">
      <p>Custom Colors &amp; Logo</p>
      <input class="slurv-color-picker" type="text" placeholder="Sidebar Background" name="slurv_partner_color_sidebar_bg" size="60" value="<?php echo $custom_sidebar_bg; ?>" />
      <input class="slurv-color-picker" type="text" placeholder="Link Color" name="slurv_partner_color_link_color" size="60" value="<?php echo $custom_link_color; ?>" />
      <input type="text" placeholder="Logo URL" name="slurv_partner_custom_logo" size="60" value="<?php echo $custom_logo_url; ?>" />
      <input type="hidden" name="slurv_form_type" value="custom_colors" />
      <?php wp_nonce_field( 'slurv-api-Save' ); ?>
      <?php submit_button( __('Save', "slurv-settings" ) ); ?>
    </form>

    <h2>Disable New WP User Sync?</h2>
    <form id="slurv-api-disable-sync" action="" method="POST">
      <input type="hidden" name="slurv_form_type" value="disable_sync" />
      <?php if ( $disable_new_users ) : ?>
        <p>SlurvSync currently does not sync new Wordpress users.</p>
        <?php wp_nonce_field( 'slurv-api-Re-enable' ); ?>
        <?php submit_button( __( 'Re-enable', "slurv-settings" ) ); ?>
      <?php else: ?>
        <p>Currently SlurvSync will register all new Wordpress users with Slurv.</p>
        <?php wp_nonce_field( 'slurv-api-Disable' ); ?>
        <?php submit_button( __( 'Disable', "slurv-settings" ) ); ?>
      <?php endif; ?>
    </form>

    <h2>Sync Existing Users to Slurv</h2>
    <p><?php echo sizeof( $syncable_users ) . " users have not been synced to Slurv."; ?></p>
    <p>Be patient, this may take a while if you have a lot of users.</p>

    <form id="slurv-api-user-sync" action="" method="POST">
      <?php wp_nonce_field( 'slurv-api-Sync' ); ?>
      <?php submit_button( __('Sync', "slurv-settings" ), 'primary', 'submit', true, array( "class" => "slurv-sync-users-btn" ) ); ?>
    </form>

    <div class="slurv-users-progress hidden">
      <div class="progress-outer">
        <div class="progress-bar blue">
          <div class="progress-inner"></div>
        </div>
      </div>

      <p>Syncing <span class="slurv-users-progress-current">0</span> out of <?php echo sizeof( $syncable_users ); ?> users.</p>
    </div>
  </div>
</div>

<style type="text/css">
  .slurv-color-picker {
    margin-bottom: 15px;
  }
</style>

<script type="text/javascript">
var $ = jQuery;
$(document).ready(function() {
  // CUSTOM COLORS COLORPICKER
  $('.slurv-color-picker').each(function() {
    $(this).colorPicker({
      renderCallback: function($elm, toggled) {
        if (this.color) {
          var hexCode = this.color.colors.HEX;
          $elm.val('#' + hexCode);
        }
      }
    });
  });

  // USER IMPORT AJAX HANDLER
  $('#slurv-api-user-sync').submit(function(e) {
    e.preventDefault();

    var form = $(this).serializeArray();
    var users = <?php echo $users_json; ?>;
    var users_to_sync = users.length;
    var users_synced = 0;

    if (users_to_sync < 1) {
      alert("All users have been synced.");
      return;
    };

    $('.slurv-users-progress').removeClass('hidden');

    var toggleSyncButton = function() {
      $('input[value="Sync"]').toggleClass('disabled');
    }

    var updateProgressBar = function(i) {
      $('.slurv-users-progress-current').text(i);
      $('.progress-bar .progress-inner').width(((i / users_to_sync) * 100) + "%") ;

      if (i == users_to_sync) {
        alert("User sync completed.");
        toggleSyncButton();
        return;
      }
    };

    var importUser = function(id, callback) {
      var data = {
        action: 'import_slurv_user',
        user: id,
        form: form
      };

      $.post(ajaxurl, data, function(response) {
        users_synced++;
        updateProgressBar(users_synced);
        callback(response);
      });
    };

    toggleSyncButton();

    async.map(users, importUser, function(err, results) {
      console.log(err);
      console.log(results);
    });
  });
});
</script>
