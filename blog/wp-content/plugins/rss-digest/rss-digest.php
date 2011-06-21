<?php
/*
Plugin Name: RSS Digest
Plugin URI: http://geekfactor.charrington.com/projects/rss-digest
Description: Publishes RSS items to daily digest
Version: 1.03
Author: Sam Charrington
Author URI: http://geekfactor.charrington.com
*/

// Copyright (c) 2009 Sam Charrington. All rights reserved.
//
// Released under the GPL license, Version 2
// http://www.opensource.org/licenses/gpl-license.php
//
// This is an add-on for WordPress
// http://wordpress.org/
//
// Portions inspired by or pilfered from Twitter Tools by Alex King ( http://alexking.org ).
// Thanks Alex.
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
// **********************************************************************

define('SCRD_MAX_ITEMS',10);
define('SCRD_MIN_ITEMS',1);
define('SCRD_DEFAULT_TITLE', "Today's Links");
define('SCRD_DEFAULT_CATEGORY', 1);
define('SCRD_DEFAULT_AUTHOR', 1);
define('SCRD_DEFAULT_STATUS', 'publish');
define('SCRD_DEFAULT_POST_DAYS', 'a:7:{s:6:"Monday";i:0;s:7:"Tuesday";i:0;s:9:"Wednesday";i:0;s:8:"Thursday";i:0;s:6:"Friday";i:1;s:8:"Saturday";i:0;s:6:"Sunday";i:0;}');
define('SCRD_DEFAULT_HOUR', '17');
define('SCRD_DEFAULT_MINUTE', '30');
define('SCRD_DEFAULT_MINUTE_GRANULARITY', '1');
define('SCRD_DEFAULT_APPEND_DATE', 0);
define('SCRD_DEFAULT_INCLUDE_DESC', 1);
define('SCRD_DAYS_LIST', 'Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday');
define('SCRD_OPTIONS', 'post_days, post_hour, post_minute, feed_url, max_items, min_items, give_credit, digest_title, post_category, post_tags, post_author, post_status, append_date_to_title, plugin_options, include_description');
define('SCRD_CACHE_OVERRIDE', 14400); // in secs; 14400 secs == 4 hours
define('SCRD_DEBUG', 0);
define('SCRD_DEBUG_DATE_FMT', 'D Y-m-d G:i:s T Y');
define('SCRD_MIN_PHP_VERSION', '5.0.0');
define('SCRD_MIN_WP_VERSION', '2.8.0');

function scrd_do_digest() {
  $next_digest = scrd_get_next_digest_time();
  scrd_debug_log("RSS Digest: About to post digest. Scheduling next digest for " . date(SCRD_DEBUG_DATE_FMT, $next_digest) . ".");
  //set up the next digest
  wp_schedule_single_event($next_digest, 'scrd_do_digest');
  //run this digest
  scrd_post_digest();
}
add_action('scrd_do_digest', 'scrd_do_digest');

function scrd_get_next_digest_time() {
  $tzs = get_option("timezone_string","UTC");
  if (!date_default_timezone_set($tzs)) {
    date_default_timezone_set("UTC");
  }
  $right_now = time();
  $hour = intval(get_option('scrd_post_hour',SCRD_DEFAULT_HOUR));
  $minute = intval(get_option('scrd_post_minute',SCRD_DEFAULT_MINUTE));
  $todays_fetch_time = mktime($hour, $minute, 0);

  $date_offset = 0;
  if ($right_now > $todays_fetch_time) {
    // passed today's digest, start search w/ tomorrow
    $date_offset = 1;
  }

  $today = getdate();
  $todays_day = $today["wday"];
  $days = explode(",", SCRD_DAYS_LIST);
  $post_days = get_option('scrd_post_days', SCRD_DEFAULT_POST_DAYS);
  
  while ($date_offset <= 7) {
    if ($post_days[$days[($todays_day + $date_offset)%7]] == 1) {
      break;
    }
    $date_offset++;
  }
  // so long as there is at least one day checked, we end up here on day that is checked
  $next_fetch_time = strtotime("+$date_offset days $hour:$minute:0");
  return $next_fetch_time;
}

