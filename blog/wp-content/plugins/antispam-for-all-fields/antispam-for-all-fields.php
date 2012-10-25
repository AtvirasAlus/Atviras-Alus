<?php
/**
 Plugin Name: Antispam for all fields
 Plugin URI: http://www.mijnpress.nl
 Description: Class and functions
 Author: Ramon Fincken
 Version: 0.8.0
 Author URI: http://www.mijnpress.nl
 */

if (!defined('ABSPATH')) die("Aren't you supposed to come here via WP-Admin?");

if(!class_exists('mijnpress_plugin_framework'))
{
	include('mijnpress_plugin_framework.php');
}

define('PLUGIN_ANTISPAM_FOR_ALL_FIELDS_VERSION', '0.8.0');

if(!class_exists('antispam_for_all_fields_core'))
{
	include('antispam-for-all-fields-core.php');
}

// Calls core function after a comments has been submit
add_filter('pre_comment_approved', 'plugin_antispam_for_all_fields', 0);

// Shows statistics
add_action('activity_box_end', 'plugin_antispam_for_all_fields_stats');

// I don't know how AJAX plugins react to this .. should work fine -> TODO test this ;)
// Disabled due to sessions, I want to store it otherwise ( I know that session_start() is an option )
// Solution -> transient
// add_action ('comment_form', 'plugin_antispam_for_all_fields_insertfields');
// add_action ('comment_post', 'plugin_antispam_for_all_fields_checkfields');

function plugin_antispam_for_all_fields_checkfields()
{
	$pass = false;
	if(wp_verify_nonce($_POST[$_SESSION['plugin_afaf_nonce1']], 'plugin_afaf1') )
	{
		// Found first nonce, and was correct
		$nonce2 = $_POST[$_SESSION['plugin_afaf_nonce1']];
		if(!empty($nonce2) && $nonce2 == $_SESSION['plugin_afaf_nonce2'])
		{
			// Was correct
			if(isset($_POST[$nonce2]) && empty($_POST[$nonce2]))
			{
				$pass = true;
			}
		}
	}
}

function plugin_antispam_for_all_fields_insertfields()
{
	$nonce1= wp_create_nonce('plugin_afaf1');
	$_SESSION['plugin_afaf_nonce1'] = $nonce1;

	set_transient('plugin_afaf_nonce1', $nonce1, 60*60); // 60*60 = 1hour

	$nonce2= wp_create_nonce('plugin_afaf2');
	$_SESSION['plugin_afaf_nonce2'] = $nonce2;
	echo '<input type="hidden" name="'.$nonce1.'" value="'.$nonce2.'" />';
	echo '<input type="hidden" name="'.$nonce2.'" value="" />';
}

/**
 * Displays stats in dashboard
 */
function plugin_antispam_for_all_fields_stats() {
	$statskilled = intval(get_option('plugin_antispam_for_all_fields_statskilled'));
	$statsspammed = intval(get_option('plugin_antispam_for_all_fields_statsspammed'));

	echo '<p>' . sprintf(__('<a href="%1$s" target="_blank">Antispam for all fields</a> has blocked <strong>%2$s</strong> and spammed <strong>%3$s</strong> comments.'), 'http://wordpress.org/extend/plugins/antispam-for-all-fields/', number_format($statskilled), number_format($statsspammed)) . '</p>';
}

/**
 * Calls core function to perform checks
 * @param unknown_type $status
 */
function plugin_antispam_for_all_fields($status) {
	global $commentdata;

	$afaf = new antispam_for_all_fields();
	$afaf->do_bugfix();
	$temp = $afaf->init($status, $commentdata);

	// Sometimes an IP is not added, so lets do that here ;)
	if(empty($commentdata['comment_author_IP']))
	{
		$commentdata['comment_author_IP'] = $afaf->user_ip;
	}

	return $temp;
}

