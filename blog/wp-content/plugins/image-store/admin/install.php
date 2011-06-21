<?php 

/**
 *Intall add options
 *
 *@package Image Store
 *@author Hafid Trujillo
 *@copyright 20010-2011
 *@since 0.5.0
*/

// Stop direct access of the file
if(preg_match('#'.basename(__FILE__).'#',$_SERVER['PHP_SELF'])) die();
if(!current_user_can('activate_plugins')) die();
	
class ImStoreInstaller{
	
	/**
	 *Constructor
	 *
	 *@return void
	 *@since 0.5.0 
	*/
	function __construct(){
		$this->version = get_option('imstore_version');
		
		if(empty($this->version)) $this->imstore_default_options();
		if(ImStore::version > $this->version || empty($this->version)) $this->update();
		
		$price_list = get_option('ims_pricelist');
		if(empty($price_list)) $this->price_lists();
	}
	
	/**
	 *Set use permission/roles
	 *and add defult price list,image sizes
	 *
	 *@return void
	 *@since 2.0.0 
	*/
	function update(){
		global $wpdb;
		
		wp_cache_flush();
		if($this->version < "2.0.0"){
			//update_option('ims_ipn_number',rand(1,10000));
			$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key 
			IN('ims_downloads','ims_download_max','_ims_image_count','_ims_customer')");
			$wpdb->query("UPDATE $wpdb->postmeta SET meta_key = '_ims_visits' WHERE meta_key = 'ims_visits'");
			$wpdb->query("UPDATE $wpdb->postmeta SET meta_key = '_ims_tracking' WHERE meta_key = 'ims_tracking'");
			$wpdb->query("UPDATE $wpdb->posts SET post_content = '[ims-gallery-content]' WHERE post_type = 'ims_gallery'");
		}
		
		if($this->version <= "2.0.8"){
			$ims_ft_opts = get_option('ims_front_options');
		  	$ims_ft_opts['tags'][]			= __('/%instructions%/',ImStore::domain);
			$ims_ft_opts['album_template']	= 'page.php';
		  	update_option('ims_front_options',$ims_ft_opts);
		}
		
		$ims_caps = array(
			'ims_add_galleries','ims_read_sales','ims_manage_galleries','ims_change_pricing',
			'ims_change_settings','ims_manage_customers','ims_change_permissions',
		);
				
		$ims_user_opts['swfupload']		= '1';
		$ims_user_opts['caplist']		= $ims_caps;
		update_option('ims_user_options',$ims_user_opts);
		
		//assign caps to adminstrato if not,to the editor
		$role = get_role('administrator');
		if(empty($role)) $role = get_role('editor');
		foreach($ims_caps as $imscap) $role->add_cap($imscap);
		
		$customer = get_role('customer');
		if(empty($customer)) add_role('customer','Customer',array('read' => 1,'ims_read_galleries' => 1));

		//create secure page
		$page_secure = get_option('ims_page_secure');
		if(empty($page_secure)){
			$secure_data = array(
				'post_title'	=> 'Secure Images',
				'post_type' 	=> 'page',
				'post_author' 	=> $user_ID,
				'ping_status' 	=> 'closed',
				'post_status' 	=> 'publish',
				'comment_status'=> 'closed',
				'post_content' 	=> '[image-store secure=1]',
				'post_excerpt' 	=> '' 
			);
			$page_secure = wp_insert_post($secure_data);
			update_option('ims_page_secure',$page_secure);
		}
		
		//create gallery page
		$page_gal = get_option('ims_page_galleries');
		if(empty($page_gal)){
			$gallery_data = array(
				'post_title'	=> 'Image Store',
				'post_type' 	=> 'page',
				'post_author' 	=> $user_ID,
				'ping_status' 	=> 'closed',
				'post_status' 	=> 'publish',
				'comment_status'=> 'closed',
				'post_content' 	=> '[image-store]',
				'post_excerpt' 	=> '' 
			);
			$page_gal = wp_insert_post($gallery_data);
			update_option('ims_page_galleries',$page_gal);
		}
				
		//add expire column
		$wpdb->query("ALTER TABLE $wpdb->posts ADD post_expire DATETIME NOT NULL");
		
		//store options to unistall plugin
		update_option('imstore_version',ImStore::version);
		update_option('ims_options',array('ims_front_options','ims_back_options','ims_page_secure', 'ims_searchable',
			'ims_pricelist','ims_options','ims_page_galleries','ims_sizes','ims_download_sizes','ims_dis_images','ims_user_options'));

	}
	
	
	/**
	 *Setup the default option array 
	 *Create required categores
	 *
	 *@return void
	 *@since 0.5.0 
	*/
	function imstore_default_options(){
		global $wpdb,$user_ID;
		
		//FRONTEND OPTIONS
		$ims_ft_opts['numThumbs']		= 8;
		$ims_ft_opts['maxPagesToShow']	= 5;
		$ims_ft_opts['transitionTime']	= 1000;
		$ims_ft_opts['slideshowSpeed']	= 3200;
		$ims_ft_opts['autoStart']		= 'false';
		$ims_ft_opts['playLinkText']	= __('Play',ImStore::domain);
		$ims_ft_opts['nextLinkText']	= __('Next',ImStore::domain);
		$ims_ft_opts['pauseLinkTex']	= __('Pause',ImStore::domain);
		$ims_ft_opts['closeLinkText']	= __('Close',ImStore::domain);
		$ims_ft_opts['prevLinkText']	= __('Previous',ImStore::domain);
		$ims_ft_opts['nextPageLinkText']= __('Next &rsaquo;',ImStore::domain);
		$ims_ft_opts['prevPageLinkText']= __('&lsaquo; Prev',ImStore::domain);
			
		$ims_ft_opts['galleriespath']	= '/_imsgalleries';
		$ims_ft_opts['fontsize']		= '12';
		$ims_ft_opts['transperency']	= '90';
		$ims_ft_opts['textcolor']		= 'ffffff';
		$ims_ft_opts['imgsortdirect']	= 'ASC';
		$ims_ft_opts['imgsortorder']	= 'menu_order';
		$ims_ft_opts['taxtype']			= 'percent';
		$ims_ft_opts['mediarss']		= '1';
		$ims_ft_opts['colorbox']		= '1';
		$ims_ft_opts['swfupload']		= '1';
		$ims_ft_opts['stylesheet']		= '1';
		$ims_ft_opts['deletefiles']		= '1';
		$ims_ft_opts['sameasbilling']	= '1';
		$ims_ft_opts['securegalleries']	= '1';
		$ims_ft_opts['watermark']		= '0';
		$ims_ft_opts['galleryexpire']	= '60';
		$ims_ft_opts['symbol']			= '&#036;';
		$ims_ft_opts['clocal']			= '1';
		$ims_ft_opts['currency']		= 'USD';
		$ims_ft_opts['gateway']			= 'paypalsand';
		$ims_ft_opts['album_template']	= 'page.php';
		$ims_ft_opts['paymentname']		= 'Pay by check';
		$ims_ft_opts['watermarktext']	= get_option('blogname');
		$ims_ft_opts['notifyemail']		= get_option('admin_email');
		$ims_ft_opts['notifysubj']		= __('New purchase notification',ImStore::domain);
		$ims_ft_opts['thankyoureceipt']	= sprintf(__("<h2>Thank You,%%customer_first%% %%customer_last%%</h2>\nsave the information bellow for your records.\n\nTotal payment:%%total%%\nTransaction number:%%order_number%%\n\nIf you have any question about your order please contat us at:%s",ImStore::domain),get_option('admin_email'));
		$ims_ft_opts['notifymssg']		= sprintf(__("A new order was place at you image store at %s \n\nOrder number: %%order_number%% \nTo view the order details please login to your site at:%s  \n\n%%instructions%%",ImStore::domain),get_option('blogname'),wp_login_url());	
		
		//dont change array order
		$ims_ft_opts['tags'] 			= array(
											 __('/%total%/',ImStore::domain),
											 __('/%status%/',ImStore::domain),
											 __('/%gallery%/',ImStore::domain),
											 __('/%shipping%/',ImStore::domain),
											 __('/%tracking%/',ImStore::domain),
											 __('/%gallery_id%/',ImStore::domain),
											 __('/%order_number%/',ImStore::domain),
											 __('/%customer_last%/',ImStore::domain),
											 __('/%customer_first%/',ImStore::domain),
											 __('/%customer_email%/',ImStore::domain),
											 __('/%instructions%/',ImStore::domain),
										);
		
		$ims_ft_opts['requiredfields']	= array('user_email','address_street','address_zip','first_name'); 
		$ims_ft_opts['checkoutfields'] 	= array(
										'address_city'	=> __('City',ImStore::domain),
										'address_state'	=> __('State',ImStore::domain),
										'user_email'	=> __('Email',ImStore::domain),
										'ims_phone'		=> __('Phone',ImStore::domain),
										'address_street'=> __('Address',ImStore::domain),
										'address_zip'	=> __('Zip Code',ImStore::domain),
										'last_name'		=> __('Last Name',ImStore::domain),
										'first_name'	=> __('First Name',ImStore::domain),
		);
		
		$ims_dis_img['mini'] 			= array('name' => 'mini','w' => 70,'h' => 60,'q' => 95,'crop' => 1);
		$ims_dis_img['preview']			= array('name' => 'preview','w' => 380,'h' => 380,'q' => 80,'crop' => 0);
		
		update_option('mini_crop',1);
		update_option('mini_size_w',70);
		update_option('mini_size_h',60);
		
		update_option('preview_crop',1);
		update_option('preview_size_w',380);
		update_option('preview_size_h',380);
		update_option('preview_size_q',80);
		
		update_option('ims_dis_images',$ims_dis_img);
		update_option('ims_front_options',$ims_ft_opts);

		
		//optomize wp tables
		$wpdb->query("OPTIMIZE TABLE $wpdb->options,$wpdb->postmeta,$wpdb->posts,$wpdb->users,$wpdb->usermeta");
	}
	
