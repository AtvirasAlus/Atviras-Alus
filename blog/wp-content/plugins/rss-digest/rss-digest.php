<?php
/*
Plugin Name: RSS Digest
Plugin URI: http://geekfactor.charrington.com/projects/rss-digest
Description: Publishes RSS items to daily digest
Version: 1.5
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
//
// TODO: Add function to clean up log file
// TODO: Activate debug tab when using tools
// TODO: Investigate problem w/ Twitter tools: Fatal error: Call to a member function do_blog_post_tweet() on a non-object in [...]/wp-content/plugins/twitter-tools.php on line 740.
// - related to publishing from shutdown() apparently TT init doesn't / hasn't run
// - maybe use schedule now, but problem is events scheduled w/in 10 minutes ignored - maybe bad for debugging
// -maybe clear schedule, set for now, then set schedule again??

// TODO: Should move init() to admin_init()?

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
define('SCRD_DEFAULT_POST_HEADER', '');
define('SCRD_DEFAULT_POST_FOOTER', '');
define('SCRD_DEFAULT_ADD_CSS_CLEAR', 0);
define('SCRD_DAYS_LIST', 'Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday');
define('SCRD_OPTIONS', 'post_days, post_hour, post_minute, feed_url, max_items, min_items, give_credit, digest_title, post_category, post_tags, post_author, post_status, append_date_to_title, css_clear, plugin_options, include_description, post_header, post_footer, debug_log');
define('SCRD_CACHE_OVERRIDE', 60); // in secs; 14400 secs == 4 hours
define('SCRD_DEFAULT_DEBUG', 1);
define('SCRD_DEBUG_DATE_FMT', 'D Y-m-d G:i:s T');
define('SCRD_MIN_PHP_VERSION', '5.0.0');
define('SCRD_MIN_WP_VERSION', '2.8.0');
define('SCRD_DEBUG_LOG_TABLE', 'scrd_debug_log');
define('SCRD_DB_VERSION', 1);
define('SCRD_LOG_PAGER_LIMIT', 25);

if (function_exists('date_default_timezone_set')) {
  $tzs = get_option("timezone_string");
  if (!date_default_timezone_set($tzs?$tzs:"UTC")) {
    date_default_timezone_set("UTC");
  }
}

function scrd_do_digest() {
  $right_now = time();
  $next_digest = scrd_get_next_digest_time();
  if ($next_digest) {
    scrd_debug_log('Starting a new digest...');
    //set up the next digest
    wp_schedule_single_event($next_digest, 'scrd_do_digest');
    //run this digest
    scrd_post_digest();
    scrd_debug_log('Scheduled next digest for ' . date(SCRD_DEBUG_DATE_FMT, $next_digest));
    update_option('scrd_last_digest_time', $right_now);
  }
}
add_action('scrd_do_digest', 'scrd_do_digest');

function scrd_get_next_digest_time() {
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
  
  if ($date_offset == 8) {
    return 0;
  } else {
    // so long as there is at least one day checked, we end up here on day that is checked
    $next_fetch_time = strtotime("+$date_offset days $hour:$minute:0");
    return $next_fetch_time;
  }
}

function scrd_initialize_feed($feed_url) {
  require_once(ABSPATH . WPINC . '/feed.php');

  if ($feed_url == '') {
    throw new ErrorException("Invalid feed URL");
  }
  
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
    throw new ErrorException("Problem fetching feed: " . $rss->get_error_message());
  }
  return $rss;
}

function scrd_theme_digest_item($item) {
  $content = '<li><a href="' . $item->get_link() . '" rel="external">' . $item->get_title() . '</a>'."\n";
  if (get_option('scrd_include_description', SCRD_DEFAULT_INCLUDE_DESC) && $item->get_description()) {
    $content .= '<div>' . $item->get_description() . '</div>'."\n";
  }
  $content .= '</li>'."\n";
  return $content;
}

function scrd_theme_digest_list($item_html) {
  $content = '<ul class="scrd_digest">' . "\n" . $item_html . '</ul>'."\n";
  return $content;
}

function scrd_add_header_footer($themed_list) {
  $post_header = get_option('scrd_post_header', SCRD_DEFAULT_POST_HEADER);
  $post_footer = get_option('scrd_post_footer', SCRD_DEFAULT_POST_FOOTER);

  if ($post_header <> '') {
    $content = '<p class="scrd_header">' . $post_header . '</p>' . "\n";
  }
  $content .= $themed_list;
  if ($post_footer <> '') {
    $content .= '<p class="scrd_footer">' . $post_footer . '</p>' . "\n";
  }
  return $content;
}

function scrd_create_digest($preview=false) {
  global $wpdb;  
 
  $feed_url = clean_url(get_option('scrd_feed_url',''), array('http','https'), '');
  scrd_debug_log("Initializing feed");
  $rss = scrd_initialize_feed($feed_url);
  
  // Build an array of all the items, starting with element 0 (first element).
  $max_items = $rss->get_item_quantity(get_option('scrd_max_items', SCRD_MAX_ITEMS)); 
  
  $feed_last_item_time = scrd_get_last_item_time($feed_url);
  
  $content = '';
  $num_items_in_digest = 0;
  
  // by default simple pie sorts reverse chronologically; first item is newest
  $item = $rss->get_item();
  
  $time_of_newest_item = $item->get_date('U');
  scrd_debug_log("Fetched $max_items items");  
  
  if ($time_of_newest_item <= $feed_last_item_time) {
    scrd_debug_log('No new items for digest');
    $content = '<div>No new items for digest.</div>';
    $title = '';
  } else {

    for ($item_index = 1; $item_index < $max_items; $item_index++) {
      if ($item->get_date('U') <= $feed_last_item_time) {
        break;
      }
      $content .= scrd_theme_digest_item($item);
      $item = $rss->get_item($item_index);
    }

    $num_items_in_digest = $item_index;
    if ($num_items_in_digest >= get_option('scrd_min_items', SCRD_MIN_ITEMS)) {
      $content = scrd_theme_digest_list($content);
      $content = scrd_add_header_footer($content);
      $title = scrd_post_title();
    } else {
      $num_items_in_digest = 0;
      scrd_debug_log('Not enough new items for digest');
      $content = '<div>Not enough new items for digest.</div>';
      $title = '';
    }
  }
  
  if ($preview) {
    return array(
      'post_content' => $content,
      'post_title' => $title,
    );
  }

  if ($num_items_in_digest == 0) {
    return array();
  }
  
  if (get_option('scrd_give_credit', 1) == 1) {
    $content .= '<p class="scrd_credit">Digest powered by <a href="http://www.rssdigestpro.com">RSS Digest</a></p>'."\n";
    if (SCRD_DEFAULT_ADD_CSS_CLEAR == 1) {
      $content .= '<div style="clear:both"></div>'."\n";
    }
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

  scrd_set_last_item_time($feed_url, $time_of_newest_item);  
  scrd_debug_log("Created digest of $num_items_in_digest items");  
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
  scrd_debug_log('Feed last item time set to ' . date(SCRD_DEBUG_DATE_FMT, $time));
}

function scrd_get_feed_hash($feed) {
  return md5($feed);
}

function scrd_post_digest() {
  try {
    $post_data = scrd_create_digest();
    if (empty($post_data)) {
      return 0;
    }
    $post_id = wp_insert_post($post_data);
    if (add_post_meta($post_id, "_rss_digest_post", "1")) {
      scrd_debug_log("Successfully added post meta");
    } else {
      throw new ErrorException("Error adding post meta");
    }
    scrd_debug_log("Posted digest: Post ID = $post_id");
  }
  catch (Exception $e) {
    scrd_debug_log($e->getMessage(), 'error');
  }  

}
add_action('scrd_post_digest', 'scrd_post_digest');

function scrd_settings_page() {
?>
  <div class="wrap" id="scrd_options_page">
    <div id="tabs">
      <h2>RSS Digest</h2>
      <ul>
        <li><a href="#scrd_settings">Settings</a></li>
        <li><a href="#scrd_debug">Debug</a></li>
      </ul>
      <div id="scrd_settings">
        <?php scrd_options_tab(); ?>
      </div>
      <div id="scrd_debug">
        <?php scrd_debug_tab(); ?>
      </div>
  </div>
  <script type="text/javascript">
    jQuery(function() {
      jQuery("#tabs").tabs();
    });
  </script>
<?
}

// Looks like WP will let posts through as these users anyway
// function scrd_options_warn() {
//   $usero = new WP_User(get_option('scrd_post_author', SCRD_DEFAULT_AUTHOR));
//   if ((get_option('scrd_post_status', SCRD_DEFAULT_STATUS) == 'publish') &&
//       (!$usero->has_cap('publish_posts'))) {
//         print('<div class="error"><p>Warning. The currently selected user does not have the ability to publish posts. Please select a different user or post status.</p></div>');
//       }
// }

function scrd_options_tab() {
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
  // setting timezone in init -- remove if works
  // if (!date_default_timezone_set($timezone_string)) {
  //   date_default_timezone_set("UTC");
  // }
  $gmt_offset = get_option('gmt_offset');
  $current_time = date_i18n('H:i:s');

  $post_header = get_option('scrd_post_header', SCRD_DEFAULT_POST_HEADER);
  $post_footer = get_option('scrd_post_footer', SCRD_DEFAULT_POST_FOOTER);
?>
    <form id="scrd_settings_form" name="scrd_settings_form" class="scrd_form" action="<?php echo get_bloginfo('wpurl'); ?>/wp-admin/options-general.php" method="post">
       <fieldset>
        <legend><strong>Feed</strong></legend>
        <div class="option">
          <label for="scrd_feed_url">Feed URL</label>
          <input type="text" size="55" name="scrd_feed_url" id="scrd_feed_url" value="<?php echo get_option('scrd_feed_url', ''); ?>" />
        </div>        
      </fieldset>
      <fieldset>
        <legend><strong>Schedule</strong></legend>
        <div class="option">
          <p><em>Hint: For a weekly digest, check only one day.</em></p>
          <label for="scrd_post_days">Days to post digest</label>
          <div id="dayboxes">
            <?php echo $post_days_checkboxes; ?>
            <a href="#" onclick="RSSDigest.checkAllDays();">Select All</a> | <a href="#" onclick="RSSDigest.uncheckAllDays();">select None</a>
          </div>
        </div>
        <div class="option">
          <label for="scrd_post_hour">Time to post digest</label>
          <select name="scrd_post_hour" id="scrd_post_hour"><?php echo $hours_options; ?></select><select name="scrd_post_minute" id="scrd_post_minute"><?php echo $minutes_options; ?></select> <?php echo $timezone_string; ?><br/>
          <div>Current Time: <?php echo $current_time .' '. $timezone_string; ?></div>
        </div>          
      </fieldset>
      <fieldset>
        <legend><strong>Posts</strong></legend>
        <div class="option">
          <label for="scrd_digest_title">Title of digest posts</label>
          <input type="text" size="15" name="scrd_digest_title" id="scrd_digest_title" value="<?php echo get_option('scrd_digest_title', SCRD_DEFAULT_TITLE); ?>" />
        </div>
        <div class="option">
          <label for="scrd_append_date_to_title">Append date to title</label>
          <select name="scrd_append_date_to_title" id="scrd_append_date_to_title"><?php echo $append_date_to_title_options; ?></select>
        </div>
        <div class="option">
          <label for="scrd_post_header">Post header text</label>
          <input type="text" size="50" name="scrd_post_header" id="scrd_post_header" value="<?php echo $post_header; ?>" />
        </div>
        <div class="option">
          <label for="scrd_post_footer">Post footer text</label>
          <input type="text" size="50" name="scrd_post_footer" id="scrd_post_footer" value="<?php echo $post_footer; ?>" />
        </div>
        <div class="option">
          <label for="scrd_post_category">Category for digest posts</label>
          <select name="scrd_post_category" id="scrd_post_category"><?php echo $cat_options; ?></select>
        </div>
        <div class="option">
          <label for="scrd_post_tags">Tags for digest posts</label>
          <input type="text" size="25" name="scrd_post_tags" id="scrd_post_tags" value="<?php echo get_option('scrd_post_tags', ''); ?>" />
        </div>
        <div class="option">
          <label for="scrd_post_status">Status for digest posts</label>
          <select name="scrd_post_status" id="scrd_post_status"><?php echo $status_options; ?></select>
        </div>
        <div class="option">
          <label for="scrd_post_author">Author for digest posts</label>
          <select name="scrd_post_author" id="scrd_post_author"><?php echo $author_options; ?></select>
        </div>
        <div class="option">
          <label for="scrd_give_credit">Give RSS Digest credit</label>
          <select name="scrd_give_credit" id="scrd_give_credit"><?php echo $give_credit_options; ?></select>
        </div>
      </fieldset>
      <fieldset>
        <legend><strong>Items</strong><legend>
        <div class="option">
          <label for="scrd_include_description">Include item descriptions</label>
          <select name="scrd_include_description" id="scrd_include_description"><?php echo $include_description_options; ?></select>
        </div>
        <div class="option">
          <label for="scrd_max_items">Maximum number of items per digest</label>
          <input type="text" size="3" name="scrd_max_items" id="scrd_max_items" value="<?php echo get_option('scrd_max_items', SCRD_MAX_ITEMS); ?>" />
          <span class="sameline">Numbers only please.</span>
        </div>
        <div class="option">
          <label for="scrd_min_items">Minimum number of items per digest</label>
          <input type="text" size="3" name="scrd_min_items" id="scrd_min_items" value="<?php echo get_option('scrd_min_items', SCRD_MIN_ITEMS); ?>" />
          <span class="sameline">Numbers only please.</span>
        </div>
      </fieldset>
      <p class="submit">
        <input type="submit" name="submit" class="button-primary" value="Update RSS Digest Options" 
          onclick="this.form.scrd_action.value='scrd_update_settings';" />
      </p>
      <input type="hidden" name="scrd_action" value="" class="hidden" style="display: none;" />
    </form>
  
<?php
}

function scrd_get_digest_post_count() {
  global $wpdb;
  $sql = "
    SELECT COUNT(*)
    FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta
    WHERE wposts.ID = wpostmeta.post_id
    AND wpostmeta.meta_key = '_rss_digest_post'
    AND wpostmeta.meta_value = '1'
  ";
  $count = $wpdb->get_var($wpdb->prepare($sql));
  return $count;
}

function scrd_get_last_digest_date() {
  global $wpdb;
  $sql = "
    SELECT UNIX_TIMESTAMP(wposts.post_date)
    FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta
    WHERE wposts.ID = wpostmeta.post_id
    AND wpostmeta.meta_key = '_rss_digest_post'
    AND wpostmeta.meta_value = '1'
    ORDER BY wposts.post_date DESC
  ";
  $date = $wpdb->get_var($wpdb->prepare($sql));
  return $date;
}

function scrd_debug_tab() {
  $debug_log_checked = get_option('scrd_debug_log', SCRD_DEFAULT_DEBUG) ? 'checked' : '';
  $plugin_data = get_plugin_data(__FILE__);
  $version = $plugin_data['Version'];
  $feed_url = clean_url(get_option('scrd_feed_url',''), array('http','https'), '');
  $last_fetch = get_option('scrd_last_digest_time', 'Unknown');
  if ($last_fetch != 'Unknown') {
    $last_fetch = date('l ' . get_option('date_format', 'F j, Y') . ', ' . get_option('time_format', 'H:m p'), $last_fetch); 
  }
  $next_digest_time = scrd_get_next_digest_time();
  $next_fetch = $next_digest_time ? date('l ' . get_option('date_format', 'F j, Y') . ', ' . get_option('time_format', 'H:m p'), $next_digest_time) : 'None';
  $feed_last_item_time = scrd_get_last_item_time($feed_url);
  $feed_last_item_time = $feed_last_item_time ? date('l ' . get_option('date_format', 'F j, Y') . ', ' . get_option('time_format', 'H:m p'), $feed_last_item_time) : 'Unknown';

  try {
    $num_digests = scrd_get_digest_post_count();
    $last_digest = date('l ' . get_option('date_format', 'F j, Y') . ', ' . get_option('time_format', 'H:m p'), scrd_get_last_digest_date());
  }
  catch (Exception $e) {
    $num_digests = '';
    $last_digest = '';
    scrd_debug_log("Error querying post metadata: $e->getMessage()");
  }
  
  $feed_error = false;
  try {
    $rss = scrd_initialize_feed($feed_url);
    $item = $rss->get_item();
    $item_date = $item->get_date('l ' . get_option('date_format', 'F j, Y') . ', ' . get_option('time_format', 'H:m p'));
    $feed_title = $rss->get_title();
    $feed_quantity = $rss->get_item_quantity();
  }
  catch (Exception $e) {
    $feed_error = true;
    $feed_error_message = $e->getMessage();
    if ($feed_error_message == '') {
      $feed_error_message = 'Unknown feed error';
    }
    scrd_debug_log($feed_error_message, 'error');
  }
 
  $php_version = phpversion();
  $php_ver_errclass = version_compare($php_version, SCRD_MIN_PHP_VERSION, '>') ? '' : 'class="scrd_error"';
  $wp_version = get_bloginfo("version");
  $wp_ver_errclass = version_compare($wp_version, SCRD_MIN_WP_VERSION, '>')  ? '' : 'class="scrd_error"';
?>
  <form id="scrd_debug_form" name="scrd_debug_form" class="scrd_form" action="<?php echo get_bloginfo('wpurl'); ?>/wp-admin/options-general.php" method="post">
    <fieldset>
      <legend><strong>System</strong></legend>
      <div><span>RSS Digest version:</span> <?php echo $version; ?>&nbsp;</div>
      <div <?php echo $php_ver_errclass; ?>><span>PHP version:</span> <?php echo $php_version; ?>&nbsp;</div>
      <div <?php echo $wp_ver_errclass; ?>><span>WordPress version:</span> <?php echo $wp_version; ?>&nbsp;</div>
      <div><span>Server:</span> <?php echo $_SERVER["SERVER_SOFTWARE"]; ?>&nbsp;</div>
    </fieldset>
    <fieldset>
      <legend><strong>Digests</strong></legend>
      <div><span>Number of digests:</span> <?php echo $num_digests; ?>&nbsp;</div>
      <div><span>Last digest:</span> <?php echo $last_digest; ?>&nbsp;</div>
      <div><span>Last fetch:</span> <?php echo $last_fetch; ?>&nbsp;</div>
      <div><span>Next fetch:</span> <?php echo $next_fetch; ?>&nbsp;</div>
    </fieldset>
    <fieldset>
      <legend><strong>Feed</strong></legend>
      <div><span>Current feed:</span> <a href="<?php echo $feed_url; ?>" target="_blank"><?php echo $feed_url; ?></a>&nbsp;</div>
      <?php if ($feed_error) { ?>
        <div class="scrd_error"><span>Error:</span> <?php echo $feed_error_message; ?>&nbsp;</div>
      <?php } else { ?>
        <div><span>Feed title:</span> <?php echo $feed_title; ?>&nbsp;</div>
        <div><span>Number of items available:</span> <?php echo $feed_quantity; ?>&nbsp;</div>
      <?php } ?>
      <div><span>Date of most recent item in feed:</span> <?php echo $item_date; ?>&nbsp;</div>
      <div><span>Date of most recent item 'digested':</span> <?php echo $feed_last_item_time; ?>&nbsp;</div> 
    </fieldset>
    <fieldset>
      <legend><strong>Debug Options</strong></legend>
      <div class="option">
        <label for="scrd_debug_log">Turn on detailed logging</label>
        <input type="checkbox" name="scrd_debug_log" id="scrd_debug_log" value="1" <?php echo $debug_log_checked; ?>><br>
      </div>
    </fieldset>
    <p class="submit">
      <input type="submit" name="submit" class="button-primary" value="Update Debug Options" 
        onclick="this.form.scrd_action.value='scrd_update_settings';" />
    </p>
    <fieldset>
      <legend><strong>Debug Tools</strong></legend>
      <p class="submit">
        <input type="submit" name="submit" class="button-secondary" value="Preview"
          onclick="jQuery('#scrd_preview').load('<?php echo get_bloginfo('wpurl'); ?>/index.php?scrd_action=scrd_post_preview');jQuery('#scrd_log').html('<p>Loading log...</p>');jQuery('#scrd_log').hide();jQuery('#scrd_preview').show();return false;" />
        <input type="submit" name="submit" class="button-secondary" value="Post Now"
          onclick="this.form.scrd_action.value='scrd_post_digest_now';" />
        <input type="submit" name="submit" class="button-secondary" value="Reset Plugin"
          onclick="this.form.scrd_action.value='scrd_clear_settings';" />
        <input type="submit" name="submit" class="button-secondary" value="Sync Item and Digest Times"
          onclick="this.form.scrd_action.value='scrd_sync_last_item_time';" />          
        <input type="submit" name="submit" class="button-secondary" value="Clear Log"
          onclick="this.form.scrd_action.value='scrd_clear_log';" />          
        <input type="submit" name="submit" class="button-secondary" value="View Log"
          onclick="jQuery('#scrd_log').load('<?php echo get_bloginfo('wpurl'); ?>/index.php?scrd_action=scrd_view_log');jQuery('#scrd_preview').html('<p>Loading preview...</p>');jQuery('#scrd_preview').hide();jQuery('#scrd_log').show();return false;" />
      </p>
    </fieldset>
    <input type="hidden" name="scrd_action" value="" class="hidden" style="display: none;" />
  </form>
  <div id="scrd_preview" style="display: none;">
    <p>Loading preview...</p>
  </div>
  <div id="scrd_log" style="display: none;">
    <p>Loading log...</p>
  </div>        

<?php
}

function scrd_sync_last_item_time() {
  // set time of last item to time of last digest
  $feed_url = clean_url(get_option('scrd_feed_url',''), array('http','https'), '');
  $last_digest = scrd_get_last_digest_date();
  $feed_last_item_time = scrd_get_last_item_time($feed_url);
  if (isset($last_digest)) {
    scrd_set_last_item_time($feed_url, $last_digest);
  }
  scrd_debug_log('Reset last item time from ' . date(SCRD_DEBUG_DATE_FMT, $feed_last_item_time) . ' to ' . date(SCRD_DEBUG_DATE_FMT, $last_digest) . ' for feed');
}

function scrd_menu_items() {
  if (current_user_can('manage_options')) {
    add_options_page('RSS Digest Options'
      , 'RSS Digest'
      , 10
      , basename(__FILE__)
      , 'scrd_settings_page'
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
// print_r($_POST);
// exit;
  $options = array_map('trim', explode(',', SCRD_OPTIONS));
  foreach ($options as $option) {
    if (isset($_POST['scrd_'.$option])) {
      switch ($option) {
        case 'feed_url':
          $value = clean_url($_POST['scrd_feed_url'], array('http','https'), 'db');
          break;
        case 'debug_log':
          continue;
        default:
          $value = stripslashes($_POST['scrd_'.$option]);
      }
      if ($value <> '') { update_option('scrd_'.$option, $value);
      }
    }
  }
  // Process debug log
  if ($_POST['submit'] == 'Update Debug Options') {
    $value = ($_POST['scrd_debug_log'] == "1") ? 1 : 0;
    update_option('scrd_debug_log', $value);
  }
  // Process post_days
  if ($_POST['submit'] == 'Update RSS Digest Options') {
    $days = explode(",", SCRD_DAYS_LIST);
    foreach ($days as $day){
      $post_days[$day] = ($_POST['scrd_post_days_'.$day] == "1") ? 1 : 0;
    }
    update_option('scrd_post_days', $post_days);
  }
  $next_digest = scrd_get_next_digest_time();
  wp_clear_scheduled_hook('scrd_do_digest');
  if ($next_digest) {
    scrd_debug_log("Updating options. Scheduling next digest for " . date(SCRD_DEBUG_DATE_FMT, $next_digest));
    wp_schedule_single_event(scrd_get_next_digest_time(), 'scrd_do_digest');
  } else {
    scrd_debug_log("Updating options. No new digest scheduled");
  }
}

function scrd_clear_settings() {
  scrd_debug_log('Clearing plugin settings');
  $options = array_map('trim', explode(',', SCRD_OPTIONS));
  foreach ($options as $option) {
    delete_option('scrd_'.$option);
  }
  delete_option('scrd_last_digest_time');
  delete_option('scrd_db_version');
}

function scrd_post_preview() {
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
    scrd_debug_log($e->getMessage(), 'error');
  }  
}

function scrd_send_admin_js(){
  remove_action('shutdown', 'scrd_post_digest');
  header("Content-Type: text/javascript");
  ?>
  var RSSDigest = function() {
    var _form = "document.scrd_settings_form";
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
}

function scrd_request_handler() {
  if (!empty($_GET['scrd_action'])) {
    switch($_GET['scrd_action']) {
      case 'scrd_post_preview':
        scrd_post_preview();
        die();
        break;    
      case 'scrd_js_admin':
        scrd_send_admin_js();
        die();
        break;
      case 'scrd_view_log':
        scrd_view_log($_GET['pager']);
        die();
        break;
    }
  }
  if (!empty($_POST['scrd_action'])) {
    switch($_POST['scrd_action']) {
      case 'scrd_update_settings':
        scrd_update_settings();
        break;
      case 'scrd_post_digest_now':
        scrd_do_digest();
        // wp_clear_scheduled_hook('scrd_do_digest');
        // wp_schedule_single_event(time(), 'scrd_do_digest');
        break;
      case 'scrd_clear_settings':
        scrd_clear_settings();
        break;
      case 'scrd_clear_log':
        scrd_clear_log();
        break;
      case 'scrd_sync_last_item_time':
        scrd_sync_last_item_time();
        break;
    }
    $plugin_file = basename(__FILE__);
    wp_redirect(get_bloginfo('wpurl').
      '/wp-admin/options-general.php?page='.$plugin_file.'&updated=true');
    die();
  }
}
add_action('init', 'scrd_request_handler', 10);

function scrd_init() {
  $plugin_page = strtolower($_GET['page']);
  if ($plugin_page == 'rss-digest.php') {
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-tabs');
  }
}
add_action('admin_init', 'scrd_init');

function scrd_head() {
  echo '<link rel="stylesheet" type="text/css" href="'.plugins_url('rss-digest/rss-digest.css').'" />';
}
add_action('wp_head', 'scrd_head');

function scrd_head_admin() {
  $plugin_page = strtolower($_GET['page']);
  if ($plugin_page == 'rss-digest.php') {
    echo '<link rel="stylesheet" type="text/css" href="'.plugins_url('rss-digest/rss-digest-admin.css').'" />';
    echo '<script type="text/javascript" src="'.get_bloginfo('wpurl').'/index.php?scrd_action=scrd_js_admin"></script>';
  }
}
add_action('admin_head', 'scrd_head_admin');

function scrd_debug_log($message, $severity='notice') {
  global $wpdb;
  //
  // Expected priorities = notice, warning, error
  //
  
  if (get_option('scrd_debug_log', SCRD_DEFAULT_DEBUG)) {
    $log_table = $wpdb->prefix . SCRD_DEBUG_LOG_TABLE;
    $time = date('Y-m-d H:i:s', time());
    $sql = "INSERT INTO `$log_table` (time, severity, message)
                                   VALUES (
                                     '$time',
                                     '$severity',
                                     '$message'
                                     )";
    $wpdb->query($wpdb->prepare($sql));
  }
}

function scrd_clear_log() {
  global $wpdb;
  $log_table = $wpdb->prefix . SCRD_DEBUG_LOG_TABLE;
  $wpdb->query($wpdb->prepare("TRUNCATE TABLE `$log_table`"));
}

function scrd_pager_link($page, $title, $link_text) {
  ?>
  <a onclick="jQuery('#scrd_log').load('<?php echo get_bloginfo('wpurl'); ?>/index.php?scrd_action=scrd_view_log&pager=<?php echo $page; ?>');return false;" title="<?php echo $title; ?>"><?php echo $link_text; ?></a>
  <?php
}

function scrd_view_log($page) {
  global $wpdb;
  $log_table = $wpdb->prefix . SCRD_DEBUG_LOG_TABLE;
  
  $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `$log_table`"));
  
  if (($count % SCRD_LOG_PAGER_LIMIT) <> 0) {
    $max_pages = floor($count/SCRD_LOG_PAGER_LIMIT) + 1;
  } else {
    $max_pages = $count/SCRD_LOG_PAGER_LIMIT;
  }
  
  if (!isset($page)) {
    $page = $max_pages;
  }
  
  $pager_min = ($page >= 1) ? ($page-1)*SCRD_LOG_PAGER_LIMIT : 0;
  $sql = "SELECT * FROM `$log_table` ORDER BY 'id' ASC LIMIT $pager_min, " . SCRD_LOG_PAGER_LIMIT;
  $results = $wpdb->get_results($wpdb->prepare($sql));

  echo "<div class='scrd_log_pane'>";
  echo "  <div class='scrd_pager'>";
  
  if ($page > 1) {
    scrd_pager_link($page-1, 'Previous Page', 'Previous Page');
  } else {
    echo '<span class="disabled">Previous Page</span>';
  }
  
  for ($page_index=1; $page_index <= $max_pages; $page_index++) { 
    if ($page_index != $page) {
      scrd_pager_link($page_index, "Page $page_index of $max_pages", $page_index);
    } else {
      echo " <span><strong>$page</strong></span>";
    }
  }
  
  if ($page < $max_pages) {
    scrd_pager_link($page+1, 'Next Page', 'Next Page');
  } else {
      echo '<span class="disabled">Next Page</span>';
  }
  
  echo "  </div>";
?>
  <table>
    <tr><th>Time</th><th>Severity</th><th>Message</th></tr>
<?php
  foreach ($results as $result) { 
      $row_class = ($result->id % 2 == 0) ? 'even' : 'odd'; 
      ?>
      <tr class="<?php echo "$row_class scrd_$result->severity"; ?>"><td class="first"><?php echo $result->time; ?></td><td><?php echo $result->severity; ?></td><td><?php echo $result->message; ?></td></tr>
<?php
  } ?>
  </table>
<?php
  echo "</div>";
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

function scrd_install() {
  // ref: http://codex.wordpress.org/Creating_Tables_with_Plugins
  global $wpdb;
  
  $log_table = $wpdb->prefix . SCRD_DEBUG_LOG_TABLE;
  
  if ($wpdb->get_var("SHOW TABLES LIKE '$log_table'") != $log_table) {
    $sql = "CREATE TABLE " . $log_table . " (
              id mediumint(9) NOT NULL AUTO_INCREMENT,
              time datetime NOT NULL,
              severity tinytext NOT NULL,
              message text NOT NULL,
              UNIQUE KEY id (id)
            );";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    add_option("scrd_db_version", SCRD_DB_VERSION);
  }
}

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
      scrd_feed_url($feed_url, $last_digest_time);
    }
  }
  
  //setup database for logging
  scrd_install();
}

register_deactivation_hook(__FILE__, 'scrd_deactivation');
function scrd_deactivation() {
  wp_clear_scheduled_hook('scrd_do_digest');
}

?>
