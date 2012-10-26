<?php

/**
 * Intall add options
 *
 * @package Image Store
 * @author Hafid Trujillo
 * @copyright 20010-2012
 * @since 0.5.0
 */

// Stop direct access of the file
if( !defined('ABSPATH') || !current_user_can( 'activate_plugins' ) ) 
	die( );

class ImStoreInstaller extends ImStore {

	/**
	 * Constructor
	 *
	 * @return void
	 * @since 0.5.0 
	 */
	function __construct() {
		
		$this->ver = get_option('imstore_version');
		$this->userid = get_current_user_id();
	
		if (empty($this->ver))
			$this->imstore_default_options();

		if ($this->version > $this->ver || empty($this->ver))
			$this->update();

		if (!get_option('ims_pricelist'))
			$this->price_lists();
		
		do_action('ims_install');
		
		//save imstore version
		update_option('imstore_version', $this->version);
	}

	/**
	 * Setup the default option array 
	 * Create required categores
	 *
	 * @return void
	 * @since 0.5.0 
	 */
	function imstore_default_options() {
		global $wpdb;

		//FRONTEND OPTIONS
		$ims_ft_opts['album_template'] = 'page.php';
		$ims_ft_opts['autoStart'] = '';
		$ims_ft_opts['attchlink'] = false;
		
		$ims_ft_opts['bottommenu'] = false;
		
		$ims_ft_opts['clocal'] = '1';
		$ims_ft_opts['closeLinkText'] = __('Close', 'ims');
		$ims_ft_opts['colorbox'] = '1';
		$ims_ft_opts['currency'] = 'USD';
		
		$ims_ft_opts['deletefiles'] = '1';
		$ims_ft_opts['disable_decimal'] = false;
		$ims_ft_opts['disablestore'] = false;

		$ims_ft_opts['emailreceipt'] = '1';
		
		$ims_ft_opts['galleriespath'] = '/_imsgalleries';
		$ims_ft_opts['gallery_template'] = false;
		$ims_ft_opts['galleryexpire'] = '60';
		$ims_ft_opts['gateway_method'] = 'post';
		$ims_ft_opts['googleid'] = '';

		$ims_ft_opts['hidefavorites'] = false;
		$ims_ft_opts['hidephoto'] = false;
		$ims_ft_opts['hideslideshow'] = false;

		$ims_ft_opts['imgs_per_page'] = false;
		$ims_ft_opts['imgsortdirect'] = 'ASC';
		$ims_ft_opts['imgsortorder'] = 'menu_order';
		$ims_ft_opts['imswidget'] = false;
		
		$ims_ft_opts['maxPagesToShow'] = 5;
		$ims_ft_opts['mediarss'] = '1';
		
		$ims_ft_opts['nextLinkText'] = __('Next', 'ims');
		$ims_ft_opts['nextPageLinkText'] = __('Next &rsaquo;', 'ims');
		$ims_ft_opts['notifyemail'] = get_option('admin_email');
		$ims_ft_opts['notifymssg'] = sprintf(__("A new order was place at you image store at %s \n\nOrder number: %%order_number%% \nTo view the order details please login to your site at: %s \n\n%%instructions%%", 'ims'), get_option('blogname'), wp_login_url());
		$ims_ft_opts['notifysubj'] = __('New purchase notification', 'ims');
		$ims_ft_opts['numThumbs'] = 8;
		
		$ims_ft_opts['pauseLinkTex'] = __('Pause', 'ims');
		$ims_ft_opts['paymentname'] = 'Pay by check';
		$ims_ft_opts['playLinkText'] = __('Play', 'ims');
		$ims_ft_opts['prevLinkText'] = __('Previous', 'ims');
		$ims_ft_opts['prevPageLinkText'] = __('&lsaquo; Prev', 'ims');
		
		$ims_ft_opts['receiptname'] =  'Image Store';
		$ims_ft_opts['receiptemail'] =  'imstore@' . $_SERVER['HTTP_HOST'];

		$ims_ft_opts['sameasbilling'] = '1';
		$ims_ft_opts['securegalleries'] = '1';
		$ims_ft_opts['showtexteditor'] = false;
		$ims_ft_opts['slideshowSpeed'] = 3200;
		$ims_ft_opts['stylesheet'] = '1';
		$ims_ft_opts['symbol'] = '$';
		$ims_ft_opts['swfupload'] = '1';
		
		$ims_ft_opts['taxtype'] = 'percent';
		$ims_ft_opts['transitionTime'] = 1000;	
		$ims_ft_opts['thankyoureceipt'] = sprintf(__("<h2>Thank You, %%customer_first%% %%customer_last%%</h2>\n Save the information bellow for your records. \n\nTotal payment: %%total%%\nTransaction number: %%order_number%%\n\nIf you have any question about your order please contact us at: %s", 'ims'), get_option('admin_email'));
			
		$ims_ft_opts['watermark'] = '0';
		$ims_ft_opts['watermark_color'] = 'ffffff';
		$ims_ft_opts['watermark_size'] = '12';
		$ims_ft_opts['watermark_text'] = get_option('blogname');
		$ims_ft_opts['watermark_trans'] = '90';
		$ims_ft_opts['widgettools'] = false;
		$ims_ft_opts['wplightbox'] = false;

		//dont change array order
		$ims_ft_opts['tags'] = array(
			__('/%total%/', 'ims'),
			__('/%status%/', 'ims'),
			__('/%gallery%/', 'ims'),
			__('/%shipping%/', 'ims'),
			__('/%order_number%/', 'ims'),
			__('/%customer_last%/', 'ims'),
			__('/%customer_first%/', 'ims'),
			__('/%customer_email%/', 'ims'),
			__('/%instructions%/', 'ims'),
		);

		$ims_ft_opts['required_ims_zip'] = 1;
		$ims_ft_opts['required_user_email'] = 1;
		$ims_ft_opts['required_first_name'] = 1;
		$ims_ft_opts['required_ims_address'] = 1;
		$ims_ft_opts['checkoutfields'] = array(
			'ims_city' => __('City', 'ims'),
			'ims_state' => __('State', 'ims'),
			'user_email' => __('Email', 'ims'),
			'ims_phone' => __('Phone', 'ims'),
			'ims_address' => __('Address', 'ims'),
			'ims_zip' => __('Zip Code', 'ims'),
			'last_name' => __('Last Name', 'ims'),
			'first_name' => __('First Name', 'ims'),
		);
		
		//gateway array
		$ims_ft_opts['gateway'] = array(
			'paypalsand' => 1,
			'paypalprod' => false,
			'googlesand' => false,
			'googleprod' => false,
			'googleprod' => false,
			'enotification' => false,
			'wepaystage'=> false,
			'wepayprod' => false,
			'custom' => false,
		);

		//add custom gateway tags
		$old = get_option($this->optionkey);
		if (isset($old['carttags']))
			$ims_ft_opts['carttags'] = $old['carttags'];

		//default image sizes
		$ims_dis_img['mini'] = array('name' => 'mini', 'w' => 70, 'h' => 60, 'q' => 95, 'crop' => 1);
		$ims_dis_img['preview'] = array('name' => 'preview', 'w' => 380, 'h' => 380, 'q' => 80, 'crop' => 0);

		//allow plugins to modify default options
		$ims_ft_opts = apply_filters('ims_default_opts', $ims_ft_opts);

		update_option('mini_crop', 1);
		update_option('mini_size_w', 70);
		update_option('mini_size_h', 60);

		update_option('preview_crop', 0);
		update_option('preview_size_w', 380);
		update_option('preview_size_h', 380);
		update_option('preview_size_q', 80);

		//display galleries on front-end
		update_option('ims_searchable', true);

		//save all options
		update_option('ims_dis_images', $ims_dis_img);

		//multisite support
		if (is_multisite() && $this->sync == true)
			update_site_option($this->optionkey, $ims_ft_opts);
		else
			update_option($this->optionkey, $ims_ft_opts);

		//stop table optiomize
		$optimize = apply_filters('ims_optimize', true, 'install');
		if ($optimize)
			$wpdb->query("OPTIMIZE TABLE $wpdb->options, $wpdb->postmeta, $wpdb->posts, $wpdb->users, $wpdb->usermeta");
	}

