<?php 

/**
 *ImStoreFront - Fontend display 
 *
 *@package Image Store
 *@author Hafid Trujillo
 *@copyright 20010-2012
 *@since 0.5.0 
*/

class ImStoreFront extends ImStore{

	/**
	*Public variables
	*/
	public $galid = 0;
	public $gal = false;
	public $error = false;
	public $orderid = false;
	public $success = false;
	public $message = false;
	public $query_id = false;
	public $is_secure = false;
	public $cart_status = false;
	
	public $order = '';
	public $sortby = '';
	
	public $cart = array( );
	public $sizes = array( );
	public $listmeta = array( );
	public $subtitutions = array( );
	public $attachments = array( );
	
	
	/**
	*Constructor
	*
	*@return void
	*@since 3.0.0
	*/
	function ImStoreFront( ){
		
		//speed up wordpress load
		if( defined( 'DOING_AJAX' ) || defined( 'DOING_AUTOSAVE' || defined( 'SHORTINIT')) ) 
			return;
			
		ob_start( ); 
		parent::ImStore( ); 
	 
		$this->page_front	= get_option( 'page_on_front' );
		$this->baseurl = apply_filters( 'ims_base_image_url', IMSTORE_URL .  '/image.php?i='  );
	
		//more speed up actions
		add_action( 'wp', array( $this, 'init_hooks' ),0 );		
		add_shortcode( 'image-store', array( $this, 'imstore_shortcode') );
		
		add_filter( 'get_pagenum_link',array( $this, 'page_link' ));
		add_filter( 'ims_subnav', array( $this, 'ad_favorite_options' ),1,1 );	
		add_filter( 'parse_query', array( $this, 'album_pagination' ),2, 20 );
		add_filter( 'query_vars', array( $this, 'add_var_for_rewrites' ),1,10 );
		add_filter( 'comments_array', array( $this, 'hide_comments' ),2, 20 );
		add_filter( 'comments_open', array( $this, 'close_comments' ),1,1 );	
		add_filter( 'template_include', array( $this, 'taxonomy_template' ),1,50 );
		
		add_filter( 'the_content', array( $this,'ims_image_content'),10 );
		add_filter( 'single_template', array( $this, 'get_image_template' ),10,1 );	
		add_filter('single_template',array($this,'change_gallery_template'),1,50 );
		
		add_filter( 'wp', array( $this, 'secure_images' ),1,50 ); 
		add_filter( 'get_next_post_sort', array( $this, 'adjacent_post_sort' ),20 );
		add_filter( 'get_previous_post_sort', array( $this, 'adjacent_post_sort' ),20); 
		add_filter( 'get_next_post_where', array( $this, 'adjacent_post_where' ),20); 
		add_filter( 'get_previous_post_where', array( $this, 'adjacent_post_where' ),20); 
		
		if( version_compare( $this->wp_version , '3.2', '<' ) )
			add_filter( 'pre_get_posts', array( $this, 'custom_types' ),1,30 ); 
		
		if( isset( $this->opts['ims_searchable'] ) )
			add_filter( 'posts_where', array( $this, 'search_image_info' ),2, 50 );
		
		if( isset( $this->opts['colorbox'] ) && $this->opts['colorbox'] )
			add_action( 'wp_head', array( $this, 'print_ie_styles' ) );
		
		//load image rss
		if( !empty( $this->opts['mediarss'] ) )
			require_once( IMSTORE_ABSPATH . '/_store/image-rss.php' );
	}

	/**
	 *Initiate actions
	 *
	 *@return void
	 *@since 3.0.0
	 */
	function init_hooks( ){
			
		if( !is_feed( ) && !is_tag( ) ) {
			
			$allow = apply_filters ( 'ims_activate_gallery_hooks', false );
			add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts_styles' ) );
			
			add_filter( 'redirect_canonical', array( $this, 'redirect_canonical' ), 20,2 );		
			add_filter( 'protected_title_format', array( $this, 'remove_protected' ) );
			
			if( is_singular() )
				add_filter( 'ims_localize_js', array( $this, 'add_gallerific_js_vars' ), 0 );
	
			if( is_singular( 'ims_gallery') || $allow || isset( $_POST['google-order-number'] ) ){
				add_action( 'get_header', array( $this, 'ims_init') );
				add_filter( 'query_vars', array( $this, 'add_var_for_rewrites' ),1, 10 );
				add_shortcode( 'ims-gallery-content', array( $this, 'ims_gallery_shortcode') );
			}
		}
		require_once( IMSTORE_ABSPATH . '/_store/shortcode.php' );
	}

	/**
	 *Populate object variables
	 *
	 *@return void
	 *@since 2.0.0
	 */
	function ims_init( ){
		global  $wp_rewrite;
		$this->permalinks = $wp_rewrite->using_permalinks();
		
		if( get_query_var('imsmessage') ){
			$messages = array(
				'1' => __('Successfully added to cart', $this->domain),
				'2' => __('Cart successfully updated', $this->domain),
				'3' => __('Your transaction has been cancel!!', $this->domain)
			);
			$this->message	= $messages[get_query_var('imsmessage')];
		}
		
		$googleid = ( isset( $this->opts['googleid'] ) ) ? $this->opts['googleid'] : '' ;
		$this->gateway = array(
			'paypalprod' => 'https://www.paypal.com/cgi-bin/webscr',
			'paypalsand' => 'https://www.sandbox.paypal.com/cgi-bin/webscr',
			'googleprod' => 'https://checkout.google.com/api/checkout/v2/checkoutForm/Merchant/' . $googleid,
			'googlesand' => 'https://sandbox.google.com/checkout/api/checkout/v2/checkoutForm/Merchant/' . $googleid,
		);
				
		//redirect photo disable
		if( !empty( $this->opts['hidephoto'] ) && $this->imspage == 'photos' ) 
			$this->imspage = 'slideshow';

		//add images to cart
		if( isset($_POST['cancelcheckout']) ){
			wp_redirect( $this->get_permalink( 'shopping-cart', false ) );	
			die( );
		}
		
		//logout gallery
		if( get_query_var('imslogout') ){
			$this->logout_ims_user( );
			wp_redirect( get_permalink( get_option('ims_page_secure') ) ); 
			die( );
		}
		
		if( is_singular( 'ims_gallery' ) ){
			global $post;
			$this->gal 				= $post;
			$this->galid			= $this->gal->ID;
			$this->query_id 		= get_query_var( 'ims_gallery' );
			$this->meta 			= get_post_custom( $this->galid );
			if( empty( $this->opts['disablestore'] ) ){
				$this->sizes 		= $this->get_price_list( );
				$this->listmeta 	= get_post_meta( $this->pricelist_id, '_ims_list_opts', true );
			}
		}

		$this->order	= empty( $this->meta['_ims_order'][0] ) ? $this->opts['imgsortdirect'] : $this->meta['_ims_order'][0];
		$this->imspage 	= ( $page = get_query_var( 'imspage') ) ? $page : 'photos';
		
		//apply sort by setting to galleries
		if( empty( $this->meta['_ims_sortby'][0] ) ){
			$this->sortby = $this->opts['imgsortorder'];
		}else{
			if( $this->meta['_ims_sortby'][0] == 'menu_order' )
				$this->sortby = $this->meta['_ims_sortby'][0];
			else
			$this->sortby = "post_" . $this->meta['_ims_sortby'][0];
		}
		
		$orderid = ( isset( $_COOKIE['ims_orderid_'.COOKIEHASH] ) ) 
		? $_COOKIE['ims_orderid_'.COOKIEHASH] : false;
		$this->orderid = ( empty( $orderid ) ) ? false : $orderid;
		
		$this->cart_status	= get_post_status( $this->orderid );
		if( $this->cart_status == "draft" && $this->orderid ) 
			$this->cart = get_post_meta( $this->orderid, '_ims_order_data', true );
		
		do_action( 'ims_gallery_init', $this );
		
		//process paypal IPN 
		if( isset( $_POST['txn_id'] ) && isset( $_POST['custom'] ) && $this->imspage == 'photos' ) 
			include_once( IMSTORE_ABSPATH . '/_store/paypal-ipn.php' );
		
		//process google notification
		if( isset( $_POST['google-order-number'] ) && isset( $_POST['shopping-cart_merchant-private-data'] )  )
			include_once( IMSTORE_ABSPATH . '/_store/google-notice.php');
			
		//checkoutims_gallery_shortcode
		if( isset( $_POST['checkout']) )
			 $this->redirect_form_post_data( );
		
		//checkoutims_gallery_shortcode
		if( isset( $_GET['checkout']) && $this->opts['gateway'] == 'custom' )
			 $this->redirect_form_post_data( );
		
		//add images to cart
		if( isset( $_POST['add-to-cart']) )
			$this->add_to_cart( );
		
		//upate cart
		if( isset( $_POST['apply-changes']) )
			$this->update_cart( );
		
		//checkout email notification only get user info	
		if( isset( $_POST['enotification'])){
			$this->success = false;
			$this->message = false;
			$this->imspage = 'checkout'; 
		}
		
		//submit notification order
		if( isset($_POST['enoticecheckout']) ){
			$this->success = false;
			$this->message = false;
			$this->imspage = 'checkout'; 
			$this->validate_user_input( );
		}
	}
	
