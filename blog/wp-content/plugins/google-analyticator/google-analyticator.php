<?php
/*
 * Plugin Name: Google Analyticator
 * Version: 6.3.4
 * Plugin URI: http://wordpress.org/extend/plugins/google-analyticator/
 * Description: Adds the necessary JavaScript code to enable <a href="http://www.google.com/analytics/">Google's Analytics</a>. After enabling this plugin you need to authenticate with Google, then select your domain and you're set.
 * Author: Video User Manuals
 * Author URI: http://www.videousermanuals.com
 * Text Domain: google-analyticator
 */

define('GOOGLE_ANALYTICATOR_VERSION', '6.3.4');

define('GOOGLE_ANALYTICATOR_CLIENTID', '1007949979410.apps.googleusercontent.com');
define('GOOGLE_ANALYTICATOR_CLIENTSECRET', 'q06U41XDXtzaXD14E-KO1hti'); //don't worry - this don't need to be secret in our case
define('GOOGLE_ANALYTICATOR_REDIRECT', 'urn:ietf:wg:oauth:2.0:oob');
define('GOOGLE_ANALYTICATOR_SCOPE', 'https://www.googleapis.com/auth/analytics.readonly');

// Constants for enabled/disabled state
define("ga_enabled", "enabled", true);
define("ga_disabled", "disabled", true);

// Defaults, etc.
define("key_ga_uid", "ga_uid", true);
define("key_ga_status", "ga_status", true);
define("key_ga_admin", "ga_admin_status", true);
define("key_ga_admin_disable", "ga_admin_disable", true);
define("key_ga_admin_role", "ga_admin_role", true);
define("key_ga_dashboard_role", "ga_dashboard_role", true);
define("key_ga_adsense", "ga_adsense", true);
define("key_ga_extra", "ga_extra", true);
define("key_ga_extra_after", "ga_extra_after", true);
define("key_ga_event", "ga_event", true);
define("key_ga_outbound", "ga_outbound", true);
define("key_ga_outbound_prefix", "ga_outbound_prefix", true);
define("key_ga_downloads", "ga_downloads", true);
define("key_ga_downloads_prefix", "ga_downloads_prefix", true);
define("key_ga_widgets", "ga_widgets", true);
define("key_ga_sitespeed", "ga_sitespeed", true);

define("ga_uid_default", "UA-XXXXXXXX-X", true);
define("ga_google_token_default", "", true);
define("ga_status_default", ga_disabled, true);
define("ga_admin_default", ga_enabled, true);
define("ga_admin_disable_default", 'remove', true);
define("ga_adsense_default", "", true);
define("ga_extra_default", "", true);
define("ga_extra_after_default", "", true);
define("ga_event_default", ga_enabled, true);
define("ga_outbound_default", ga_enabled, true);
define("ga_outbound_prefix_default", 'outgoing', true);
define("ga_downloads_default", "", true);
define("ga_downloads_prefix_default", "download", true);
define("ga_widgets_default", ga_enabled, true);
define("ga_sitespeed_default", ga_enabled, true);

// Create the default key and status
add_option(key_ga_status, ga_status_default, '');
add_option(key_ga_uid, ga_uid_default, '');
add_option(key_ga_admin, ga_admin_default, '');
add_option(key_ga_admin_disable, ga_admin_disable_default, '');
add_option(key_ga_admin_role, array('administrator'), '');
add_option(key_ga_dashboard_role, array('administrator'), '');
add_option(key_ga_adsense, ga_adsense_default, '');
add_option(key_ga_extra, ga_extra_default, '');
add_option(key_ga_extra_after, ga_extra_after_default, '');
add_option(key_ga_event, ga_event_default, '');
add_option(key_ga_outbound, ga_outbound_default, '');
add_option(key_ga_outbound_prefix, ga_outbound_prefix_default, '');
add_option(key_ga_downloads, ga_downloads_default, '');
add_option(key_ga_downloads_prefix, ga_downloads_prefix_default, '');
add_option(key_ga_sitespeed, ga_sitespeed_default, '');
add_option(key_ga_widgets, ga_widgets_default, '');
add_option('ga_defaults', 'yes' );
add_option('ga_google_token', '', '');


 $useAuth = ( get_option( 'ga_google_token' ) == '' ? false : true );


# Check if we have a version of WordPress greater than 2.8
if ( function_exists('register_widget') ) {

	# Check if widgets are enabled and the auth has been set!
	if ( get_option(key_ga_widgets) == 'enabled'  && $useAuth ) {

		# Include Google Analytics Stats widget
		require_once('google-analytics-stats-widget.php');

		# Include the Google Analytics Summary widget
		require_once('google-analytics-summary-widget.php');
		$google_analytics_summary = new GoogleAnalyticsSummary();

	}

}

// Create a option page for settings
add_action('admin_init', 'ga_admin_init');
add_action('admin_menu', 'add_ga_option_page');

// Initialize the options
function ga_admin_init() {
	# Load the localization information
	$plugin_dir = basename(dirname(__FILE__));
	load_plugin_textdomain('google-analyticator', 'wp-content/plugins/' . $plugin_dir . '/localizations', $plugin_dir . '/localizations');
}

# Add the core Google Analytics script, with a high priority to ensure last script for async tracking
add_action('wp_head', 'add_google_analytics', 999999);
add_action('login_head', 'add_google_analytics', 999999);

