<?php

/**
 * Image store - core
 *
 * @package Image Store
 * @author Hafid Trujillo
 * @copyright 20010-2012
 * @since 3.0.0
 */

class ImStore {

	/**
	 * Constant variables
	 *
	 * @param $domain plugin Gallery IDentifier
	 * Make sure that new language( .mo ) files have 'ims-' as base name
	 */
	public $version = '3.1.7';

	/**
	 * Public variables
	 */
	public $dformat = '';
	public $sync = false;
	public $perma = false;
	public $blog_id = false;
	public $color = array();
	public $opts = array();
	public $pages = array();
	public $promo_types = array();
	public $rules_property = array();
	public $optionkey = 'ims_front_options';

	/**
	 * Constructor
	 *
	 * @return void
	 * @since 0.5.0
	 */
	function ImStore() {
		global $wp_version;

		$this->define_constant();
		$this->wp_version = $wp_version;

		$this->content_url = rtrim(WP_CONTENT_URL, '/');
		$this->content_dir = rtrim(WP_CONTENT_DIR, '/');

		if (is_multisite() && isset($GLOBALS['blog_id'])) {
			$this->blog_id = (int) $GLOBALS['blog_id'];
			$this->sync = get_site_option('ims_sync_settings');
			$this->content_url = get_site_url(1) . "/wp-content";
		}

		if (empty($this->opts) && $this->sync == true)
			switch_to_blog(1);

		//set default setting for updates
		$this->opts = get_option($this->optionkey);
		$this->opts['ims_searchable'] = get_option('ims_searchable');

		if (is_multisite())
			restore_current_blog();

		add_filter('posts_orderby', array(&$this, 'posts_orderby'), 10, 3);
		add_filter('post_type_link', array(&$this, 'gallery_permalink'), 10, 3);

		add_action('init', array(&$this, 'int_actions'), 0);
		add_action('wp_loaded', array(&$this, 'flush_rules'));
		add_action('wp_logout', array(&$this, 'logout_ims_user'), 10);
		add_action('imstore_expire', array(&$this, 'expire_galleries'));
		add_action('set_current_user', array(&$this, 'set_user_caps'), 10);
		add_action('plugins_loaded', array(&$this, 'image_store_init'), 100);
		add_action('generate_rewrite_rules', array(&$this, 'add_rewrite_rules'), 10, 1);
	}

	/**
	 * inital plugin actions
	 *
	 * @return void
	 * @since 0.3.1
	 */
	function image_store_init() {
		$this->locale = get_locale();
		
		if ($this->locale == 'en_US' || is_textdomain_loaded('ims'))
			return;

		$filedir = $this->content_dir . '/languages/_ims/' . 'ims' . '-' . $this->locale . '.mo';
		if (!file_exists($filedir) && is_admin() && current_user_can('activate_plugins')) {
			$time = get_option('_ims_no_lan_file');
			if ($time + (86400 * 2) <= current_time('timestamp'))
				$this->download_language_file($filedir);
		}

		if (function_exists('load_plugin_textdomain'))
			load_plugin_textdomain('ims', false, apply_filters('ims_load_textdomain', '../languages/_ims/', 'ims', $this->locale));
		elseif (function_exists('load_textdomain'))
			load_textdomain('ims', apply_filters('ims_load_textdomain', $filedir, 'ims', $this->locale));
	}

	/**
	 * Download language file
	 *
	 * @return void
	 * @since 3.0.1
	 */
	function download_language_file($filedir) {

		$data = @file_get_contents("http://xparkmedia.com/xm/wp-content/languages/ims-" . $this->locale . ".zip");
		if (empty($data)) {
			add_option('_ims_no_lan_file', current_time('timestamp'));
			return;
		}

		if (!file_exists($path = dirname($filedir)))
			@mkdir($path, 0755, true);
		
		if(!is_writable($path))
			return;
			
		$temp = $path . '/temp.zip';
		@file_put_contents($temp, $data);

		include_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );
		$PclZip = new PclZip($temp);

		if (false == ( $archive = $PclZip->extract(PCLZIP_OPT_EXTRACT_AS_STRING)))
			return;
		
		foreach ($archive as $file)
			@file_put_contents($path . "/" . $file['filename'], $file['content']);