function scrd_create_digest($preview=false) {
  global $wpdb;  
  require_once(ABSPATH . WPINC . '/feed.php');

  $tzs = get_option("timezone_string");
  if (!date_default_timezone_set($tzs?$tzs:"UTC")) {
    date_default_timezone_set("UTC");
  }
  
  $feed_url = clean_url(get_option('scrd_feed_url',''), array('http','https'), '');
  if ($feed_url == '') {
    throw new ErrorException("Invalid feed URL");
  }
  scrd_debug_log("RSS Digest: Feed URL [$feed_url]");
  
  // Get a SimplePie feed object from the specified feed source.
  if (function_exists('fetch_feed')) {
    //reduce cache period for debugging
    add_filter( 'wp_feed_cache_transient_lifetime', create_function('$a', "return ${SCRD_CACHE_OVERRIDE};") );
    $rss = fetch_feed($feed_url);
    remove_filter( 'wp_feed_cache_transient_lifetime', create_function('$a', "return ${SCRD_CACHE_OVERRIDE};") );  
  }
  else {
    throw new ErrorException("RSS Digest requires Wordpress 2.8 and greater");
  }
   
  if (is_wp_error($rss)) {
    throw new ErrorException("Problem fetching feed [" . $rss->get_error_message() . "]");
  }
 
  // Build an array of all the items, starting with element 0 (first element).
  $maxitems = $rss->get_item_quantity(get_option('scrd_max_items', SCRD_MAX_ITEMS)); 
  $rss_items = $rss->get_items(0, $maxitems); 
  scrd_debug_log("RSS Digest: Fetched $maxitems items");
  
  $feed_last_item_time = scrd_get_last_item_time($feed_url);
  
  $num_items_in_digest = 0;
  $max_item_time = 0;
  
  $content = '<ul class="scrd_digest">'."\n";
  foreach ( $rss_items as $item ) {
    if ($item->get_date('U') <= $feed_last_item_time) {
      continue;
    }
    $content .= '<li><a href="' . $item->get_link() . '" rel="external">' . $item->get_title() . '</a>'."\n";
    if (get_option('scrd_include_description', SCRD_DEFAULT_INCLUDE_DESC) && $item->get_description()) {
      $content .= '<div>' . $item->get_description() . '</div>'."\n";
    }
    $content .= '</li>'."\n";
    $num_items_in_digest++;
    if ($item->get_date('U') > $max_item_time) {
      $max_item_time = $item->get_date('U');
    }
  }
  $content .= '</ul>'."\n";
  
  $title = scrd_post_title();
  
  if ($preview) {
    if ($num_items_in_digest < get_option('scrd_min_items', SCRD_MIN_ITEMS)) {
      $content = '<div>Not enough new items for digest.</div>';
      $title = '';
    }
    if ($num_items_in_digest == 0) {
      $content = ' <div>No new items for digest.</div>';
      $title = '';
    }
    return array(
      'post_content' => $content,
      'post_title' => $title,
    );
  }

  if ($num_items_in_digest == 0) {
    throw new ErrorException('No new items for digest');
  }

  if ($num_items_in_digest < get_option('scrd_min_items', SCRD_MIN_ITEMS)) {
    throw new ErrorException('Not enough new items for digest');
  }

  if ($max_item_time > 0) {
    scrd_set_last_item_time($feed_url, $max_item_time);
    scrd_debug_log("RSS Digest: Feed last item time set at $max_item_time");
  }

  scrd_debug_log("RSS Digest: Created digest of $num_items_in_digest items");

  if (get_option('scrd_give_credit', 1) == 1) {
    $content .= '<p class="scrd_credit">Digest powered by <a href="http://geekfactor.charrington.com/projects/rss-digest">RSS Digest</a></p>'."\n";
    //$content .= '<div style="clear:both"></div>'."\n";
  }
  
  $post_data = array(
    'post_content' => $wpdb->escape($content),
    'post_title' => $title,
    'post_date' => date('Y-m-d H:i:s'),
    'post_category' => array(get_option('scrd_post_category', SCRD_DEFAULT_CATEGORY)),
    'post_status' => get_option('scrd_post_status', SCRD_DEFAULT_STATUS),
    'post_author' => get_option('scrd_post_author', SCRD_DEFAULT_AUTHOR),
    'tags_input' => get_option('scrd_post_tags',''),
  );
  
  return $post_data;
}