	/**
	 *Setup the default price and lists 
	 *and image sizes
	 *
	 *@param $update bool
	 *@return void
	 *@since 2.0.0 
	*/
	function price_lists(){
		
		// default image sizes
		$sizes = array(
			array('name' => '4x6','price' => '4.95','unit' => 'in'),
			array('name' => '8x10','price' => '15.90','unit' => 'in'),
			array('name' => '11x14','price' => '25.90','unit' => 'in'),
			array('name' => '16X20','price' => '64.75','unit' => 'in'),
			array('name' => '20x24','price' => '88.30','unit' => 'in'),
			array('name' => '2.5x3.5','price' => '1.25','unit' => 'in'),
		); update_option('ims_sizes',$sizes);	
		
		// price list
		$price_list = array(
			'ID' => $list_id,
			'post_status'=> 'publish',
			'post_type' => 'ims_pricelist',
			'post_title' => __('Default Price List',ImStore::domain),
			'sizes'	=> array(
				array('name' => '4x6','price' => '4.95','unit' => 'in'),
				array('name' => '8x10','price' => '15.90','unit' => 'in'),
				array('name' => '11x14','price' => '25.90','unit' => 'in'),
				array('name' => '16X20','price' => '64.75','unit' => 'in'),
				array('name' => '20x24','price' => '88.30','unit' => 'in')
			),
			'options' => array(
				'ims_bw' => '1.00',
				'ims_sepia' => '1.00',
				'ims_ship_local' => '3.00',
				'ims_ship_inter' => '20.00',
		));
		
		// packages
		$packages = array(
			array('post_title' => __('Package 1',ImStore::domain),'post_type' => 'ims_package','post_status' => 'publish',
			'_ims_price' => '35.00','_ims_sizes' => array(
				'8x10' => array('unit' => 'in','count' => 1),
				'5x7' => array('unit' => 'in','count' => 1),
				'2.5x3.5' => array('unit' => 'in','count' => '8')) 
			),
			array('post_title' => __('Package 2',ImStore::domain),'post_type' => 'ims_package','post_status' => 'publish',
			'_ims_price' => '47.10','_ims_sizes' => array(
				'8x10' => array('unit' => 'in','count' => 1),
				'5x7' => array('unit' => 'in','count' => 2),
				'2.5x3.5' => array('unit' => 'in','count' => 16)) 
			),
			array('post_title' => __('Package 3',ImStore::domain),'post_type' => 'ims_package','post_status' => 'publish',
			'_ims_price' => '58.85','_ims_sizes' => array(
				'8x10' => array('unit' => 'in','count' => 2),
				'5x7' => array('unit' => 'in','count' => 2),
				'2.5x3.5' => array('unit' => 'in','count' => 16)) 
			),
			array('post_title' => __('Wallets',ImStore::domain),'post_type' => 'ims_package','post_status' => 'publish',
			'_ims_price' => '15.90','_ims_sizes' => array('2.5x3.5' => array('unit' => 'in','count' => 8)))
		);
		
		foreach($packages as $package){
			$package_id = wp_update_post($package);
			if(!$package_id) continue;
			$price_list['sizes'][] = array('ID' => $package_id,'name' => $package['post_title']);
			update_post_meta($package_id,'_ims_price',$package['_ims_price']);
			update_post_meta($package_id,'_ims_sizes',$package['_ims_sizes']);
		}
		
		$list_id = wp_update_post($price_list);
		update_option('ims_pricelist',$list_id);
					
		if(empty($list_id)) return; 
		update_post_meta($list_id,'_ims_list_opts',$price_list['options']);
		update_post_meta($list_id,'_ims_sizes',$price_list['sizes']);
	
	}
	