// Admin only
if(mijnpress_plugin_framework::is_admin())
{
	add_action('admin_menu',  array('antispam_for_all_fields', 'addPluginSubMenu'));
	add_filter('plugin_row_meta',array('antispam_for_all_fields', 'addPluginContent'), 10, 2);
}


/**
 * Class, based on my PhpBB2 antispam for all fields module: http://www.phpbbantispam.com
 * @author Ramon Fincken
 */
class antispam_for_all_fields extends antispam_for_all_fields_core
{
	function __construct()
	{
		$this->showcredits = true;
		$this->showcredits_fordevelopers = true;
		$this->plugin_title = 'Antispam for all fields';
		$this->plugin_class = 'antispam_for_all_fields';
		$this->plugin_filename = 'antispam-for-all-fields/antispam-for-all-fields.php';
		$this->plugin_config_url = 'plugins.php?page='.$this->plugin_filename;

		$this->language = array(); // TODO make seperate file
		$this->language['explain'] = 'Thank you for your comment!. Your comment has been temporary held by our antispam system for moderation. <br/><strong>Site administration has been notified and will approve your comment after review.</strong><br/><br/>Do not re-submit your comment!';

		// Defaults
		$this->wpdb_spam_status = 'spam';
		$this->store_comment_in_days = 7;

		// Defaults, falltrough by admin panel settings
		$this->limits['lower'] = 2;
		$this->limits['upper'] = 10;
		$this->limits['numbersites'] = 10;
		$this->mail['sent'] = true;
		$this->mail['admin'] = ''; // '' == 'default' and will use admin_email. Values:  '' || 'default' || 'e@mail.com'
		$this->api_stopforumspam = 1;

		$installed = get_option('plugin_antispam_for_all_fields_installed');
		if($installed == 'true')
		{
			// Get config
			$settings = get_option('plugin_antispam_for_all_fields_settings');
			$this->limits = $settings['limits'];
			$this->mail = $settings['mail'];
			$this->api_stopforumspam = $settings['api_stopforumspam'];
			$this->words = $settings['words'];
				
			// Upgrade?
			$version = get_option('plugin_antispam_for_all_fields_version');
			// Compare with PLUGIN_ANTISPAM_FOR_ALL_FIELDS_VERSION and perform upgrades
			if($this->is_admin())
			{
				$this->upgrade_check($version);
			}
			
			// Check
			if($this->limits['lower'] == 0 || empty($this->limits['lower'])) $this->limits['lower'] = 2;
			if($this->limits['upper'] == 0 || empty($this->limits['upper'])) $this->limits['upper'] = 10;
			if($this->limits['numbersites'] == 0 || empty($this->limits['numbersites'])) $this->limits['numbersites'] = 10;			
		}
		else
		{
			// Make install
			add_option('plugin_antispam_for_all_fields_installed','true');
			add_option('plugin_antispam_for_all_fields_version',PLUGIN_ANTISPAM_FOR_ALL_FIELDS_VERSION);
				
			$settings = array();
			$settings['words'] = $this->get_words();
			$settings['mail'] = $this->mail;
			$settings['limits'] = $this->limits;
			$settings['api_stopforumspam'] = $this->api_stopforumspam;

			// Save default options
			add_option('plugin_antispam_for_all_fields_settings',$settings);

			// Store
			$this->words = $settings['words'];
		}

		$this->user_ip = htmlspecialchars(preg_replace('/[^0-9a-fA-F:., ]/', '', $_SERVER['REMOTE_ADDR']));
		$this->user_ip_fwd = htmlspecialchars(preg_replace('/[^0-9a-fA-F:., ]/', '', @$_SERVER['HTTP_X_FORWARDED_FOR'])); // For future use
	}

	function antispam_for_all_fields()
	{
		$args= func_get_args();
		call_user_func_array
		(
		array($this, '__construct'),
		$args
		);
	}

