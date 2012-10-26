<?php

/**
 * ImStoreFront - Fontend display
 *
 * @package Image Store
 * @author Hafid Trujillo
 * @copyright 20010-2012
 * @since 0.5.0
 */
class ImStoreFront extends ImStore {
	
	/**
	 * Public variables
	 */
	public $galid = 0;
	public $gal = false;
	public $baseurl = '';
	public $error = false;
	public $orderid = false;
	public $pricelist_id = 0;
	public $success = false;
	public $imspage = false;
	public $message = false;
	public $is_secure = false;
	public $cart_status = false;
	public $order = '';
	public $sortby = '';
	public $sizes = array();
	public $listmeta = array();
	public $attachments = array();
	public $favorites_ids = '';
	public $favorites_count = 0;
	public $posts_per_page = 0;
	public $gateways = array();
	
	private $query_id = false;
	private $page_cart = false;
	private $page_front = false;
	private $page_galleries = false;
	private $subtitutions = array();
	
	private $gallery_tags = array();
	private $shipping_opts = array();
	private $cart = array('items' => false);
	private $download_links = false;
	
	/**
	 * Constructor
	 *
	 * @return void
	 * @since 3.0.0
	 */
	function ImStoreFront() {
		
		//speed up wordpress load
		if (defined('DOING_AJAX') || defined('DOING_AUTOSAVE' || defined('SHORTINIT')))
			return;
		
		ob_start();
		parent::ImStore();
		
		add_action('wp', array(&$this, 'add_hooks'), 0); //add imstore hooks
		
		add_filter('wp', array(&$this, 'secure_images'), 1, 50);//sercure images
		add_filter('parse_query', array(&$this, 'album_pagination'), 20, 2); //set album pagination
		add_filter('query_vars', array(&$this, 'add_var_for_rewrites'), 10, 1); //add rewrite vars
		
		//admin bar menu
		add_action('admin_bar_menu', array(&$this, 'admin_bar_menu'), 99);
		add_action('network_admin_menu', array(&$this, 'admin_bar_menu'), 99);
		
		add_shortcode('image-store', array(&$this, 'imstore_shortcode')); //main shortcode
		
		if (version_compare($this->wp_version, '3.2', '<'))
			add_filter('pre_get_posts', array(&$this, 'custom_types'), 30, 1);
		
		if ($this->opts['ims_searchable'])
			add_filter('posts_where', array(&$this, 'search_image_info'), 50, 2);
		
		if ($this->opts['colorbox'])
			add_action('wp_head', array(&$this, 'print_ie_styles'));
		
		//load image rss
		if (!empty($this->opts['mediarss']))
			require_once( IMSTORE_ABSPATH . '/_store/image-rss.php' );
	}
	
	/**
	 * Initiate hooks
	 *
	 * @return void
	 * @since 3.1.6
	 */
	function add_hooks() {

		$this->posts_per_page = get_query_var('posts_per_page');
		$this->baseurl = apply_filters('ims_base_image_url', IMSTORE_URL . '/image.php?i=');
		
		//return if is a feed page
		if (is_feed()) 
			return;
					
		$this->set_cart();
		$this->set_favorites();
		
		//set gallery tags
		$this->gallery_tags = apply_filters(
		'ims_gallery_tags', array(
			'gallerytag' => 'div',
			'imagetag' => 'figure',
			'captiontag' => 'figcaption'
		), $this);
		
		//load styles  - change title format
		add_action('wp_enqueue_scripts', array(&$this, 'load_scripts_styles'));
		add_filter('protected_title_format', array(&$this, 'remove_protected'));
		
		//shortcode dependencies
		if(is_singular() || is_front_page()){
			add_filter('ims_localize_js', array(&$this, 'add_gallerific_js_vars'), 0);
			add_action('template_redirect', array(&$this, 'post_actions'), 2); //post actions
		}
		
		$allow = apply_filters('ims_activate_gallery_hooks', false);
		
		if (is_singular('ims_image')) {
			add_filter('the_content', array(&$this, 'ims_image_content'), 10);
			add_filter('single_template', array(&$this, 'get_image_template'), 10, 1);
			add_filter('get_next_post_sort', array(&$this, 'adjacent_post_sort'), 20);
			add_filter('get_next_post_where', array(&$this, 'adjacent_post_where'), 20);
			add_filter('get_previous_post_sort', array(&$this, 'adjacent_post_sort'), 20);
			add_filter('get_previous_post_where', array(&$this, 'adjacent_post_where'), 20);
		}
		
		if (is_tax('ims_album') || is_tax('ims_tag')){
			add_filter('the_content', array(&$this, 'taxonomy_content'), 10);
			add_filter('template_include', array(&$this, 'taxonomy_template'), 1);
		}
		
		if (is_singular('ims_gallery') || $allow ) {
			
			add_action('template_redirect', array(&$this, 'ims_init'), 1); //popular object data
			add_action('template_redirect', array(&$this, 'redirect_actions'), 0); //redirect actions
			
			add_filter('wp_title', array(&$this, 'add_title'), 1, 10); //improve page title for seo
			add_filter('redirect_canonical', array(&$this, 'redirect_canonical'), 20, 2); //stop canonical redirect
			add_filter('single_template', array(&$this, 'change_gallery_template'), 1); //change single gallery template
			
			add_filter('comments_array', array(&$this, 'hide_comments'), 1, 1); //hide comments from store pages
			add_filter('comments_open', array(&$this, 'close_comments'), 1, 1); //remove comments from albums
			
			add_filter('ims_subnav', array(&$this, 'ad_favorite_options'), 1, 1); //allow favorites when store is disabled
			add_filter('ims_after_pricelist_page', array(&$this, 'after_pricelist'), 10, 2); //Display list notes
			
			add_filter('get_pagenum_link', array(&$this, 'page_link'));
			add_shortcode('ims-gallery-content', array(&$this, 'gallery_shortcode')); //process shortcode
		}
		
		require_once( IMSTORE_ABSPATH . '/_store/shortcode.php' );
	}
	
	/**
	 * request post actions
	 *
	 * @return void
	 * @since 3.1.7
	 */
	function post_actions(){
		
		//process wepay IPN
		if(isset($_REQUEST['checkout_id'])){
			
			include_once( IMSTORE_ABSPATH . '/_store/wepaysdk.php' );
			$checkout = $wepay->request('checkout', array('checkout_id' => $_REQUEST['checkout_id'] ));
			
			if( empty($checkout) || empty($checkout->reference_id))
				return ;
				
			$data['last_name'] = false;
			$data['ims_phone'] = false;
			$data['instructions'] = false;
			$data['first_name'] = $checkout->payer_name;
			$data['method'] = 'WePay Checkout';
			$data['num_cart_items'] = $this->cart['items'];
			$data['mc_gross'] = $checkout->amount;
			$data['payment_gross'] = $checkout->gross;
			$data['txn_id'] = $checkout->account_id;
			$data['mc_currency'] = $checkout->currency;
			$data['payment_status'] = $checkout ->state;
			$data['payer_email'] = $checkout->payer_email;
			
			if( isset($checkout->shipping_address) ) {
				$data['address_city'] = $checkout->shipping_address->city;
				$data['address_zip'] = $checkout->shipping_address->zip;
				$data['address_state'] = $checkout->shipping_address->state;
				$data['address_street'] = $checkout->shipping_address->address1;
				$data['address_street'] .= "" .$checkout->shipping_address->address2;
				$data['address_country'] = $checkout->shipping_address->country;
			}
			
			$this->orderid = (int) $checkout->reference_id;
			$this->checkout( $this->orderid, $data);
			return;
		}
		
		//checkout email notification only get user info
		if (isset($_REQUEST['enotification'])
		&& isset($this->cart['items'])) {
			$this->success = false;
			$this->message = false;
			$this->imspage = 'checkout';
			return;
		}
		
		//submit notification order
		if (isset($_REQUEST['enoticecheckout'])
		&& isset($this->cart['items'])) {
			$this->success = false;
			$this->message = false;
			$this->imspage = 'checkout';
			$this->validate_user_input();
			return;
		}
		
		if(empty($_POST)) 
			return;
			
		//add images to cart
		if (isset($_POST['add-to-cart']))
			$this->add_to_cart();
		
		//upate cart
		elseif (isset($_REQUEST['apply-changes']))
			$this->update_cart();
		
		//process google notification
		if (isset($_POST['google-order-number']) && isset($_POST['shopping-cart_merchant-private-data']))
			include_once( IMSTORE_ABSPATH . '/_store/google-notice.php');
		
		//process paypal IPN
		if (isset($_POST['txn_id']) && isset($_POST['custom']) && is_numeric($_POST['custom']))
			include_once( IMSTORE_ABSPATH . '/_store/paypal-ipn.php' );
		
	}
	
	/**
	 * redirect actions
	 *
	 * @return void
	 * @since 3.1.7
	 */
	function redirect_actions(){
		
		//redirect photo disable
		if (!empty($this->opts['hidephoto']) && $this->imspage == 'photos')
			$this->imspage = 'slideshow';
		
		//cancel checkout
		if (isset($_POST['cancelcheckout'])) {
			wp_redirect($this->get_permalink('shopping-cart', false));
			die();
		}
		
		//logout gallery
		if (get_query_var('imslogout')) {
			$this->logout_ims_user();
			wp_redirect(get_permalink(get_option('ims_page_secure')));
			die();
		}
	}
	
	/**
	 * Add rewrite vars
	 *
	 * @param array $vars
	 * @return array
	 * @since 0.5.0
	 */
	function add_var_for_rewrites($vars) {
		array_push($vars, 'imspage', 'imsmessage', 'imslogout');
		return $vars;
	}
	
	/**
	 * Display albums(taxonomy)
	 *
	 * @param obj $query
	 * @return void
	 * @since 3.0.0
	 */
	function custom_types(&$query) {
		if ((!is_archive() && empty($query->query_vars['post_type']) ) ||
		( isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == 'nav_menu_item' ))
			return $query;
		$query->set('post_type', get_post_types(array('publicly_queryable' => true)));
	}
	