	/**
	*Return 404 for secure images
	*if the user is not loged in
	*
	*@return void
	*@since 3.0.5
	*/
	function secure_images(  ){
		global $post;

		if( is_singular('ims_gallery') && get_query_var( 'imspage')
		&& get_post_meta( $post->ID, '_dis_store', true )  ){
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );	 
		}
		
		if( 	!is_singular( 'ims_image' ) )
			return; 
		
		$this->gal = get_post( $post->post_parent );
		
		if( !empty( $this->gal->post_password ) && ( empty( $_COOKIE['wp-postpass_'.COOKIEHASH] )
		|| $this->gal->post_password !=  $_COOKIE['wp-postpass_'.COOKIEHASH] )){
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
		}
	}
	
	/**
	*Stop canonical redirect for
	*Custom permalink structure
	*
	*@return void
	*@since 0.5.0 
	*/
	function redirect_canonical( $redirect_url, $requested_url ){		
		if( strpos( $requested_url, "/page/" ) 
			&& is_singular( 'ims_gallery' ) )
			return false;
		return $redirect_url;
	}

	/**
	 * Send post data to a url
	 *
	 * @return void
	 * @since 0.5.0 
	 */
	function redirect_form_post_data( ){
		
		if( !wp_verify_nonce( $_REQUEST["_wpnonce"], "ims_submit_order") ) {
			wp_redirect( $this->get_permalink( 'shopping-cart', false ) );	
			die( );
		}
		
		if( empty($this->cart) || empty($this->orderid) || $this->cart_status != 'draft'){
			$this->error .= __('Your shopping cart is empty.', $this->domain );
			return;
		}

		$req = ''; $key = 'amount_'; $i = 1; $total = 0; $qkey = 'quantity_';
		if( $this->in_array( $this->opts['gateway'] , array( 'googlesand', 'googleprod' )) ){
			$key = 'item_price_'; $qkey = 'item_quantity_';
		}
				
		//validate post data
		if( !empty( $this->cart['discounted'] ) ){
			$total = $this->cart['discounted'];
		}elseif( $this->opts['gateway_method'] ==  'get' && !empty( $_GET ) ){
			while( isset( $_GET[$key.$i]) ){
				$total += ( $_GET[$key.$i] * $_GET[$qkey.$i] );
				$i++;
			}
		}else{
			while( isset($_POST[$key.$i]) ){
				$total += ( $_POST[$key.$i] * $_POST[$qkey.$i] );
				$i++;
			}
		}
		
		if( empty( $this->cart['discounted'] ) )
			$total += $this->cart['shipping'];
			
		if( isset( $this->cart['tax'] ))
			$total += $this->cart['tax'];
		echo "<!--  $total -->";
		 
		if( $total != $this->cart['total'] ){
			$this->error .= __('There was a problem processing the cart.', $this->domain );
			return;
		}
		
		foreach( $_GET as $k => $v ){
			if(!is_array($v)) $req .= "&$k=" . urlencode($v);
		}
		
		if( $this->opts['gateway'] == 'custom' && empty( $this->opts['gateway_url']  ) )
			return;
		
		$this->cart['tracking'] 	= get_post_meta( $this->galid, 'ims_tracking', true);
		$this->cart['gallery_id'] 	= get_post_meta( $this->galid, '_ims_gallery_id', true);
		$this->cart['instructions'] = isset($_POST['instructions']) ? $_POST['instructions'] : '';
		
		update_post_meta( $this->orderid, '_ims_order_data', $this->cart );
		$url =  ( $this->opts['gateway'] == 'custom'  ) ?  $this->opts['gateway_url'] . "?$req" : $this->gateway[$this->opts['gateway']];
		
		header( "Content-Length: ". strlen($req) ."\r\n");
		header( "Location: " . $url . "\r\n", true, 307 );
		header( "Content-Type: application/x-www-form-urlencoded\r\n" );
		exit( );
	}
	
	/**
	 *Validate user input from 
	 *shipping information
	 *
	 *@since 1.0.2
	 *return array|errors
	 */
	function validate_user_input( ){
		
		if( !wp_verify_nonce( $_POST["_wpnonce"], "ims_submit_order") ) {
			wp_redirect( $this->get_permalink( 'checkout', false ) );	
			die( );
		}
		
		$this->cart['instructions'] = isset($_POST['instructions']) ? $_POST['instructions'] : '';
		
		foreach( $this->opts['checkoutfields'] as $key => $label ){
			if( isset( $this->opts['required_' . $key]  ) && empty( $_POST[$key] ))
			$this->error .= sprintf( __('The %s is required.', $this->domain), $label ) . "<br />";
		}

		if( !empty($_POST['user_email']) && !is_email($_POST['user_email']) )
			$this->error .= __( 'Wrong email format.', $this->domain ) . "<br />";
		
		if( empty($this->cart) || empty($this->orderid) || $this->cart_status != 'draft')
			$this->error .= __('Your shopping cart is empty.', $this->domain );
		
		if(!empty($this->error)) 
			return;
			
		$data['custom'] = $this->orderid;
		$data['mc_gross'] = $this->cart['total'];
		$data['mc_currency'] = $this->opts['currency'];
		$data['num_cart_items'] = $this->cart['items'];
		$data['txn_id'] = sprintf( "%017d" , $this->orderid );
		$data['payment_status'] = __( 'Pending', $this->domain );
		$data['payment_gross'] = number_format( $this->cart['total'], 2);

		wp_update_post(array(
			'post_expire' => '0', 'ID' => $this->orderid,
			'post_status' => 'pending', 'post_date' => current_time('timestamp') 
		));
			
		update_post_meta( $this->orderid, '_ims_order_data', $this->cart );
		update_post_meta( $this->orderid, '_response_data', array_merge( $data, $_POST ) );
		
		//dont change array order
		$this->subtitutions = array(
			$data['mc_gross'], $data['payment_status'], get_the_title( $this->orderid ),
			$this->cart['mc_shipping'], $this->cart['tracking'], $this->cart['gallery_id'], $data['txn_id'],
			$_POST['last_name'], $_POST['first_name'], $_POST['user_email'],
		);
		
		do_action( 'ims_after_checkout', $this->cart );
		
		//create/update customer
		if( is_user_logged_in( ) && current_user_can('customer') ){
			global $user_ID;
			$new_user = array(
				'ID' 					=> $user_ID,
				'user_email' 	=> $_POST['user_email'],
				'first_name' 	=> $_POST['first_name'],
				'last_name' 	=> $_POST['last_name'],
			); wp_update_user( $new_user );
			
			foreach( $this->opts['checkoutfields'] as $key => $label ){
				if( isset( $_POST[$key]) ) 
					update_user_meta( $user_ID, $key, $_POST[$key] );
			}
		}
		
		$to 			= $this->opts['notifyemail'];
		$subject 	= $this->opts['notifysubj'];
		$message 	= preg_replace( $this->opts['tags'] , $this->subtitutions, $this->opts['notifymssg'] );
		$headers 	= 'From: "Image Store" <imstore@'.$_SERVER['HTTP_HOST'].">\r\n";
		wp_mail( $to, $subject, $message, $headers );
		
		$this->imspage = 'receipt';
	}
	
	/**
	 *Display albums(taxonomy)
	 *
	 *@param obj $query 
	 *@return void
	 *@since 3.0.0
	 */
	function custom_types( &$query ){
		$types = get_query_var( 'post_type' );
		if( (!is_archive( ) && empty( $query->query_vars['post_type'] ) ) ||
			( isset( $query->query_vars['post_type'] ) && $query->query_vars['post_type'] == 'nav_menu_item' ) )
				return $query;
	 	$query->set( 'post_type', get_post_types( array( 'publicly_queryable' => true ) ) );
	}
	
	/**
	 *Print IE styles
	 *needed for colorbox
	 *
	 *@return void
	 *@since 0.5.2 
	 */
	function print_ie_styles( ){
		if( isset($_SERVER['HTTP_USER_AGENT']) && 
		( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false && !defined( 'JQUERYCOLORBOX_NAME')) )
			echo '<!--[if IE]><link rel="stylesheet" id="colorboxie-css" href="' . IMSTORE_URL . '/_css/colorbox.ie.php?url=' .
			IMSTORE_URL . '&amp;ver=' . $this->version.'" type="text/css" media="all" /><![endif]-->';
	}
	
	/**
	 *Load frontend js/css
	 *
	 *@return void
	 *@since 0.5.0
	 */
	function load_scripts_styles( ){
		
		if( !empty( $this->opts['stylesheet'] ) ) 
			wp_enqueue_style( 'imstore', IMSTORE_URL . '/_css/imstore.css', NULL, $this->version );
		
		if( !empty( $this->opts['colorbox'] ) || !empty( $this->opts['wplightbox'] ) ){
			if( !defined( 'JQUERYCOLORBOX_NAME') )
				wp_enqueue_style( 'colorbox', IMSTORE_URL . '/_css/colorbox.css',NULL, '1.1.6' );
			wp_enqueue_script( 'colorbox', IMSTORE_URL . '/_js/colorbox.js', array( 'jquery' ), '1.1.6', true ); 
		}
		
		wp_enqueue_script( 'galleriffic', IMSTORE_URL . '/_js/galleriffic.js', array( 'jquery' ), '1.3.6 ', true ); 
		wp_enqueue_script( 'imstorejs', IMSTORE_URL . '/_js/imstore.js', array( 'jquery', 'galleriffic' ), $this->version, true );
		
		$localize = apply_filters( 'ims_localize_js', array(
			'galleriffic'			=> false,
			'galid'					=> $this->galid,	
			'imstoreurl'			=> IMSTORE_ADMIN_URL,	
			'addtocart'				=> __('Add to cart', $this->domain ),
			'attchlink'				=> isset( $this->opts['attchlink'] ) ? $this->opts['attchlink'] : false,			
			'colorbox'				=> isset( $this->opts['colorbox'] ) ? $this->opts['colorbox'] : false,									 
			'wplightbox'			=> isset( $this->opts['wplightbox'] ) ? $this->opts['wplightbox'] : false,
			'ajaxnonce'				=> wp_create_nonce("ims_ajax_favorites")
		) ); wp_localize_script( 'imstorejs', 'imstore', $localize );
	}
	
	/**
	*Add rewrite vars
	*
	*@param array $vars
	*@return array
	*@since 0.5.0 
	*/
	function add_var_for_rewrites( $vars){
		array_push( $vars, 'imspage', 'imsmessage', 'imslogout', 'paypalipn' );
		return $vars;
	}
	
	/**
	 *Remove "protected" 
	 *from gallery title
	 *
	 *@param $title string
	 *@return string
	 *@since 2.0.4
	 */ 
	function remove_protected( $title ){
		global $post;
		if( $post->post_type == 'ims_gallery' ) 
			return $post->post_title;
		return $title;
	}
	
	/**
	 *Change single gallery template
	 *
	 *@param string $template
	 *@return string
	 *@since 2.0.4
	 */
	function change_gallery_template( $template ){
		global $post;
		if( $post->post_type == 'ims_gallery' && !empty( $this->opts['gallery_template'] ) )
			return WP_TEMPLATE_DIR . "/". $this->opts['gallery_template'];
		return $template;
	}
	
	/*
	*
	*/
	function ims_image_content( $content ){
		global $post;
		if( $post->post_type != 'ims_image' )
				return $content;
		
		$tags = apply_filters( 'ims_gallery_tags', array(
			'gallerytag' => 'div', 
			'imagetag' => 'figure', 
			'captiontag' => 'figcaption'
		 ), $this ); extract( $tags );
		
		$next_post =  get_adjacent_post(  false, false, false  ); 
		if( empty( $next_post ) ) {
			$attachments = get_children( array( 
				 'post_parent' => $post->post_parent,
				 'post_status' => 'publish',
				 'post_type' => 'ims_image',
				 'order' => $this->order,
				 'orderby' => $this->sortby ,
				 'numberposts'=> 1,
			)); 
			foreach ( $attachments as $k => $attachment ) {
				$next_post = $attachment; break;
			}
		}
					
		$title 	= get_the_title( );		
		$image = get_post_meta( $post->ID, '_wp_attachment_metadata', true );
		$img		= '<img src="' . $this->get_image_url( $image, 'preview' ) . '" title="' . esc_attr( $title ) . '" class="colorbox-2" alt="' . esc_attr(  $post->post_excerpt ) . '" />'; 
		
		$output = "<{$imagetag} class='ims-img'>" ;
		$output .= '<a href="' . get_permalink( $next_post->ID ) . "#post-{$next_post->ID}" . '" class="ims-image" rel="image" title="' . esc_attr( $title ) . '">' . $img . '</a>';
		$output .= "<{$captiontag} class='gallery-caption'><span class='ims-img-name'>" . wptexturize( $post->post_excerpt ) . "</span>";
				
		$output .= "</{$captiontag}></{$imagetag}>";
		return apply_filters( 'ims_image_content' , $output,  $tags, $next_post );
	}
		
	/**
	 *Fix next/previous links on single galleries
	 *when message is displayed
	 *
	 *@param $order string
	 *@return string
	 *@since 3.0.2
	 */ 	
	function page_link( $link ){

		if( !is_singular('ims_gallery') && !is_singular('ims_image') )
			return $link;
		$link =  preg_replace( '/\/ms\/([0-9]+)/', '', $link );
		
		global $paged;
		$link =  preg_replace( "/\/page\/$paged/", '', $link );
		
		$num =  basename( $link );
		if( $paged == $num ){
			if( ($paged -1) > 1 ) $link = dirname( $link ) . "/" . ( $paged -1 );
			else $link = dirname( $link );
		}
			
		return $link;
	}
	
	/**
	 *Fix pagination order to attachment (im_image) page 
	 *
	 *@param $order string
	 *@return string
	 *@since 3.0.1
	 */ 
	function adjacent_post_sort( $order ){
		if( !is_singular( 'ims_image' ) )
			return $order;
		$dir = ( $this->direct == '<' ) ? 'DESC' : 'ASC';
		return " ORDER BY p.{$this->sortby} $dir, p.ID $dir";
	}

	/**
	 *Fix pagination to attachment (im_image) page 
	 *
	 *@param $where string
	 *@return string
	 *@since 3.0.1
	 */ 
	function adjacent_post_where( $where ){
		if( !is_singular( 'ims_image' ) )
			return $where;
			
		global $post,$wpdb;
		$order =  get_post_meta( $post->post_parent, '_ims_order', true );
		$sortby =  get_post_meta( $post->post_parent, '_ims_sortby', true );
		
		$this->order = empty( $order ) ? $this->opts['imgsortdirect'] : $order;
		$this->sortby  = empty( $sortby ) ? $this->opts['imgsortorder'] : $sortby;
		
		$this->direct = ( preg_match( '/\>/', $where) ) ? '>' : '<';
		$where = preg_replace( array('/\>/', '/\</'), array( '>=', '<='),  $where);
		
		switch( $this->sortby ){
			case 'menu_order':
				if( $post->menu_order )
					$where = $wpdb->prepare( "WHERE p.post_type = 'ims_image' AND p.post_status = 'publish' AND p.menu_order $this->direct %d ", $post->menu_order );
				else $where = $where . " AND p.ID $this->direct $post->ID";
				break;
			case 'title':
					$this->sortby = "post_title";
					$where = $wpdb->prepare( "WHERE p.post_type = 'ims_image' AND p.post_status = 'publish' AND p.post_title $this->direct %s", $post->post_title );
				break;
			case 'date':
					$this->sortby = "post_date";
					$where = $where . " AND p.ID $this->direct $post->ID";
				break;
			case 'excerpt':
					$this->sortby = "post_excerpt";
					$where = $wpdb->prepare( "WHERE p.post_type = 'ims_image' AND p.post_status = 'publish' AND p.post_excerpt $this->direct %s", substr($post->post_excerpt,0,10) );
				break;
			default:
		}
		return $where . " AND p.post_parent = $post->post_parent";
	}
	
	/*Redirect single image templage
	 *
	 *@param string $template 
	 *@return string
	 *@since 3.0.0
	 */
	function get_image_template( $template ){
		if( !is_singular( 'ims_image' ))
			return $template;
		return locate_template(  array( 'single-ims-image.php', 'ims-image.php', 'ims_image.php', 'image.php', 'single.php' ) );
	}
	
	/*Redirect taxonomy template
	 *to display album galleries
	 *
	 *@param string $template 
	 *@return string
	 *@since 2.0.0
	 */
	function taxonomy_template( $template ){
		if( !is_tax( 'ims_album' ) )
			return $template; 
					
		if( file_exists( WP_TEMPLATE_DIR . "/page.php" )
		&& $this->opts['album_template'] == 'page.php' ){
			global $wp_query, $post;
			$count = empty(  $this->opts['album_per_page'] ) ? '' : 'count=' . $this->opts['album_per_page'];
			$post->post_password= NULL;
			$post->comment_status = false;
			$post->post_title 		= $wp_query->queried_object->name;
			$post->post_content 	= '[image-store '. $count .' album=' . $wp_query->queried_object->term_id.']';
			return WP_TEMPLATE_DIR . "/page.php";
		}
		
		if( file_exists( WP_TEMPLATE_DIR . '/'. $this->opts['album_template'] ))
			return WP_TEMPLATE_DIR . '/' . $this->opts['album_template'] ;
		
		if( file_exists( IMSTORE_ABSPATH . "/theme/taxonomy-ims_album.php" ) 
			&& !preg_match( '/taxonomy/', $template ) )
			return IMSTORE_ABSPATH . "/theme/taxonomy-ims_album.php";
			
		return $template;
	}
	
	/**
	 *Add paging option to albums 
	 *
	 *@param $query object
	 *@return object
	 *@since 3.0.0
	 */ 
	function album_pagination( $query ){
		if( !is_tax('ims_album') || empty( $this->opts['album_per_page'] ) ||
		empty( $query->query_vars['ims_album'])  )
			return $query;
			
		$query->set( 'posts_per_page',  $this->opts['album_per_page'] );
		return $query;
	}
	
	/**
	 *Search image title and caption 
	 *
	 *@param $where string
	 *@param $query object
	 *@return string
	 *@since 2.0.7
	 */ 
	function search_image_info( $where, $query ){
		$q = $query->query_vars;
		if( !is_search( ) || empty($q['s']) ) return $where;
		
		global $wpdb;
		$searchand = '';
		$n = empty( $q['exact'] ) ? '%' : '';
		
		foreach( (array) $q['search_terms'] as $term ){
			$term = esc_sql( like_escape( $term ) );
			$search = "{$searchand}(($wpdb->posts.post_title LIKE '{$n}{$term}{$n}') 
				OR ($wpdb->posts.post_content LIKE '{$n}{$term}{$n}') 
				OR ($wpdb->posts.post_excerpt LIKE '{$n}{$term}{$n}')
				OR ($wpdb->posts.post_excerpt LIKE '{$n}{$term}{$n}'))";
				$searchand = ' AND ';
		}
		
		$term = esc_sql( like_escape( $q['s'] ) );
		if ( empty($q['sentence']) && count($q['search_terms']) > 1 && $q['search_terms'][0] != $q['s'] )
			$search .= " OR ($wpdb->posts.post_title LIKE '{$n}{$term}{$n}') 
			OR ($wpdb->posts.post_content LIKE '{$n}{$term}{$n}')";
			
		return " $where OR ( ID IN ( SELECT DISTINCT post_parent FROM $wpdb->posts 
		WHERE 1=1 AND $search AND $wpdb->posts.post_status = 'publish'))";
	}
	
	/**
	 *Load gallerific variables
	 *only if they are required
	 *
	 *@param array $vars
	 *@return void
	 *@since 3.0.0
	 */
	function add_gallerific_js_vars( $vars ){
		$vars = array_merge( $vars, array(
			'galleriffic'				=> true,
			'numThumbs'			=> $this->opts['numThumbs'],
			'autoStart'			=> $this->opts['autoStart'],
			'playLinkText'		=> $this->opts['playLinkText'],
			'pauseLinkTex'		=> $this->opts['pauseLinkTex'],
			'prevLinkText'		=> $this->opts['prevLinkText'],
			'nextLinkText' 		=> $this->opts['nextLinkText'],
			'closeLinkText' 	=> $this->opts['closeLinkText'],
			'maxPagesToShow'	=> $this->opts['maxPagesToShow'],
			'slideshowSpeed'	=> $this->opts['slideshowSpeed'],
			'transitionTime'	=> $this->opts['transitionTime'],
			'nextPageLinkText' 	=> $this->opts['nextPageLinkText'],
			'prevPageLinkText'	=> $this->opts['prevPageLinkText'],	 
		) ); return $vars;
	}
	
	/**
	 *Core fuction display store
	 *
	 *@return void
	 *@since 0.5.0 
	 */
	function imstore_shortcode( $atts ){
		
		if( isset($atts['secure']) ) 
			return $this->display_secure( $atts );
		
		if( isset( $atts['nav'] ) && $atts['nav'] == true )
			$this->store_nav( ); 
			
		$this->get_galleries( $atts ); 
		return $this->display_galleries( );
	}
	
	/**
	 * Display the secure section
	 * of the image store
	 *
	 *@param obj $errors
	 *@return string
	 *@since 3.0.0
	 */	
	function display_secure( ){
				
		$message = '';
		$this->is_secure = true;

		$errors = $this->validate_user( );
		if( is_wp_error( $errors ) ) 
		$message = $this->error_message( $errors, true );
		
		if( empty( $_COOKIE[ 'wp-postpass_' . COOKIEHASH ] ) 
		|| empty( $_COOKIE[ 'ims_galid_' . COOKIEHASH ]) )
		return $message .= $this->get_login_form( );
		
		if( isset($_COOKIE[ 'wp-postpass_' . COOKIEHASH ]) ){ 
			wp_redirect( get_permalink( $_COOKIE[ 'ims_galid_' . COOKIEHASH] ) );	
			die( );
		}
	}
	
	/**
	 *Display secure galleries login form
	 *
	 *@return void
	 *@since 0.5.0 
	 */
	function get_login_form( ){
		
		$glabel = "ims-galbox-{$this->galid}";
		$plabel	= "ims-pwdbox-{$this->galid}";
		$nonce 	= wp_create_nonce( 'ims_access_form' ) ;
		
		$output = '<form action="' . get_permalink( $this->galid ) . '" method="post">
		<p class="message login">'.__("To view your images please enter your login information below:", $this->domain ).'</p>
			<div class="ims-fields">
				<label for="'.$glabel.'">'.__("Gallery ID:", $this->domain ).'</label> <input type="text" name="'.$glabel.'" id="'.$glabel.'" />
				<span class="linebreak"></span>
				<label for="'.$plabel.'">'.__("Password:", $this->domain ).'
				</label> <input name="'.$plabel.'" id="'.$plabel.'" type="password" />
				<span class="linebreak"></span>
				<input type="submit" name="login-imstore" value="'. esc_attr__( "log in", $this->domain ).'" />
				<input type="hidden" name="_wpnonce" value="'. esc_attr( $nonce ).'" />
				' . apply_filters( 'ims_after_login_form', '' ) . '
			</div>
		</form>
		';
		return apply_filters( 'ims_login_form', $output , $this->gal );
	}
	
	/**
	 *User login function
	 *
	 *@return object
	 *@since 0.5.0 
	 */
	function validate_user( ){
		
		//try to login first
		if( empty( $_POST ) || (isset( $_REQUEST["login-imstore"] ) && 
		!wp_verify_nonce($_REQUEST["_wpnonce"], 'ims_access_form' )) ) 
			return false;
			
		$errors = new WP_Error( );
		if( empty($_REQUEST["ims-galbox-" . $this->galid]) )
			$errors->add( 'emptyid', __( 'Please enter a gallery id. ', $this->domain ) );
			
		if( empty($_REQUEST["ims-pwdbox-" . $this->galid]))
			$errors->add( 'emptypswd', __( 'Please enter a password.', $this->domain ) );
		
		if( !empty( $errors->errors)) 
			return $errors;
		
		$pass = $_REQUEST["ims-pwdbox-" . $this->galid];
		$galid = $_REQUEST["ims-galbox-" . $this->galid]; 	
		
		$post = get_posts( array(
			'meta_value' => $galid ,
			'post_type' => 'ims_gallery', 
			'meta_key' => '_ims_gallery_id', 
		) ); $gal = isset( $post[0] ) ? $post[0] : $post ;
		
		
		if( empty( $gal->post_password ) || $gal->post_password !== $pass ){
			
			$errors->add( 'nomatch', __( 'Gallery ID or password is incorrect. Please try again. ', $this->domain ) );
			return $errors;
			
		}elseif( $gal->post_password === $pass ){
			
			setcookie( 'ims_galid_' . COOKIEHASH, $gal->ID, 0, COOKIEPATH, COOKIE_DOMAIN );
			setcookie( 'wp-postpass_' . COOKIEHASH, $gal->post_password, 0, COOKIEPATH, COOKIE_DOMAIN );
			update_post_meta( $gal->post_id, '_ims_visits', get_post_meta( $gal->ID, '_ims_visits', true ) +1 );
			
			wp_redirect( get_permalink( $gal->ID ) );
			die( );
		}
	}
	
	/**
	*Get gallery price list
	*
	*@return array
	*@since 0.5.0 
	*/
	function get_price_list( ){
		
		$sizedata = wp_cache_get( 'ims_pricelist_' . $this->galid );
		
		if ( false == $sizedata ){
			global $wpdb;
			$sizedata = $wpdb->get_results( $wpdb->prepare("
			SELECT meta_value meta, post_id FROM $wpdb->postmeta 
			WHERE post_id = ( SELECT meta_value FROM $wpdb->postmeta 
				WHERE post_id = %s AND meta_key = '_ims_price_list ') 
			AND meta_key = '_ims_sizes' "
			, $this->galid ) );
			wp_cache_set( 'ims_pricelist_' . $this->galid, $sizedata );
		} 
		
		$this->pricelist_id = isset( $sizedata[0]->post_id ) ? $sizedata[0]->post_id : get_option( 'ims_pricelist' ); ;
		
		if ( empty( $sizedata[0]->meta ) ) 
			return array( );
		
		$data = maybe_unserialize( $sizedata[0]->meta );
		unset( $data['random'] );
		
		foreach( $data as $size ) 
			$sizes[$size['name']] = $size;
			
		return $sizes;
	}
	
	/**
	*Get gallery images
	*
	*@return array
	*@since 0.5.0 
	*/
	function get_gallery_images( ){
		global $wpdb, $wp_query;
		
		$paged			= ( get_query_var( 'paged') ) ? get_query_var( 'paged') : false;
		$per_page		= ( empty($this->opts['imgs_per_page']) ) ? get_query_var( 'posts_per_page') : $this->opts['imgs_per_page'];
		$offset			= (empty($paged)) ? 0 : (($per_page) * $paged) - $per_page ;
		
		$this->attachments = $wpdb->get_results( $wpdb->prepare(
			"SELECT SQL_CALC_FOUND_ROWS ID, post_title, guid, post_author,
			meta_value meta, post_excerpt, post_expire FROM $wpdb->posts AS p 
			LEFT JOIN $wpdb->postmeta AS pm ON p.ID = pm.post_id
			WHERE post_type = 'ims_image'
			AND meta_key = '_wp_attachment_metadata'
			AND post_status = 'publish' AND post_parent = %d
			ORDER BY $this->sortby $this->order
			LIMIT $offset, $per_page" 
		, $this->galid ) );
		
		if( empty($this->attachments)) 
			return false;
		if( $this->imspage == 'photos' && is_singular("ims_gallery")){
			$wp_query->post_count		= count($this->attachments );
			$wp_query->found_posts	= $wpdb->get_var( 'SELECT FOUND_ROWS( )' );
			$wp_query->max_num_pages	= ceil($wp_query->found_posts / $per_page );
		}
		
		foreach( $this->attachments as $post){
			$post->meta = maybe_unserialize( $post->meta );
			$images[] = $post;
		} $this->attachments = $images;
	}
	
	/**
	*Get favorites
	*
	*@return array
	*@since 0.5.0 
	*/
	function get_favorite_images( ){
		global $user_ID;
		
		if( is_user_logged_in( ) ) 
			$ids = trim( get_user_meta( $user_ID, '_ims_favorites', true ), ', ' ) ;
		elseif( isset( $_COOKIE[ 'ims_favorites_'. COOKIEHASH] ) )
			$ids = trim( $_COOKIE[ 'ims_favorites_'. COOKIEHASH] , ', ' ); 
		
		if( empty( $ids ) )
			return false;
			
		global $wpdb;	
		
		$ids = $wpdb->escape( $ids );
		$this->attachments = $wpdb->get_results(
			"SELECT DISTINCT ID, guid, meta_value meta, post_excerpt, post_author
			FROM $wpdb->posts AS p LEFT JOIN $wpdb->postmeta AS pm
			ON p.ID = pm.post_id WHERE post_type = 'ims_image'
			AND meta_value !='' AND p.ID IN ( $ids ) GROUP BY ID  
			ORDER BY $this->sortby $this->order" 
		 ); if( empty($this->attachments)) return;

		foreach( $this->attachments as $post ){
			$post->meta = maybe_unserialize( $post->meta );
			$images[] = $post;
		} $this->attachments = $images;
		
	}
	
	/**
	*Get gallery images
	*
	*@param $atts array
	*@return array
	*@since 2.0.0
	*/
	function get_galleries( $atts ){
		
		$album = false;
		if( is_array( $atts ) )
		extract( $atts );
		
		$order			= (empty($order)) ? "DESC" : $order;
		$orderby		= (empty($sortby)) ? "post_date" : $sortby;	
		$paged			= (get_query_var( 'paged')) ? get_query_var( 'paged') : false;
		$per_page		= (!isset($count)) ? get_query_var( 'posts_per_page') : (int) $count;
		$offset			= (empty($paged)) ? 0 : (($per_page) * $paged) - $per_page ;
		$limit			= ( $per_page  < 1 ) ? '' : "LIMIT %d, %d";
		
		global $wpdb;
		$type = ( $album ) ? 
			"SELECT DISTINCT object_id, post_parent FROM $wpdb->terms AS t 
			INNER JOIN $wpdb->term_taxonomy tt ON t.term_id = tt.term_id 
			INNER JOIN $wpdb->term_relationships tr ON tt.term_taxonomy_id = tr.term_taxonomy_id 
			WHERE t.term_id = %d " 
			
		: " SELECT DISTINCT ID FROM $wpdb->posts WHERE 0 = %d AND
		post_type = 'ims_gallery' AND post_status = 'publish' AND post_password = '' " ;
		
		$this->attachments = $wpdb->get_results( $wpdb->prepare( 
			"SELECT SQL_CALC_FOUND_ROWS im.ID, im.post_title, p.comment_status,
			pm.meta_value meta, im.post_excerpt, im.post_parent, im.post_type, p.post_author
			FROM ( SELECT * FROM $wpdb->posts  ORDER BY 
			 " . $this->opts['imgsortorder'] . " " . $this->opts['imgsortdirect'] ." )  AS im 
			
			LEFT JOIN $wpdb->postmeta AS pm ON pm.post_id = im.ID
			LEFT JOIN $wpdb->posts AS p ON p.ID =  im.post_parent
			
			WHERE im.post_type = 'ims_image' AND pm.meta_key = '_wp_attachment_metadata'
			AND im.post_status = 'publish' AND p.post_status = 'publish' AND im.post_parent IN ( $type )
			GROUP BY im.post_parent ORDER BY p.{$orderby} $order, p.post_date DESC $limit
		", $album , $offset, $per_page  ));
		
		if( empty($this->attachments) ) return;
		if( is_singular( "ims_gallery" ) || is_tax("ims_album") ){
			$wp_query->post_count			= count( $this->attachments );
			$wp_query->found_posts		= $wpdb->get_var( 'SELECT FOUND_ROWS( )' );
			$wp_query->max_num_pages	= ceil( $wp_query->found_posts / $per_page );
		}
		
		foreach( $this->attachments as $post){
			$post->meta = maybe_unserialize( $post->meta );
			$images[] = $post;
		} $this->attachments = $images;
	}
	
	/**
	 *Validate promotion code
	 *
	 *@parm $code string
	 *@return bool
	 *@since 0.5.0 
	 */
	function validate_code($code){
		if( empty($code) ) 
			return false;
		
		global $wpdb;
		$promo_id = $wpdb->get_var($wpdb->prepare(
			"SELECT ID FROM $wpdb->posts AS p
			INNER JOIN $wpdb->postmeta AS pm
			ON p.ID = pm.post_id
			WHERE meta_key = '_ims_promo_code' 
			AND meta_value = BINARY '%s'
			AND post_status = 'publish' 
			AND post_date <= '" . date( 'Y-m-d', current_time( 'timestamp') ) . "'
			AND post_expire >= '" . date( 'Y-m-d', current_time( 'timestamp') ) . "' "
		, $code ) );
		
		if( empty($promo_id) ){
			$this->error = __( "Invalid promotion code", $this->domain );
			return false;
		}
		
		$data = get_post_meta( $promo_id , '_ims_promo_data', true );
		$this->cart['promo']['discount'] = $data['discount'];
		$this->cart['promo']['promo_type'] = $data['promo_type'];

		switch($data['rules']['logic']){
			case 'equal':
				if( $this->cart[$data['rules']['property']] > $data['rules']['value'])
					return true;
				break;
			case 'more':
				if( $this->cart[$data['rules']['property']] > $data['rules']['value'])
					return true;
				break;
			case 'less':
				if( $this->cart[$data['rules']['property']] > $data['rules']['value'])
					return true;
				break;
		}
		
		$this->error = __( "Your current purchase doesn't meet the promotion requirements.", $this->domain );
		return false;
	}
	
	/**
	*Display galleries
	*
	*@return array
	*@since 0.5.0 
	*/
	function display_galleries( ){ 
		global $post, $wp_query;
		
		$tags = apply_filters( 'ims_gallery_tags', array(
			'gallerytag' => 'div', 
			'imagetag' => 'figure', 
			'captiontag' => 'figcaption'
		 ), $this ); extract( $tags );
		
		//allow plugins to overwrite output
		$output = apply_filters( 'ims_before_galleries', '', $tags, $this );
		if( '' != $output ) return $output;
				
		if( !empty( $post->post_excerpt ) && ( $this->imspage == 'photos' || $this->imspage == 'slideshow' )) 
			$output = '<div class="ims-excerpt">'.$post->post_excerpt.'</div>';
		
		//$wm	= ( !empty( $this->opts['watermark'] )) ? "?p=1" : '';
		$output .= "<{$gallerytag} id='ims-gallery-".$this->galid."' class='ims-gallery'>";
		$attach  = ( (!empty( $this->opts['attchlink'] ) && empty( $this->meta['_to_attach'][0] )) || !empty( $this->meta['_to_attach'][0] ));

		foreach( $this->attachments as $image ){
			$enc = esc_attr( $this->encrypt_id( $image->ID ) ) ;
			if( !empty( $image->post_parent )){
				$post 		= $image;
				$tagatts	= ' class="ims-image" rel="image" ';
				$link 		=  get_permalink( $image->post_parent ); 
				$title		= $caption = str_replace(__( 'Protected:' ), '',get_the_title($image->post_parent) );
			}else{
				$caption	= $image->post_excerpt;
				$title		= get_the_title( $image->ID );
				if( $attach ){
					$link 	= 	get_permalink( $image->ID );
					$tagatts= ' class="ims-image" rel="image" ' ;
				}else{
					$link 	= 	 $this->get_image_url( $image );
					$tagatts= ' class="ims-colorbox" rel="gallery"';   
				}
			}
			
			$size = '';
			if( isset( $image->meta['sizes']['thumbnail']['width'] ) ) 
				$size .= ' width="' .  $image->meta['sizes']['thumbnail']['width'] . '" ';
			if( isset( $image->meta['sizes']['thumbnail']['height'] ) ) 
				$size .=  'height="' .  $image->meta['sizes']['thumbnail']['height'] . '"';
						
			$tag 		= '<img src="' . $this->get_image_url( $image, 'thumbnail' ) . '" title="' . esc_attr($caption) . '" class="colorbox-2" alt="' . esc_attr($title) . '"'. $size . ' />'; 
			
			$output .= "<{$imagetag} class='ims-img imgid-{$image->ID}'>";
			$output .= '<a id="'. $enc .'" href="' . $link . '"' . $tagatts . ' title="' . esc_attr( $title ) . '">' . $tag . '</a>';
			$output .= "<{$captiontag} class='gallery-caption'><span class='ims-img-name'>" . wptexturize( $title ) . "</span>";
			
			if( ( empty( $this->opts['disablestore'] ) || empty( $this->opts['hidefavorites'] ) ) && $this->query_id
			&& empty($this->meta['_dis_store'][0]) ) 
				$output .= ' <label><input name="imgs[]" type="checkbox" value="' . $enc . '" />
				<span class="ims-label">' . __( 'Select', $this->domain ) . '</span> </label>';
			$output .= "</{$captiontag}></{$imagetag}>";
			
		}
		
		$output 	.= "</{$gallerytag}>";
				
		//regisgter visit
		if( empty( $_COOKIE['ims_gal_' . $this->galid . '_' . COOKIEHASH] ) ){
			setcookie( 'ims_gal_' . $this->galid . '_' . COOKIEHASH, true, 0, COOKIEPATH, COOKIE_DOMAIN );
			update_post_meta( $this->galid, '_ims_visits', get_post_meta( $this->galid, '_ims_visits', true) +1 );
		}
		
		$wp_query->is_single = false;
		$output .= '<div class="ims-navigation">';
		$output .= '<div class="nav-previous">'.get_previous_posts_link(__( '<span class="meta-nav">&larr;</span> Previous images', $this->domain )).'</div>';
		$output .= '<div class="nav-next">'.get_next_posts_link(__( 'More images <span class="meta-nav">&rarr;</span>', $this->domain )).'</div>';
		$output .= '</div><div class="ims-cl"></div>';
		
		$wp_query->post_count	=1;
		$wp_query->is_single		= true;
		
		return $output;
	}
		
	/**
	*Get encripted image url
	*
	*@param object $image
	*@param string $size
	*@since 3.0.0
	*return string
	*/
	function get_image_url( $data, $size = 'preview' ){

		$image = new stdClass();
		if( is_array( $data ) ) 
			$image->meta = $data;
		else $image = $data;
		
		$rel	= ( preg_match( " /(_resized)/i", $image->meta['file'] ) ) ? 
		dirname( $image->meta['file'] ) . "/" : dirname( $image->meta['file'] ) . "/_resized/";
		
		if( $size == 'original' ) 
			$url = $image->meta['file'];
		elseif( !empty( $image->meta['sizes'][$size]['file'] ) )
			$url = $rel . $image->meta['sizes'][$size]['file'];
		elseif( !empty( $image->meta['sizes']['preview']['file'] ) )
			$url = $rel . $image->meta['sizes']['preview']['file'];
		else $url = $image->meta['file'];
		
		//add watermark
		$wm = "";
		if( !empty( $this->opts['watermark'] ) 
		&& !$this->in_array( $size, array( 'mini', 'thumbnail' )) ){
			$url .= "/". rand( 0, 99 );
			$wm = "&w=1";
		}
		return apply_filters( 'ims_image_url', $this->baseurl . $this->url_encrypt( $url ) . $wm );
	}
	
	/**
	*Get imstore permalink
	*
	*@param string $page
	*@since 0.5.0 
	*return void
	*/
	function get_permalink( $page = '', $encode = true, $paged = false ){
		
		$link = '';
		if( $this->permalinks ){
			
			$link = ( !isset( $this->pages[$page] )
			|| preg_match( '/[^\\p{Common}\\p{Latin}]/u', $this->pages[$page] )) 
			? '/' . $page : '/' . sanitize_title( $this->pages[$page] );
			
			if( $link == '/' )
				$link .= $page;
			
			if( $paged )
				$link .= '/page/'. $paged;
				
			if( $this->success != false) 
				$link .= '/ms/' . $this->success;
		}else{
			if( is_front_page( ) ) 
				$link .= '?page_id=' . $this->page_front;
			
			if( $page == 'logout' ) $link .= '&imslogout=1';
			elseif( $page ) $link .= '&imspage=' . $page;
			
			if( $this->success != false ) 
				$link .= '&imsmessage='. $this->success; 
		}
		
		if( $encode ) 
			$link =  get_permalink( ) . htmlspecialchars( $link );
		else $link = get_permalink( ) . $link;
		
		return apply_filters( 'ims_permalink', $link, $page, $encode );
	}
	
	/**
	*Display Order form
	*
	*@return void
	*@since 0.5.0 
	*/
	function display_list_form( ){	
		$form = 
		'<form id="ims-pricelist" method="post"> ' . apply_filters( 'ims_before_order_form', '', $this ) . '
			<div class="ims-image-count">' . __( 'Selected', $this->domain ) . '</div>
			<div class="ims-add-error">' . __( 'There are no images selected', $this->domain ) . '</div>
			<div class="ims-instructions">' . __( 'These preferences will be apply to all the selected images', $this->domain ) . '</div>
			
		<div class="ims_prlicelist-wrap">
		
			<div class="ims-field"> 
				<label for="ims-quantity">' . __( 'Quantity', $this->domain ) . ' </label>
				<input name="ims-quantity" type="text" class="inputsm" id="ims-quantity" value="1" />
			</div><!--.ims-field-->';
		
		if( empty($this->opts['disablebw']) || empty($this->opts['disablesepia']) ){ 
			$form .= '
			<div class="ims-field">
				<label for="_imstore-color">' . __( 'Color', $this->domain ). ' </label>
				<select name="_imstore-color" id="_imstore-color" class="select">';
					foreach( $this->color as $color => $label ){
						$form .= '<option value="' . esc_attr( $color ). '">' . $label . 
						( isset( $this->listmeta[$color] ) ? ' + ' . esc_html( $this->listmeta[$color] ) : '' ) . '</option>';
					}
			$form .= '</select>
			</div><!--.ims-field-->';
		}
				
		$form .= '<span class="ims-image-size">' . __( 'Sizes', $this->domain ) . '</span>
		<div class="ims-image-sizes">';
		 if( !empty( $this->sizes ) ){
			foreach( $this->sizes as $size ){
				$form .= '<label> <input type="checkbox" name="ims-image-size[]" value="';
				if( isset($size['ID']) ){
					$form .= esc_attr( $size['name'] ).'" /> ' . $size['name'] . ': '; $package_sizes = '';
					foreach( (array)get_post_meta( $size['ID'], '_ims_sizes', true ) as $package_size => $count ){
						if( is_array($count)) $package_sizes .= $package_size .$count['unit'] . ' ( '.$count['count'].' ), '; 
						else $package_sizes .= $package_size .'( '.$count.' ), '; 
					}
					$price = sprintf($this->cformat[$this->loc], get_post_meta( $size['ID'], '_ims_price', true ) );
					$form .= rtrim("$price &mdash; ". $package_sizes, ', ') . " </label>\n"; 
				}elseif( isset( $size['name'] ) ) { 
					$form .= esc_attr( $size['name'] ).'" /> '.$size['name']." &mdash; " . sprintf( $this->cformat[$this->loc], $size['price'] ) . " </label>\n"; 
				}
			} 
		 }
		$form .= '</div>';
	
		$form .= 
			'<div class="ims-field ims-submit">
				<input type="submit"name="add-to-cart" value="' . esc_attr__( 'Add to cart', $this->domain ) . '" class="button" />
				<input type="hidden" name="_wpnonce" value="' . wp_create_nonce("ims_add_to_cart") . '" />
				<input type="hidden" name="ims-to-cart-ids" id="ims-to-cart-ids" />
			</div>
		</div>
		' . apply_filters( 'ims_after_order_form', '', $this ) . '
		</form><!--.ims-pricelis-->';
		return $form;
	}
	
	/**
	*Display store sub-navigation
	*
	*@return void
	*@since 2.0.0
	*/
	function store_subnav( ){
		
		if( !empty($this->meta['_dis_store'][0]) || (!empty( $this->opts['disablestore'] ) 
		&&  !empty( $this->opts['hidefavorites'] )) )
			return;
		
		$this->subnav = apply_filters( 'ims_subnav', array(
			'ims-select-all' => __( "Select all", $this->domain ),
			'ims-unselect-all' => __( "Unselect all", $this->domain ),
			'add-to-favorite' => __( "Add to favorites", $this->domain ),
			'remove-from-favorite' => __( "Remove", $this->domain ),
			'add-images-to-cart' => __( "Add to cart", $this->domain )
		));
		
		$nav = '<div class="ims-toolbar"><ul class="ims-tools-nav">';
		foreach( $this->subnav as $key => $label ){
			if( ($this->imspage != 'photos' && $key == 'add-to-favorite') ||
			($this->imspage != 'favorites' && $key == 'remove-from-favorite' ) ) continue;
			$nav .= '<li class="' . $key . '"><a href="#" rel="nofollow">' . $label . '</a></li>';
		}
		
		return $nav .= '</ul></div>';

	}
	
	/**
	*Filter Subnavigation options to allow
	*favorites when store is disabled
	*
	*@return array
	*@since 3.0.0
	*/
	function ad_favorite_options( $pages ){
		
		if( !empty( $this->opts['disablestore'] )){
			unset( $pages['ims-select-all']);
			unset( $pages['ims-unselect-all']);
			unset( $pages['add-images-to-cart']);
		}
		
		if( !empty( $this->opts['hidefavorites'] )){
			unset( $pages['add-to-favorite']);
			unset( $pages['remove-from-favorite']);
		}
		
		return $pages;
	}
	
	/**
	*Display store navigation
	*
	*@return void
	*@since 0.5.0 
	*/
	function store_nav( ){
		global $post;
		
		if( !empty($this->meta['_dis_store'][0]) )
			return;
		
		$nav = "\n" . '<ul id="imstore-nav" class="imstore-nav" >'. "\n";
		foreach( $this->pages as $key => $page ){
			if( $key == 'receipt' || $key == 'checkout' || ( $key == 'photos' && !empty($this->opts['hidephoto']) )
			|| ( $key == 'slideshow' && isset( $this->opts['hideslideshow'])) || $key == 'favorites' && isset( $this->opts['hidefavorites']) ) 
				continue;
		
			$title = ( preg_match( '/[^\\p{Common}\\p{Latin}]/u', $page )) ? $key : sanitize_title($page );
			$css 	= ( $key == $this->imspage || ( $key == 'photos' && empty($this->imspage)) ) ? ' current' : '';	
			$count = ($key == 'shopping-cart' && isset( $this->cart['items']) && $this->imspage != 'receipt' ) ? "<span>(".$this->cart['items'].")</span>" : '';
			$nav 	.= '<li class="ims-menu-'.$title.$css.'"><a href="'. $this->get_permalink( $key ) .'">'.$page."</a> $count </li> "." \n";
		}
		
		if( isset($this->cart['total']) ) 
			$nav .= '<li class="ims-menu-total">' . $this->format_price( $this->cart['total'] ) . '</li>' . "\n";
		if( $post->post_password && isset( $_COOKIE['wp-postpass_'.COOKIEHASH] ) )
			$nav .= '<li class="ims-menu-logout"><a href="' . $this->get_permalink( "logout" ) . '">' . __("Exit Gallery", $this->domain ) . '</a></li>'."\n";
		 
		return $nav."</ul>\n";
	}
	
	/**
	*Add items to cart
	 *
	*@return void
	*@since 0.5.0 
	*/
	function add_to_cart( ){
		
		if( !wp_verify_nonce( $_REQUEST["_wpnonce"], "ims_add_to_cart" )) 
			wp_die( 'Security check failed. Try refreshing the page.' ); 
			
		if( !is_numeric($_POST['ims-quantity']) || empty($_POST['ims-quantity']) )
			$this->error = __( 'Please, enter a valid image quantity', $this->domain );
		
		if( empty($_POST['ims-image-size']) )
			$this->error = __( 'Please, select an image size.', $this->domain );

		if( empty( $_POST['ims-to-cart-ids']) )
			$this->error = __( 'There was a problem adding the images to the cart.', $this->domain );
		
		do_action( 'ims_berofe_add_to_cart', $this->cart );
		
		if( !empty($this->error) ) 
			return;
		
		$this->cart['items'] = 0;
		if( empty($this->cart['subtotal'] ) )
			$this->cart['subtotal'] = 0;
	
		$images = explode( ',', $_POST['ims-to-cart-ids'] );
		$color	= ( empty($_POST['_imstore-color']) ) ? 'color' : $_POST['_imstore-color'];
		
		foreach( $images as $id ){
			$id = $this->decrypt_id( $id );
			
			foreach( $_POST['ims-image-size'] as $size ){
				$this->cart['images'][$id][$size][$color]['quantity'] = 
				isset( $this->cart['images'][$id][$size][$color]['quantity'] ) ? 
				$this->cart['images'][$id][$size][$color]['quantity'] += $_POST['ims-quantity']
				: $_POST['ims-quantity'];

				$this->cart['images'][$id][$size][$color]['gallery'] = $this->galid;
				$this->cart['images'][$id][$size][$color]['unit'] 	= $this->sizes[$size]['unit'];
				$this->cart['images'][$id][$size][$color]['color'] 	= isset( $this->listmeta[$color] ) ? $this->listmeta[$color] : 0;
				
				if( isset($this->sizes[$size]['ID']) )
					 $this->cart['images'][$id][$size][$color]['price'] = get_post_meta( $this->sizes[$size]['ID'], '_ims_price', true );
				else $this->cart['images'][$id][$size][$color]['price'] = $this->sizes[$size]['price'];
				
				if( isset($this->sizes[$size]['download']) )
					$this->cart['images'][$id][$size][$color]['download'] = $this->sizes[$size]['download'];
				else $this->cart['shippingcost'] = 1;
				
				$this->cart['images'][$id][$size][$color]['subtotal'] =
				(($this->cart['images'][$id][$size][$color]['price'] + $this->cart['images'][$id][$size][$color]['color'] ) * 
				$this->cart['images'][$id][$size][$color]['quantity'] );
				
				$this->cart['subtotal'] += $this->cart['images'][$id][$size][$color]['subtotal'];
			}
		}
		
		//count image numbers
		foreach( $this->cart['images'] as $id => $sizes){
			foreach( $sizes as $size => $colors ){
				foreach( $colors as $color => $values )
					$this->cart['items'] += $values['quantity'];
			}
		}
		
		$this->cart['shipping'] = 0;
		
		//add shipping cost
		if( isset($this->cart['shippingcost']) ){
			if( empty($this->cart['shipping_type']) )
				$this->cart['shipping_type'] = 'ims_ship_local';
			$this->cart['shipping'] = $this->listmeta[$this->cart['shipping_type']];
		}
		
		$this->cart['total'] = $this->cart['subtotal'] + $this->cart['shipping'];
		
		if( isset($this->cart['promo']['code']) ){
			switch($this->cart['promo']['promo_type']){
				case 2: $this->cart['promo']['discount']; break;
				case 3: $this->cart['promo']['discount'] = $this->cart['shipping']; break;
				case 1: $this->cart['promo']['discount'] = ($this->cart['total']*($this->cart['promo']['discount']/100) ); break;
			}
			$this->cart['discounted'] = $this->cart['total'] - $this->cart['promo']['discount'];
		}
		
		$this->cart['total'] = ( isset($this->cart['discounted']) ) ? $this->cart['discounted'] : $this->cart['total'];
		
		if( isset($this->opts['taxamount']) ){
			if( $this->opts['taxtype'] == 'percent' ) 
				$this->cart['tax'] = ($this->cart['total'] * ($this->opts['taxamount']/100) );
			else $this->cart['tax'] = $this->opts['taxamount']; $this->cart['total'] += $this->cart['tax'];
		}
				
		if( empty( $this->cart['instructions'] ) )
			$this->cart['instructions'] = '' ;
			
		do_action( 'ims_before_save_cart', $this->cart );
		
		if( empty( $_COOKIE['ims_orderid_'.COOKIEHASH] )
		|| empty(  $this->cart_stat  ) || $this->cart_stat != 'draft' ){
			
			$order = array(
				'ping_status' 	=> 'close',
				'post_status' 	=> 'draft',
				'comment_status'=> 'close',
				'post_type' 	=> 'ims_order',
				'post_expire' 	=> date( 'Y-m-d H:i',current_time( 'timestamp')+86400 ),
				'post_title' 	=> 'Ims Order - '.date( 'Y-m-d H:i',current_time( 'timestamp') ),
			 );
			$orderid = wp_insert_post( apply_filters( 'ims_new_order', $order, $this->cart ) );
			
			if( !empty( $orderid ) && !empty( $this->cart )){
				setcookie( 'ims_orderid_' . COOKIEHASH, $orderid, time( )+31536000, COOKIEPATH, COOKIE_DOMAIN );
				add_post_meta( $orderid, '_ims_order_data', $this->cart );
			}
		} else update_post_meta( $this->orderid, '_ims_order_data', $this->cart );
		
		do_action( 'ims_after_add_to_cart', $this->cart );
		
		$this->success = '1';
		$paged = get_query_var( 'paged');
		
		wp_redirect( html_entity_decode( $this->get_permalink( $this->imspage, false, $paged ) ) . $page ); 
		die( );
	}
	
	
	/**
	 *update cart information
	 *
	 *@return void
	 *@since 3.0.0 
	 */
	function update_cart( ){
			
		if( !wp_verify_nonce( $_REQUEST["_wpnonce"], "ims_submit_order" )) 
			wp_die( 'Security check failed. Try refreshing the page.' ); 
		
		do_action( 'ims_before_update_cart', $this );
		
		//remove items
		if( isset( $_POST['ims-remove'] ) && is_array( $_POST['ims-remove'] ) ){
			if( isset( $this->cart['shippingcost'] ) ) 
				unset( $this->cart['shippingcost'] );
			foreach( $_POST['ims-remove'] as $delete){
				
				$values = explode( '|', $delete );
				$values[0] = $this->decrypt_id( $values[0 ] );
				unset( $this->cart['images'][$values[0]][$values[1]][$values[2]] );
				
				if( empty( $this->cart['images'][$values[0]][$values[1]] ))
					unset( $this->cart['images'][$values[0]][$values[1]] );
				if( empty( $this->cart['images'][$values[0]] ))
					unset( $this->cart['images'][$values[0]] );
				unset( $this->cart['shippingcost'] );
			}
		}
		
		if( empty($this->cart['images']) ){ 
			update_post_meta( $this->orderid, '_ims_order_data', false ); 
			return;
		}
		
		$this->cart['items'] = 0;
		$this->cart['subtotal'] = 0;
		
		foreach( $this->cart['images'] as $id => $sizes){
			foreach( $sizes as $size => $colors ){
				foreach( $colors as $color => $values ){
					
					$this->cart['items'] += $_POST['ims-quantity'][$id][$size][$color];
					
					$this->cart['subtotal'] += (( 
						$this->cart['images'][$id][$size][$color]['price'] + 
						$this->cart['images'][$id][$size][$color]['color'] ) * 
						$_POST['ims-quantity'][$id][$size][$color]
					 );
					
					$this->cart['images'][$id][$size][$color]['subtotal'] = ((
						$this->cart['images'][$id][$size][$color]['price'] +
						$this->cart['images'][$id][$size][$color]['color']) * 
						$_POST['ims-quantity'][$id][$size][$color]
					 );
					
					if( empty( $colors[$color]['download'] ) ) 
						$this->cart['shippingcost'] = 1;
					$this->cart['images'][$id][$size][$color]['quantity'] = $_POST['ims-quantity'][$id][$size][$color];
				}
			}
		}
		
		$this->cart['shipping'] = 0;
		$this->cart['shipping_type'] = $_POST['shipping'];
		
		//add shipping cost
		if( isset($this->cart['shippingcost']) ){
				if( empty($this->cart['shipping_type']) )
					$this->cart['shipping_type'] = 'ims_ship_local';
				$this->cart['shipping'] = $this->listmeta[$this->cart['shipping_type']];
		}
		
		$this->cart['total'] = $this->cart['subtotal'] + $this->cart['shipping'];
					
		if( $this->validate_code( $_POST['promocode'] ) ){
			switch($this->cart['promo']['promo_type']){
				case 2: $this->cart['promo']['discount']; break;
				case 3: $this->cart['promo']['discount'] = $this->cart['shipping']; break;
				case 1: $this->cart['promo']['discount'] = ( $this->cart['total'] * ($this->cart['promo']['discount']/100) ); break;
			}
			$this->cart['promo']['code'] = $_POST['promocode'];
			$this->cart['discounted'] = $this->cart['total'] - $this->cart['promo']['discount'];
		}else{
			unset($this->cart['discounted'] ); unset($this->cart['promo']['code'] );
		}
			
		//apply tax
		$this->cart['total'] = isset( $this->cart['discounted'] ) ? $this->cart['discounted'] : $this->cart['total'];
		if( !empty( $this->opts['taxamount'] ) ){
			if( $this->opts['taxtype'] == 'percent' ) 
				$this->cart['tax'] = ( $this->cart['total'] * ($this->opts['taxamount']/100) );
			else $this->cart['tax'] = $this->opts['taxamount']; 
			$this->cart['total'] += $this->cart['tax']; 
		}
		
		do_action( 'ims_after_update_cart', $this );
		
		if( isset( $_POST['instructions'] ))
			$this->cart['instructions'] = trim($_POST['instructions'] );
		
		$this->success = '2';
		update_post_meta( $this->orderid, '_ims_order_data', $this->cart );
		
		wp_redirect( html_entity_decode( $this->get_permalink( $this->imspage )) ); 
		die( );
	}

	/**
	 *Display gallery 
	 *
	 *@return void
	 *@since 2.0.0
	 */ 
	function ims_gallery_shortcode( ){
		if( !is_singular( 'ims_gallery') )
			return;
		
		$output = '<div id="ims-mainbox" class="ims-' . sanitize_title($this->pages[$this->imspage]).'">';
		$output .= $this->store_nav( ); 
		
		global $post;
		$output .= '<div class="ims-labels">';
		if( $post->post_expire != '0000-00-00 00:00:00' ) $output .= '<span class="ims-expires">'.
			__("Expires: ", $this->domain ) . date_i18n( $this->dformat, strtotime($post->post_expire) ) .'</span>';
		$output .= '</div>';
		
		$error = '';
		if( $this->error ){
			$error = ' ims-error';
			$this->message = $this->error;
		}elseif( $this->message ){
			$error = ' ims-success';
		}
		
		$output .= '<div class="ims-message'.$error.'">'.$this->message.'</div>';
			
		$output .= '<div class="ims-innerbox">';
		$output .= apply_filters( 'ims_before_page', '', $this->imspage );
		
		switch( $this->imspage ){
			case 'slideshow':
				$this->get_gallery_images( );
				include_once( apply_filters( 'ims_slideshow_path', IMSTORE_ABSPATH . '/_store/slideshow.php' ) );
				break;
			case 'price-list':
				$post->comment_status = false;
				include_once( apply_filters( 'ims_pricelist_path', IMSTORE_ABSPATH . '/_store/pricelist.php' ) );
				break;
			case "favorites":
				$post->comment_status = false;
				$this->get_favorite_images( );
				$output .= $this->store_subnav( );
				$output .= $this->display_galleries( );
				break;
			case "shopping-cart":
				$post->comment_status = false;
				include_once( apply_filters( 'ims_cart_path', IMSTORE_ABSPATH . '/_store/cart.php' ) );
				break;
			case "receipt":
				$post->comment_status = false;
				include_once( apply_filters( 'ims_receipt_path', IMSTORE_ABSPATH . '/_store/receipt.php' ) );
				break;
			case "checkout":
				$post->comment_status = false;
				include_once( apply_filters( 'ims_checkout_path', IMSTORE_ABSPATH . '/_store/checkout.php' ) );
				break;
			default:
				$this->get_gallery_images( );
				$output .= $this->store_subnav( );
				$output .= $this->display_galleries( );
		}
		$output .= apply_filters( 'ims_after_page', '', $this->imspage);
		$output .= '</div>';
		
		if( empty( $this->opts['disablestore'] ) ) 
			$output .= $this->display_list_form( );
			
		return $output .= '</div>'; 
	}
	
	/**
	 * remove comments from albums
	 *
	 *@param bool $bool
	 *@return array
	 *@since 3.0.0
	 */	
	function close_comments( $bool ){
		if( !is_tax( 'ims_album') ) 
			return $bool;
		return false;
	}
	
	/**
	 * remove comments from store pages
	 * except photos and slideshow
	 *
	 *@param array $comments
	 *@param int $postid
	 *@return array
	 *@since 3.0.0
	 */	
	function hide_comments( $comments, $postid ){
		if( is_tax( 'ims_album') ) 
			return array( );
		
		if( get_post_type( $postid ) != 'ims_gallery' 
		|| $this->in_array( $this->imspage, array( 'photos', 'slideshow' ) ) )
			return $comments;
		return array( );
	}
	
	/**
	 *Shipping options dropdown
	 *
	 *@return string
	 *@since 3.0.0
	 */	
	function shipping_options( ){
	
		$select = '<select name="shipping" id="shipping" class="shipping-opt">';
		
		$select .= '<option value="ims_ship_local"' . selected( 'ims_ship_local', $this->cart['shipping_type'], false ) . '>'
		. __( 'Local + ', $this->domain ) . $this->format_price( $this->listmeta['ims_ship_local'] ) . '</option>';
		
		$select .= '<option value="ims_ship_inter"' . selected( 'ims_ship_inter', $this->cart['shipping_type'], false ) . '>'
		. __( 'International + ', $this->domain ) . $this->format_price( $this->listmeta['ims_ship_inter'] ) . '</option>';
		
		$select .= '</select>';
		
		return $select;
	}
	
	/**
	 *Encrypt url
	 *
	 *@parm string $string 
	 *@return string
	 *@since 2.1.1
	 */	
	function url_encrypt( $string ){
		$str = '';
		$key =  $this->key;
		for( $i=0; $i < strlen($string); $i++ ) { 
			$char = substr( $string, $i, 1 ); 
			$keychar = substr( $key, ($i % strlen($key)) -1, 1); 
			$char = chr(ord($char)+ord($keychar)); 
			$str .= $char; 
		}
		return urlencode( implode('||',explode( '/',  base64_encode( $str )))); 
	}
	
}