	/**
	 * Checks if an upgrade is needed and performs the upgrade
	 * @param unknown_type $currentversion
	 */
	function upgrade_check($currentversion)
	{
		// Need update?
		if (version_compare($currentversion, PLUGIN_ANTISPAM_FOR_ALL_FIELDS_VERSION, '<')) {
			
			if (version_compare($currentversion, "0.7.0", '<')) {
				$this->perfom_upgrade("0.7.0");
			}
			
			// Write current version to DB
			update_option('plugin_antispam_for_all_fields_version',PLUGIN_ANTISPAM_FOR_ALL_FIELDS_VERSION);
			if(is_admin())
			{
				$this->show_message('Antispam for all fields: Upgrade succesfull. Please visit the plugin settings page now.');
			}
		}

	}
	
	/**
	 * Calls the functions needed for this upgrade
	 * @param unknown_type $step
	 */
	private function perfom_upgrade($step)
	{
		$upgrade_todo=array();
		
		// Decision logic
		switch ($step)
		{
			case "0.7.0":
				$upgrade_todo['wordlist'] = true;
				$settings = get_option('plugin_antispam_for_all_fields_settings');
				if(!isset($this->api_stopforumspam))
				{
					$settings['api_stopforumspam'] = 1;
					update_option('plugin_antispam_for_all_fields_settings',$settings);
				}			
			break;
		}
		
		// GOGOGO
		if(isset($upgrade_todo['wordlist']))
		{
			$this->upgrade_wordlist($step);
		}
	}
	
	private function upgrade_wordlist($step)
	{
		$upgradewordlist = array();

		$upgradewordlist["0.7.0"] = array('SEOPlugins.org');

		$newwordlist = array();
		$upgraded_list = false;

		foreach($this->words as $word)
		{
			$word = str_replace(array("\r","\n"),array('',''),$word);
			$newwordlist[] = $word;
		}

		foreach($upgradewordlist[$step] as $checkfor)
		{
			if(!in_array('*'.$checkfor.'*', $newwordlist))
			{
				$upgraded_list = true;
				$newwordlist[] = '*'.$checkfor.'*';
			}
		}

		if($upgraded_list)
		{
			// Store
			$settings['words'] = $newwordlist;
			update_option('plugin_antispam_for_all_fields_settings',$settings);
				
			$this->words = $settings['words'];
			$this->show_message('Antispam for all fields: Wordlist has been updated');
		}
	}
	
	
	function addPluginSubMenu()
	{
		$plugin = new antispam_for_all_fields();
		parent::addPluginSubMenu($plugin->plugin_title,array($plugin->plugin_class, 'admin_menu'),__FILE__);
	}

	/**
	 * Additional links on the plugin page
	 */
	function addPluginContent($links, $file) {
		$plugin = new antispam_for_all_fields();
		$links = parent::addPluginContent($plugin->plugin_filename,$links,$file,$plugin->plugin_config_url);
		return $links;
	}