	/**
	 * Set use permission/roles
	 * and add defult price list, image sizes
	 *
	 * @return void
	 * @since 2.0.0 
	 */
	function update() {
		global $wpdb;

		wp_cache_flush();

		//update database if updating before 2.0.0
		if ($this->ver < "2.0.0") {
			$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key 
			IN( 'ims_downloads', 'ims_download_max', '_ims_image_count', '_ims_customer' )");
			$wpdb->query("UPDATE $wpdb->postmeta SET meta_key = '_ims_visits' WHERE meta_key = 'ims_visits'");
			$wpdb->query("UPDATE $wpdb->postmeta SET meta_key = '_ims_tracking' WHERE meta_key = 'ims_tracking'");
			$wpdb->query("UPDATE $wpdb->posts SET post_content = '[ims-gallery-content]' WHERE post_type = 'ims_gallery'");
		}

		$ims_ft_opts = get_option($this->optionkey);

		//update options if updating from 2.0.8
		if ($this->ver <= "2.0.8") {
			$ims_ft_opts['album_template'] = 'page . php';
			$ims_ft_opts['tags'][] = __('/%instructions%/', 'ims');
			update_option($this->optionkey, $ims_ft_opts);
		}
		
		//update options if updating to 3.0.0
		if ($this->ver <= "3.0.0" || empty($ims_ft_opts['carttags'])) {
			$ims_ft_opts['gateway_method'] = 'post';
			$ims_ft_opts['carttags'] = array(
				__('%image_id%', 'ims'),
				__('%image_name%', 'ims'),
				__('%image_value%', 'ims'),
				__('%image_color%', 'ims'),
				__('%image_quantity%', 'ims'),
				__('%image_download%', 'ims'),
				__('%cart_id%', 'ims'),
				__('%cart_tax%', 'ims'),
				__('%cart_total%', 'ims'),
				__('%cart_status%', 'ims'),
				__('%cart_shipping%', 'ims'),
				__('%cart_currency%', 'ims'),
				__('%cart_subtotal%', 'ims'),
				__('%cart_discount%', 'ims'),
				__('%cart_discount_code%', 'ims'),
				__('%cart_total_items%', 'ims'),
			);
		}
			
		//update options if updating to 3.1.0
		if ($this->ver <= "3.1.0"){
			$ims_ft_opts['googleid'] =  '';
			$ims_ft_opts['attchlink'] =  false;
			$ims_ft_opts['hidephoto'] =  false;
			$ims_ft_opts['imswidget'] =  false;
			$ims_ft_opts['wplightbox'] =  false;
			$ims_ft_opts['widgettools'] =  false;
			$ims_ft_opts['disablestore'] =  false;
			$ims_ft_opts['hidefavorites'] =  false;
			$ims_ft_opts['hideslideshow'] =  false;
			$ims_ft_opts['gallery_template'] =  false;
			$ims_ft_opts['showtexteditor'] =  false;
			$ims_ft_opts['disable_decimal'] =  false;
			$ims_ft_opts['receiptname'] =  'Image Store';
			$ims_ft_opts['receiptemail'] =  'imstore@' . $_SERVER['HTTP_HOST'];
			
			$ims_ft_opts['bottommenu'] = false;
			$ims_ft_opts['required_ims_city'] = false;
			$ims_ft_opts['required_ims_state'] = false;
			$ims_ft_opts['required_last_name'] = false;
			$ims_ft_opts['required_ims_phone'] = false;
			
			if( !is_array( $ims_ft_opts['gateway'] ) ){
				$key = $ims_ft_opts['gateway'];
				$ims_ft_opts['gateway'] = array(
					'paypalsand' => 1,
					'paypalprod' => false,
					'googlesand' => false,
					'googleprod' => false,
					'googleprod' => false,
					'wepaystage' => false,
					'wepayprod' => false,
					'enotification' => false,
					'custom' => false,
				);
				$ims_ft_opts['gateway'][$key] = 1;
			}
			
			$this->ims_xtra_pricing_opts();
			update_option('ims_site_url',get_option('siteurl'));
			update_option($this->optionkey, $ims_ft_opts);
		}
		
		//update options if updating to 3.1.5
		if ($this->ver <= "3.1.5"){	
			$ims_ft_opts['tags'] = array(
				__('/%total%/', 'ims'),
				__('/%status%/', 'ims'),
				__('/%gallery%/', 'ims'),
				__('/%shipping%/', 'ims'),
				__('/%order_number%/', 'ims'),
				__('/%customer_last%/', 'ims'),
				__('/%customer_first%/', 'ims'),
				__('/%customer_email%/', 'ims'),
				__('/%instructions%/', 'ims'),
			);
			update_option($this->optionkey, $ims_ft_opts);
		}
		
		//update options if updating to 3.1.6
		if ($this->ver <= "3.1.6"){	
			$ims_ft_opts['gateway']['wepaystage'] = false;
			$ims_ft_opts['gateway']['wepayprod'] = false;
			update_option($this->optionkey, $ims_ft_opts);
			
			$gateways = array(
				'paypalprod' => array(
					'name' => __('PayPal', 'ims'),
					'url' => 'https://www.paypal.com/cgi-bin/webscr',
				),
				'paypalsand' => array(
					'name' => __('PayPal Sandbox', 'ims'),
					'url' => 'https://www.sandbox.paypal.com/cgi-bin/webscr',
				),
				'googleprod' => array(
					'name' => __('Google Checkout', 'ims'),
					'url' => 'https://checkout.google.com/api/checkout/v2/checkoutForm/Merchant/',
				),
				'googlesand' => array(
					'name' => __('Google Checkout Sandbox', 'ims'),
					'url' => 'https://sandbox.google.com/checkout/api/checkout/v2/checkoutForm/Merchant/',
				),
				'wepayprod' => array(
					'url' => false,
					'name' => __('WePay', 'ims'),
				),
				'wepaystage' => array(
					'url' => false,
					'name' => __('WePay Stage', 'ims')
				),
				'enotification' => array(
					'url' => false,
					'name' => __('Checkout', 'ims'),
				),
				'custom' => array(
					'name' => $this->opts['gateway_name'],
					'url' => $this->opts['gateway_url'],
				)
			);
			update_option('ims_gateways', $gateways);
		}

		//add imstore capabilities
		$ims_caps = array(
			'read_sales' => __('Read sales', 'ims'),
			'add_galleries' => __('Add galleries', 'ims'),
			'change_pricing' => __('Change pricing', 'ims'),
			'change_settings' => __('Change Settings', 'ims'),
			'manage_galleries' => __('Manage galleries', 'ims'),
			'manage_customers' => __('Manage Customers', 'ims'),
			'change_permissions' => __('Change Permissions', 'ims'),
		);
		$ims_caps = apply_filters('ims_user_caps', $ims_caps);

		//user options
		$ims_user_opts['swfupload'] = '1';
		$ims_user_opts['caplist'] = $ims_caps;
		update_option('ims_user_options', $ims_user_opts);

		//assign caps to adminstrator, if not, to the editor
		$role = get_role('administrator');
		$role = ( empty($role) ) ? get_role('editor') : $role;
		foreach ($ims_caps as $cap => $capname)
			$role->add_cap('ims_' . $cap);

		//add capabilities to the customer role
		$customer = @get_role('customer');
		if (empty($customer))
			add_role('customer', 'Customer', array('read' => 1, 'ims_read_galleries' => 1));

		//save all ims options to be deleted when plugin is unstalled
		update_option('ims_options', array('ims_front_options', $this->optionkey, 'ims_back_options', 'ims_page_secure', 'ims_searchable', 
		'ims_print_finishes','ims_shipping_options', 'ims_pricelist', 'ims_options', 'ims_page_galleries', 'ims_sizes', 'ims_image_key', 'ims_download_sizes',
		'ims_dis_images', 'ims_user_options', 'ims_site_url', 'ims_color_filters', 'ims_page_cart', 'ims_color_options','ims_gateways'));

		//add expire column
		if (empty($this->ver) || ( $this->ver <= "3.0.1" && is_multisite() ))
			$wpdb->query("ALTER IGNORE TABLE  $wpdb->posts ADD post_expire DATETIME NOT NULL");

		//create secure page
		$page_secure = get_option('ims_page_secure');
		if (empty($page_secure)) {
			$secure_data = array(
				'ID' => false,
				'post_type' => 'page',
				'ping_status' => 'closed',
				'post_status' => 'publish',
				'comment_status' => 'closed',
				'post_author' => $this->userid,
				'post_content' => '[image-store secure=1]',
				'post_title' =>__('Secure Images', 'ims'),
				'post_excerpt' => ''
			);
			$page_secure = wp_insert_post($secure_data);
			if ($page_secure)
				update_option('ims_page_secure', $page_secure);
		}
		
		//create cart page
		$page_cart = get_option('ims_page_cart');
		if (empty($page_cart)) {
			$cart_data = array(
				'ID' => false,
				'post_type' => 'page',
				'ping_status' => 'closed',
				'post_status' => 'publish',
				'comment_status' => 'closed',
				'post_author' => $this->userid,
				'post_title' => __('Cart', 'ims'),
				'post_content' => '[image-store cart=1]',
				'post_excerpt' => ''
			);
			$page_cart = wp_insert_post($cart_data);
			if ($page_cart)
				update_option('ims_page_cart', $page_cart);
		}

		//create galleries page
		$page_gal = get_option('ims_page_galleries');
		if (empty($page_gal)) {
			$gallery_data = array(
				'ID' => false,
				'post_title' => 'Image Store',
				'post_type' => 'page',
				'ping_status' => 'closed',
				'post_status' => 'publish',
				'comment_status' => 'closed',
				'post_author' => $this->userid,
				'post_content' => '[image-store]',
				'post_excerpt' => ''
			);
			$page_gal = wp_insert_post($gallery_data);
			if ($page_gal)
				update_option('ims_page_galleries', $page_gal);
		}
	}
	