	/**
	 * Populate object variables
	 *
	 * @return void
	 * @since 2.0.0
	 */
	function ims_init() {
		global $post;
		
		$this->gal = $post; //set gallery
		$this->galid = (int)$this->gal->ID; // set gallery id
		$this->meta = get_post_custom($this->galid);
		
		if (get_query_var('imsmessage')) {
			$messages = array(
				'1' => __('Successfully added to cart', 'ims'),
				'2' => __('Cart successfully updated', 'ims'),
				'3' => __('Your transaction has been cancel!!', 'ims')
			);
			$this->message = $messages[get_query_var('imsmessage')];
		}
		
		//apply sort by setting to galleries
		if (empty($this->meta['_ims_sortby'][0]))
			$this->sortby = $this->opts['imgsortorder'];
		elseif($this->meta['_ims_sortby'][0] == 'menu_order')
			$this->sortby = $this->meta['_ims_sortby'][0];
		else $this->sortby = "post_" . $this->meta['_ims_sortby'][0];
		
		//set price list data
		if (empty($this->opts['disablestore'])) {
			$this->get_price_list(); //get list sizes / data
			$this->shipping_opts = get_option('ims_shipping_options');
			$this->listmeta = get_post_meta($this->pricelist_id, '_ims_list_opts', true); //get list metadata
		}
		
		//set gallery page
		if (is_singular('ims_gallery') && !$this->imspage )
			$this->imspage = ($page = get_query_var('imspage')) ? $page : 'photos';
		
		//get list of gateways
		$this->gateways = get_option('ims_gateways');
		$this->gateways['enotification']['url'] = get_permalink();
		$this->gateways['googleprod']['url'] .= $this->opts['googleid'];
		$this->gateways['googlesand']['url'] .= $this->opts['googleid'];
		
		do_action('ims_gallery_init', $this);
	}
	
	/**
	 * Populate cart variables
	 *
	 * @return void
	 * @since 3.1.7
	 */
	function set_cart(){
		if (isset($_COOKIE['ims_orderid_' . COOKIEHASH]))
			$this->orderid = $_COOKIE['ims_orderid_' . COOKIEHASH];

		$this->cart_status = get_post_status($this->orderid);
		
		if ($this->cart_status == "draft" && $this->orderid)
			$this->cart = get_post_meta($this->orderid, '_ims_order_data', true);
	}
	
	/**
	 * Populate favorites variables
	 *
	 * @return void
	 * @since 3.1.7
	 */
	function set_favorites(){
		global $user_ID;
		
		if (is_user_logged_in())
			$this->favorites_ids = trim( get_user_meta($user_ID, '_ims_favorites', true), ', ');
		elseif ( isset($_COOKIE['ims_favorites_' . COOKIEHASH]) )
			$this->favorites_ids = trim($_COOKIE['ims_favorites_' . COOKIEHASH], ', ');

		if ($this->favorites_ids)
			$this->favorites_count = count(explode(',', $this->favorites_ids));
	}
	
	/**
	 * Print IE styles
	 * needed for colorbox
	 *
	 * @return void
	 * @since 0.5.2
	 */
	function print_ie_styles() {
		if (empty($_SERVER['HTTP_USER_AGENT'])
		|| strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') === false
		|| defined('JQUERYCOLORBOX_NAME'))
			return;
		echo '<!--[if IE]><link rel="stylesheet" id="colorboxie-css" href="' . IMSTORE_URL . '/_css/colorbox.ie.php?url=' .
		IMSTORE_URL . '&amp;ver=' . $this->version . '" type="text/css" media="all" /><![endif]-->';
	}
	
	/**
	 * Load frontend js/css
	 *
	 * @return void
	 * @since 0.5.0
	 */
	function load_scripts_styles() {
		if ($this->opts['stylesheet'])
			wp_enqueue_style('imstore', IMSTORE_URL . '/_css/imstore.css', NULL, $this->version);

		if ($this->opts['colorbox'] || $this->opts['wplightbox']) {
			if (!defined('JQUERYCOLORBOX_NAME'))
				wp_enqueue_style('colorbox', IMSTORE_URL . '/_css/colorbox.css', NULL, '1.3.19');
			wp_enqueue_script('colorbox', IMSTORE_URL . '/_js/colorbox.js', array('jquery'), '1.3.19', true);
		}

		wp_enqueue_script('galleriffic', IMSTORE_URL . '/_js/galleriffic.js', array('jquery'), '1.3.6 ', true);
		wp_enqueue_script('jquery-sonar', IMSTORE_URL . '/_js/sonar.js', array('jquery'), $this->version, true);
		wp_enqueue_script('imstore', IMSTORE_URL . '/_js/imstore.js', array('jquery', 'galleriffic'), $this->version, true);

		$localize = apply_filters('ims_localize_js', array(
			'galleriffic' => false,
			'galid' => $this->galid,
			'imstoreurl' => IMSTORE_ADMIN_URL,
			'attchlink' => $this->opts['attchlink'],
			'colorbox' => $this->opts['colorbox'],
			'wplightbox' => $this->opts['wplightbox'],
			'favorites' => $this->pages['favorites'],
			'addtocart' => __('Add to cart', 'ims'),
			'ajaxnonce' => wp_create_nonce("ims_ajax_favorites"),
		)); wp_localize_script('imstore', 'imstore', $localize);
	}
	
	/**
	 * Remove "protected"
	 * from gallery title
	 *
	 * @param $title string
	 * @return string
	 * @since 2.0.4
	 */
	function remove_protected($title) {
		global $post;
		if ($post->post_type == 'ims_gallery')
			return $post->post_title;
		return $title;
	}
	
	/**
	 * Add paging option to albums
	 *
	 * @param $query object
	 * @return object
	 * @since 3.0.0
	 */
	function album_pagination($query) {
		if (!is_tax('ims_album') || empty($this->opts['album_per_page']) 
		|| empty($query->query_vars['ims_album']))
			return $query;
		
		$query->set('posts_per_page', $this->opts['album_per_page']);
		return $query;
	}
	
	/**
	 * Add visted count
	 *
	 * @since 3.1.0
	 * return void
	 */
	function visited_gallery() {
		if (isset($_COOKIE['ims_gal_' . $this->galid . '_' . COOKIEHASH]))
			return;
		setcookie('ims_gal_' . $this->galid . '_' . COOKIEHASH, true, 0, COOKIEPATH, COOKIE_DOMAIN);
		update_post_meta($this->galid, '_ims_visits', get_post_meta($this->galid, '_ims_visits', true) + 1);
	}
	
	/**
	 * Better SEO, append imstore page to title
	 *
	 * @return string
	 * @since 3.1.0
	 */
	function add_title($title, $sep, $seplocation) {
		global $paged;
		if ($seplocation == 'right') {
			if ($paged)
				$sep .= " " . $paged . " $sep";
			return $this->pages[$this->imspage] . " $sep " . get_the_title() . " $sep ";
		}else {
			if ($paged)
				$sep .= "$sep " . $paged . " ";
			return " $sep " . get_the_title() . " $sep " . $this->pages[$this->imspage];
		}
	}
	
	/**
	 * Fix next/previous links on single galleries
	 * when message is displayed
	 *
	 * @param $order string
	 * @return string
	 * @since 3.0.2
	 */
	function page_link($link) {
		$link = preg_replace('/\/ms\/([0-9]+)/', '', $link);

		global $paged;
		$link = preg_replace("/\/page\/$paged/", '', $link);

		$num = basename($link);
		if ($paged == $num) {
			if (($paged - 1) > 1)
				$link = dirname($link) . "/" . ( $paged - 1 );
			else
				$link = dirname($link);
		}
		return $link;
	}
	
	/**
	 * Stop canonical redirect for
	 * Custom permalink structure
	 *
	 * @param string $redirect_url
	 * @param string $requested_url
	 * @return void
	 * @since 0.5.0
	 */
	function redirect_canonical($redirect_url, $requested_url) {
		if (strpos($requested_url, "/page/"))
			return false;
		return $redirect_url;
	}
	
	/* Redirect taxonomy template
	 * to display album galleries
	 *
	 * @param string $template
	 * @return string
	 * @since 2.0.0
	 */
	function taxonomy_template($template) {
		if (file_exists(WP_TEMPLATE_DIR . "/page.php")
		&& $this->opts['album_template'] == 'page.php')
			return WP_TEMPLATE_DIR . "/page.php";

		if (file_exists(WP_TEMPLATE_DIR . '/' . $this->opts['album_template']))
			return WP_TEMPLATE_DIR . '/' . $this->opts['album_template'];
		
		if (file_exists(IMSTORE_ABSPATH . "/theme/taxonomy-ims_album.php")
		&& !preg_match('/taxonomy/', $template))
			return IMSTORE_ABSPATH . "/theme/taxonomy-ims_album.php";

		return $template;
	}
	
	/* Redirect single image templage
	 *
	 * @return string
	 * @since 3.0.0
	 */
	function get_image_template($template) {
		if (!is_singular('ims_image'))
			return $template;
		return locate_template(array(
			'single-ims-image.php', 
			'ims-image.php', 
			'ims_image.php', 
			'image.php', 
			'single.php',
			'index.php'
		));
	}
	
	/**
	 * Change single gallery template
	 *
	 * @return string
	 * @since 2.0.4
	 */
	function change_gallery_template() {
		global $wp_query;
		$type = $wp_query->get_queried_object()->post_type;
		return locate_template(array(
			$this->opts['gallery_template'],
			"single-{$type}-{$this->imspage}.php",
			"single-{$type}.php",
			"single.php",
			"index.php"
		));
	}
	
	/**
	 * Display list notes
	 *
	 * @param string $output
	 * @param int $list_id
	 * @return string
	 * @since 3.0.9
	 */
	function after_pricelist($output, $list_id) {
		$post = get_post($list_id);
		if (isset($post->post_excerpt))
			$output .= '<div class="ims-list-notes">' . $post->post_excerpt . '</div>';
		return $output;
	}
	