# Initialize outbound link tracking
add_action('init', 'ga_outgoing_links');

// Hook in the options page function
function add_ga_option_page() {

	$plugin_page = add_options_page(__('Google Analyticator Settings', 'google-analyticator'), 'Google Analytics', 'manage_options', basename(__FILE__), 'ga_options_page');
	add_action('load-'.$plugin_page, 'ga_pre_load' );

        $activate_page = add_submenu_page( null, 'Activation', 'Google Analytics', 'manage_options', 'ga_activate' , 'ga_activate');


        $reset_page = add_submenu_page(null, 'Reset', 'Reset', 'activate_plugins', 'ga_reset', 'ga_reset' );
        add_action('load-'.$reset_page, 'ga_do_reset' );

}

add_action('plugin_action_links_' . plugin_basename(__FILE__), 'ga_filter_plugin_actions');

function ga_pre_load()
{

    if( isset( $_POST['key_ga_google_token'] ) ):

        check_admin_referer('google-analyticator-update_settings');

        // Nolonger defaults
        update_option('ga_defaults', 'no');

        // Update GA Token
        update_option('ga_google_token', $_POST['key_ga_google_token']);


    endif;

    if( get_option('ga_defaults') == 'yes' ):

        wp_redirect( admin_url('options-general.php?page=ga_activate') );
        exit;

    endif;
}

function ga_activate()
{

if (! function_exists('curl_init')) {
  print('Google PHP API Client requires the CURL PHP extension');
  return;
}

if (! function_exists('json_decode')) {
  print('Google PHP API Client requires the JSON PHP extension');
  return;
}

if (! function_exists('http_build_query')) {
  print('Google PHP API Client requires http_build_query()');
  return;
}

$url = http_build_query( array(
                                'next' => admin_url('/options-general.php?page=google-analyticator.php'),
                                'scope' => GOOGLE_ANALYTICATOR_SCOPE,
                                'response_type'=>'code',
                                'redirect_uri'=>GOOGLE_ANALYTICATOR_REDIRECT,
                                'client_id'=>GOOGLE_ANALYTICATOR_CLIENTID
                                )
                        );

    ?>
    <div class="wrap">

        <h2>Activate Google Analyticator</h2>

            <p><strong>Google Authentication Code </strong> </p>

        <p>You need to sign in to Google and grant this plugin access to your Google Analytics account</p>

        <p>
            <a
                onclick="window.open('https://accounts.google.com/o/oauth2/auth?<?php echo $url ?>', 'activate','width=700, height=600, menubar=0, status=0, location=0, toolbar=0')"
                target="_blank"
                href="javascript:void(0);"> Click Here </a> - <small> Or <a target="_blank" href="https://accounts.google.com/o/oauth2/auth?<?php echo $url ?>">here</a> if you have popups blocked</small>
        </p>

        <div  id="key">

            <p>Enter your Google Authentication Code in this box. This code will be used to get an Authentication Token so you can access your website stats.</p>
            <form method="post" action="<?php echo admin_url('options-general.php?page=google-analyticator.php');?>">
                <?php wp_nonce_field('google-analyticator-update_settings'); ?>
                <input type="text" name="key_ga_google_token" value="" style="width:450px;"/>
                <input type="submit"  value="Save &amp; Continue" />
            </form>
        </div>

		<br /><br /><br />
		<hr />
		<br />

            <p><strong>I Don't Want To Authenticate Through Google </strong> </p>
            
            <p>If you don't want to authenticate through Google and only use the tracking capability of the plugin (<strong><u>not the dashboard functionality</u></strong>), you can do this by clicking the button below. </p>
            <p>You will be asked on the next page to manually enter your Google Analytics UID.</p>
            <form method="post" action="<?php echo admin_url('options-general.php?page=google-analyticator.php');?>">
            <input type="hidden" name="key_ga_google_token" value="" />
            <?php wp_nonce_field('google-analyticator-update_settings'); ?>
            <input type="submit"  value="Continue Without Authentication" />
            </form>


    </div>

    <?php
}

// Add settings option
function ga_filter_plugin_actions($links) {
	$new_links = array();

	$new_links[] = '<a href="' . admin_url('options-general.php?page=google-analyticator.php').'">' . __('Settings', 'google-analyticator') . '</a>';
        $new_links[] = '<a href="' . admin_url('options-general.php?page=ga_reset">') . __('Reset', 'google-analyticator') . '</a>';

	return array_merge($new_links, $links);
}

function ga_do_reset()
{
    // Delete all GA options.
    delete_option(key_ga_status);
    delete_option(key_ga_uid);
    delete_option(key_ga_admin);
    delete_option(key_ga_admin_disable);
    delete_option(key_ga_admin_role);
    delete_option(key_ga_dashboard_role);
    delete_option(key_ga_adsense);
    delete_option(key_ga_extra);
    delete_option(key_ga_extra_after);
    delete_option(key_ga_event);
    delete_option(key_ga_outbound);
    delete_option(key_ga_outbound_prefix);
    delete_option(key_ga_downloads);
    delete_option(key_ga_downloads_prefix);
    delete_option(key_ga_sitespeed);
    delete_option(key_ga_widgets);
    delete_option('ga_defaults');
    delete_option('ga_google_token');
    delete_option('ga_google_authtoken');
    delete_option('ga_profileid');



    wp_redirect( admin_url( 'options-general.php?page=ga_activate' ) );
    exit;
}