	/**
	 * Shows the admin plugin page
	 */
	public function admin_menu()
	{
		$plugin = new antispam_for_all_fields();
		$plugin->content_start();

		// Handle submit here
		if(isset($_POST['action']) && $_POST['action'] == 'afal_update')
		{
			$temp = $_POST['words'];
			$_POST['words'] = explode("\n",$temp);
			
			if($_POST['mail']['sent'] == 1) { $_POST['mail']['sent'] = true; } else { $_POST['mail']['sent'] = false; }
				
			$settings_post = array();
			$settings_post['words'] = $_POST['words'];
			$settings_post['mail'] = $_POST['mail'];
			$settings_post['limits'] = $_POST['limits'];
			$settings_post['api_stopforumspam'] = $_POST['api_stopforumspam'];

			// Append POST values
			$settings = $settings_post;
				
			// Update
			update_option('plugin_antispam_for_all_fields_settings',$settings);
				
			// Reload settings
			$plugin = new antispam_for_all_fields();
		}

		switch (@$_GET['action'])
		{
			case 'approve':
				if(isset($_GET['comment_key']))
				{
					$comment_key = $_GET['comment_key'];
					$commentdata = get_transient($comment_key);
						
					if($commentdata === false)
					{
						$plugin->show_message('Could not find stored comment.<br/>Did you approve this one earlier on? If not .. must have been here more then '.$plugin->store_comment_in_days. ' days and was auto deleted.');
					}
					else
					{
						// Now insert
						wp_insert_comment($commentdata);
						$plugin->show_message('Comment approved');

						// Delete
						delete_transient($comment_key);
					}
				}
				break;
			case 'blacklist_ip':
				if(isset($_GET['ip']))
				{
					$ip = trim(stripslashes($_GET['ip']));
						
					// Ereg code from wp-spamfree
					if (ereg("^([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])$",$ip))
					{
						$plugin->blacklist_ip($ip);
						$plugin->show_message('IP blacklisted');

						// Delete
						if(isset($_GET['comment_key']))
						{
							$comment_key = $_GET['comment_key'];
							delete_transient($comment_key);
							$plugin->show_message('Comment deleted');
						}

						global $wpdb;
						
						if(isset($_GET['blacklist_extra']))
						{
							// Same email?
							$sql = 'SELECT comment_ID FROM ' . $wpdb->comments . ' WHERE comment_author_email IN (';
							$sql .= 'SELECT comment_author_email FROM ' . $wpdb->comments . ' WHERE `comment_author_IP` = %s';
							$sql .= ')';
							$preparedsql = $wpdb->prepare($sql, $ip);
							$results = $wpdb->get_results($preparedsql, ARRAY_A);
		
							foreach($results as $row)
							{
								wp_delete_comment($row['comment_ID']);
							}
							$plugin->show_message('All comments with same email deleted');
	
							// Same URL?
							$sql = 'SELECT comment_ID FROM ' . $wpdb->comments . ' WHERE comment_author_url IN (';
							$sql .= 'SELECT comment_author_url FROM ' . $wpdb->comments . ' WHERE `comment_author_IP` = %s';
							$sql .= ')';
							$preparedsql = $wpdb->prepare($sql, $ip);
							$results = $wpdb->get_results($preparedsql, ARRAY_A);
		
							foreach($results as $row)
							{
								wp_delete_comment($row['comment_ID']);
							}
							$plugin->show_message('All comments with same URL deleted');
						}
						
						$sql = 'SELECT comment_ID FROM ' . $wpdb->comments . ' WHERE `comment_author_IP` = %s';
						$preparedsql = $wpdb->prepare($sql, $ip);
						$results = $wpdb->get_results($preparedsql, ARRAY_A);

						foreach($results as $row)
						{
							wp_delete_comment($row['comment_ID']);
						}

						$plugin->show_message('All comments from same IP deleted');

					}
				}

				break;
					
			default:
				echo '<h1>Antispam for all fields settings</h1>';
				echo '<p>Layout is not prio number 1 right now, but everything is working</p>';
				include('admin_menu.php');
				break;
		}


		$plugin->content_end();
	}