	/**
	 * Setup default print finishes,
	 * color and shipping options
	 *
	 * @return void
	 * @since 3.1.0 
	 */
	function ims_xtra_pricing_opts(){
		//finishes
		$sizes = array(
			array('name' => __( 'None', 'ims'), 'price' => '0', 'type' => 'amount' ),
			array('name' => __( 'Matte', 'ims'), 'price' => '2.95', 'type' => 'amount' ),
			array('name' => __( 'Glossy', 'ims'), 'price' => '3.45', 'type' => 'amount' ),
			array('name' => __( 'Semi-gloss', 'ims'), 'price' => '3', 'type' => 'percent' ),
			array('name' => __( 'Metallic', 'ims'), 'price' => '2', 'type' => 'percent' ),
			array('name' => __( 'Lustre', 'ims'), 'price' => '5', 'type' => 'percent' ),
			array('name' => __( 'Texture', 'ims'), 'price' => '7', 'type' => 'percent' ),
		);
		update_option('ims_print_finishes', $sizes);
		
		//colors
		$colors = array(
			array( 'price' => '0', 'code' =>false, 'name' => __( 'Color', 'ims')),
			array( 'price' => '1.00', 'code' =>'bw','name' => __( 'BW', 'ims')),
			array( 'price' => '1.00', 'code' =>'sp','name' => __('Sepia', 'ims')),
			array( 'price' => '1.00', 'code' =>'gn','name' => __('Green Tone', 'ims'))
		);
		update_option('ims_color_options', $colors);
		
		//filters
		$filters = array(
			'bw' => array( 'code' =>'bw','name' => __('BW', 'ims'),'grayscale'=>1, 'contrast'=>'', 'brightness'=>'+10','colorize'=>''),
			'sp' => array( 'code' =>'sp','name' => __('Sepia', 'ims'),'grayscale'=>1,'contrast'=>'', 'brightness'=>'+5','colorize'=>'35,25,10'),
			'gn' => array( 'code' =>'gn','name' => __('Green Tone', 'ims'),'grayscale'=>'', 'contrast'=>'-5','brightness'=>'','colorize'=>'93,111,38,80')
		);
		update_option('ims_color_filters', $filters);
		
		//shipping
		$shipping =  array(
			array( 'price' => '3.00', 'name' => __('Local', 'ims')),
			array( 'price' => '20.00', 'name' => __('International', 'ims')),
		);
		update_option('ims_shipping_options', $shipping);
	}