function ga_reset(){ /* Wont ever run. */ }


function ga_options_page() {

	// If we are a postback, store the options
	if (isset($_POST['info_update'])) {
		# Verify nonce
		check_admin_referer('google-analyticator-update_settings');

                update_option('ga_defaults', 'no');


		// Update the status
		$ga_status = $_POST[key_ga_status];
		if (($ga_status != ga_enabled) && ($ga_status != ga_disabled))
			$ga_status = ga_status_default;
		update_option(key_ga_status, $ga_status);

		// Update the UID
		$ga_uid = $_POST[key_ga_uid];
		if ($ga_uid == '')
			$ga_uid = ga_uid_default;
		update_option(key_ga_uid, $ga_uid);

		// Update the admin logging
		$ga_admin = $_POST[key_ga_admin];
		if (($ga_admin != ga_enabled) && ($ga_admin != ga_disabled))
			$ga_admin = ga_admin_default;
		update_option(key_ga_admin, $ga_admin);

		// Update the admin disable setting
		$ga_admin_disable = $_POST[key_ga_admin_disable];
		if ( $ga_admin_disable == '' )
			$ga_admin_disable = ga_admin_disable_default;
		update_option(key_ga_admin_disable, $ga_admin_disable);

		// Update the admin level
		if ( array_key_exists(key_ga_admin_role, $_POST) ) {
			$ga_admin_role = $_POST[key_ga_admin_role];
		} else {
			$ga_admin_role = "";
		}
		update_option(key_ga_admin_role, $ga_admin_role);

		// Update the dashboard level
		if ( array_key_exists(key_ga_dashboard_role, $_POST) ) {
			$ga_dashboard_role = $_POST[key_ga_dashboard_role];
		} else {
			$ga_dashboard_role = "";
		}
		update_option(key_ga_dashboard_role, $ga_dashboard_role);

		// Update the extra tracking code
		$ga_extra = $_POST[key_ga_extra];
		update_option(key_ga_extra, $ga_extra);

		// Update the extra after tracking code
		$ga_extra_after = $_POST[key_ga_extra_after];
		update_option(key_ga_extra_after, $ga_extra_after);

		// Update the adsense key
		$ga_adsense = $_POST[key_ga_adsense];
		update_option(key_ga_adsense, $ga_adsense);

		// Update the event tracking
		$ga_event = $_POST[key_ga_event];
		if (($ga_event != ga_enabled) && ($ga_event != ga_disabled))
			$ga_event = ga_event_default;
		update_option(key_ga_event, $ga_event);

		// Update the outbound tracking
		$ga_outbound = $_POST[key_ga_outbound];
		if (($ga_outbound != ga_enabled) && ($ga_outbound != ga_disabled))
			$ga_outbound = ga_outbound_default;
		update_option(key_ga_outbound, $ga_outbound);

		// Update the outbound prefix
		$ga_outbound_prefix = $_POST[key_ga_outbound_prefix];
		if ($ga_outbound_prefix == '')
			$ga_outbound_prefix = ga_outbound_prefix_default;
		update_option(key_ga_outbound_prefix, $ga_outbound_prefix);

		// Update the download tracking code
		$ga_downloads = $_POST[key_ga_downloads];
		update_option(key_ga_downloads, $ga_downloads);

		// Update the download prefix
		$ga_downloads_prefix = $_POST[key_ga_downloads_prefix];
		if ($ga_downloads_prefix == '')
			$ga_downloads_prefix = ga_downloads_prefix_default;
		update_option(key_ga_downloads_prefix, $ga_downloads_prefix);

		// Update the widgets option
		$ga_widgets = $_POST[key_ga_widgets];
		if (($ga_widgets != ga_enabled) && ($ga_widgets != ga_disabled))
			$ga_widgets = ga_widgets_default;
		update_option(key_ga_widgets, $ga_widgets);


		// Update the sitespeed option
		$ga_sitespeed = $_POST[key_ga_sitespeed];
		if (($ga_sitespeed != ga_enabled) && ($ga_sitespeed != ga_disabled))
			$ga_sitespeed = ga_widgets_default;
		update_option(key_ga_sitespeed, $ga_sitespeed);

		// Give an updated message
		echo "<div class='updated fade'><p><strong>" . __('Google Analyticator settings saved.', 'google-analyticator') . "</strong></p></div>";
	}


        // Are we using the auth system?
        $useAuth = ( get_option( 'ga_google_token' ) == '' ? false : true );


	// Output the options page
	?>

		<div class="wrap">

		<h2><?php _e('Google Analyticator Settings', 'google-analyticator'); ?></h2>

		<form method="post" action="<?php echo admin_url('options-general.php?page=google-analyticator.php');?>">
			<?php
			# Add a nonce
			wp_nonce_field('google-analyticator-update_settings');
			?>

			<?php if (get_option(key_ga_status) == ga_disabled) { ?>
				<div style="margin:10px auto; border:3px #f00 solid; background-color:#fdd; color:#000; padding:10px; text-align:center;">
				<?php _e('Google Analytics integration is currently <strong>DISABLED</strong>.', 'google-analyticator'); ?>
				</div>
			<?php } ?>
			<?php if ((get_option(key_ga_uid) == "XX-XXXXX-X") && (get_option(key_ga_status) != ga_disabled)) { ?>
				<div style="margin:10px auto; border:3px #f00 solid; background-color:#fdd; color:#000; padding:10px; text-align:center;">
				<?php _e('Google Analytics integration is currently enabled, but you did not enter a UID. Tracking will not occur.', 'google-analyticator'); ?>
				</div>
			<?php } ?>
			<table class="form-table" cellspacing="2" cellpadding="5" width="100%">

                            <tr>
                                <td colspan="2">
                                    <h3><?php _e('Basic Settings', 'google-analyticator'); ?></h3>
                                </td>
                            </tr>

				<tr>
					<th width="30%" valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_status ?>"><?php _e('Google Analytics logging is', 'google-analyticator'); ?>:</label>
					</th>
					<td>
						<?php
						echo "<select name='".key_ga_status."' id='".key_ga_status."'>\n";

						echo "<option value='".ga_enabled."'";
						if(get_option(key_ga_status) == ga_enabled)
							echo " selected='selected'";
						echo ">" . __('Enabled', 'google-analyticator') . "</option>\n";

						echo "<option value='".ga_disabled."'";
						if(get_option(key_ga_status) == ga_disabled)
							echo" selected='selected'";
						echo ">" . __('Disabled', 'google-analyticator') . "</option>\n";

						echo "</select>\n";
						?>
					</td>
				</tr>
				<tr id="ga_ajax_accounts">
					<th valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_uid; ?>"><?php _e('Google Analytics UID', 'google-analyticator'); ?>:</label>
					</th>
					<td>
                                            <?php

                                            if( $useAuth ):
                                                
                                                $uids = ga_get_analytics_accounts();

                                                echo "<select name='".key_ga_uid."'> ";

                                                foreach($uids as $id=>$domain):

                                                    echo '<option value="'.$id.'"';
                                                    // If set in DB.
                                                    if( get_option(key_ga_uid) == $id ) { echo ' selected="selected"'; }
                                                    // Else if the domain matches the current domain & nothing set in DB.
                                                    elseif( $_SERVER['HTTP_HOST'] == $domain && ( get_option(key_ga_uid) != '' ) ) { echo ' selected="selected"'; }
                                                    echo '>'.$domain.'</option>';

                                                endforeach;
                                                
                                                echo '</select>';

                                            else:

                                                echo '<input type="text" name="'.key_ga_uid.'" value="'. get_option( key_ga_uid ) .'" />';

                                            endif;
                                            ?>
					</td>
				</tr>
                                <tr>
                                    <td colspan="2">
                                        <h3><?php _e('Tracking Settings', 'google-analyticator'); ?></h3>
                                    </td>
                                </tr>
				<tr>
					<th width="30%" valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_admin ?>"><?php _e('Track all logged in WordPress users', 'google-analyticator'); ?>:</label>
					</th>
					<td>
						<?php
						echo "<select name='".key_ga_admin."' id='".key_ga_admin."'>\n";

						echo "<option value='".ga_enabled."'";
						if(get_option(key_ga_admin) == ga_enabled)
							echo " selected='selected'";
						echo ">" . __('Yes', 'google-analyticator') . "</option>\n";

						echo "<option value='".ga_disabled."'";
						if(get_option(key_ga_admin) == ga_disabled)
							echo" selected='selected'";
						echo ">" . __('No', 'google-analyticator') . "</option>\n";

						echo "</select>\n";

						?>
						<p  class="setting-description"><?php _e('Selecting "no" to this option will prevent logged in WordPress users from showing up on your Google Analytics reports. This setting will prevent yourself or other users from showing up in your Analytics reports. Use the next setting to determine what user groups to exclude.', 'google-analyticator'); ?></p>
					</td>
				</tr>
				<tr>
					<th width="30%" valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_admin_role ?>"><?php _e('User roles to not track', 'google-analyticator'); ?>:</label>
					</th>
					<td>
						<?php
						global $wp_roles;
						$roles = $wp_roles->get_names();
						$selected_roles = get_option(key_ga_admin_role);
						if ( !is_array($selected_roles) ) $selected_roles = array();

						# Loop through the roles
						foreach ( $roles AS $role => $name ) {
							echo '<input type="checkbox" value="' . $role . '" name="' . key_ga_admin_role . '[]"';
							if ( in_array($role, $selected_roles) )
								echo " checked='checked'";
							$name_pos = strpos($name, '|');
							$name = ( $name_pos ) ? substr($name, 0, $name_pos) : $name;
							echo ' /> ' . _x($name, 'User role') . '<br />';
						}
						?>
						<p  class="setting-description"><?php _e('Specifies the user roles to not include in your WordPress Analytics report. If a user is logged into WordPress with one of these roles, they will not show up in your Analytics report.', 'google-analyticator'); ?></p>
					</td>
				</tr>
				<tr>
					<th width="30%" valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_admin_disable ?>"><?php _e('Method to prevent tracking', 'google-analyticator'); ?>:</label>
					</th>
					<td>
						<?php
						echo "<select name='".key_ga_admin_disable."' id='".key_ga_admin_disable."'>\n";

						echo "<option value='remove'";
						if(get_option(key_ga_admin_disable) == 'remove')
							echo " selected='selected'";
						echo ">" . __('Remove', 'google-analyticator') . "</option>\n";

						echo "<option value='admin'";
						if(get_option(key_ga_admin_disable) == 'admin')
							echo" selected='selected'";
						echo ">" . __('Use \'admin\' variable', 'google-analyticator') . "</option>\n";

						echo "</select>\n";
						?>
						<p  class="setting-description"><?php _e('Selecting the "Remove" option will physically remove the tracking code from logged in users. Selecting the "Use \'admin\' variable" option will assign a variable called \'admin\' to logged in users. This option will allow Google Analytics\' site overlay feature to work, but you will have to manually configure Google Analytics to exclude tracking from pageviews with the \'admin\' variable.', 'google-analyticator'); ?></p>
					</td>
				</tr>
				<tr>
					<th width="30%" valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_sitespeed ?>"><?php _e('Site speed tracking', 'google-analyticator'); ?>:</label>
					</th>
					<td>
						<?php
						echo "<select name='".key_ga_sitespeed."' id='".key_ga_sitespeed."'>\n";

						echo "<option value='".ga_enabled."'";
						if(get_option(key_ga_sitespeed) == ga_enabled)
							echo " selected='selected'";
						echo ">" . __('Enabled', 'google-analyticator') . "</option>\n";

						echo "<option value='".ga_disabled."'";
						if(get_option(key_ga_sitespeed) == ga_disabled)
							echo" selected='selected'";
						echo ">" . __('Disabled', 'google-analyticator') . "</option>\n";

						echo "</select>\n";
						?>
						<p  class="setting-description"><?php _e('Disabling this option will turn off the tracking required for <a href="http://www.google.com/support/analyticshelp/bin/answer.py?hl=en&answer=1205784&topic=1120718&utm_source=gablog&utm_medium=blog&utm_campaign=newga-blog&utm_content=sitespeed">Google Analytics\' Site Speed tracking report</a>.', 'google-analyticator'); ?></p>
					</td>
				</tr>
                                <tr>
                                    <td colspan="2">
                                        <h3>Link Tracking Settings</h3>
                                    </td>
                                </tr>
				<tr>
					<th width="30%" valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_outbound ?>"><?php _e('Outbound link tracking', 'google-analyticator'); ?>:</label>
					</th>
					<td>
						<?php
						echo "<select name='".key_ga_outbound."' id='".key_ga_outbound."'>\n";

						echo "<option value='".ga_enabled."'";
						if(get_option(key_ga_outbound) == ga_enabled)
							echo " selected='selected'";
						echo ">" . __('Enabled', 'google-analyticator') . "</option>\n";

						echo "<option value='".ga_disabled."'";
						if(get_option(key_ga_outbound) == ga_disabled)
							echo" selected='selected'";
						echo ">" . __('Disabled', 'google-analyticator') . "</option>\n";

						echo "</select>\n";
						?>
						<p  class="setting-description"><?php _e('Disabling this option will turn off the tracking of outbound links. It\'s recommended not to disable this option unless you\'re a privacy advocate (now why would you be using Google Analytics in the first place?) or it\'s causing some kind of weird issue.', 'google-analyticator'); ?></p>
					</td>
				</tr>
				<tr>
					<th width="30%" valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_event ?>"><?php _e('Event tracking', 'google-analyticator'); ?>:</label>
					</th>
					<td>
						<?php
						echo "<select name='".key_ga_event."' id='".key_ga_event."'>\n";

						echo "<option value='".ga_enabled."'";
						if(get_option(key_ga_event) == ga_enabled)
							echo " selected='selected'";
						echo ">" . __('Enabled', 'google-analyticator') . "</option>\n";

						echo "<option value='".ga_disabled."'";
						if(get_option(key_ga_event) == ga_disabled)
							echo" selected='selected'";
						echo ">" . __('Disabled', 'google-analyticator') . "</option>\n";

						echo "</select>\n";
						?>
						<p  class="setting-description"><?php _e('Enabling this option will treat outbound links and downloads as events instead of pageviews. Since the introduction of <a href="https://developers.google.com/analytics/devguides/collection/gajs/eventTrackerGuide">event tracking in Analytics</a>, this is the recommended way to track these types of actions. Only disable this option if you must use the old pageview tracking method.', 'google-analyticator'); ?></p>
					</td>
				</tr>
				<tr>
					<th valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_downloads; ?>"><?php _e('Download extensions to track', 'google-analyticator'); ?>:</label>
					</th>
					<td>
						<?php
						echo "<input type='text' size='50' ";
						echo "name='".key_ga_downloads."' ";
						echo "id='".key_ga_downloads."' ";
						echo "value='".stripslashes(get_option(key_ga_downloads))."' />\n";
						?>
						<p  class="setting-description"><?php _e('Enter any extensions of files you would like to be tracked as a download. For example to track all MP3s and PDFs enter <strong>mp3,pdf</strong>. <em>Outbound link tracking must be enabled for downloads to be tracked.</em>', 'google-analyticator'); ?></p>
					</td>
				</tr>
				<tr>
					<th valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_outbound_prefix; ?>"><?php _e('Prefix external links with', 'google-analyticator'); ?>:</label>
					</th>
					<td>
						<?php
						echo "<input type='text' size='50' ";
						echo "name='".key_ga_outbound_prefix."' ";
						echo "id='".key_ga_outbound_prefix."' ";
						echo "value='".stripslashes(get_option(key_ga_outbound_prefix))."' />\n";
						?>
						<p  class="setting-description"><?php _e('Enter a name for the section tracked external links will appear under. This option has no effect if event tracking is enabled.', 'google-analyticator'); ?></em></p>
					</td>
				</tr>
				<tr>
					<th valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_downloads_prefix; ?>"><?php _e('Prefix download links with', 'google-analyticator'); ?>:</label>
					</th>
					<td>
						<?php
						echo "<input type='text' size='50' ";
						echo "name='".key_ga_downloads_prefix."' ";
						echo "id='".key_ga_downloads_prefix."' ";
						echo "value='".stripslashes(get_option(key_ga_downloads_prefix))."' />\n";
						?>
						<p  class="setting-description"><?php _e('Enter a name for the section tracked download links will appear under. This option has no effect if event tracking is enabled.', 'google-analyticator'); ?></em></p>
					</td>
				</tr>
				<tr>
					<th valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_adsense; ?>"><?php _e('Google Adsense ID', 'google-analyticator'); ?>:</label>
					</th>
					<td>
						<?php
						echo "<input type='text' size='50' ";
						echo "name='".key_ga_adsense."' ";
						echo "id='".key_ga_adsense."' ";
						echo "value='".get_option(key_ga_adsense)."' />\n";
						?>
						<p  class="setting-description"><?php _e('Enter your Google Adsense ID assigned by Google Analytics in this box. This enables Analytics tracking of Adsense information if your Adsense and Analytics accounts are linked.', 'google-analyticator'); ?></p>
					</td>
				</tr>
                                <tr>
                                    <td colspan="2">
                                        <h3>Additional Tracking Code </h3>
                                    </td>
                                </tr>
				<tr>
					<th valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_extra; ?>"><?php _e('Additional tracking code', 'google-analyticator'); ?><br />(<?php _e('before tracker initialization', 'google-analyticator'); ?>):</label>
					</th>
					<td>
						<?php
						echo "<textarea cols='50' rows='8' ";
						echo "name='".key_ga_extra."' ";
						echo "id='".key_ga_extra."'>";
						echo stripslashes(get_option(key_ga_extra))."</textarea>\n";
						?>
						<p  class="setting-description"><?php _e('Enter any additional lines of tracking code that you would like to include in the Google Analytics tracking script. The code in this section will be displayed <strong>before</strong> the Google Analytics tracker is initialized.', 'google-analyticator'); ?></p>
					</td>
				</tr>
				<tr>
					<th valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_extra_after; ?>"><?php _e('Additional tracking code', 'google-analyticator'); ?><br />(<?php _e('after tracker initialization', 'google-analyticator'); ?>):</label>
					</th>
					<td>
						<?php
						echo "<textarea cols='50' rows='8' ";
						echo "name='".key_ga_extra_after."' ";
						echo "id='".key_ga_extra_after."'>";
						echo stripslashes(get_option(key_ga_extra_after))."</textarea>\n";
						?>
						<p  class="setting-description"><?php _e('Enter any additional lines of tracking code that you would like to include in the Google Analytics tracking script. The code in this section will be displayed <strong>after</strong> the Google Analytics tracker is initialized.', 'google-analyticator'); ?></p>
					</td>
				</tr>
				<tr>
                                    <td colspan="2">
                                        <h3>Admin Dashboard Widgets</h3>
                                        <?php if(!$useAuth): ?>
                                        <div style="margin:10px auto; border:3px #f00 solid; background-color:#fdd; color:#000; padding:10px; text-align:center;">
                                            <?php _e('You have not authenticated with Google - you cannot use dashboard widgets! Reset the plugin to authenticate..', 'google-analyticator'); ?>
                                        </div>
                                        <?php endif;?>
                                    </td>
                                </tr>
                                <tr<?php if(!$useAuth){echo ' style="display:none"';}?>>
					<th width="30%" valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_widgets; ?>"><?php _e('Include widgets', 'google-analyticator'); ?>:</label>
					</th>
					<td>
						<?php
						echo "<select name='".key_ga_widgets."' id='".key_ga_widgets."'>\n";

						echo "<option value='".ga_enabled."'";
						if(get_option(key_ga_widgets) == ga_enabled)
							echo " selected='selected'";
						echo ">" . __('Enabled', 'google-analyticator') . "</option>\n";

						echo "<option value='".ga_disabled."'";
						if(get_option(key_ga_widgets) == ga_disabled)
							echo" selected='selected'";
						echo ">" . __('Disabled', 'google-analyticator') . "</option>\n";

						echo "</select>\n";
						?>
						<p  class="setting-description"><?php _e('Disabling this option will completely remove the Dashboard Summary widget and the theme Stats widget. Use this option if you would prefer to not see the widgets.', 'google-analyticator'); ?></p>
					</td>
				</tr>
                                <tr<?php if(!$useAuth){echo ' style="display:none"';}?>>
					<th width="30%" valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_dashboard_role ?>"><?php _e('User roles that can see the dashboard widget', 'google-analyticator'); ?>:</label>
					</th>
					<td>
						<?php
						global $wp_roles;
						$roles = $wp_roles->get_names();
						$selected_roles = get_option(key_ga_dashboard_role);
						if ( !is_array($selected_roles) ) $selected_roles = array();

						# Loop through the roles
						foreach ( $roles AS $role => $name ) {
							echo '<input type="checkbox" value="' . $role . '" name="' . key_ga_dashboard_role . '[]"';
							if ( in_array($role, $selected_roles) )
								echo " checked='checked'";
							$name_pos = strpos($name, '|');
							$name = ( $name_pos ) ? substr($name, 0, $name_pos) : $name;
							echo ' /> ' . _x($name, 'User role') . '<br />';
						}
						?>
						<p  class="setting-description"><?php _e('Specifies the user roles that can see the dashboard widget. If a user is not in one of these role groups, they will not see the dashboard widget.', 'google-analyticator'); ?></p>
					</td>
				</tr>

				</table>
			<p class="submit">
				<input type="submit" name="info_update" value="<?php _e('Save Changes', 'google-analyticator'); ?>" />
			</p>

                        <a href="<?php echo admin_url('/options-general.php?page=ga_reset'); ?>"><?php _e('Deauthorize &amp; Reset Google Analyticator.', 'google-analyticator'); ?></a>

                </form>


<?php  if (!get_option('wpm_o_user_id')): ?>
    <img src="<?php echo plugins_url('wlcms-plugin-advert.png', __FILE__ ); ?>" alt="Learn how to make WordPress better" />
    <form method="post" onsubmit="return quickValidate()"  action="http://www.aweber.com/scripts/addlead.pl" target="_blank" >
    <div style="display: none;">
    <input type="hidden" name="meta_web_form_id" value="672327302" />
    <input type="hidden" name="meta_split_id" value="" />
    <input type="hidden" name="listname" value="vumpublic2" />
    <input type="hidden" name="redirect" value="http://www.aweber.com/thankyou-coi.htm?m=video" id="redirect_9567c93ed4b6fb0c7cd9247553c362eb" />
    <input type="hidden" name="meta_adtracking" value="ga-plugin" />
    <input type="hidden" name="meta_message" value="1" />
    <input type="hidden" name="meta_required" value="name,email" />
    <input type="hidden" name="meta_tooltip" value="" />
    </div>
    <table style="text-align:center;margin-left: 20px;">
    <tr>
    <td><label class="previewLabel" for="awf_field-37978044"><strong>Name: </strong></label><input id="sub_name" type="text" name="name" class="text"  tabindex="500" value="" /></td>
    <td><label class="previewLabel" for="awf_field-37978045"><strong>Email: </strong></label> <input class="text" id="sub_email" type="text" name="email" tabindex="501"  value="" /></td>
    <td><span class="submit"><input name="submit" type="image" alt="submit" tabindex="502" src="<?php echo plugins_url('download-button.png', __FILE__); ?>" width="157" height="40" style="background: none; border: 0;" /></span></td>
    </tr>
    <tr>
    <td colspan="3" style="padding-top: 20px;">
    <a title="Privacy Policy" href="http://www.aweber.com/permission.htm" target="_blank"><img src="<?php echo plugins_url('privacy.png', __FILE__); ?>"  alt="" title="" /></a>
    </td>
    </tr>
    </table>
    </form>
<?php endif;?>

<script type="text/javascript">
function quickValidate()
{
        if (! jQuery('#sub_name').val() )
            {
                alert('Your Name is required');
                return false;
            }
        if(! jQuery('#sub_email').val() )
            {
                alert('Your Email is required');
                return false;
            }

            return true;

}
</script>

		</div>
		</form>

<?php
}