function scrd_post_title() {
  global $wpdb;  
  $post_title = $wpdb->escape(get_option('scrd_digest_title', SCRD_DEFAULT_TITLE));
  if (get_option('scrd_append_date_to_title', SCRD_DEFAULT_APPEND_DATE)) {
    $post_title .= " " . date(get_option('date_format', 'F j, Y'));
  }
  return $post_title;
}

function scrd_get_last_item_time($feed) {
  $hash = scrd_get_feed_hash($feed);
  $options = get_option('scrd_plugin_options');
  if (isset($options['feed_settings'])) {
    $feed_settings = $options['feed_settings'][$hash];
    return $feed_settings['last_item_time'];
  }
  return 0;
}
  

function scrd_set_last_item_time($feed, $time) {
  $hash = scrd_get_feed_hash($feed);
  $options = get_option('scrd_plugin_options');
  if (isset($options['feed_settings'][$hash])) {
    $options['feed_settings'][$hash]['last_item_time'] = $time;
  }
  else {
    $options['feed_settings'][$hash] = array(
      'last_item_time' => $time,
    );
  }
  update_option('scrd_plugin_options', $options);
}

function scrd_get_feed_hash($feed) {
  return md5($feed);
}

function scrd_post_digest() {
  try {
    $post_data = scrd_create_digest();
    $post_id = wp_insert_post($post_data);
    scrd_debug_log("RSS Digest: Created digest post ID = $post_id");
  }
  catch (Exception $e) {
    scrd_debug_log("RSS Digest: ".$e->getMessage());
  }  
  add_post_meta($post_id, "_rss_digest_post", "1")
    ? scrd_debug_log("RSS Digest: Successfully added post meta")
    : scrd_debug_log("RSS Digest: Error adding post meta");
}
add_action('scrd_post_digest', 'scrd_post_digest');

// Looks like WP will let posts through as these users anyway
// function scrd_options_warn() {
//   $usero = new WP_User(get_option('scrd_post_author', SCRD_DEFAULT_AUTHOR));
//   if ((get_option('scrd_post_status', SCRD_DEFAULT_STATUS) == 'publish') &&
//       (!$usero->has_cap('publish_posts'))) {
//         print('<div class="error"><p>Warning. The currently selected user does not have the ability to publish posts. Please select a different user or post status.</p></div>');
//       }
// }