	/**
	 *Uninstall all settings
	 *
	 *@return void
	 *@since 0.5.0 
	*/
	function imstore_uninstall(){
		global $wpdb;
		
		if(!current_user_can('edit_plugins') || !current_user_can('ims_change_settings'))
			return;

		//remove Image Store caps from roles
		//self::remove_imstore_caps();
		
		//remove scheduled_hook for expire galleries
		wp_clear_scheduled_hook('ims_expire');
		
		//delete manager pages
		wp_delete_post(get_option('ims_page_secure'),true);
		wp_delete_post(get_option('ims_page_galleries'),true);
		
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
		
		//remove all options
		$ims_ops = get_option('ims_options');
		foreach((array)$ims_ops as $ims_op) delete_option($ims_op);
		 
		//deactivate plugin
		$active_plugins = get_option('active_plugins');
		if($key = array_search(IMSTORE_FILE_NAME,$active_plugins));
			unset($active_plugins[$key]);
		update_option('active_plugins',$active_plugins);
		
		//delete posts/galleries/pricelist/reports
		$wpdb->query("DELETE FROM $wpdb->posts WHERE post_type IN('ims_package','ims_pricelist','ims_gallery','ims_order','ims_promo')");
		
		//hand over the images to wp media gallery
		$wpdb->query("UPDATE $wpdb->posts SET post_type = 'attachment',post_parent = 0,post_status = 'inherit' WHERE post_type IN('ims_image')");
		
		//delete post metadata
		$wpdb->query("
			DELETE FROM $wpdb->postmeta WHERE meta_key 
			IN('_ims_list_opts','_ims_sizes','_ims_price','_ims_folder_path','_ims_price_list','_ims_gallery_id','_ims_sortby',
				 '_ims_order','_ims_customer','_ims_image_count','ims_download_max','_ims_tracking','_ims_visits',
				 'ims_downloads','_ims_favorites','_ims_order_data','_ims_promo_data','_ims_promo_code','_response_data',
				 'ims_visits','ims_tracking'
			) " 
		);
		
		//delete user metadata
		$wpdb->query(
			"DELETE FROM $wpdb->usermeta WHERE meta_key 
			 IN('ims_user_caps','ims_customers_per_page','ims_galleries_per_page','ims_address',
				 'ims_city','ims_phone','ims_state','ims_zip','_ims_favorites',
			)" 
		);

		//optomize wp tables
		$wpdb->query("OPTIMIZE TABLE $wpdb->options,$wpdb->postmeta,$wpdb->posts,$wpdb->users,$wpdb->usermeta");
		
		//delete expire table
		$wpdb->query("ALTER TABLE $wpdb->posts DROP post_expire");
		
		//destroy active cookies
		setcookie('ims_orderid_'.COOKIEHASH,' ',time() - 31536000,COOKIEPATH,COOKIE_DOMAIN);
		setcookie('imstore_galleryid'.COOKIEHASH,' ',time() - 31536000,COOKIEPATH,COOKIE_DOMAIN);
		
		//redirect user
		wp_redirect(admin_url().'plugins.php?deactivate=true');
		wp_clear_scheduled_hook('imstore_expire');
	
	}
	
}
new ImStoreInstaller();
?>