		@unlink($temp);
	}

	/**
	 * Initial actions
	 *
	 * @return void
	 * @since 0.5.0
	 */
	function int_actions() {

		$this->register_post_types();

		//load gallery widget
		if ($this->opts['imswidget'])
			include_once( apply_filters('ims_widget_path', IMSTORE_ABSPATH . '/_inc/widget.php') );

		//load navigation widget
		if ($this->opts['widgettools'] && empty($this->opts['disablestore']))
			include_once( apply_filters('ims_widget_path', IMSTORE_ABSPATH . '/_inc/widget-tools.php') );

		//speed up wordpress load
		if (defined('DOING_AJAX') || defined('DOING_AUTOSAVE' || defined('SHORTINIT')))
			return;

		global $wp_rewrite;
		$this->permalinks = $wp_rewrite->using_permalinks();

		if (empty($this->pages))
			$this->load_pages();

		$this->load_color_opts();

		$this->loc = $this->opts['clocal'];
		$this->sym = $this->opts['symbol'];

		$this->dformat = get_option('date_format');
		$this->perma = get_option('permalink_structure');

		$this->cformat = array('', "$this->sym%s", "$this->sym %s", "%s$this->sym", "%s $this->sym");
		$this->units = apply_filters('ims_units', array(
			'in' => __('in', 'ims'), 'cm' => __('cm', 'ims'), 'px' => __('px', 'ims')
		));

		$this->promo_types = apply_filters('ims_promo_types', array(
			'1' => __('Percent', 'ims'),
			'2' => __('Amount', 'ims'),
			'3' => __('Free Shipping', 'ims'),
		));

		$this->rules_property = apply_filters('ims_rules_property', array(
			'items' => __('Item quantity', 'ims'),
			'total' => __('Total amount', 'ims'),
			'subtotal' => __('Subtotal amount', 'ims'),
		));

		$this->rules_logic = apply_filters('ims_rules_logic', array(
			'equal' => __('Is equal to', 'ims'),
			'more' => __('Is greater than', 'ims'),
			'less' => __('Is less than', 'ims'),
		));
	}

	/**
	 * Flush rules
	 *
	 * @return void
	 * @since 3.0.0
	 */
	function flush_rules() {
		$rules = get_option('rewrite_rules');
		$galleries = preg_match('/[^\\p{Common}\\p{Latin}]/u', __('galleries', 'ims')) ? 'galleries' : __('galleries', 'ims');

		if (!isset($rules[$galleries . "/([^/]+)/feed/(imstore)/?$"])) {
			global $wp_rewrite;
			$wp_rewrite->flush_rules();
		}
	}

	/**
	 * Allow post to be sorted by excerpt
	 *
	 * @param string $orderby
	 * @param obj $query
	 * @return string
	 * @since 3.0.0
	 */
	function posts_orderby($orderby, $query) {
		if (empty($query->query_vars['orderby'])
		|| empty($query->query['orderby'])
		|| $query->query['orderby'] != 'excerpt')
			return $orderby;

		global $wpdb;
		return $wpdb->posts . ".post_excerpt";
	}

	/**
	 * Add support to archives permalink
	 *
	 * @param string $permalink
	 * @param obj $post
	 * @param string $leavename
	 * @return string
	 * @since 3.0.0
	 */
	function gallery_permalink($permalink, $post) {
		if ($post->post_type != 'ims_gallery')
			return $permalink;
		return trim(str_replace('%imspage%', '', $permalink), '/');
	}

	/**
	 * logout user
	 *
	 * @return void
	 * @since 0.5.0
	 */
	function logout_ims_user() {
		setcookie('ims_galid_' . COOKIEHASH, false, ( time() - 315360000), COOKIEPATH, COOKIE_DOMAIN);
		setcookie('wp-postpass_' . COOKIEHASH, false, ( time() - 315360000), COOKIEPATH, COOKIE_DOMAIN);
	}

	/**
	 * Set galleries to expired
	 * and delete unprocess orders
	 *
	 * @return void
	 * @since 0.5.0
	 */
	function expire_galleries() {
		global $wpdb;

		do_action('ims_before_cron');
		$time = date('Y-m-d', current_time('timestamp'));

		$wpdb->query(
			"UPDATE $wpdb->posts SET post_status = 'expire'  WHERE post_expire <= '$time'
			AND post_expire != '0000-00-00 00:00:00' AND post_type = 'ims_gallery'"
		);

		$wpdb->query("DELETE p,pm FROM $wpdb->posts p  LEFT JOIN $wpdb->postmeta pm ON(p.ID = pm.post_id)
			WHERE post_expire <='$time' AND post_type = 'ims_order' AND post_status = 'draft'"
		);

		do_action('ims_after_cron');
	}

	/**
	 * Rewrites for custom page managers
	 *
	 * @param array $wp_rewrite
	 * @return array
	 * @since 0.5.0
	 */
	function add_rewrite_rules($wp_rewrite) {

		if (empty($this->pages))
			$this->load_pages();

		$wp_rewrite->add_rewrite_tag("%gallery%", '([^/]+)', "ims_gallery=");
		$wp_rewrite->add_rewrite_tag('%imslogout%', '([^/]+)', 'imslogout=');
		$wp_rewrite->add_rewrite_tag('%imsmessage%', '([0-9]+)', 'imsmessage=');
		$wp_rewrite->add_permastruct('ims_gallery', __('galleries', 'ims') . '/%ims_gallery%/%imspage%/', false);
		$galleries = preg_match('/[^\\p{Common}\\p{Latin}]/u', __('galleries', 'ims')) ? 'galleries' : __('galleries', 'ims');

		$new_rules[$galleries . "/([^/]+)/feed/(imstore)/?$"] =
		"index.php?ims_gallery=" . $wp_rewrite->preg_index(1) . "&feed=" . $wp_rewrite->preg_index(2);

		$new_rules[$galleries . "/([^/]+)/logout/?$"] = "index.php?ims_gallery=" . $wp_rewrite->preg_index(1) . '&imslogout=1';

		foreach ($this->pages as $id => $page) {
			$slug = ( preg_match('/[^\\p{Common}\\p{Latin}]/u', $page)) ? $id : sanitize_title($page);

			if ($id == 'photos') {
				$new_rules[$galleries . "/([^/]+)/page/([0-9]+)/?$"] =
				"index.php?ims_gallery=" . $wp_rewrite->preg_index(1) . "&imspage=$id" .
				'&paged=' . $wp_rewrite->preg_index(2);

				$new_rules[$galleries . "/([^/]+)/$slug/page/([0-9]+)/?$"] =
				"index.php?ims_gallery=" . $wp_rewrite->preg_index(1) . "&imspage=$id" .
				'&paged=' . $wp_rewrite->preg_index(2);

				$new_rules[$galleries . "/([^/]+)/$slug/page/([0-9]+)/ms/?([0-9]+)/?$"] =
				"index.php?ims_gallery=" . $wp_rewrite->preg_index(1) . "&imspage=$id" .
				'&paged=' . $wp_rewrite->preg_index(2) . '&imsmessage=' . $wp_rewrite->preg_index(3);
			}

			$new_rules[$galleries . "/([^/]+)/$slug/ms/([0-9]+)/?$"] =
			"index.php?ims_gallery=" . $wp_rewrite->preg_index(1) . "&imspage=$id" .
			'&imsmessage=' . $wp_rewrite->preg_index(2);

			$new_rules[$galleries . "/([^/]+)/$slug/?$"] =
			"index.php?ims_gallery=" . $wp_rewrite->preg_index(1) . "&imspage=$id";
						
			if( $id == 'receipt' ){
				$new_rules["(.?.+?)/$slug/?$"] =
				"index.php?pagename=" . $wp_rewrite->preg_index(1) .  "&imspage=$id";
			}
		}

		$new_rules["(.?.+?)/ms/?([0-9]+)/?$"] =
		"index.php?pagename=" . $wp_rewrite->preg_index(1) . '&imsmessage=' . $wp_rewrite->preg_index(2);

		$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
		$wp_rewrite->rules["/page/?([0-9]+)/?$"] = "index.php?paged=" . $wp_rewrite->preg_index(1); //print_r($wp_rewrite );

		return $wp_rewrite;
	}

	/**
	 * Define contant variables
	 *
	 * @return void
	 * @since 0.5.0
	 */
	function define_constant() {

		do_action('ims_define_constants', IMSTORE_ABSPATH);

		define('IMSTORE_URL', WP_PLUGIN_URL . "/" . IMSTORE_FOLDER);
		define('IMSTORE_ADMIN_URL', IMSTORE_URL . '/admin');

		if (!defined('WP_SITE_URL'))
			define('WP_SITE_URL', get_bloginfo('url'));
		if (!defined('WP_CONTENT_URL'))
			define('WP_CONTENT_URL', get_bloginfo('wpurl') . '/wp-content');
		if (!defined('WP_TEMPLATE_DIR'))
			define('WP_TEMPLATE_DIR', get_template_directory());

		$this->key = apply_filters('ims_image_key', substr(preg_replace("([^a-zA-Z0-9])", '', NONCE_KEY), 0, 15));
	}

	/**
	 * Get all packages
	 *
	 * @return array
	 * @since 3.0.0
	 */
	function get_packages() {
		global $wpdb;
		return $wpdb->get_results("SELECT DISTINCT ID,post_title FROM $wpdb->posts WHERE post_type = 'ims_package'");
	}

	/**
	 * load pages to use for permalinks
	 * and to display the correct section
	 *
	 * @return void
	 * @since 0.5.0
	 */
	function load_pages() {
		$this->pages['photos'] = __('Photos', 'ims');
		$this->pages['slideshow'] = __('Slideshow', 'ims');
		$this->pages['favorites'] = __('Favorites', 'ims');

		if (empty($this->opts['disablestore'])) {
			$this->pages['price-list'] = __('Price List', 'ims');
			$this->pages['shopping-cart'] = __('Shopping Cart', 'ims');
			$this->pages['receipt'] = __('Receipt', 'ims');
			$this->pages['checkout'] = __('Checkout', 'ims');
		}
		$this->pages = apply_filters('ims_load_pages', $this->pages);
	}

	/**
	 * load color options
	 *
	 * @return void
	 * @since 0.5.0
	 */
	function load_color_opts() {
		$this->color = array(
			'ims_color' => __('Full Color', 'ims'),
			'ims_sepia' => __('Sepia ', 'ims'),
			'ims_bw' => __('B &amp; W', 'ims'),
		);

		if (isset($this->opts['disablebw']))
			unset($this->color['ims_bw']);

		if (isset($this->opts['disablesepia']))
			unset($this->color['ims_sepia']);

		$this->color = apply_filters('ims_color_opts', $this->color);
	}

	/**
	 * Add user capabilities to current user
	 *
	 * @return void
	 * @since 3.0.0
	 */
	function set_user_caps() {
		global $current_user;
		if (!isset($current_user->ID) || isset($current_user->caps['administrator']))
			return;

		if (!empty($current_user->ims_user_caps))
			$current_user->allcaps += $current_user->ims_user_caps;
	}

	/**
	 * Register custom post types
	 *
	 * @return void
	 * @since 3.0.0
	 */
	function register_post_types() {

		//register image type to be able to edit images
		register_post_type('ims_image', array(
			'public' => true,
			'show_ui' => false,
			'revisions' => false,
			'publicly_queryable' => true,
			'exclude_from_search' => true,
			'show_in_nav_menus' => false,
		));

		$texedit = $this->opts['showtexteditor'] ? 'editor' : false;
		$searchable = get_option('ims_searchable') ? false : true;

		//register gallery post type assign ims_album taxonomy
		$posttype = apply_filters('ims_gallery_post_type', array(
			'labels' => array(
				'name' => _x('Galleries', 'post type general name', 'ims'),
				'singular_name' => _x('Gallery', 'post type singular name', 'ims'),
				'add_new' => _x('Add New', 'Gallery', 'ims'),
				'add_new_item' => __('Add New Gallery', 'ims'),
				'edit_item' => __('Edit Gallery', 'ims'),
				'new_item' => __('New Gallery', 'ims'),
				'view_item' => __('View Gallery', 'ims'),
				'search_items' => __('Search galleries', 'ims'),
				'not_found' => __('No galleries found', 'ims'),
				'not_found_in_trash' => __('No galleries found in Trash', 'ims'),
			),
			'public' => true,
			'show_ui' => true,
			'menu_position' => 33,
			'publicly_queryable' => true,
			'hierarchical' => false,
			'revisions' => false,
			'capability_type' => 'page',
			'query_var' => 'ims_gallery',
			'show_in_nav_menus' => false,
			'exclude_from_search' => $searchable,
			'menu_icon' => IMSTORE_URL . '/_img/imstore.png',
			'supports' => array('title', 'comments', 'author', 'excerpt', 'page-attributes', $texedit),
			'rewrite' => array('slug' => __('galleries', 'ims'), 'with_front' => false),
			'taxonomies' => array('ims_album')
		));

		register_post_type('ims_gallery', $posttype);

		$statuses = array(
			'expire' => __('Expired', 'ims'),
			'closed' => __('Closed', 'ims'),
			'shipped' => __('Shipped', 'ims'),
			'cancelled' => __('Cancelled', 'ims'),
		);

		foreach ($statuses as $status => $label) {
			register_post_status($status, array(
				'protected' => true,
				'publicly_queryable' => false, 'label' => $label,
				'label_count' => _n_noop("{$label} <span class='count'>(%s)</span>", "{$label} <span class='count'>(%s)</span>")
			));
		}

		//register taxomomy albums
		register_taxonomy('ims_album', array('ims_gallery'), array(
			'labels' => array(
				'name' => _x('Albums', 'taxonomy general name', 'ims'),
				'singular_name' => _x('Album', 'taxonomy singular name', 'ims'),
				'search_items' => __('Search Albums', 'ims'),
				'all_items' => __('All Albums', 'ims'),
				'parent_item' => __('Parent Album', 'ims'),
				'parent_item_colon' => __('Parent Album:', 'ims'),
				'edit_item' => __('Edit Album', 'ims'),
				'update_item' => __('Update Album', 'ims'),
				'add_new_item' => __('Add New Album', 'ims'),
				'new_item_name' => __('New Album Name', 'ims'),
				'menu_name' => __('Album', 'ims'),
			),
			'show_ui' => true,
			'query_var' => true,
			'hierarchical' => true,
			'show_in_nav_menus' => true,
			'rewrite' => array('slug' => __('albums', 'ims')),
		));

		//register taxomomy tags
		register_taxonomy('ims_tags', array('ims_gallery'), array(
			'labels' => array(
				'name' => _x('Tags', 'taxonomy general name', 'ims'),
				'singular_name' => _x('Tag', 'taxonomy singular name', 'ims'),
				'search_items' => __('Search Tags', 'ims'),
				'all_items' => __('All Tags', 'ims'),
				'edit_item' => __('Edit Tag', 'ims'),
				'update_item' => __('Update Tag', 'ims'),
				'add_new_item' => __('Add New Tag', 'ims'),
				'new_item_name' => __('New Tag Name', 'ims'),
				'menu_name' => __('Tags', 'ims'),
			),
			'show_ui' => true,
			'query_var' => true,
			'hierarchical' => false,
			'rewrite' => array('slug' => __('ims_tag', 'ims')),
		));
	}

	/**
	 * Fast in_array function
	 *
	 * @parm string $elem
	 * @parm array $array
	 * @return bool
	 * @since 3.0.0
	 */
	function in_array($elem, $array) {
		foreach ($array as $val)
			if ($val == $elem)
				return true;
		return false;
	}

	/**
	 * Format price
	 *
	 * @parm unit $price
	 * @parm string $before
	 * @parm string $after
	 * @return string
	 * @since 3.0.0
	 */
	function format_price($price, $sym = true, $before = '', $after = '') {
		if (stripos($price, $this->sym) !== false)
			return $price;

		if ($this->opts['disable_decimal'])
			$price = number_format_i18n((double) $price);
		else
			$price = number_format((double) $price, 2);

		$char = ( $sym ) ? $this->cformat[$this->loc] : "%s";
		return sprintf($before . $char, $price . $after);
	}

	/**
	 * Error messages
	 *
	 * @param obj $errors
	 * @param bol $retrun
	 * @return string|null
	 * @since 3.0.0
	 */
	function error_message($errors, $return = false) {
		$error = '<div class="ims-message ims-error error">' . "\n";
		foreach ($errors->get_error_messages()as $err)
			$error .= "<p><strong>$err</strong></p>\n";
		$error .= '</div>' . "\n";

		if ($return)
			return $error;
		else
			echo $error;
	}
	
	/**
	 * Get memory limit
	 *
	 * @return string
	 * @since 3.1.0
	 */
	function get_memory_limit(){
		if(!defined('WP_MAX_MEMORY_LIMIT') )
			return '256M';
		elseif(WP_MAX_MEMORY_LIMIT == false || WP_MAX_MEMORY_LIMIT == '')
			return '256M';
		else return WP_MAX_MEMORY_LIMIT;
	}

	/**
	 * Encrypt url
	 *
	 * @parm string $string
	 * @return string
	 * @since 2.1.1
	 */
	function url_decrypt($string) {
		$str = '';
		$string = base64_decode(implode('/', explode('::', urldecode($string))));
		for ($i = 0; $i < strlen($string); $i++) {
			$char = substr($string, $i, 1);
			$keychar = substr($this->key, ($i % strlen($this->key)) - 1, 1);
			$char = chr(ord($char) - ord($keychar));
			$str.=$char;
		}
		return $str;
	}

}

/**
 * Escaping for textarea values.
 *
 * @param string $text
 * @return string
 * @since 3.0.0
 */
if (!function_exists('esc_textarea')) {
	function esc_textarea($text) {
		$safe_text = htmlspecialchars($text, ENT_QUOTES);
		return apply_filters('esc_textarea', $safe_text, $text);
	}
}