function ga_sort_account_list($a, $b) {
	return strcmp($a['title'],$b['title']);
}

/**
 * Checks if the WordPress API is a valid method for selecting an account
 *
 * @return a list of accounts if available, false if none available
 **/
function ga_get_analytics_accounts()
{
	$accounts = array();

	# Get the class for interacting with the Google Analytics
	require_once('class.analytics.stats.php');

	# Create a new Gdata call
	if ( isset($_POST['token']) && $_POST['token'] != '' )
		$stats = new GoogleAnalyticsStats($_POST['token']);
	elseif ( trim(get_option('ga_google_token')) != '' )
		$stats = new GoogleAnalyticsStats();
	else
		return false;

	# Check if Google sucessfully logged in
	if ( ! $stats->checkLogin() )
		return false;

	# Get a list of accounts
	$accounts = $stats->getAllProfiles();

        natcasesort ($accounts);

	# Return the account array if there are accounts
	if ( count($accounts) > 0 )
		return $accounts;
	else
		return false;
}

/**
 * Add http_build_query if it doesn't exist already
 **/
if ( !function_exists('http_build_query') ) {
	function http_build_query($params, $key = null)
	{
		$ret = array();

		foreach( (array) $params as $name => $val ) {
			$name = urlencode($name);

			if ( $key !== null )
				$name = $key . "[" . $name . "]";

			if ( is_array($val) || is_object($val) )
				$ret[] = http_build_query($val, $name);
			elseif ($val !== null)
				$ret[] = $name . "=" . urlencode($val);
		}

		return implode("&", $ret);
	}
}