function scrd_options_form() {
  //scrd_options_warn();
  
  $categories = get_categories('hide_empty=0');
  $cat_options = '';
  foreach ($categories as $category) {
    // WP < 2.3 compatibility
    !empty($category->term_id) ? $cat_id = $category->term_id : $cat_id = $category->cat_ID;
    !empty($category->name) ? $cat_name = $category->name : $cat_name = $category->cat_name;
    if ($cat_id == get_option('scrd_post_category', SCRD_DEFAULT_CATEGORY)) {
      $selected = 'selected="selected"';
    }
    else {
      $selected = '';
    }
    $cat_options .= "\n\t<option value='$cat_id' $selected>$cat_name</option>";
  }

  $statuses = array('draft','publish','pending');
  $status_options = '';
  foreach ($statuses as $status) {
    if ($status == get_option('scrd_post_status', SCRD_DEFAULT_STATUS)) {
      $selected = 'selected="selected"';
    }
    else {
      $selected = '';
    }
    $status_options .= "\n\t<option value='$status' $selected>$status</option>";
  }  
  
  $authors = get_users_of_blog();
  $author_options = '';
  foreach ($authors as $user) {
    $usero = new WP_User($user->user_id);
    $author = $usero->data;
    // Only list users who are allowed to publish
    //if (! $usero->has_cap('publish_posts')) {
    //  continue;
    //  }
    if ($author->ID == get_option('scrd_post_author', SCRD_DEFAULT_AUTHOR)) {
      $selected = 'selected="selected"';
    }
    else {
      $selected = '';
    }
    $author_options .= "\n\t<option value='$author->ID' $selected>$author->user_nicename</option>";
  }
  
  $yes_no = array(
    array('append_date_to_title', SCRD_DEFAULT_APPEND_DATE),
    array('give_credit', 1),
    array('include_description', SCRD_DEFAULT_INCLUDE_DESC),
  );
  foreach ($yes_no as $key) {
    $var = $key[0].'_options';
    if (get_option('scrd_'.$key[0], $key[1]) == '0') {
      $$var = '
        <option value="0" selected="selected">No</option>
        <option value="1">Yes</option>
      ';
    }
    else {
      $$var = '
        <option value="0">No</option>
        <option value="1" selected="selected">Yes</option>
      ';
    }
  }

  $hours = range(0, 23);
  $hours_options = '';
  foreach ($hours as $hour) {
    if (get_option('scrd_post_hour', SCRD_DEFAULT_HOUR) == $hour) {
      $selected = 'selected="selected"';
    }
    else {
      $selected = '';
    }
    $hours_options .= "\n\t<option value='$hour' $selected>" . str_pad($hour, 2, '0', STR_PAD_LEFT) . "</option>";
  }

  $minutes = range(0, 59, SCRD_DEFAULT_MINUTE_GRANULARITY); 
  $minutes_options = '';
  foreach ($minutes as $minute) {
    if (get_option('scrd_post_minute', SCRD_DEFAULT_MINUTE) == $minute) {
      $selected = 'selected="selected"';
    }
    else {
      $selected = '';
    }
    $minutes_options .= "\n\t<option value='$minute' $selected>" . str_pad($minute, 2, '0', STR_PAD_LEFT) . "</option>";
  }

  $days = explode(",", SCRD_DAYS_LIST);
  $post_days = get_option('scrd_post_days', SCRD_DEFAULT_POST_DAYS);
  $post_days_checkboxes = '';
  foreach ($days as $day) {
    if ($post_days[$day]) {
      $checked = 'checked';
    }
    else {
      $checked = '';
    }
    $day_lower = strtolower($day);
    $post_days_checkboxes .= "\n<input type='checkbox' name='scrd_post_days_$day' value='1' $checked>$day<br>";
  }

  $timezone_string = get_option("timezone_string", "UTC");
  if (!date_default_timezone_set($timezone_string)) {
    date_default_timezone_set("UTC");
  }
  $gmt_offset = get_option('gmt_offset');
  $current_time = date_i18n('H:i:s');

  print('
    <div class="wrap" id="scrd_options_page">
      <form id="sc_rssdigest" name="sc_rssdigest" action="'.get_bloginfo('wpurl').'/wp-admin/options-general.php" method="post">
        <h2>RSS Digest</h2>
        <fieldset>
          <legend><strong>Feed</strong></legend>
          <div class="option">
            <label for="scrd_feed_url">Feed URL</label>
            <input type="text" size="55" name="scrd_feed_url" id="scrd_feed_url" value="'.get_option('scrd_feed_url', '').'" />
          </div>        
        </fieldset>
        <fieldset>
          <legend><strong>Schedule</strong></legend>
          <div class="option">
						<p><em>Hint: For a weekly digest, check only one day.</em></p>
            <label for="scrd_post_days">Days to post digest</label>
            <div id="dayboxes">
              '.$post_days_checkboxes.'
              <a href="#" onclick="RSSDigest.checkAllDays();">Select All</a> | <a href="#" onclick="RSSDigest.uncheckAllDays();">select None</a>
            </div>
          </div>
          <div class="option">
            <label for="scrd_post_hour">Time to post digest</label>
            <select name="scrd_post_hour" id="scrd_post_hour">'.$hours_options.'</select><select name="scrd_post_minute" id="scrd_post_minute">'.$minutes_options.'</select><span class="sameline">'.$timezone_string.'</span><br/>
            <div>Current Time: '.$current_time.' '.$timezone_string.'</div>
          </div>          
        </fieldset>
        <fieldset>
          <legend><strong>Posts</strong></legend>
          <div class="option">
            <label for="scrd_digest_title">Title of digest posts</label>
            <input type="text" size="15" name="scrd_digest_title" id="scrd_digest_title" value="'.get_option('scrd_digest_title', SCRD_DEFAULT_TITLE). '" />
          </div>
          <div class="option">
            <label for="scrd_append_date_to_title">Append date to title</label>
            <select name="scrd_append_date_to_title" id="scrd_append_date_to_title">'.$append_date_to_title_options.'</select>
          </div>
          <div class="option">
            <label for="scrd_post_category">Category for digest posts</label>
            <select name="scrd_post_category" id="scrd_post_category">'.$cat_options.'</select>
          </div>
          <div class="option">
            <label for="scrd_post_tags">Tags for digest posts</label>
            <input type="text" size="25" name="scrd_post_tags" id="scrd_post_tags" value="'.get_option('scrd_post_tags', '').'" />
          </div>
          <div class="option">
            <label for="scrd_post_status">Status for digest posts</label>
            <select name="scrd_post_status" id="scrd_post_status">'.$status_options.'</select>
          </div>
          <div class="option">
            <label for="scrd_post_author">Author for digest posts</label>
            <select name="scrd_post_author" id="scrd_post_author">'.$author_options.'</select>
          </div>          
          <div class="option">
            <label for="scrd_give_credit">Give RSS Digest credit</label>
            <select name="scrd_give_credit" id="scrd_give_credit">'.$give_credit_options.'</select>
          </div>
        </fieldset>
        <fieldset>
          <legend><strong>Items</strong><legend>
          <div class="option">
            <label for="scrd_include_description">Include item descriptions</label>
            <select name="scrd_include_description" id="scrd_include_description">'.$include_description_options.'</select>
          </div>
          <div class="option">
            <label for="scrd_max_items">Maximum number of items per digest</label>
            <input type="text" size="3" name="scrd_max_items" id="scrd_max_items" value="'.get_option('scrd_max_items', SCRD_MAX_ITEMS).'" />
            <span class="sameline">Numbers only please.</span>
          </div>
          <div class="option">
            <label for="scrd_min_items">Minimum number of items per digest</label>
            <input type="text" size="3" name="scrd_min_items" id="scrd_min_items" value="'.get_option('scrd_min_items', SCRD_MIN_ITEMS).'" />
            <span class="sameline">Numbers only please.</span>
          </div>
        </fieldset>
        <p class="submit">
          <input type="submit" name="submit" class="button-primary" value="Update RSS Digest Options" 
            onclick="this.form.sc_action.value=\'scrd_update_settings\';" />
        </p>
        <h2>Debug Options</h2>
        <p class="submit">
          <input type="submit" name="submit" class="button-secondary" value="Preview"
            onclick="jQuery(\'#sc_preview\').load(\'' . get_bloginfo('wpurl') . '/index.php?sc_action=scrd_post_preview\');jQuery(\'#sc_preview\').show();return false;" />
          <input type="submit" name="submit" class="button-secondary" value="Post Now"
            onclick="this.form.sc_action.value=\'scrd_post_digest_now\';" />
          <input type="submit" name="submit" class="button-secondary" value="Reset Settings"
            onclick="this.form.sc_action.value=\'scrd_clear_settings\';" />
        </p>
        <div id="sc_preview" style="display: none;">
          <p>Loading preview...</p>
        </div>
        <input type="hidden" id="sc_action" name="sc_action" value="" class="hidden" style="display: none;" />
      </form>
    </div>
  ');
}

function scrd_menu_items() {
  if (current_user_can('manage_options')) {
    add_options_page('RSS Digest Options'
      , 'RSS Digest'
      , 10
      , basename(__FILE__)
      , 'scrd_options_form'
    );
  }
}
add_action('admin_menu', 'scrd_menu_items');

function scrd_plugin_action_links($links, $file) {
  $plugin_file = basename(__FILE__);
  if (basename($file) == $plugin_file) {
    $settings_link = '<a href="options-general.php?page='.$plugin_file.'">Settings</a>';
    array_unshift($links, $settings_link);
  }
  return $links;
}
add_filter('plugin_action_links', 'scrd_plugin_action_links', 10, 2);

function scrd_update_settings() {
  $options = array_map('trim', explode(',', SCRD_OPTIONS));
  foreach ($options as $option) {
    $value = stripslashes($_POST['scrd_'.$option]);
    if (isset($_POST['scrd_'.$option]) && ($value <> '')) {
      if ($option == "feed_url") {
          $value = clean_url($value, array('http','https'), 'db');
      }
      update_option('scrd_'.$option, $value);
    }
  }
  // Process post_days
  $days = explode(",", SCRD_DAYS_LIST);
  foreach ($days as $day){
    $post_days[$day] = ($_POST['scrd_post_days_'.$day] == "1") ? 1 : 0;
  }
  update_option('scrd_post_days', $post_days);
  $next_digest = scrd_get_next_digest_time();
  scrd_debug_log("RSS Digest: Updating options. Scheduling next digest for " . date(SCRD_DEBUG_DATE_FMT, $next_digest) . ".");
  wp_clear_scheduled_hook('scrd_do_digest');
  wp_schedule_single_event(scrd_get_next_digest_time(), 'scrd_do_digest');
}

function scrd_clear_settings() {
  $options = array_map('trim', explode(',', SCRD_OPTIONS));
  foreach ($options as $option) {
    delete_option('scrd_'.$option);
  }
}

function scrd_request_handler() {
  $plugin_file = basename(__FILE__);
  if (!empty($_GET['sc_action'])) {
    switch($_GET['sc_action']) {
      case 'scrd_post_preview':
        try {
          $preview_post = scrd_create_digest(true);
          print('
            <link rel="stylesheet" type="text/css" href="'.plugins_url('rss-digest/rss-digest.css').'" />
            <h3>'.stripslashes($preview_post['post_title']).'</h3>
                 ');
          print(stripslashes($preview_post['post_content']));         
        }
        catch (Exception $e) {
          print($e->getMessage());
          scrd_debug_log("RSS Digest: ".$e->getMessage());
        }
        die();
        break;    
      case 'scrd_js_admin':
        remove_action('shutdown', 'scrd_post_digest');
        header("Content-Type: text/javascript");
?>
var RSSDigest = function() {
  var _form = "document.sc_rssdigest";
  var _field = "scrd_post_days";
  var _days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

  function checkAllDays() {
    for (var i =0; i < _days.length; i++) {
      eval(_form + '.' + _field + "_" + _days[i]).checked = true;
    }
  }
  function uncheckAllDays() {
    for (var i =0; i < _days.length; i++) {
      eval(_form + '.' + _field + "_" + _days[i]).checked = false;
    }  
  }
  return {
    checkAllDays:checkAllDays,
    uncheckAllDays:uncheckAllDays
  }
}();
<?php
        die();
        break;
      case 'scrd_css_admin':
        remove_action('shutdown', 'scrd_post_digest');
        header("Content-Type: text/css");
?>
#sc_readme {
  height: 300px;
  width: 95%;
}
#sc_rssdigest .options {
  overflow: hidden;
  border: none;
}
#sc_rssdigest .option {
  overflow: hidden;
  padding-bottom: 9px;
  padding-top: 9px;
}
#sc_rssdigest .option label {
  display: block;
  float: left;
  width: 250px;
  margin-right: 24px;
  text-align: right;
}
#sc_rssdigest fieldset {
  margin-top: 18px;
}
#sc_rssdigest legend strong {
  font-size: 110%;
}
#sc_rssdigest .option div {
  float: left;
  margin-left: 280px;
  margin-top: 6px;
  clear: left;
}
#sc_rssdigest .option span {
  float: none;
  margin-left: 7px;
  margin-top: 0px;
  clear: none;
}
#sc_rssdigest select,
#sc_rssdigest input {
  float: left;
  display: block;
  margin-right: 6px;
}
#sc_rssdigest p.submit {
  overflow: hidden;
}
#sc_rssdigest .option span, #sc_rssdigest .option div {
  color: #666;
}
#sc_rssdigest #dayboxes {
  margin-left: 280px;
}
#sc_preview {
  margin-left: 100px;
  padding: 20px;
  width: 500px;
  border: 1px solid #ccc;
}
#sc_preview ul.scrd_digest {
  list-style: disc outside none;
  margin-left: 24px;
}
<?php
        die();
        break;
    }
  }
  if (!empty($_POST['sc_action'])) {
    switch($_POST['sc_action']) {
      case 'scrd_update_settings':
        scrd_update_settings();
        wp_redirect(get_bloginfo('wpurl').
          '/wp-admin/options-general.php?page='.$plugin_file.'&updated=true');
        die();
        break;
      case 'scrd_post_digest_now':
        update_option('scrd_last_digest', time());
        add_action('shutdown', 'scrd_post_digest');
        wp_redirect(get_bloginfo('wpurl').
          '/wp-admin/options-general.php?page='.$plugin_file.'&updated=true');
        die();
        break;
      case 'scrd_clear_settings':
        scrd_clear_settings();
        wp_redirect(get_bloginfo('wpurl').
          '/wp-admin/options-general.php?page='.$plugin_file.'&updated=true');
        die();
        break;
    }
  }
}
add_action('init', 'scrd_request_handler', 10);