	/**
	 * Core function to init spamchecks
	 */
	function init($status, $commentdata) {
		// WP blacklisted IP?
		$ip_blacklisted = $this->ip_is_blacklisted($this->user_ip);
		if($ip_blacklisted)
		{
			$this->update_stats('killed');
			if ( defined('DOING_AJAX') )
			{
				die( __($this->language['explain']) );
			}
			wp_die( __($this->language['explain']), '', array('response' => 403) );
		}
		
		$email = $commentdata['comment_author_email'];
		$author = $commentdata['comment_author'];
		$url = $commentdata['comment_author_url'];
		$comment_content = $commentdata['comment_content'];
		$comment_agent = $commentdata['comment_agent'];
				
		// Trackback or pingback?
		if ($commentdata['comment_type'] == 'trackback' || $commentdata['comment_type'] == 'pingback') {
			
			// Simple trackback validation with topsy blocker Stage 1
			$tmpSender_IP = preg_replace('/[^0-9.]/', '', $_SERVER['REMOTE_ADDR'] );

			$authDomainname = stbv_get_domainname_from_uri($url);
			$tmpURL_IP = preg_replace('/[^0-9.]/', '', gethostbyname($authDomainname) );

			if ( $tmpSender_IP != $tmpURL_IP) {
				
				$status = 'spam';
				
				$body = "Details are below: \n";
				$body .= "action: ".'Sender\'s IP address (' . $tmpSender_IP . ') not equal to IP address of host (' . $tmpURL_IP . ')'."\n";

				$body .= "IP adress " . $this->user_ip . "\n";
				$body .= "Email adress " . $email . "\n";

				foreach ($commentdata as $key => $val) {
					$body .= "$key : $val \n";
				}

				$commment_key = $this->store_comment($commentdata,'spammed');
				$this->mail_details('rejected spammed sender IP not equal to host IP', $body,$commment_key);
				$this->update_stats('spammed');				
			}
			return $status;
		}
		
		// Comments only below this point
	
		// Antispam Extra V 0.2 By Budhiman
		// No comments without proper HTTP referer
		if (get_option('antispamextra_disallow_nonreferers')) {
			if (!isset($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER'] == '' || strpos($_SERVER['HTTP_REFERER'], get_option('siteurl')) < 0) {
				$this->update_stats('killed');
				if ( defined('DOING_AJAX') )
				{
					die( __($this->language['explain']) );
				}
				wp_die( __($this->language['explain']), '', array('response' => 403) );				
			}
		}


		if(!empty($comment_agent))
		{
			$check = $this->check_user_agent_is_spam($comment_agent);
			if($check == $this->wpdb_spam_status)
			{
					// Get lost
					foreach ($commentdata as $key => $val) {
						$body .= "$key : $val \n";
					}

					$commment_key = $this->store_comment($commentdata,'spammed');
					$this->mail_details('rejected spammed based comment agent', $body,$commment_key);
					$this->update_stats('spammed');
					return 'spam';
			}
			else
			{
				if($check == 'challenge')
				{
					
				}
			}
		}
		
		if (!empty ($email)) {
			$count = $this->check_count('comment_author_email', $email);
			$temp = $this->compare_counts($count, 'comment_author_email', $commentdata);
			if ($temp) {
				return $temp;
			}
		}

		// Lots of (more then 25%) dots in mail and free-email?
		$temp = explode('@',$email);
		$domain = strtolower($temp[1]);
		$split1len = strlen($temp[0]);
		$dotlen = substr_count($temp[0],'.');
		if(intval($dotlen/($split1len/100)) > 25)
		{
			if(in_array($domain,array('google.com','gmail.com','hotmail.com','live.com','mail.ru')))
			{
				$this->update_stats('spammed');
				return 'spam';				
			}
		}		
		
		if (!empty ($author)) {
			$count = $this->check_count('comment_author', $author);
			$temp = $this->compare_counts($count, 'comment_author', $commentdata);
			if ($temp) {
				return $temp;
			}
		}

		// IP check
		$count = $this->check_count('comment_author_IP', $this->user_ip);
		$temp = $this->compare_counts($count, 'comment_author_IP', $commentdata);
		if ($temp) {
			return $temp;
		}



		if (!empty ($comment_content)) {
			//

			$number_of_sites = $this->count_number_of_sites($comment_content);
			if($number_of_sites > $this->limits['numbersites'])
			{
				$body = "Details are below: \n";
				$body .= "action: found ".$number_of_sites. " URIs in comment that is a lot, comment marked as spam \n";

				$body .= "IP adress " . $this->user_ip . "\n";
				$body .= "Email adress " . $email . "\n";
				$body .= "low threshold " . $this->limits['lower'] . "\n";
				$body .= "upper threshold " . $this->limits['upper'] . "\n";

				foreach ($commentdata as $key => $val) {
					$body .= "$key : $val \n";
				}

				$commment_key = $this->store_comment($commentdata,'spammed');
				$this->mail_details('rejected spammed based on '.$number_of_sites. ' URIs in comment', $body,$commment_key);
				$this->update_stats('spammed');
				return 'spam';
			}

			foreach ($this->words as $word) {
				$string_is_spam = $this->string_is_spam($word, $comment_content);

				if ($string_is_spam) {
					$body = "Details are below: \n";
					$body .= "action: found spamword in comment, comment denied \n";

					$body .= "IP adress " . $this->user_ip . "\n";
					$body .= "Email adress " . $email . "\n";
					$body .= "low threshold " . $this->limits['lower'] . "\n";
					$body .= "upper threshold " . $this->limits['upper'] . "\n";

					$body .= "word found  : " . $word . " \n\n";

					foreach ($commentdata as $key => $val) {
						$body .= "$key : $val \n";
					}

					$commment_key = $this->store_comment($commentdata,'killed');
					$this->mail_details('rejected comment based on word', $body, $commment_key);
					$this->update_stats('killed');
					if ( defined('DOING_AJAX') )
					{
						die( __($this->language['explain']) );
					}
					wp_die( __($this->language['explain']), '', array('response' => 403) );
				}
			}
			
			
			// Any uri found?
			if (preg_match('/^(.*?)(http)(.*?)$/', $comment_content)) {
				// Protects
				// random <a href="http://website.com" rel="nofollow">random</a>, [url=http://website.com]random[/url], [link=http://website.com]random[/link], http://website.com
				// random <a href="http://website.com">random</a>, [url=http://website.com]random[/url], [link=http://website.com]random[/link], http://website.com
				if (preg_match('/^([[:alnum:]])( ?)(.*?)(href)(.*?)((nofollow)?(.*?))(url)(.*?)(link)(.*?)$/', $comment_content)) {
					$this->update_stats('killed');
					if ( defined('DOING_AJAX') )
					{
						die( __($this->language['explain']) );
					}
					wp_die( __($this->language['explain']), '', array('response' => 403) );
				}
				// Protects
				// random [url=http://website.com]random[/url], [link=http://website.com]random[/link], http://website.com
				if (preg_match('/^([[:alnum:]])( ?)(.*?)(url)(.*?)(link)(.*?)$/', $comment_content)) {
					$this->update_stats('killed');
					if ( defined('DOING_AJAX') )
					{
						die( __($this->language['explain']) );
					}
					wp_die( __($this->language['explain']), '', array('response' => 403) );
				}				
			}
			
			// HTML?
			if (preg_match('/^(<strong>)(.*?)(<\/strong>)(.*?)([(\.)])(.*?)([(\.)])(.*?)$/s', $comment_content) || preg_match('/^(<b>)(.*?)(<\/b>)(.*?)([(\.)])(.*?)([(\.)])(.*?)$/s', $comment_content)) {
					$this->update_stats('killed');
					if ( defined('DOING_AJAX') )
					{
						die( __($this->language['explain']) );
					}
					wp_die( __($this->language['explain']), '', array('response' => 403) );					
			}
			
		} // !empty comment_content

		if (!empty ($url)) {
			$count = $this->check_count('comment_author_url', $url);
			$temp = $this->compare_counts($count, 'comment_author_url', $commentdata);
			if ($temp) {
				return $temp;
			}

			$nonsence_urls = array('www.google.','www.bing.');
			$url = strtolower($url);
			foreach($nonsence_urls as $url_to_find)
			{
				if(strstr($url, $url_to_find))
				{
					// Get lost
					foreach ($commentdata as $key => $val) {
						$body .= "$key : $val \n";
					}

					$this->update_stats('spammed');
					if ( defined('DOING_AJAX') )
					{
						die( __($this->language['explain']) );
					}
					wp_die( __($this->language['explain']), '', array('response' => 403) );					
				}
			}
			
			// Now check for words
			if ($html_body = wp_remote_retrieve_body(wp_remote_get($url))) {
				if (!empty ($html_body)) {
					foreach ($this->words as $word) {

						$string_is_spam = $this->string_is_spam($word, $html_body);
						if ($string_is_spam) {
							$body = "Details are below: \n";
							$body .= "action: I visited URL of commenter, found spamword on that page, comment denied \n";

							$body .= "IP adress " . $this->user_ip . "\n";
							$body .= "Email adress " . $email . "\n";
							$body .= "low threshold " . $this->limits['lower'] . "\n";
							$body .= "upper threshold " . $this->limits['upper'] . "\n";

							$body .= "word found  : " . $word . " \n\n";

							foreach ($commentdata as $key => $val) {
								$body .= "$key : $val \n";
							}

							$commment_key = $this->store_comment($commentdata,'spammed');
							$this->mail_details('rejected comment based on word', $body, $commment_key);
							$this->update_stats('spammed');
							if ( defined('DOING_AJAX') )
							{
								die( __($this->language['explain']) );
							}
							wp_die( __($this->language['explain']), '', array('response' => 403) );
						}
					}
				}
			}
		}
		
				
		if($this->api_stopforumspam == 1)
		{
			// IP
			$ip_check = unserialize(wp_remote_retrieve_body(wp_remote_get('http://www.stopforumspam.com/api?ip='.$this->user_ip.'&f=serial')));
			if(isset($ip_check['success']) && $ip_check['success'])
			{
				if($ip_check['ip']['frequency'] > $this->limits['upper'])
				{
					$body = "Details are below: \n";
					$body .= "action: I checked IP against www.stopforumspam.com, exceeded upper threshold, comment denied \n";

					$body .= "IP adress " . $this->user_ip . "\n";
					$body .= "Email adress " . $email . "\n";
					$body .= "low threshold " . $this->limits['lower'] . "\n";
					$body .= "upper threshold " . $this->limits['upper'] . "\n";
					$body .= "Details: http://www.stopforumspam.com/ipcheck/".$this->user_ip. "\n";

					foreach ($commentdata as $key => $val) {
						$body .= "$key : $val \n";
					}

					$commment_key = $this->store_comment($commentdata,'spammed');
					$this->mail_details('rejected comment based on spamdatabase lookup', $body, $commment_key);
					$this->update_stats('spammed');
					if ( defined('DOING_AJAX') )
					{
						die( __($this->language['explain']) );
					}
					wp_die( __($this->language['explain']), '', array('response' => 403) );
				}
			}

			// Email
			$email_check = unserialize(wp_remote_retrieve_body(wp_remote_get('http://www.stopforumspam.com/api?email='.$email.'&f=serial')));
			if(isset($email_check['success']) && $email_check['success'])
			{
				if($email_check['email']['frequency'] > $this->limits['upper'])
				{
					$body = "Details are below: \n";
					$body .= "action: I checked IP against www.stopforumspam.com, exceeded upper threshold, comment denied \n";

					$body .= "IP adress " . $this->user_ip . "\n";
					$body .= "Email adress " . $email . "\n";
					$body .= "low threshold " . $this->limits['lower'] . "\n";
					$body .= "upper threshold " . $this->limits['upper'] . "\n";
					$body .= "Details: http://www.stopforumspam.com/search/?q=".$email. "\n";

					foreach ($commentdata as $key => $val) {
						$body .= "$key : $val \n";
					}
								
					$commment_key = $this->store_comment($commentdata,'spammed');
					$this->mail_details('rejected comment based on spamdatabase lookup', $body, $commment_key);
					$this->update_stats('spammed');
					if ( defined('DOING_AJAX') )
					{
						die( __($this->language['explain']) );
					}
					wp_die( __($this->language['explain']), '', array('response' => 403) );
				}
			}
		}	
		
		return $status;
	}
}
?>