/**
 * Echos out the core Analytics tracking code
 **/
function add_google_analytics()
{
	# Fetch variables used in the tracking code
	$uid = stripslashes(get_option(key_ga_uid));
	$extra = stripslashes(get_option(key_ga_extra));
	$extra_after = stripslashes(get_option(key_ga_extra_after));
	$extensions = str_replace (",", "|", get_option(key_ga_downloads));

	# Determine if the GA is enabled and contains a valid UID
	if ( ( get_option(key_ga_status) != ga_disabled ) && ( $uid != "XX-XXXXX-X" ) )
	{
		# Determine if the user is an admin, and should see the tracking code
		if ( ( get_option(key_ga_admin) == ga_enabled || !ga_current_user_is(get_option(key_ga_admin_role)) ) && get_option(key_ga_admin_disable) == 'remove' || get_option(key_ga_admin_disable) != 'remove' )
		{
			# Disable the tracking code on the post preview page
			if ( !function_exists("is_preview") || ( function_exists("is_preview") && !is_preview() ) )
			{
				# Add the notice that Google Analyticator tracking is enabled
				echo "<!-- Google Analytics Tracking by Google Analyticator " . GOOGLE_ANALYTICATOR_VERSION . ": http://www.videousermanuals.com/google-analyticator/ -->\n";

				# Add the Adsense data if specified
				if ( get_option(key_ga_adsense) != '' )
					echo '<script type="text/javascript">window.google_analytics_uacct = "' . get_option(key_ga_adsense) . "\";</script>\n";

				# Include the file types to track
				$extensions = explode(',', stripslashes(get_option(key_ga_downloads)));
				$ext = "";
				foreach ( $extensions AS $extension )
					$ext .= "'$extension',";
				$ext = substr($ext, 0, -1);

				# Include the link tracking prefixes
				$outbound_prefix = stripslashes(get_option(key_ga_outbound_prefix));
				$downloads_prefix = stripslashes(get_option(key_ga_downloads_prefix));
				$event_tracking = get_option(key_ga_event);

				?>
<script type="text/javascript">
	var analyticsFileTypes = [<?php echo strtolower($ext); ?>];
<?php if ( $event_tracking != 'enabled' ) { ?>
	var analyticsOutboundPrefix = '/<?php echo $outbound_prefix; ?>/';
	var analyticsDownloadsPrefix = '/<?php echo $downloads_prefix; ?>/';
<?php } ?>
	var analyticsEventTracking = '<?php echo $event_tracking; ?>';
</script>
<?php
				# Add the first part of the core tracking code
				?>
<script type="text/javascript">
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', '<?php echo $uid; ?>']);
        _gaq.push(['_addDevId', 'i9k95']); // Google Analyticator App ID with Google 
<?php

				# Add any tracking code before the trackPageview
				do_action('google_analyticator_extra_js_before');
				if ( '' != $extra )
					echo "	$extra\n";

				# Add the track pageview function
				echo "	_gaq.push(['_trackPageview']);\n";

				# Add the site speed tracking
				if ( get_option(key_ga_sitespeed) == ga_enabled )
					echo "	_gaq.push(['_trackPageLoadTime']);\n";

				# Disable page tracking if admin is logged in
				if ( ( get_option(key_ga_admin) == ga_disabled ) && ( ga_current_user_is(get_option(key_ga_admin_role)) ) )
					echo "	_gaq.push(['_setCustomVar', 'admin']);\n";

				# Add any tracking code after the trackPageview
				do_action('google_analyticator_extra_js_after');
				if ( '' != $extra_after )
					echo "	$extra_after\n";

				# Add the final section of the tracking code
				?>

	(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();
</script>
<?php
			}
		} else {
			# Add the notice that Google Analyticator tracking is enabled
			echo "<!-- Google Analytics Tracking by Google Analyticator " . GOOGLE_ANALYTICATOR_VERSION . ": http://ronaldheft.com/code/analyticator/ -->\n";
			echo "	<!-- " . __('Tracking code is hidden, since the settings specify not to track admins. Tracking is occurring for non-admins.', 'google-analyticator') . " -->\n";
		}
	}
}

/**
 * Adds outbound link tracking to Google Analyticator
 **/
function ga_outgoing_links()
{
	# Fetch the UID
	$uid = stripslashes(get_option(key_ga_uid));

	# If GA is enabled and has a valid key
	if (  (get_option(key_ga_status) != ga_disabled ) && ( $uid != "XX-XXXXX-X" ) )
	{
		# If outbound tracking is enabled
		if ( get_option(key_ga_outbound) == ga_enabled )
		{
			# If this is not an admin page
			if ( !is_admin() )
			{
				# Display page tracking if user is not an admin
				if ( ( get_option(key_ga_admin) == ga_enabled || !ga_current_user_is(get_option(key_ga_admin_role)) ) && get_option(key_ga_admin_disable) == 'remove' || get_option(key_ga_admin_disable) != 'remove' )
				{
					add_action('wp_print_scripts', 'ga_external_tracking_js');
				}
			}
		}
	}
}

/**
 * Adds the scripts required for outbound link tracking
 **/
function ga_external_tracking_js()
{
	wp_enqueue_script('ga-external-tracking', plugins_url('/google-analyticator/external-tracking.min.js'), array('jquery'), GOOGLE_ANALYTICATOR_VERSION);
}

/**
 * Determines if a specific user fits a role
 **/
function ga_current_user_is($roles)
{
	if ( !$roles ) return false;

	global $current_user;
	get_currentuserinfo();
	$user_id = intval( $current_user->ID );

	if ( !$user_id ) {
		return false;
	}
	$user = new WP_User($user_id); // $user->roles

	foreach ( $roles as $role )
		if ( in_array($role, $user->roles) ) return true;

	return false;
}


?>