function scrd_head() {
  print('
    <link rel="stylesheet" type="text/css" href="'.plugins_url('rss-digest/rss-digest.css').'" />
  ');
}
add_action('wp_head', 'scrd_head');

function scrd_head_admin() {
  print('
    <link rel="stylesheet" type="text/css" href="'.get_bloginfo('wpurl').'/index.php?sc_action=scrd_css_admin" />
  ');
  print('
    <script type="text/javascript" src="'.get_bloginfo('wpurl').'/index.php?sc_action=scrd_js_admin"></script>
  ');
}
add_action('admin_head', 'scrd_head_admin');

function scrd_debug_log($message) {
  if (SCRD_DEBUG) {
    error_log($message);
  }
}

function scrd_requirements_message() {
  global $wpdb;

  $is_php_valid = version_compare(phpversion(), SCRD_MIN_PHP_VERSION, '>');
  $is_wp_valid = version_compare(get_bloginfo("version"), SCRD_MIN_WP_VERSION, '>');
  $meets_requirements = ($is_php_valid && $is_wp_valid);
  $class = $meets_requirements ? "update-message" : "error";

  if (!$meets_requirements) {
    $message_head = "<h2 style='display:none;'></h2><div class='error' style='margin:5px; padding:3px; text-align:left;'>";
    $top_message_head = "<div class='error' style='margin:5px; padding:3px; text-align:left;'>";
    $message = "Your host is not compatible with RSS Digest. The following items must be upgraded:<br/> ";
    if (!$is_php_valid) {
      $message .= " - <strong>PHP</strong> (Current version: " .  phpversion() . ", Required: " . SCRD_MIN_PHP_VERSION . ")<br/> ";
    }
    if (!$is_wp_valid) {
      $message .= " - <strong>WordPress</strong> (Current version: " .  get_bloginfo("version") . ", Required: " . SCRD_MIN_WP_VERSION . ")<br/> ";
    }

    $message .= "</div>";
  }
  echo '</tr><tr class="plugin-update-tr"><td colspan="5" class="plugin-update">' . $top_message_head . $message . $message_head . $message . '</td></tr>';
}
add_action('after_plugin_row_rss-digest/rss-digest.php', 'scrd_requirements_message');

register_activation_hook(__FILE__, 'scrd_activation');

function scrd_activation() {
  //remove old options
  // 0.6
  $last_digest_time = get_option('scrd_last_digest',0);
  delete_option('scrd_last_digest');
  // 0.6.1
  delete_option('scrd_next_digest');
  
  //upgrade path

  //from 0.6x to 1.0
  //set last item time to the time of the last digest. not perfect, but close enough.
  if ($last_digest_time > 0) {  // if last_digest_time == 0, then 1.x has been previously activated & option was deleted
    $feed_url = clean_url(get_option('scrd_feed_url',''), array('http','https'), '');
    if ($feed_url != '') { 
      scrd_set_last_item_time($feed_url, $last_digest_time);
    }
  }
}

register_deactivation_hook(__FILE__, 'scrd_deactivation');

function scrd_deactivation() {
  wp_clear_scheduled_hook('scrd_do_digest');
  //scrd_clear_settings();
}

?>