	/**
	 * Setup the default price and lists 
	 * and image sizes
	 *
	 * @return void
	 * @since 2.0.0 
	 */
	function price_lists() {

		// default image sizes
		$sizes = array(
			array('name' => '4x6', 'price' => '4.95', 'unit' => 'in', 'type' => 'p'),
			array('name' => '8x10', 'price' => '15.90', 'unit' => 'in', 'type' => 'p'),
			array('name' => '11x14', 'price' => '25.90', 'unit' => 'in', 'type' => 'p'),
			array('name' => '16X20', 'price' => '64.75', 'unit' => 'in', 'type' => 'p'),
			array('name' => '20x24', 'price' => '88.30', 'unit' => 'in', 'type' => 'p'),
			array('name' => '2.5x3.5', 'price' => '1.25', 'unit' => 'in', 'type' => 'p'),
			array('name' => '600x600', 'w' => 600, 'h' => 600, 'price' => '1.25', 'unit' => 'px', 'type' => 'd'),
		);
		update_option('ims_sizes', $sizes);


		// price list
		$price_list = array(
			'ID' => false,
			'post_status' => 'publish',
			'post_type' => 'ims_pricelist',
			'post_title' => __('Default Price List', 'ims'),
			'sizes' => array(
				array('name' => '4x6', 'price' => '4.95', 'unit' => 'in', 'type' => 'p'),
				array('name' => '8x10', 'price' => '15.90', 'unit' => 'in', 'type' => 'p'),
				array('name' => '11x14', 'price' => '25.90', 'unit' => 'in', 'type' => 'p'),
				array('name' => '16X20', 'price' => '64.75', 'unit' => 'in', 'type' => 'p'),
				array('name' => '20x24', 'price' => '88.30', 'unit' => 'in', 'type' => 'p'),
				array('name' => '600x600', 'w' => 600, 'h' => 600, 'price' => '1.25', 'unit' => 'px', 'type' => 'd'),
			),
			
			//list options
			'options' => array(
				'finishes' => array( ),
				//colors
				'colors' =>array(
					array( 'price' => '0', 'name' => __( 'Color', 'ims')),
					array( 'price' => '1.00', 'name' => __( 'BW', 'ims')),
					array( 'price' =>  '1.00', 'name' => __('Sepia', 'ims')),
				),
			));


		// packages
		$packages = array(
			array('ID' => false, 'post_title' => __('Package 1', 'ims'), 'post_type' => 'ims_package', 'post_status' => 'publish',
				'_ims_price' => '35.00', '_ims_sizes' => array(
					'8x10' => array('unit' => 'in', 'count' => 1),
					'5x7' => array('unit' => 'in', 'count' => 1),
					'2.5x3.5' => array('unit' => 'in', 'count' => '8'))
			),
			array('ID' => false, 'post_title' => __('Package 2', 'ims'), 'post_type' => 'ims_package', 'post_status' => 'publish',
				'_ims_price' => '47.10', '_ims_sizes' => array(
					'8x10' => array('unit' => 'in', 'count' => 1),
					'5x7' => array('unit' => 'in', 'count' => 2),
					'2.5x3.5' => array('unit' => 'in', 'count' => 16))
			),
			array('ID' => false, 'post_title' => __('Package 3', 'ims'), 'post_type' => 'ims_package', 'post_status' => 'publish',
				'_ims_price' => '58.85', '_ims_sizes' => array(
					'8x10' => array('unit' => 'in', 'count' => 2),
					'5x7' => array('unit' => 'in', 'count' => 2),
					'2.5x3.5' => array('unit' => 'in', 'count' => 16))
			),
			array('ID' => false, 'post_title' => __('Wallets', 'ims'), 'post_type' => 'ims_package', 'post_status' => 'publish',
				'_ims_price' => '15.90', '_ims_sizes' => array('2.5x3.5' => array('unit' => 'in', 'count' => 8)))
		);

		//fix insallation headers already sent wit multisites
		if (is_multisite()) {
			global $wp_post_types;
			$obj = new stdClass();
			$obj->name = false;
			$obj->public = false;
			$obj->_builtin = false;
			$obj->hierarchical = false;
			$wp_post_types['ims_pricelist'] = $obj;
			$wp_post_types['ims_package'] = $obj;
		}

		//update package information
		foreach ($packages as $package) {
			$package_id = wp_insert_post($package);
			if (!$package_id)
				continue;
			$price_list['sizes'][] = array('ID' => $package_id, 'name' => $package['post_title']);
			update_post_meta($package_id, '_ims_price', $package['_ims_price']);
			update_post_meta($package_id, '_ims_sizes', $package['_ims_sizes']);
		}

		$price_list = apply_filters('ims_default_pricelists', $price_list);

		$list_id = wp_insert_post($price_list);
		update_option('ims_pricelist', $list_id);

		if (empty($list_id))
			return;
			
		update_post_meta($list_id, '_ims_list_opts', $price_list['options']);
		update_post_meta($list_id, '_ims_sizes', $price_list['sizes']);

		if (is_multisite()) {
			unset($wp_post_types['ims_pricelist']);
			unset($wp_post_types['ims_package']);
		}
	}