	/**
	 * hide comments from store pages
	 * except photos and slideshow
	 *
	 * @param array $comments
	 * @return array
	 * @since 3.0.0
	 */
	function hide_comments($comments) {
		if ($this->in_array($this->imspage, array('photos', 'slideshow')))
			return $comments;
		return array();
	}
	
	/**
	 * remove comments from albums
	 *
	 * @param bool $bool
	 * @return array
	 * @since 3.0.0
	 */
	function close_comments($bool) {
		if (!$this->in_array($this->imspage, array('photos', 'slideshow')))
			return false;
		return $bool;
	}
	
	/**
	 * Filter sub-navigation options to allow
	 * favorites when store is disabled
	 *
	 * @param array $pages
	 * @return array
	 * @since 3.0.0
	 */
	function ad_favorite_options($pages) {
		if (!empty($this->opts['disablestore'])) {
			unset($pages['ims-select-all']);
			unset($pages['ims-unselect-all']);
			unset($pages['add-images-to-cart']);
		}

		if (!empty($this->opts['hidefavorites'])) {
			unset($pages['add-to-favorite']);
			unset($pages['remove-from-favorite']);
		}
		return $pages;
	}
	
	/**
	 * Load gallerific variables
	 * only if they are required
	 *
	 * @param array $vars
	 * @return void
	 * @since 3.0.0
	 */
	function add_gallerific_js_vars($vars) {
		$vars = array_merge($vars, array(
			'galleriffic' => true,
			'numThumbs' => $this->opts['numThumbs'],
			'autoStart' => $this->opts['autoStart'],
			'playLinkText' => $this->opts['playLinkText'],
			'pauseLinkTex' => $this->opts['pauseLinkTex'],
			'prevLinkText' => $this->opts['prevLinkText'],
			'nextLinkText' => $this->opts['nextLinkText'],
			'closeLinkText' => $this->opts['closeLinkText'],
			'maxPagesToShow' => $this->opts['maxPagesToShow'],
			'slideshowSpeed' => $this->opts['slideshowSpeed'],
			'transitionTime' => $this->opts['transitionTime'],
			'nextPageLinkText' => $this->opts['nextPageLinkText'],
			'prevPageLinkText' => $this->opts['prevPageLinkText'],
		));
		return $vars;
	}
	
	/**
	 * Display Order form
	 *
	 * @return void
	 * @since 3.1.7
	 */
	function display_order_form() {
		if ($this->opts['disablestore'])
			return;
		include_once( apply_filters('ims_order_form_path', IMSTORE_ABSPATH . '/_store/order-form.php') );
		return $form;
	}
	
	/**
	 * Fix pagination order to attachment (im_image) page
	 *
	 * @param $order string
	 * @return string
	 * @since 3.0.1
	 */
	function adjacent_post_sort($order) {
		if (!is_singular('ims_image'))
			return $order;
		$dir = ( $this->direct == '<' ) ? 'DESC' : 'ASC';
		return " ORDER BY p.{$this->sortby} $dir, p.ID $dir";
	}
	
	/**
	 * Return 404 for secure images
	 * if the user is not loged in
	 *
	 * @return void
	 * @since 3.0.5
	 */
	function secure_images() {

		if (!is_singular('ims_image'))
			return;

		global $post, $wp_version;

		if (get_query_var('imspage')
				&& get_post_meta($post->ID, '_dis_store', true)) {
			global $wp_query;
			$wp_query->set_404();
			status_header(404);
		}

		$this->gal = get_post($post->post_parent);
		if (isset($_COOKIE['wp-postpass_' . COOKIEHASH]))
			$denied = $this->gal->post_password !== $_COOKIE['wp-postpass_' . COOKIEHASH];

		if (version_compare($wp_version, '3.4', '>=')) {
			global $wp_hasher;
			if (empty($wp_hasher)) {
				require_once( ABSPATH . 'wp-includes/class-phpass.php');
				$wp_hasher = new PasswordHash(8, true);
			}
			if (empty($_COOKIE['wp-postpass_' . COOKIEHASH]))
				$denied = true;
			else
				$denied = !$wp_hasher->CheckPassword($this->gal->post_password, $_COOKIE['wp-postpass_' . COOKIEHASH]);
		}

		if (!empty($this->gal->post_password) && ( empty($_COOKIE['wp-postpass_' . COOKIEHASH]) || $denied )) {
			global $wp_query;
			$wp_query->set_404();
			status_header(404);
		}
	}
	
	/**
	 * Fix pagination to attachment (im_image) page
	 *
	 * @param $where string
	 * @return string
	 * @since 3.0.1
	 */
	function adjacent_post_where($where) {
		if (!is_singular('ims_image'))
			return $where;

		global $post, $wpdb;
		$order = get_post_meta($post->post_parent, '_ims_order', true);
		$sortby = get_post_meta($post->post_parent, '_ims_sortby', true);

		$this->order = empty($order) ? $this->opts['imgsortdirect'] : $order;
		$this->sortby = empty($sortby) ? $this->opts['imgsortorder'] : $sortby;

		$this->direct = ( preg_match('/\>/', $where) ) ? '>' : '<';
		$where = preg_replace(array('/\>/', '/\</'), array('>=', '<='), $where);

		switch ($this->sortby) {
			case 'menu_order':
				if ($post->menu_order)
					$where = $wpdb->prepare("WHERE p.post_type = 'ims_image' AND p.post_status = 'publish' AND p.menu_order $this->direct %d ", $post->menu_order);
				else
					$where = $where . " AND p.ID $this->direct $post->ID";
				break;
			case 'title':
				$this->sortby = "post_title";
				$where = $wpdb->prepare("WHERE p.post_type = 'ims_image' AND p.post_status = 'publish' AND p.post_title $this->direct %s", $post->post_title);
				break;
			case 'date':
				$this->sortby = "post_date";
				$where = $where . " AND p.ID $this->direct $post->ID";
				break;
			case 'excerpt':
				$this->sortby = "post_excerpt";
				$where = $wpdb->prepare("WHERE p.post_type = 'ims_image' AND p.post_status = 'publish' AND p.post_excerpt $this->direct %s", substr($post->post_excerpt, 0, 10));
				break;
			default:
		}
		return $where . " AND p.post_parent = $post->post_parent";
	}
	
	/**
	 * Get gallery price list
	 *
	 * @return array
	 * @since 0.5.0
	 */
	function get_price_list() {
		
		$sizes = array();
		$listdata = wp_cache_get( 'ims_pricelist_' . $this->galid );
		
		if (false == $listdata) {
			global $wpdb;
			$listdata = $wpdb->get_results($wpdb->prepare("
				SELECT meta_value meta, post_id FROM $wpdb->postmeta
				WHERE post_id = ( SELECT meta_value FROM $wpdb->postmeta
				WHERE post_id = %s AND meta_key = '_ims_price_list ' LIMIT 1 )
				AND meta_key = '_ims_sizes' ", $this->galid
			));
			wp_cache_set( 'ims_pricelist_' . $this->galid, $listdata );
		}
		
		//set pricelist id
		if (isset($listdata[0]->post_id))
			$this->pricelist_id = $listdata[0]->post_id;
		else $this->pricelist_id = get_option('ims_pricelist');
		
		if (empty($listdata[0]->meta))
			return array();
		
		$data = maybe_unserialize($listdata[0]->meta);
		unset($data['random']);
		
		//remove unsave charecters
		foreach ($data as $size){
			$key = str_replace(array('|','\\','.',' '),'',$size['name']);
			$sizes[$key] = $size;
		}
		
		$this->sizes = $sizes;
	}
	
	/**
	 * Get gallery images
	 *
	 * @return array
	 * @since 0.5.0
	 */
	function get_gallery_images() {
		global $wpdb, $wp_query, $paged;
		
		$limit =''; $offset = 0;
		
		if( $this->imspage == 'slideshow' )
			$this->posts_per_page = -1;
		elseif( $this->opts['imgs_per_page'] )
			$this->posts_per_page = $this->opts['imgs_per_page'];
		
		if( $this->posts_per_page > 0 ){
			if($paged) $offset = ($this->posts_per_page * $paged) - $this->posts_per_page;
			$limit = "LIMIT $offset, $this->posts_per_page";
		}
		
		do_action('ims_get_gallery_images', $this, $this->posts_per_page, $offset);
		
		if(empty($this->attachments)){
			$this->attachments =  $wpdb->get_results($wpdb->prepare(
				"SELECT SQL_CALC_FOUND_ROWS 
				ID, p.post_title, p.guid, p.post_author, p.post_expire,
				pm.meta_value meta, p.post_excerpt, p.post_date
				FROM $wpdb->posts AS p
				LEFT JOIN $wpdb->postmeta AS pm ON p.ID = pm.post_id
				WHERE p.post_type = 'ims_image'
				AND pm.meta_key = '_wp_attachment_metadata'
				AND p.post_status = 'publish' AND p.post_parent = %d
				ORDER BY $this->sortby $this->order $limit", $this->galid
			));
		}
		
		if(empty($this->attachments))
			return false;
		
		if ($this->imspage == 'photos' && is_singular("ims_gallery")) {
			$wp_query->post_count = count($this->attachments);
			$wp_query->found_posts = $wpdb->get_var('SELECT FOUND_ROWS( )');
			$wp_query->max_num_pages = ceil($wp_query->found_posts / $this->posts_per_page);
		}
		
		foreach ($this->attachments as $post) {
			$post->meta = maybe_unserialize($post->meta);
			$images[] = $post;
		} $this->attachments = $images;
	}
	
	/**
	 * Search image title and caption
	 *
	 * @param $where string
	 * @param $query object
	 * @return string
	 * @since 2.0.7
	 */
	function search_image_info($where, $query) {
		$q = $query->query_vars;
		if (!is_search() || empty($q['s']))
			return $where;

		global $wpdb;
		$searchand = '';
		$n = empty($q['exact']) ? '%' : '';

		foreach ((array) $q['search_terms'] as $term) {
			$term = esc_sql(like_escape($term));
			$search = "{$searchand}(($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')
				OR ($wpdb->posts.post_content LIKE '{$n}{$term}{$n}')
				OR ($wpdb->posts.post_excerpt LIKE '{$n}{$term}{$n}')
				OR ($wpdb->posts.post_excerpt LIKE '{$n}{$term}{$n}'))";
			$searchand = ' AND ';
		}