	/**
	 * Uninstall all settings
	 *
	 * @return void
	 * @since 0.5.0 
	 */
	function imstore_uninstall() {
		global $wpdb, $wp_rewrite;

		if (!current_user_can('edit_plugins') || !current_user_can('ims_change_settings'))
			return;

		do_action('ims_before_uninstall');

		//remove scheduled_hook for expire galleries
		wp_clear_scheduled_hook('ims_expire');

		//delete manager pages
		wp_delete_post(get_option('ims_page_cart'), true);
		wp_delete_post(get_option('ims_page_secure'), true);
		wp_delete_post(get_option('ims_page_galleries'), true);

		//delete database version
		delete_option('imstore_version');

		//delete image sizes
		delete_option('mini_crop');
		delete_option('mini_size_w');
		delete_option('mini_size_h');
		delete_option('mini_size_q');

		delete_option('preview_crop');
		delete_option('preview_size_w');
		delete_option('preview_size_h');
		delete_option('preview_size_q');

		//Remove capabilities from user roles
		$role = get_role('administrator');
		$userops = get_option('ims_user_options');
		$role = ( empty($role) ) ? get_role('editor') : $role;
		foreach ($userops['caplist'] as $cap => $capname)
			$role->remove_cap('ims_' . $cap);

		//remove all options
		$ims_ops = get_option('ims_options');
		foreach ((array) $ims_ops as $ims_op)
			delete_option($ims_op);

		//deactivate plugin
		deactivate_plugins( IMSTORE_FILE_NAME, false, is_network_admin() );

		//delete posts/galleries/pricelist/reports
		$wpdb->query("DELETE FROM $wpdb->posts WHERE post_type IN( 'ims_package', 'ims_pricelist', 'ims_gallery', 'ims_order', 'ims_promo' )");

		//hand over the images to wp media gallery
		$wpdb->query("UPDATE $wpdb->posts SET post_type = 'attachment', post_parent = 0, post_status = 'inherit' WHERE post_type IN( 'ims_image' )");

		//delete post metadata
		$wpdb->query("
			DELETE FROM $wpdb->postmeta WHERE meta_key 
			IN( '_ims_list_opts', '_ims_sizes', '_ims_price', '_ims_folder_path', '_ims_price_list', '_ims_gallery_id', '_ims_sortby', 
				 '_ims_order', '_ims_customer', '_ims_image_count', 'ims_download_max', '_ims_tracking', '_ims_visits', '_ims_promo_count',
				 'ims_downloads', '_ims_favorites', '_ims_order_data', '_ims_promo_data', '_ims_promo_code', '_response_data', 
				 'ims_visits', 'ims_tracking'
			 ) "
		);

		//delete user metadata
		$wpdb->query(
			"DELETE FROM $wpdb->usermeta WHERE meta_key 
			 IN( 'ims_user_caps', 'ims_customers_per_page', 'ims_galleries_per_page', 'ims_address', 
				 'ims_city', 'ims_phone', 'ims_state', 'ims_zip', '_ims_favorites'
			 )"
		);

		//optomize wp tables
		$optimize = apply_filters('ims_optimize', true);
		if ($optimize)
			$wpdb->query("OPTIMIZE TABLE $wpdb->options, $wpdb->postmeta, $wpdb->posts, $wpdb->users, $wpdb->usermeta");

		//delete expire table
		$wpdb->query("ALTER TABLE $wpdb->posts DROP post_expire");

		//destroy active cookies
		setcookie('ims_orderid_' . COOKIEHASH, ' ', ( time() - 31536000), COOKIEPATH, COOKIE_DOMAIN);
		setcookie('imstore_galleryid' . COOKIEHASH, ' ', ( time() - 31536000), COOKIEPATH, COOKIE_DOMAIN);
		
		//clean rewrite rules
		$wp_rewrite->flush_rules();
			
		do_action('ims_after_uninstall');

		//redirect user
		wp_redirect(admin_url('plugins . php?deactivate=true'));
		die();
	}

}
new ImStoreInstaller( );