		$term = esc_sql(like_escape($q['s']));
		
		if (empty($q['sentence']) && count($q['search_terms']) > 1 && $q['search_terms'][0] != $q['s'])
			$search .= " OR ($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')
			OR ($wpdb->posts.post_content LIKE '{$n}{$term}{$n}')";

		return " $where OR ( ID IN ( SELECT DISTINCT post_parent FROM $wpdb->posts
		WHERE 1=1 AND $search AND $wpdb->posts.post_status = 'publish'))";
	}

	/**
	 * Get favorites
	 *
	 * @return array
	 * @since 0.5.0
	 */
	function get_favorite_images() {
		if (empty($this->favorites_ids))
			return false;

		global $wpdb;

		$ids = $wpdb->escape($this->favorites_ids);
		$this->attachments = $wpdb->get_results(
			"SELECT DISTINCT ID, guid, meta_value meta, post_excerpt, post_author
			FROM $wpdb->posts AS p LEFT JOIN $wpdb->postmeta AS pm
			ON p.ID = pm.post_id WHERE post_type = 'ims_image'
			AND meta_key = '_wp_attachment_metadata' AND p.ID IN ( $ids ) GROUP BY ID
			ORDER BY " . $this->opts['imgsortorder'] . " " . $this->opts['imgsortdirect']
		);

		if (empty($this->attachments))
			return;

		foreach ($this->attachments as $post) {
			$post->meta = maybe_unserialize($post->meta);
			$images[] = $post;
		} $this->attachments = $images;
	}
	
	/**
	 * Get gallery images
	 *
	 * @param $atts array
	 * @return array
	 * @since 2.0.0
	 */
	function get_galleries($atts) {

		global $wpdb, $paged;
		extract( wp_parse_args( $atts, array(
			'order'=>'DESC','orderby'=>'post_date','offset' =>0,
			'album'=>false,'count'=>$this->posts_per_page, 'all' => false)
		));
	
		$limit = ( $count < 1 ) ? '' : "LIMIT %d, %d";
		$secure = ($all) ? '' : "AND post_password = ''";
		$offset = (empty($paged)) ? 0 : (($count) * $paged) - $count;

		if( $album ){
			$type = "SELECT DISTINCT object_id FROM $wpdb->terms AS t
			INNER JOIN $wpdb->term_taxonomy tt ON t.term_id = tt.term_id
			INNER JOIN $wpdb->term_relationships tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
			WHERE t.term_id = %d ";
		}else{
			$type = " SELECT DISTINCT ID FROM $wpdb->posts WHERE 0 = %d AND
			post_type = 'ims_gallery' AND post_status = 'publish' $secure";
		}

		$this->attachments = $wpdb->get_results($wpdb->prepare(
			"SELECT SQL_CALC_FOUND_ROWS im.ID, im.post_title, p.comment_status,
			pm.meta_value meta, im.post_excerpt, im.post_parent, im.post_type, p.post_author
			FROM ( SELECT * FROM $wpdb->posts  ORDER BY
			 " . $this->opts['imgsortorder'] . " " . $this->opts['imgsortdirect'] . " )  AS im

			LEFT JOIN $wpdb->postmeta AS pm ON pm.post_id = im.ID
			LEFT JOIN $wpdb->posts AS p ON p.ID =  im.post_parent

			WHERE im.post_type = 'ims_image' AND pm.meta_key = '_wp_attachment_metadata'
			AND im.post_status = 'publish' AND p.post_status = 'publish' AND im.post_parent IN ( $type )
			GROUP BY im.post_parent ORDER BY p.{$orderby} $order, p.post_date DESC $limit
		", $album, $offset, $count));

		if (empty($this->attachments))
			return;
		
		if (is_singular("ims_gallery") || is_tax("ims_album")) {
			$wp_query->post_count = count($this->attachments);
			$wp_query->found_posts = $wpdb->get_var('SELECT FOUND_ROWS( )');
			$wp_query->max_num_pages = ceil($wp_query->found_posts / $count);
		}

		foreach ($this->attachments as $post) {
			$post->meta = maybe_unserialize($post->meta);
			$images[] = $post;
		} $this->attachments = $images;
	}
	
	/**
	 * Get imstore permalink
	 *
	 * @param string $page
	 * @since 0.5.0
	 * return void
	 */
	function get_permalink($page = '', $encode = true, $paged = false) {
		
		$link = '';
		if ($this->permalinks && !is_preview()) {
			
			if( isset($this->pages[$page]) && 
			preg_match('/[^\\p{Common}\\p{Latin}]/u', $this->pages[$page]) ) 
				$link =  '/' . $page;
			elseif( isset( $this->pages[$page] ) ) 
				$link =  '/' . sanitize_title($this->pages[$page]);
			
			if ($page == 'logout')
				$link .= "/".$page;

			if ($paged)
				$link .= '/page/' . $paged;

			if ($this->success != false)
				$link .= '/ms/' . $this->success;
		
		}else{
			
			if (is_front_page())
				$link .= '?page_id=' . $this->page_front;

			if ($page == 'logout')
				$link .= '&imslogout=1';
			elseif ($page)
				$link .= '&imspage=' . $page;

			if ($this->success != false)
				$link .= '&imsmessage=' . $this->success;

			if (is_preview())
				$link .= '&preview=true';
		}
		
		if ($encode) 
			return apply_filters('ims_permalink', trim( get_permalink(),'/' ) . htmlspecialchars($link) , $page, $encode);
		else return apply_filters('ims_permalink', trim( get_permalink(),'/' ) . $link , $page, $encode);
	}
	
	/**
	 * Adding Admin bar
	 *
	 * @since  3.1
	 * @return void
	 */
	function admin_bar_menu() {

		if (!current_user_can('ims_manage_galleries'))
			return;

		global $wp_admin_bar;

		$wp_admin_bar->add_menu(array(
			'id' => 'ims-menu', 'title' => __('Galleries', 'ims'),
			'href' => admin_url('edit.php?post_type=ims_gallery')
		));
		$wp_admin_bar->add_menu(array(
			'parent' => 'ims-menu', 'id' => 'ims-menu-add', 'title' => __('Add New', 'ims'),
			'href' => admin_url('post-new.php?post_type=ims_gallery')
		));
		$wp_admin_bar->add_menu(array(
			'parent' => 'ims-menu', 'id' => 'ims-menu-albums', 'title' => __('Albums', 'ims'),
			'href' => admin_url('edit-tags.php?taxonomy=ims_album&post_type=ims_gallery')
		));
		$wp_admin_bar->add_menu(array(
			'parent' => 'ims-menu', 'id' => 'ims-menu-tags', 'title' => __('Tags', 'ims'),
			'href' => admin_url('edit-tags.php?taxonomy=ims_tags&post_type=ims_gallery')
		));
		$wp_admin_bar->add_menu(array(
			'parent' => 'ims-menu', 'id' => 'ims-menu-sales', 'title' => __('Sales', 'ims'),
			'href' => admin_url('edit.php?post_type=ims_gallery&page=ims-sales')
		));
		$wp_admin_bar->add_menu(array(
			'parent' => 'ims-menu', 'id' => 'ims-menu-pricing', 'title' => __('Pricing', 'ims'),
			'href' => admin_url('edit.php?post_type=ims_gallery&page=ims-pricing')
		));
		$wp_admin_bar->add_menu(array(
			'parent' => 'ims-menu', 'id' => 'ims-menu-customers', 'title' => __('Customers', 'ims'),
			'href' => admin_url('edit.php?post_type=ims_gallery&page=ims-customers')
		));
	}
	
	/**
	 * Display store navigation
	 *
	 * @param bool $return
	 * @return string
	 * @since 0.5.0
	 */
	function store_nav($return = true) {
		
		if (!empty($this->meta['_dis_store'][0])
		|| ( $this->opts['widgettools'] && $return ))
			return;
		
		$nav = "\n" . '<div class="imstore-nav"><ul  class="imstore-nav-inner" role="navigation" >' . "\n";
		
		foreach ($this->pages as $key => $page) {
			if ($key == 'receipt'
			|| $key == 'checkout'
			|| ( $key == 'photos' && $this->opts['hidephoto'] )
			|| ( $key == 'favorites' && $this->opts['hidefavorites'] )
			|| ( $key == 'slideshow' && $this->opts['hideslideshow'] )
			) continue;
			
			$count = '';
			if ($key == 'shopping-cart' && !empty($this->cart['items']))
				$count = "<span>(" . $this->cart['items'] . ")</span>";
			elseif ($key == 'favorites' && $this->favorites_count)
				$count = "<span>(" . $this->favorites_count . ")</span>";
			
			$css = ( $key == $this->imspage ) ? ' current' : '';
			$nav .= '<li class="ims-menu-' . $key . $css . '"><a href="' . $this->get_permalink($key) . '">' . $page . "</a> $count </li> " . " \n";
		}
		
		if ($this->gal->post_password && isset($_COOKIE['wp-postpass_' . COOKIEHASH]))
			$nav .= '<li class="ims-menu-logout"><a href="' . $this->get_permalink("logout") . '">' . __("Exit Gallery", 'ims') . '</a></li>' . "\n";
		
		return $nav . "</ul></div>\n";
	}
	
	/**
	 * Display store sub-navigation
	 *
	 * @return void
	 * @since 2.0.0
	 */
	function store_subnav($return = true) {

		if (!empty($this->meta['_dis_store'][0]) || (!empty($this->opts['disablestore'])
		&& !empty($this->opts['hidefavorites'])) || ( $this->opts['widgettools'] && $return ))
			return;

		$this->subnav = apply_filters('ims_subnav', array(
			'ims-select-all' => __("Select all", 'ims'),
			'ims-unselect-all' => __("Unselect all", 'ims'),
			'add-to-favorite' => __("Add to favorites", 'ims'),
			'remove-from-favorite' => __("Remove", 'ims'),
			'add-images-to-cart' => __("Add to cart", 'ims')
		));

		$nav = '<div class="ims-toolbar"><ul class="ims-tools-nav">';
		foreach ($this->subnav as $key => $label) {
			if (($this->imspage != 'photos' && $key == 'add-to-favorite') ||
					($this->imspage != 'favorites' && $key == 'remove-from-favorite' ))
				continue;
			$nav .= '<li class="' . $key . '"><a href="#" rel="nofollow" title="' . $label . '">' . $label . '</a></li>';
		}
		return $nav .= '</ul></div>';
	}
	
	/**
	 * Display slideshow navigation
	 *
	 * @param  array $attachments
	 * @return string
	 * @since 3.1.0
	 */
	function slide_show_nav( $attachments = array()){
		
		if(empty($attachments))
			$attachments = $this->attachments;
		
		$output = '<div class="ims-imgs-nav">' . "\n";
		$output .= '<div id="ims-thumbs">' . "\n";
		$output .= '<ul role="list" class="thumbs">' . "\n";

		foreach ($attachments as $image) {
			$size = ' width="' . $image->meta['sizes']['mini']['width'] . '" height="' .  $image->meta['sizes']['mini']['height'] . '"';

			$img = '<img role="img" src="' . $this->get_image_url($image->ID, 3) . '" class="photo" title="' . 
			esc_attr($image->post_excerpt) . '" alt="' . esc_attr($image->post_title) . '"' . $size . ' />';

			$output .=
			'<li data-id="'.$this->url_encrypt($image->ID).'" role="hmedia listitem" class="ims-thumb">
				<a rel="enclosure" class="thumb" href="' . $this->get_image_url($image->ID, 1) . '" title="' . esc_attr($image->post_title) . '">' . $img . '</a> 
				<span class="fn caption">' . apply_filters('ims_image_caption', $image->post_excerpt, $image) . '</span>
			</li>';
		}

		$output .= '</ul><!--.thumbs-->' . "\n";
		$output .= '</div><!--#ims-thumbs-->' . "\n";
		$output .= '</div><!--.ims-imgs-nav-->' . "\n";	
		
		return $output;
	}
	
	/**
	 * Get post id by using image url
	 *
	 * @param string $path
	 * @since 3.1.0
	 * return unit
	 */
	function get_id_from_path($path) {
		global $wpdb;
		$tittle = sanitize_title(basename($path));
		$id = wp_cache_get('ims_imageid_' . $tittle);

		if (false == $id) {
			$id = $wpdb->get_var($wpdb->prepare(
				"SELECT post_id FROM $wpdb->postmeta
				WHERE meta_key = '_wp_attachment_metadata'
				AND meta_value LIKE %s", "%{$path}%"
			));
			wp_cache_set('ims_imageid_' . $tittle, $id);
		}
		return $id;
	}
	
	/**
	 * Get encrypted image url
	 *
	 * @param int $id
	 * @param int $size
	 * @since 3.0.0
	 * return string
	 */
	function get_image_url($id, $size = 1) {

		//backwards compatibilty
		if (is_object($id) && isset($id->ID))
			$id = $id->ID;
		elseif (is_array($id) && isset($id['sizes']['thumbnail']['path']))
			$id = $this->get_id_from_path($id['sizes']['thumbnail']['path']);

		$url = "$id:$size";

		//add watermark
		if (!empty($this->opts['watermark'])
		&& !$this->in_array($size, array(2, 3)))
			$url .= ":1";

		$imgurl = $this->baseurl . $this->url_encrypt($url);
		return apply_filters('ims_image_url', $imgurl, $id, $size);
	}
	
	/* get image tag
	 *
	 * @param int $id
	 * @param array $data
	 * @param int $sz size code
	 * @return string
	 * @since 3.1.7
	 */
	function image_tag( $ID, $data, $sz = 2){
		
		extract($this->gallery_tags);
		
		$size = '';
		$class = 'photo ims-image';
		$enc = $this->url_encrypt($ID);
		
		if( isset($data['class']) )
			$class = esc_attr($data['class']);
			
		if (is_array($data) && isset($data['sizes']['thumbnail']['width']))
			$size .= ' width="' . esc_attr($data['sizes']['thumbnail']['width']) . '" ';
		if (is_array($data) && isset($data['sizes']['thumbnail']['height']))
			$size .= 'height="' . esc_attr($data['sizes']['thumbnail']['height']) . '"';
				
		$output  = '<' . $imagetag . ' class="hmedia ims-img' . " imgid-{$enc}" . '" itemscope itemprop="thumbnail" itemtype="http://schema.org/ImageObject">';
		$output .= '<a  id="' . $enc . '" href="'. $data['link'] . '" class="' . $class . '" itemprop="contentUrl significantLink" title="' . esc_attr( $data['title'] ) . '" rel="enclosure">';
		$output .= '<img src="' . IMSTORE_URL . '/_img/1x1.trans.gif" alt="'.esc_attr($data['alt']).'"'.$size.' data-ims-src="' . $this->get_image_url($ID,$sz).'" role="img"/></a>'; 
		
		if (( empty($this->opts['disablestore']) || empty($this->opts['hidefavorites']) ) && is_singular('ims_gallery') && empty($data['_dis_store'][0]))
			$output .= ' <label><input name="imgs[]" type="checkbox" value="' . $enc . '" /><span class="ims-label"> ' . __('Select', 'ims') . '</span></label>';
			
		return $output .= '<'.$captiontag.' class="gallery-caption"><span class="fn ims-img-name">'.esc_attr($data['caption']).'</span></'.$captiontag.'></'.$imagetag.'>';
	}
	
	/* Display taxonomy content
	 *
	 * @return string
	 * @since 3.1.7
	 */
	function taxonomy_content(){
		global $post;
		
		$meta = false;
		$images = get_children( array(
			'numberposts' => 1,
			'post_type'=>'ims_image', 
			'post_parent' => $post->ID,
			'orderby' => 'menu_order',
			'order' => 'ASC'
		)); 
		
		foreach( $images as  $image )
			$meta = wp_get_attachment_metadata( $image->ID );
		
		if( empty($meta) ) return;
		$title = get_the_title( $post->ID );
		
		$meta += array('link' => get_permalink(), 'alt' => $title, 'caption' => $title, 'title' => sprintf(__('View &quot;%s&quot; gallery', 'ims'), $title));
		return $this->image_tag($image->ID, $meta);
	}
	
	/*
	 * Display image for attachment pages
	 *
	 * @param string $content
	 * @return string
	 * @since 3.0.0
	 */
	function ims_image_content() {
		global $post;
		$next_post = get_adjacent_post(false, false, false);

		if (empty($next_post)) {
			$attachments = get_children(array(
				'post_parent' => $post->post_parent,
				'post_status' => 'publish',
				'post_type' => 'ims_image',
				'order' => $this->order,
				'orderby' => $this->sortby,
				'numberposts' => 1,
			));
			foreach ($attachments as $attachment) {
				$next_post = $attachment;
				break;
			}
		}
		
		$title = get_the_title();
		$meta = get_post_meta($post->ID, '_wp_attachment_metadata', true);
		$meta += array( 'link' => get_permalink($next_post->ID), 'alt' => $title, 'caption' => wptexturize($post->post_excerpt), 'title' => $title);
		
		return $this->image_tag($post->ID, $meta, 1);
	}
		
	/**
	 * Display galleries
	 *
	 * @return array
	 * @since 0.5.0
	 */
	function display_galleries() {
		
		//allow plugins to overwrite output
		$output = apply_filters('ims_before_galleries', '', $this->gallery_tags, $this);
		if ('' != $output) return $output;
		
		extract($this->gallery_tags);
		
		global $post, $wp_query;
		$attach = ( (!empty($this->opts['attchlink']) && empty($this->meta['_to_attach'][0])) || !empty($this->meta['_to_attach'][0]));
		
		if (!empty($post->post_excerpt) && $this->in_array($this->imspage, array('photos', 'slideshow')))
			$output = '<div class="ims-excerpt">' . $post->post_excerpt . '</div>';
		
		$output .= "<{$gallerytag} id='ims-gallery-" . $this->galid . "' class='ims-gallery' itemscope itemtype='http://schema.org/ImageGallery'>";
		
		foreach ($this->attachments as $image) {
			
			$title = get_the_title($image->ID);
			
			if (!empty($image->post_parent)) {
				$title = get_the_title($image->post_parent);
				$link = get_permalink($image->post_parent);
			} elseif ($attach) {
				$link = get_permalink($image->ID);
			} else {
				$image->meta['class'] = 'photo ims-colorbox';
				$link = $this->get_image_url($image->ID);
			}
			
			$image->meta += array( 'link' => $link, 'alt' => $title, 'caption' => $title, 'title' => $title);
			$output .= $this->image_tag( $image->ID, $image->meta );
		}

		$output .= "</{$gallerytag}>";
		
		$wp_query->is_single = false;

		$output .= '<div class="ims-navigation">';
		$output .= '<div class="nav-previous">' . get_previous_posts_link(__('<span class="meta-nav">&larr;</span> Previous images', 'ims')) . '</div>';
		$output .= '<div class="nav-next">' . get_next_posts_link(__('More images <span class="meta-nav">&rarr;</span>', 'ims')) . '</div>';
		$output .= '</div><div class="ims-cl"></div>';
		
		$wp_query->post_count = 1;
		$wp_query->is_single = true;

		$this->visited_gallery(); //register visit
		
		return $output;
	}
	
	/**
	 * Core fuction display store
	 *
	 * @return void
	 * @since 0.5.0
	 */
	function imstore_shortcode($atts) {
		if (!is_singular())
			return false;
		
		if (isset($atts['secure']) && $atts['secure'] == true): //secure form
			
			return $this->display_secure($atts);
		
		elseif (isset($atts['list']) && is_numeric($atts['list'])): //pricelist
		
			$this->imspage = 'price-list';
			$this->pricelist_id = $atts['list'];
			$this->shipping_opts = get_option('ims_shipping_options');
			$this->sizes = get_post_meta($this->pricelist_id, '_ims_sizes', true);

			return $this->gallery_shortcode();
		
		elseif (isset($atts['favorites']) && $atts['favorites'] == true): //favorites
		
			$this->get_favorite_images();
			$output = $this->display_galleries();
			return $output;
		
		elseif (isset($atts['cart']) && $atts['cart'] == true): //cart
		
			if(isset($_POST['txn_id']) && isset($_POST['custom']))
				$this->imspage = 'receipt';
			elseif (empty($this->imspage))
				$this->imspage = 'shopping-cart';
			return $this->gallery_shortcode();

		else:

			$this->get_galleries($atts);
			return $this->display_galleries();
			
		endif;
	}
	
	/**
	 * Display gallery
	 *
	 * @return void
	 * @since 3.1.0
	 */
	function gallery_shortcode() {
		
		$error = '';
		$css = ( $this->opts['widgettools'] ) ? ' ims-widget-s' : '';
		
		$output = '<div id="ims-mainbox" class="ims-' . sanitize_title($this->pages[$this->imspage]) . $css . '" >';
		
		if (is_singular('ims_gallery'))
			$output .= $this->store_nav();
		
		$output .= '<div class="ims-labels">';
		if ( $this->gal->post_expire != '0000-00-00 00:00:00' )
			$output .= '<span class="ims-expires">' . __("Expires: ", 'ims') . date_i18n($this->dformat, strtotime($this->gal->post_expire)) . '</span>';
		
		$output .= '</div><!--.ims-labels-->';
		
		$error = ' ims-error';
		if ($this->error)
			$this->message = $this->error;
		elseif ($this->message)
			$error = ' ims-success';
		else
			$error = '';
		
		$output .= '<div class="ims-message' . $error . '">' . $this->message . '</div>';
		
		$output .= '<div class="ims-innerbox">';
		switch ($this->imspage) {
			case 'slideshow':
				$this->get_gallery_images();
				include_once( apply_filters('ims_slideshow_path', IMSTORE_ABSPATH . '/_store/slideshow.php') );
				break;
			case 'price-list':
				$post->comment_status = false;
				include_once( apply_filters('ims_pricelist_path', IMSTORE_ABSPATH . '/_store/price-list.php') );
				break;
			case "favorites":
				$post->comment_status = false;
				$this->get_favorite_images();
				$output .= $this->store_subnav();
				$output .= $this->display_galleries();
				break;
			case "shopping-cart":
				$post->comment_status = false;
				include_once( apply_filters('ims_cart_path', IMSTORE_ABSPATH . '/_store/cart.php') );
				break;
			case "receipt":
				$post->comment_status = false;
				include_once( apply_filters('ims_receipt_path', IMSTORE_ABSPATH . '/_store/receipt.php') );
				break;
			case "checkout":
				$post->comment_status = false;
				include_once( apply_filters('ims_checkout_path', IMSTORE_ABSPATH . '/_store/checkout.php') );
				break;
			default:
				$this->get_gallery_images();
				$output .= $this->store_subnav();
				
				if($this->attachments)
					$output .= $this->display_galleries();
		}
		$output .= apply_filters('ims_after_page', '', $this->imspage);
		$output .= '</div><!--.ims-innerbox-->';

		$output .= $this->display_order_form();
		return $output .= '</div><!--#ims-mainbox-->';
	}
	
	/**
	 * Display the secure section
	 * of the image store
	 *
	 * @param obj $errors
	 * @return string
	 * @since 3.0.0
	 */
	function display_secure() {

		$message = '';
		$this->is_secure = true;

		$errors = $this->validate_user();
		if (is_wp_error($errors))
			$message = $this->error_message($errors, true);

		if (empty($_COOKIE['wp-postpass_' . COOKIEHASH])
		|| empty($_COOKIE['ims_galid_' . COOKIEHASH]))
			return $message .= $this->get_login_form();

		if (isset($_COOKIE['wp-postpass_' . COOKIEHASH])) {
			wp_redirect(get_permalink($_COOKIE['ims_galid_' . COOKIEHASH]));
			die();
		}
	}

	/**
	 * Shipping options dropdown
	 *
	 * @return string
	 * @since 3.0.0
	 */
	function shipping_options() {
		if (empty($this->shipping_opts))
			return;

		$select = '<select name="shipping" id="shipping" class="shipping-opt">';
		foreach ($this->shipping_opts as $key => $val)
			$select .= '<option value="' . esc_attr($key) . '"' . selected($key, $this->cart['shipping_type'], false) . '>' .
			esc_attr($val['name']) . ' + ' . $this->format_price($val['price']) . '</option>';
		$select .= '</select>';
		return $select;
	}
	
	/**
	 * Display secure galleries login form
	 *
	 * @return void
	 * @since 0.5.0
	 */
	function get_login_form() {

		$glabel = "ims-galbox-{$this->galid}";
		$plabel = "ims-pwdbox-{$this->galid}";
		$nonce = wp_create_nonce('ims_access_form');

		$output = '<form action="' . get_permalink($this->galid) . '" method="post">
		<p class="message login">' . __("To view your images please enter your login information below:", 'ims') . '</p>
			<div class="ims-fields">
				<label for="' . $glabel . '">' . __("Gallery ID:", 'ims') . '</label> <input type="text" id="' . $glabel . '" name="' . $glabel . '" />
				<span class="linebreak"></span>
				<label for="' . $plabel . '">' . __("Password:", 'ims') . '
				</label> <input name="' . $plabel . '" id="' . $plabel . '" type="password" />
				<span class="linebreak"></span>
				<input type="submit" name="login-imstore" value="' . esc_attr__("log in", 'ims') . '" />
				<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />
				' . apply_filters('ims_after_login_form', '') . '
			</div>
		</form>
		';
		return apply_filters('ims_login_form', $output, $this->gal);
	}
	
	/**
	 * User login function
	 *
	 * @return object
	 * @since 0.5.0
	 */
	function validate_user() {

		//try to login first
		if (empty($_POST) || (isset($_REQUEST["login-imstore"]) 
		&& !wp_verify_nonce($_REQUEST["_wpnonce"], 'ims_access_form')))
			return false;

		$errors = new WP_Error( );
		if (empty($_REQUEST["ims-galbox-" . $this->galid]))
			$errors->add('emptyid', __('Please enter a gallery id. ', 'ims'));

		if (empty($_REQUEST["ims-pwdbox-" . $this->galid]))
			$errors->add('emptypswd', __('Please enter a password.', 'ims'));

		if (!empty($errors->errors))
			return $errors;

		$pass = $_REQUEST["ims-pwdbox-" . $this->galid];
		$galid = $_REQUEST["ims-galbox-" . $this->galid];

		$post = get_posts(array(
			'meta_value' => $galid,
			'post_type' => 'ims_gallery',
			'meta_key' => '_ims_gallery_id',
		)); $gal = isset($post[0]) ? $post[0] : $post;

		if (empty($gal->post_password) || $gal->post_password !== $pass) {
			$errors->add('nomatch', __('Gallery ID or password is incorrect. Please try again. ', 'ims'));
			return $errors;
		} elseif ($gal->post_password === stripslashes($pass)) {

			global $wp_version;
			$cookie_val = $gal->post_password;
			if ( version_compare($wp_version, '3.4', '>=')) {
				global $wp_hasher;
				if (empty($wp_hasher)) {
					require_once( ABSPATH . 'wp-includes/class-phpass.php');
					$wp_hasher = new PasswordHash(8, true);
				}
				$cookie_val = $wp_hasher->HashPassword(stripslashes($gal->post_password));
			}

			setcookie('ims_galid_' . COOKIEHASH, $gal->ID, 0, COOKIEPATH, COOKIE_DOMAIN);
			setcookie('wp-postpass_' . COOKIEHASH, $cookie_val, 0, COOKIEPATH, COOKIE_DOMAIN);

			update_post_meta($gal->post_id, '_ims_visits', get_post_meta($gal->ID, '_ims_visits', true) + 1);
			wp_redirect(get_permalink($gal->ID));
			die();
		}
	}
	
	/**
	 * Validate user input from
	 * shipping information
	 *
	 * @since 1.0.2
	 * return array|errors
	 */
	function validate_user_input() {

		if (!wp_verify_nonce($_POST["_wpnonce"], "ims_submit_order")) {
			wp_redirect($this->get_permalink('checkout', false));
			die();
		}

		foreach ($this->opts['checkoutfields'] as $key => $label) {
			if ($this->opts['required_' . $key] && empty($_POST[$key]))
				$this->error .= sprintf(__('The %s is required.', 'ims'), $label) . "<br />";
		}

		if (!empty($_POST['user_email']) && !is_email($_POST['user_email']))
			$this->error .= __('Wrong email format.', 'ims') . "<br />";

		if (empty($this->cart) || empty($this->orderid) || $this->cart_status != 'draft')
			$this->error .= __('Your shopping cart is empty.', 'ims');

		if (!empty($this->error))
			return;
			
		$data['gallery_id'] = false;
		$data['custom'] = $this->orderid;
		$data['mc_gross'] = $this->cart['total'];
		$data['payer_email'] = $_POST['user_email'];
		$data['last_name'] = $_POST['last_name'];
		$data['first_name'] = $_POST['first_name'];
		$data['mc_currency'] = $this->opts['currency'];
		$data['num_cart_items'] = $this->cart['items'];
		$data['txn_id'] = sprintf("%08d", $this->orderid);
		$data['payment_status'] = __('Pending', 'ims');
		$data['method'] = __('Email Notification', 'ims');
		$data['payment_gross'] = number_format($this->cart['total'], 2);
		$data['instructions'] = isset($_POST['instructions']) ? $_POST['instructions'] : '';
			
		$this->checkout($this->orderid,$data);
	}
	
	/**
	 * Validate promotion code
	 *
	 * @parm $code string
	 * @return bool
	 * @since 0.5.0
	 */
	function validate_code($code) {
		if (empty($code))
			return false;

		global $wpdb;
		$promo_id = $wpdb->get_var($wpdb->prepare(
			"SELECT ID FROM $wpdb->posts AS p
			INNER JOIN $wpdb->postmeta AS pm
			ON p.ID = pm.post_id
			WHERE meta_key = '_ims_promo_code'
			AND meta_value = BINARY '%s'
			AND post_status = 'publish'
			AND post_date <= '" . date('Y-m-d', current_time('timestamp')) . "'
			AND post_expire >= '" . date('Y-m-d', current_time('timestamp')) . "' "
		, $code));

		if (empty($promo_id)) {
			$this->error = __("Invalid promotion code", 'ims');
			return false;
		}
				
		//check for code limit	
		$data = get_post_meta($promo_id, '_ims_promo_data', true);
		If( !empty($data['promo_limit']) && $data['promo_limit'] <= get_post_meta($promo_id, '_ims_promo_count', true)){
			$this->error = __("Invalid promotion code", 'ims');
			return false;
		}
		
		$this->promo_id = $promo_id;
		$this->cart['promo']['promo_type'] = $data['promo_type'];
	
		if(isset($data['discount'])) 
			$this->cart['promo']['discount'] = $data['discount'];

		//set promotional cart values
		switch ($data['rules']['logic']) {
			case 'equal':
				if ($this->cart[$data['rules']['property']] > $data['rules']['value'])
					return true;
				break;
			case 'more':
				if ($this->cart[$data['rules']['property']] > $data['rules']['value'])
					return true;
				break;
			case 'less':
				if ($this->cart[$data['rules']['property']] > $data['rules']['value'])
					return true;
				break;
		}
		
		$this->promo_id = false;
		$this->error = __("Your current purchase doesn't meet the promotion requirements.", 'ims');
		return false;
	}
	
	/**
	 * Generate download links
	 *
	 * @param $cart array
	 * @param $total string
	 * @param $entegrity boolean
	 * @return string
	 * @since 3.1.0
	 */
	function get_download_links($cart, $total, $integrity = false) {

		if (empty($cart) || $total === false || !$integrity || $cart['total'] != $total)
			return false;

		if ($this->download_links !== false)
			return $this->download_links;

		$downlinks = array();

		//normalize nonce field
		wp_set_current_user(0);
		$nonce = "_wpnonce=" . wp_create_nonce("ims_download_img");

		//creat links
		foreach ($cart['images'] as $id => $sizes) {
			$enc = $this->url_encrypt($id);
			foreach ($sizes as $size => $colors) {
				foreach ($colors as $color => $item) {
					if (!empty($item['download']))
						$downlinks[] = '<a href="' . IMSTORE_ADMIN_URL . "/download.php?$nonce&amp;img=" .
								$enc . "&amp;sz=$size&amp;c=" . $item['color_code'] . '" class="ims-download">' .
								get_the_title($id) . " " . $item['color_name'] . " </a>";
				}
			}
		}

		if (!empty($downlinks)) {
			$output = '<div class="imgs-downloads">';
			$output .= '<h4 class="title">Downloads</h4>';
			$output .= '<ul role="list" class="download-links">';
			foreach ($downlinks as $link)
				$output .= "<li>$link</li>\n";
			$output .= "</ul>\n</div>";
		}

		return $this->download_links = $output;
	}
	
	/**
	 * checkout process
	 *
	 * @return void
	 * @since 3.1.7
	 */
	function checkout($cartid, $data){
		
		if(get_post_status($cartid) != 'draft')
			return;
			
		$cart = get_post_meta($cartid, '_ims_order_data', true);
		if(empty($cart)) return;
		
		foreach ($data as $key => $value){
			if( is_string($value) || is_numeric($value))
				$data[$key] = trim($value);
		}
		
		if(empty($data)) return;
		
		if(!isset($data['instructions'])) 	
			$data['instructions'] = false;
		
		$data['data_integrity'] = false;
		$total = (empty($cart['discounted'])) ? $cart['total'] : $cart['discounted'];
		
		if ($cart['items'] && $data['mc_currency'] == $cart['currency'] &&
		abs($data['mc_gross'] - $this->format_price($total, false)) < 0.00001)
			$data['data_integrity'] = true;
	
		sleep(1); 
		$this->orderid = $cartid;
		$this->imspage = 'receipt';
		
		wp_update_post(array(
			'post_expire' => '0', 'ID' => $cartid,
			'post_status' => 'pending', 'post_date' => current_time('timestamp')
		));
		
		//save response data
		$this->subtitutions[] = $data['instructions'];
		update_post_meta($cartid, '_response_data',$data);
		
		//update promotional count
		if(isset($cart['promo']['promo_id'])){
			update_post_meta($cart['promo']['promo_id'], '_ims_promo_count', 
			(int)get_post_meta($cart['promo']['promo_id'], '_ims_promo_count', true) +1);
		}
		
		//dont change array order
		$this->subtitutions = array(
			$data['mc_gross'], $data['payment_status'], get_the_title($cartid),
			$this->format_price($cart['shipping']), $data['txn_id'],$data['last_name'], $data['first_name'], $data['payer_email'],
		);
		
		do_action('ims_after_checkout', $this->cart);
		
		//create/update customer
		if (is_user_logged_in() && current_user_can('customer')) {
			global $user_ID;
			
			wp_update_user(array( 
				'ID' => $user_ID, 'user_email' => $data['user_email'], 'first_name' => $data['first_name'], 'last_name' => $data['last_name']
			));
			
			foreach ($this->opts['checkoutfields'] as $key => $label) {
				if (isset($data[$key])) update_user_meta($user_ID, $key, $data[$key]);
			}
		}
		
		//send emails 
		$message = preg_replace($this->opts['tags'], $this->subtitutions, $this->opts['notifymssg']);
		$download_links = $this->get_download_links($cart, $data['mc_gross'], $data['data_integrity']);
		
		$headers = 'From: "' . $this->opts['receiptname'] . '" <' . $this->opts['receiptemail'] . ">\r\n";
		$headers .= "Content-type: text/html; charset=utf8\r\n";
		
		wp_mail($this->opts['notifyemail'], $this->opts['notifysubj'], $message . $download_links , $headers);
		setcookie('ims_orderid_' . COOKIEHASH, false, (time() - 315360000), COOKIEPATH, COOKIE_DOMAIN);
		
		if (empty($this->opts['emailreceipt']))
			return;
		
		//notify buyers
		if (isset($data['payer_email']) && is_email($data['payer_email']) && !get_post_meta($cartid, '_ims_email_sent', true)){
			
			$message = make_clickable(wpautop(stripslashes(preg_replace($this->opts['tags'], $this->subtitutions, $this->opts['thankyoureceipt']))));
			wp_mail($data['payer_email'], sprintf(__('%s receipt.', 'ims'), get_bloginfo('blogname')), $message . $download_links, $headers);
			
			update_post_meta($cartid, '_ims_email_sent', 1);
		}
	}
	
	/**
	 * Add items to cart
	 *
	 * @return void
	 * @since 0.5.0
	 */
	function add_to_cart() {

		if (!wp_verify_nonce($_REQUEST["_wpnonce"], "ims_add_to_cart"))
			wp_die('Security check failed. Try refreshing the page.');

		if (!is_numeric($_POST['ims-quantity']) || empty($_POST['ims-quantity']))
			$this->error = __('Please, enter a valid image quantity', 'ims');

		if (empty($_POST['ims-image-size']))
			$this->error = __('Please, select an image size.', 'ims');

		if (empty($_POST['ims-to-cart-ids']))
			$this->error = __('There was a problem adding the images to the cart.', 'ims');

		do_action('ims_berofe_add_to_cart', $this->cart);

		if (!empty($this->error))
			return;

		//set defaults 
		$color = $finish = 0;
		$this->cart['items'] = 0;
		$this->cart['tax'] = false;
		$this->cart['shipping'] = 0;
		$this->cart['tracking'] = false;
		$this->cart['gallery_id'] = false;
		$this->cart['instructions'] = false;
		$this->cart['currency'] = $this->opts['currency'];
		
		if (empty($this->cart['promo']['discount']))
			$this->cart['promo']['discount'] = false;
		
		if (empty($this->cart['shipping_type']))
			$this->cart['shipping_type'] = 0;
		
		if (empty($this->cart['shippingcost']))
			$this->cart['shippingcost'] = false;

		if (empty($this->cart['subtotal']))
			$this->cart['subtotal'] = 0;

		if (isset($_POST['imstore-color']))
			$color = $_POST['imstore-color'];

		if (isset($_POST['imstore-finish']))
			$finish = $_POST['imstore-finish'];

		$images = explode(',', $_POST['ims-to-cart-ids']);

		//add images
		foreach ($images as $id) {

			$id = $this->url_decrypt($id);

			foreach ($_POST['ims-image-size'] as $sizename) {
				
				$size = str_replace(array('|','\\','.',' '),'',$sizename);
				$this->cart['images'][$id][$size][$color]['quantity'] =
				isset($this->cart['images'][$id][$size][$color]['quantity']) ?
				$this->cart['images'][$id][$size][$color]['quantity'] += $_POST['ims-quantity'] : $_POST['ims-quantity'];
				
				$this->cart['images'][$id][$size][$color]['size'] = $sizename;
				$this->cart['images'][$id][$size][$color]['gallery'] = $this->galid;
				$this->cart['images'][$id][$size][$color]['unit'] = $this->sizes[$size]['unit'];

				//size prices
				if (isset($this->sizes[$size]['ID']))
					$this->cart['images'][$id][$size][$color]['price'] = get_post_meta($this->sizes[$size]['ID'], '_ims_price', true);
				else $this->cart['images'][$id][$size][$color]['price'] = str_replace($this->sym, '',$this->sizes[$size]['price']);

				//finishes
				$this->cart['images'][$id][$size][$color]['finish_name'] = $this->listmeta['finishes'][$finish]['name'];
				$this->cart['images'][$id][$size][$color]['finish'] =
						( $this->listmeta['finishes'][$finish]['type'] == 'percent' ) ?
						( $this->cart['images'][$id][$size][$color]['price'] * ($this->listmeta['finishes'][$finish]['price'] / 100)) : 
						$this->listmeta['finishes'][$finish]['price'];

				//color price
				$this->cart['images'][$id][$size][$color]['color'] =
						(isset($this->listmeta['colors'][$color]['price'])) ? $this->listmeta['colors'][$color]['price'] : 0;
				$this->cart['images'][$id][$size][$color]['color_code'] = $this->listmeta['colors'][$color]['code'];
				$this->cart['images'][$id][$size][$color]['color_name'] = $this->listmeta['colors'][$color]['name'];

				//is downloadable
				if (isset($this->sizes[$size]['download']))
					$this->cart['images'][$id][$size][$color]['download'] = $this->sizes[$size]['download'];
				elseif(empty($this->opts['disable_shipping'])) $this->cart['shippingcost'] = 1;

				$this->cart['images'][$id][$size][$color]['subtotal'] =
						(($this->cart['images'][$id][$size][$color]['price'] +
						$this->cart['images'][$id][$size][$color]['color'] +
						$this->cart['images'][$id][$size][$color]['finish'] ) *
						$this->cart['images'][$id][$size][$color]['quantity'] );

				$this->cart['subtotal'] += $this->cart['images'][$id][$size][$color]['subtotal'];
			}
		}

		$this->cart['total'] = $this->cart['subtotal'];

		//count image numbers
		foreach ($this->cart['images'] as $id => $sizes) {
			foreach ($sizes as $size => $colors) {
				foreach ($colors as $color => $values)
					$this->cart['items'] += $values['quantity'];
			}
		}

		//apply promotions
		if (isset($this->cart['promo']['code']) && $this->validate_code($this->cart['promo']['code'])) {
			switch ($this->cart['promo']['promo_type']) {
				case 2: $this->cart['promo']['discount'];
					break;
				case 3: $this->cart['promo']['discount'] = $this->shipping_opts[$this->cart['shipping_type']]['price'];
					break;
				case 1: $this->cart['promo']['discount'] = ( $this->cart['subtotal'] * ($this->cart['promo']['discount'] / 100) );
					break;
			}
			$this->cart['promo']['promo_id'] = $this->promo_id;
			$this->cart['total'] = $this->cart['subtotal'] - $this->cart['promo']['discount'];
		}
		
		//add shipping cost
		if ($this->cart['shippingcost'] && isset($this->cart['shipping_type']))
			$this->cart['shipping'] = $this->shipping_opts[$this->cart['shipping_type']]['price'];

		//set cart total
		$this->cart['total'] += $this->cart['shipping'];

		//apply tax
		if (!empty($this->opts['taxamount'])) {
			if ($this->opts['taxtype'] == 'percent')
				$this->cart['tax'] = ( $this->cart['total'] * ($this->opts['taxamount'] / 100) );
			else
				$this->cart['tax'] = $this->opts['taxamount'];
			$this->cart['total'] += $this->cart['tax'];
		}

		do_action('ims_before_save_cart', $this->cart);

		if (empty($_COOKIE['ims_orderid_' . COOKIEHASH])
				|| empty($this->cart_stat) || $this->cart_stat != 'draft') {

			$order = array(
				'ping_status' => 'close',
				'post_status' => 'draft',
				'comment_status' => 'close',
				'post_type' => 'ims_order',
				'post_expire' => date('Y-m-d H:i', current_time('timestamp') + 86400),
				'post_title' => 'Ims Order - ' . date('Y-m-d H:i', current_time('timestamp')),
			);
			$orderid = wp_insert_post(apply_filters('ims_new_order', $order, $this->cart));

			if (!empty($orderid) && !empty($this->cart)) {
				setcookie('ims_orderid_' . COOKIEHASH, $orderid, time() + 31536000, COOKIEPATH, COOKIE_DOMAIN);
				add_post_meta($orderid, '_ims_order_data', $this->cart);
			}
		}else
			update_post_meta($this->orderid, '_ims_order_data', $this->cart);

		do_action('ims_after_add_to_cart', $this->cart);
		
		if (!empty($this->error))
			return;
			
		global $paged;
		$this->success = '1';
		wp_redirect(html_entity_decode($this->get_permalink($this->imspage, false, $paged)));
		die();
	}
	
	/**
	 * update cart information
	 *
	 * @return void
	 * @since 3.0.0
	 */
	function update_cart() {

		if (!wp_verify_nonce($_REQUEST["_wpnonce"], "ims_submit_order"))
			wp_die('Security check failed. Try refreshing the page.');

		do_action('ims_before_update_cart', $this);

		//remove images
		if (isset($_POST['ims-remove']) && is_array($_POST['ims-remove'])) {
			foreach ($_POST['ims-remove'] as $delete) {
				$values = explode('|', $delete,3);
				$values[0] = $this->url_decrypt($values[0]);

				unset($this->cart['images'][$values[0]][$values[1]][$values[2]]);

				if (empty($this->cart['images'][$values[0]][$values[1]]))
					unset($this->cart['images'][$values[0]][$values[1]]);

				if (empty($this->cart['images'][$values[0]]))
					unset($this->cart['images'][$values[0]]);
			}
		}

		//if cart is empty save and redirect
		if (empty($this->cart['images'])) {
			update_post_meta($this->orderid, '_ims_order_data', false);
			wp_redirect(html_entity_decode($this->get_permalink($this->imspage)));
			die();
		}

		//reset cart 
		$this->cart['tax'] = false;
		$this->cart['items'] = 0;
		$this->cart['subtotal'] = 0;
		$this->cart['shipping'] = 0;
		$this->cart['discounted'] = false;
		$this->cart['shippingcost'] = false;
		$this->cart['promo']['discount'] = false;
		$this->cart['promo']['code'] = $_POST['promocode'];
		
		if(isset($_POST['shipping'] ))
			$this->cart['shipping_type'] = $_POST['shipping'];
	
		//recalculate values
		foreach ($this->cart['images'] as $id => $sizes) {
			foreach ($sizes as $size => $colors) {
				foreach ($colors as $color => $values) {
					$enc = $this->url_encrypt($id);
					
					if( $_POST['ims-quantity'][$enc][$size][$color]['quantity'] < 1){
						unset( $this->cart['images'][$id] );
						continue;
					}
						
					$this->cart['items'] += $_POST['ims-quantity'][$enc][$size][$color]['quantity'];

					//get cart subtotal
					$this->cart['subtotal'] += ((
							$this->cart['images'][$id][$size][$color]['price'] +
							$this->cart['images'][$id][$size][$color]['color'] +
							$this->cart['images'][$id][$size][$color]['finish'] ) *
							$_POST['ims-quantity'][$enc][$size][$color]['quantity']
					);

					//get image subtotal
					$this->cart['images'][$id][$size][$color]['subtotal'] = ((
							$this->cart['images'][$id][$size][$color]['price'] +
							$this->cart['images'][$id][$size][$color]['color'] +
							$this->cart['images'][$id][$size][$color]['finish']) *
							$_POST['ims-quantity'][$enc][$size][$color]['quantity']
					);

					//check for downloadable images
					if (empty($colors[$color]['download']) && empty($this->opts['disable_shipping']))
						$this->cart['shippingcost'] = true;

					//update image quantity
					$this->cart['images'][$id][$size][$color]['quantity'] = $_POST['ims-quantity'][$enc][$size][$color]['quantity'];
				}
			}
		}

		$this->cart['total'] = $this->cart['subtotal'];

		//apply promotions
		if ($this->validate_code($this->cart['promo']['code'])) {
			switch ($this->cart['promo']['promo_type']) {
				case 2: $this->cart['promo']['discount'];
					break;
				case 3: $this->cart['promo']['discount'] = $this->shipping_opts[$this->cart['shipping_type']]['price'];
					break;
				case 1: $this->cart['promo']['discount'] = ( $this->cart['subtotal'] * ($this->cart['promo']['discount'] / 100) );
					break;
			}
			$this->cart['promo']['promo_id'] = $this->promo_id;
			$this->cart['total'] = $this->cart['subtotal'] - $this->cart['promo']['discount'];
		}

		//add shipping cost
		if ($this->cart['shippingcost'] && $this->cart['shipping_type'] !== false)
			$this->cart['shipping'] = $this->shipping_opts[$this->cart['shipping_type']]['price'];

		//set cart total
		$this->cart['total'] += $this->cart['shipping'];

		//apply tax
		if (!empty($this->opts['taxamount'])) {
			if ($this->opts['taxtype'] == 'percent')
				$this->cart['tax'] = ( $this->cart['total'] * ($this->opts['taxamount'] / 100) );
			else
				$this->cart['tax'] = $this->opts['taxamount'];
			$this->cart['total'] += $this->cart['tax'];
		}

		if (isset($_POST['instructions']))
			$this->cart['instructions'] = trim($_POST['instructions']);

		do_action('ims_after_update_cart', $this);
		update_post_meta($this->orderid, '_ims_order_data', $this->cart);
		
		if (!empty($this->error))
			return;
			
		$this->success = '2';
		wp_redirect(html_entity_decode($this->get_permalink($this->imspage)));
		die();
	}
	
	/**
	 * Encrypt url
	 *
	 * @parm string $string
	 * @return string
	 * @since 2.1.1
	 */
	function url_encrypt($string) {
		$str = '';
		for ($i = 0; $i < strlen($string); $i++) {
			$char = substr($string, $i, 1);
			$keychar = substr($this->key, ($i % strlen($this->key)) - 1, 1);
			$char = chr(ord($char) + ord($keychar));
			$str .= $char;
		}
		return urlencode(implode('::', explode('/', str_replace('=', '', base64_encode($str)))));
	}

}


/*backwards compatability functions*/

if(!function_exists('single_term_title')){
	function single_term_title(){
		global $wp_query;
		return $wp_query->get_queried_object()->name;
	}
}

if(!function_exists('wp_get_post_parent_id')){
	function wp_get_post_parent_id( $post_ID ) {
		$post = get_post( $post_ID );
		if ( !$post || is_wp_error( $post ) )
			return false;
		return (int) $post->post_parent;
	}
}