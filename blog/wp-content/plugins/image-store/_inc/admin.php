<?php 

/**
*Image store - admin core
*
*@package Image Store
*@author Hafid Trujillo
*@copyright 20010-2012
*@since 0.5.0
*/

class ImStoreAdmin extends ImStore{
	
	/**
	*Public variables
	*/
	public $pageurl 		= '';
	public $galid			= 0;
	public $spageid 	= 0;
	public $per_page 	= 20;
	public $page			= false;
	public $action 		= false;
	public $pagenow 	= false;
	public $uopts 		= array( );
	public $screens		= array( );
	public $user_fields	= array( );
	public $user_status = array( );
	
	/**
	*Constructor
	*
	*@return void
	*@since 0.5.0 
	*/
	function ImStoreAdmin( ){
		
		global $pagenow;
		parent::ImStore( );
		
		add_filter( 'get_attached_file', array( $this, 'load_ims_image_path' ), 15,2 );
		add_filter( 'ims_album_row_actions', array( $this, 'add_taxonomy_link' ), 1,3 );
		add_filter( 'intermediate_image_sizes', array( $this, 'alter_image_sizes' ), 50,1 );
		add_filter( 'load_image_to_edit_path', array( $this, 'load_ims_image_path' ), 15,2 );
		add_filter( 'manage_edit-ims_gallery_columns', array( $this,"add_columns" ),10 );
		add_filter( 'image_make_intermediate_size', array( $this, 'move_resized_file' ), 10,3 );
		add_filter( 'manage_ims_album_custom_column', array( $this, 'show_cat_id' ), 10,3 );
		add_filter( 'manage_posts_custom_column', array( $this, 'add_columns_val_gal' ), 15,2 );
		add_filter( 'wp_update_attachment_metadata', array( $this, 'generate_image_metadata' ), 50,2 );
		add_filter( 'wp_generate_attachment_metadata', array( $this, 'generate_image_metadata' ), 50,2 );
		
		add_action( 'delete_post', array( $this, 'delete_post' ), 1 );
		add_action( 'admin_print_styles', array( $this, 'register_screen_columns' ), 0 );
		add_action( 'manage_edit-ims_album_columns', array( $this, 'add_id_column') );
		add_action( 'manage_edit-ims_album_sortable_columns', array( $this, 'add_id_column') );
		
		//speed up wordpress load
		if( defined( 'DOING_AJAX' ) || defined( 'DOING_AUTOSAVE' || defined( 'SHORTINIT')) ) 
			return;
			
		//register hooks
		register_activation_hook( IMSTORE_FILE_NAME, array( $this, 'activate') );
		register_deactivation_hook( IMSTORE_FILE_NAME, array( $this, 'deactivate') );
		
		add_action( 'init', array( $this, 'admin_init' ),1 );	
		add_action( 'init', array( $this, 'save_screen_option' ),5 );	
		add_action( 'admin_menu', array( $this, 'add_menu' ),20 );
		add_action( 'user_register', array( $this, 'update_user' ),1 );
		add_action( 'edit_user_profile', array( $this, 'profile_fields' ),1);
		add_action( 'show_user_profile', array( $this, 'profile_fields' ),1 );
		add_action( 'edit_user_profile_update', array( $this, 'update_user' ),1 );

		add_filter( 'manage_users_columns', array( $this, 'add_columns' ),10 );
		add_filter( 'wp_insert_post_data', array( $this, 'insert_post_data' ),20, 2 );
		add_filter( 'manage_users_sortable_columns', array( $this, 'add_columns' ),10 );
		add_filter( 'manage_users_custom_column', array( $this, 'add_columns_val' ),15, 3 );

		add_action( 'admin_print_styles', array( $this, 'load_styles' ),1 );
		add_action( 'admin_print_scripts', array( $this, 'load_admin_scripts' ),1 );
	
		if( is_multisite( ) ){
			add_action( 'wpmu_options', array( $this, 'wpmu_options' ) );
			add_action( 'activated_plugin', array( $this, 'activated_plugin' ),1, 2 );
			add_action( 'wpmu_new_blog', array( $this, 'wpmu_create_blog' ),1 );
			add_action( 'update_wpmu_options', array( $this, 'update_wpmu_options' ) );
		}
	
		if( $pagenow == 'profile.php' )
			add_filter( 'screen_settings', array( $this, 'screen_settings' ), 15,2 ); 

 		if( !$this->in_array( $pagenow, array( 'post.php', 'post-new.php') )) 
			return;
		
		add_filter( 'mce_css', array( $this, 'mce_css') );
		add_filter( 'mce_buttons_2', array( $this, 'register_ims_button') );
		add_filter("mce_external_plugins", array( $this, 'add_ims_tinymce_plugin') );
	}
	
	/**
	*Add css for tinymce support
	*
	*@param string $css
	*@return string
	*@since 3.0.0
	*/
	function mce_css( $css ){
		return $css . ', ' . IMSTORE_URL ."/_css/tinymce.css";
	}

	/**
	*Add imstore button to the 
	* second tinymce button bar
	*
	*@param array $buttons
	*@return array
	*@since 3.0.0
	*/
	function register_ims_button( $buttons ){
		array_push( $buttons, "separator", "imstore" );
		return $buttons;
	}
	
	/**
	*Add js for tinymce support
	*
	*@param array $plugins
	*@return array
	*@since 3.0.0
	*/
	function add_ims_tinymce_plugin( $plugins ){
		$plugins['imstore'] = IMSTORE_URL.'/_js/tinymce/imstore.js';
	 	return $plugins;
	}
	
	/**
	*Deactivate 
	*
	*@return void
	*@since 0.5.0 
	*/
	function deactivate( ){
		wp_clear_scheduled_hook( 'imstore_expire' );
	}
	
	/**
	*Activite and save default options
	*Activite the expire cron 
	*
	*@return void
	*@since 0.5.0 
	*/
	function activate( ){
		wp_schedule_event( strtotime("tomorrow 1 hours" ), 'twicedaily', 'imstore_expire' );
		include_once( IMSTORE_ABSPATH.'/admin/install.php' );
	}
	
	/**
	*Display the pages 
	*
	*@return void
	*@since 0.5.0 
	*/
	function show_menu( ){
		global $wpdb;
		include_once(IMSTORE_ABSPATH.'/admin/template.php' );
	}
	
	/**
	*Set settings when the pluigin
	*is activated in the entire network 
	*
	*@return void
	*@since 0.5.0 
	*/
	function activated_plugin( $plugin, $network_wide ){
		if( !$network_wide || $plugin != IMSTORE_FOLDER ) 
                    return;
                
		global $wpdb;
		
		$opts = get_site_option( $this->optionkey );
		if( get_site_option( 'ims_sync_settings' ) && empty( $opts ) ){
			include_once( IMSTORE_ABSPATH.'/admin/install.php' );
			ImStoreInstaller::imstore_default_options( );
		}else{
			$blogs = $wpdb->get_results(
				"SELECT blog_id id FROM $wpdb->blogs WHERE public = '1' AND archived = '0' AND deleted = '0'"
			 ); 
			foreach( $blogs as $blog ){
				switch_to_blog ( $blog->id );
				$customer = @get_role( 'customer' );
				if( empty( $customer ) )  add_role( 'customer', 'Customer', array('read' => 1, 'ims_read_galleries' => 1 ) );
				 $wpdb->query( "ALTER IGNORE TABLE  $wpdb->posts ADD post_expire DATETIME NOT NULL" );
			}
			restore_current_blog( );
		}
	}
	
	/**
	*Add cutomer role and expire column
	*to blogs under wpmu
	*
	*@return void
	*@since 3.0.2
	*/
	function wpmu_create_blog( $blog_id ){
		if( !is_plugin_active_for_network( IMSTORE_FILE_NAME ) )
			return;
			
		switch_to_blog ( $blog_id );
		include_once( IMSTORE_ABSPATH . '/admin/install.php' );
		restore_current_blog( );
	}
	
	/**
	*Update WPMU optons
	*
	*@return void
	*@since 3.0.0
	*/
	function update_wpmu_options( ){
		check_admin_referer( 'siteoptions' );
		$val = empty( $_POST['ims_sync_settings'] ) ? false : $_POST['ims_sync_settings'];
		update_site_option( 'ims_sync_settings', $val );
	}

	/**
	*Add WPMU optons
	*
	*@return void
	*@since 3.0.0
	*/
	function wpmu_options( ){
		$sync = get_site_option( 'ims_sync_settings' );
		echo '<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="ims_settings">' . __( 'Image Store settings sync', $this->domain ) . '</label></td>
				<td>
					<label>
						<input type="checkbox" name="ims_sync_settings" id="ims_settings" value="1" '. checked( 1, $sync, false ) .' />
						' . __( 'Check to use the master settings for all the sites', $this->domain ) . '
					</label>
				</td>
			</tr>
		</table>';
	}
		
	/**
	*Load admin styles
	*
	*@return void
	*@since 3.0.0
	*/
	function load_styles( ){
		global $current_screen;
		if( !$this->in_array( $current_screen->id, $this->screens ) ) 
			return;
		wp_enqueue_style( ' adminstyles', IMSTORE_URL.'/_css/admin.css', false, $this->version, 'all' );
		
		if( !is_readable( IMSTORE_ABSPATH . "/admin/_key") ) 
			echo '<div class="updated fade"><p>' . __( "Please, make <strong>image-store/admin/_key</strong> readeable", $this->domain ) . '</p></div>';
		
		if( is_multisite( ) && empty( $this->opts ) ) 
			echo '<div class="error fade"><p>' . __( "Options not available, please reset all settings under the reset tab.", $this->domain ) . '</p></div>';
		
		if( $current_screen->id == 'ims_gallery_page_ims-sales' ) 
			wp_enqueue_style( 'print', IMSTORE_URL.'/_css/print.css', false, $this->version, 'print' );
		if( $current_screen->id == 'ims_gallery_page_ims-pricing' )
			wp_enqueue_style( 'datepicker',IMSTORE_URL.'/_css/jquery-datepicker.css',false, $this->version, 'all' );
	}

	/**
	*Return image path for ims_images to be edited
	*
	*@param string $filepath
	*@param unit $postid
	*@return string
	*@since 0.5.0 
	*/	
	function load_ims_image_path( $filepath, $postid ){
		if( 'ims_image' != get_post_type( $postid ) )
			return $filepath;
		
		$imagedata = get_post_meta( $postid, '_wp_attachment_metadata', true );
		
		if( stristr( $imagedata['file'], 'wp-content' ) !== false ) 
			return str_ireplace( '_resized/', '', $imagedata['file'] );
		else  return $this->content_dir . "/" . str_ireplace( '_resized/', '', $imagedata['file'] );
	}

	/**
	*Add additional image sizes for gallery images
	*
	*@param array $size
	*@return string
	*@since 3.0.0
	*/
	function alter_image_sizes( $sizes ){
		global $pagenow;
		if( $pagenow == 'upload-img.php' || 
		 ( isset( $_REQUEST['postid'] ) && 'ims_image' == get_post_type( $_REQUEST['postid'] )) ){
			$sizes = apply_filters ( 'ims_aternative_image_sizes', array( 'mini', 'thumbnail', 'preview' ) );
		}
		return $sizes;
	}
		
	/*Movie resized images to a subfolder
	*
	*@return void
	*@since 3.0.0
	*/
	function move_resized_file( $file ){
		global $pagenow;
		if( preg_match( " /(_resized)/i", $file ) )
			return $file;
			
		//move files and upatedata
		if( $pagenow == 'upload-img.php' || 
		 ( isset( $_REQUEST['postid'] ) && 'ims_image' == get_post_type( $_REQUEST['postid'] )) ){
			$pathinfo 	= pathinfo( $file );
			
			$despath 	= $pathinfo['dirname'] . "/_resized/";
			if( !file_exists( $despath ) ) @mkdir( $despath, 0775, true );
			if ( copy( $file, $despath . $pathinfo['basename'] ) ){
			 	@unlink( $file ); 
				$file = $despath . $pathinfo['basename']; 
			}
		}
		return $file;
	}
	
	/**
	* Generate aditions metadata for image
	*
	*@param array $metadata
	*@param unit $attachment_id
	*@return array
	*@since 3.0.0
	*/	
	function generate_image_metadata( $metadata, $attachment_id ){
		if( 'ims_image' != get_post_type( $attachment_id ) 
		|| empty( $metadata['file'] ) || !defined( 'DOING_AJAX' ) )
			return $metadata;
		
		if( stristr( $metadata['file'], 'wp-content' ) !== false )
			$path = dirname( str_ireplace( $this->content_dir, '', $metadata['file'] ) );
		else $path =	dirname( $metadata['file'] );
		
		if( !preg_match( " /(_resized)/i", $path ) )
			$path = "$path/_resized";
		
		//generate mini image for thumbnail edit
		if ( isset( $_REQUEST['target'] ) &&  
		'thumbnail' == preg_replace( '/[^a-z0-9_-]+/i', '', $_REQUEST['target'] ) ){
			$resized_file = image_resize( 
				$this->content_dir . "$path/" . $metadata['sizes']['thumbnail']['file'] , 
				get_option("mini_size_w" ), get_option("mini_size_h" ), true 
			);
			if ( !is_wp_error( $resized_file ) && $resized_file && $info = getimagesize( $resized_file ) )
				$metadata['sizes']['mini'] = array(
					'file' => basename( $resized_file ),
					'width' => $info[0],
					'height' => $info[1],
				 );
		}
	
		if( empty( $metadata['sizes']['mini'] ) || empty( $metadata['sizes']['preview'] ) || empty($metadata['sizes']['thumbnail'] ) ){
			$filename = basename( $metadata['file'] );
			$orginal_data =  array( 'file' => $filename, 'width' =>$metadata['width'], 'height' => $metadata['height']  );
			if( !file_exists( $this->content_dir . "/$path/" .$filename  ) )
				@copy( $this->content_dir . '/' . $metadata['file'],  $this->content_dir . "/$path/" .$filename );
		}
		
		if( empty( $metadata['sizes']['mini']  ) )
			$metadata['sizes']['mini'] = $orginal_data;
		
		if( empty( $metadata['sizes']['preview']  ) )
			$metadata['sizes']['preview'] = $orginal_data;
		
		if( empty( $metadata['sizes']['thumbnail']  ) )
			$metadata['sizes']['thumbnail'] = $orginal_data;
		
		foreach( $metadata['sizes'] as $size => $sizedata ){
			$metadata['sizes'][$size]['path'] = $this->content_dir . "/$path/". $sizedata['file'];
			$metadata['sizes'][$size]['url'] = $this->content_url . "/$path/" . $sizedata['file'];
		}
		return $metadata;
	}
	
	/**
	*Load admin scripts
	*
	*@return void
	*@since 2.0.0
	*/
	function load_admin_scripts( ){
		global $current_screen;
		if( !$this->in_array( $current_screen->id, $this->screens ) ) 
			return;
			
		if( $current_screen->id == 'ims_gallery_page_ims-pricing' ){
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'jquery-ui-draggable' );
			
			wp_enqueue_script( 'ims-gallery', IMSTORE_URL.'/_js/galleries.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_script( 'datepicker', IMSTORE_URL.'/_js/jquery-ui-datepicker.js', array( 'jquery' ), $this->version );
		}
		
		$jquery = array( 'dd', 'D', 'd', 'DD', '*', '*', '*', 'o', '*', 'MM', 'mm', 'M', 'm', '*', '*', '*', 'yy', 'y' );
		$php 	= array( '/d/', '/D/', '/j/', '/l/', '/N/', '/S/', '/w/', '/z/', '/W/', '/F/', '/m/', '/M/', '/n/', '/t/', '/L/', '/o/', '/Y/', '/y/' );
		$format = preg_replace($php,$jquery,get_option( 'date_format') );
		
		wp_enqueue_script( 'ims-admin', IMSTORE_URL.'/_js/admin.js', array( 'jquery' ), $this->version, true );
		wp_localize_script( 'ims-admin', 'imslocal', array( 'download' => __( 'Downloadable', $this->domain ),
		'deletelist' => __( 'Are you sure that you want to delete this list?', $this->domain ),	'nonceajax' => wp_create_nonce( 'ims_ajax' ), 
		'imsajax' => IMSTORE_ADMIN_URL . '/ajax.php', 'deletepackage' => __( 'Are you sure that you want to delete this package?', $this->domain ),
		'dateformat'	=> $format 
		) );
	}
	
	/**
	*Initial actions
	*
	*@return void
	*@since 3.0.0
	*/
	function admin_init( ){
		global $user_ID, $pagenow;
		
		$this->uid			= $user_ID;
		$this->pagenow	= $pagenow;
		$this->spageid	= get_option( 'ims_page_secure' );
		$this->uopts 		= get_option( 'ims_user_options' );
		$this->page		= isset($_GET['page']) ? $_GET['page'] : false;
		$this->galid 		= isset($_GET['post']) ? (int)$_GET['post'] : false;	
		$this->action 		= isset($_GET['action']) ? $_GET['action'] : false;	
		
		if( $this->galid ) 		$url = $this->pagenow	. "?post=$this->galid&action=" . $this->action;
		elseif( $this->page ) 	$url = $this->pagenow	. '?post_type=ims_gallery&page=' . $this->page;
		else							$url = $this->pagenow	. '?post_type=ims_gallery';
		
		$this->pageurl	= admin_url( $url );

		$user_status = array( 
			'active' 	=> __( 'Active', $this->domain ),
			'inative'	=> __( 'Inative', $this->domain ),
		 );
		$user_fields = array(
			'ims_address' => __( 'Address', $this->domain ),
			'ims_city' 		=> __( 'City', $this->domain ),
			'ims_state' 	=> __( 'State', $this->domain ),
			'ims_zip' 		=> __( 'Zip', $this->domain ),
			'ims_phone' 	=> __( 'Phone', $this->domain ),
		 );
		
		$this->user_fields = apply_filters( 'ims_user_fields', $user_fields );
		$this->user_status = apply_filters( 'ims_user_status', $user_status, NULL );
		$this->screens = array( 'ims_gallery_page_ims-settings', 'ims_gallery_page_ims-customers',
		'ims_gallery_page_ims-pricing', 'ims_gallery_page_ims-sales', 'edit-ims_album', 'ims_gallery', 'edit-ims_gallery' );
		
		do_action( 'ims_admin_init', $this );
	}
	
	/**
	*Get all price list
	*
	*@return array
	*@since 3.0.0
	*/
	function get_pricelists( ){
		global $wpdb;
		
		$pricelists = wp_cache_get( 'ims_pricelists' );
		if ( false == $pricelists ){
			$pricelists = $wpdb->get_results("SELECT DISTINCT ID, post_title FROM $wpdb->posts WHERE post_type = 'ims_pricelist'" );
			wp_cache_set( 'ims_pricelists', $pricelists );
		}
		return $pricelists;
	}
	
	/**
	*Get all packages
	*
	*@return array
	*@since 3.0.0
	*/
	function get_packages( ){
		global $wpdb;
		
		$packages = wp_cache_get( 'ims_packages' );
		if ( false == $packages ){
			$packages = $wpdb->get_results("SELECT DISTINCT ID, post_title FROM $wpdb->posts WHERE post_type = 'ims_package'" );
			wp_cache_set( 'ims_packages', $packages );
		}
		return $packages;
	}
	
	/**
	*save user screen settings
	*
	*@return void
	*@since 3.0.0
	*/
	function save_screen_option( ){
		if( !isset( $_POST['ims_screen_options'] ) || 
		!isset( $this->uid ) || !is_numeric( $this->uid ))
			return;
			
		update_user_meta( $this->uid, $_POST['ims_screen_options']['option'], $_POST['ims_screen_options']['value'] );
		do_action( 'ims_update_screen_settings', $this->pageurl );
		wp_redirect( $this->pageurl . "&ms=40" );	
		die( );
	}
	
	/**
	*Display album link 
	*
	*@return array
	*@since 3.0.0
	*/
	function add_taxonomy_link( $actions, $tag ){
		if( isset($actions['view']) ) return $actions; 
		
		$actions['view'] = '<a href="'.get_term_link( $tag , $tag->taxonomy ).'" title="'. 
		sprintf( __( 'View %s', $this->domain ),$tag->name).'">'.__( 'View', $this->domain ).'</a>';
		return $actions;
	}
	
	/**
	*Add ID Column
	*
	*@param array $columns
	*@since 2.1.1
	*return unit|string
	*/
	function add_id_column($columns){
		if( current_user_can( 'manage_categories' ) ) 
			$columns['id'] = 'ID';
		return $columns;
	}
	
	/**
	*Add value to ID album Column
	*
	*@param null $none
	*@param string $column_name
	*@param unit $postid
	*@since 2.1.1
	*return unit|string
	*/
	function show_cat_id( $none, $column_name, $id ){
		if ( $column_name == 'id' )
			return $id;
	}	
	
	/**
	*Display aditional colums for 
	*cutomer status
	*
	*param array $columns
	*@return array
	*@since 2.0.0
	*/
	function add_columns( $columns ){ 
		global $current_screen;
		switch($current_screen->id){
			case "edit-ims_gallery":
				return array(
					'cb' 		=> '<input type="checkbox">',	
					'title'		=> __( 'Gallery', $this->domain ), 'galleryid' => __( 'Gallery ID', $this->domain ),
					'visits' 	=> __( 'Visits', $this->domain ), 'tracking'	=> __( 'Tracking', $this->domain ),
					'images' 	=> __( 'Images', $this->domain ), 'author' => __( 'Author', $this->domain ),
					'expire' 	=> __( 'Expires', $this->domain ), 'date' => __( 'Date', $this->domain ) 
				 );
				break;
			default:
				if( !isset($_GET['role']) || $_GET['role'] != 'customer' ) 
					return $columns;
				return array(
				'cb' 		=> '<input type="checkbox">', 'username' => __( 'Username', $this->domain ),
				'fistname'	=> __( 'First Name', $this->domain ), 'lastname' => __( 'Last Name', $this->domain ),
				'email' 	=> __( 'E-mail', $this->domain ), 'city' => __( 'City', $this->domain ),
				'phone' 	=> __( 'Phone', $this->domain ), 'status' => __( 'Status', $this->domain )
			 );
		}
	}
	
	/**
	 *Add status column to users screen
	 *
	 *@param null $null
	 *@param array $column_name
	 *@param unit $user_id
	 *@return string
	 *@since 2.0.0
	*/
	function add_columns_val( $null,$column_name,$user_id){
		$data = get_userdata($user_id );
		switch($column_name){
			case 'status':
				return isset( $data->ims_status ) 
					? $this->user_status[$data->ims_status] : false ;
				break;
			case 'fistname':
				return $data->first_name;
				return isset( $data->first_name ) 
					? $data->first_name: false ;
				break;
			case 'lastname':
				return isset( $data->last_name ) 
					? $data->last_name: false ;
				break;
			case 'city':
				return isset( $data->ims_city ) 
					? $data->ims_city: false ;
				break;
			case 'phone':
				return isset( $data->ims_phone ) 
					? $data->ims_phone: false ;
				break;
			default:
		}
	}
	
	/**
	 *Add stuts column to galleries
	 *
	 *@param null $null
	 *@param array $column_name
	 *@param unit $user_id
	 *@return string
	 *@since 2.0.0
	*/
	function add_columns_val_gal($column_name,$postid){
		global $post,$wpdb;
		switch($column_name){
			case 'galleryid':
				echo get_post_meta( $postid, '_ims_gallery_id', true );
				break;
			case 'visits':
				echo get_post_meta( $postid, '_ims_visits', true );
				break;
			case 'tracking':
				echo get_post_meta( $postid, '_ims_tracking', true );
				break;
			case 'images':
				echo $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_parent = $postid AND post_status = 'publish'" );
				break;	
			case 'expire':
				echo( $post->post_expire == '0000-00-00 00:00:00') 
					? '' : date_i18n( $this->dformat, strtotime($post->post_expire) );
				break;
			default:
		}
	}
	
	/**
	*ImStore admin menu	
	*
	*@return void
	*@since 0.5.0 
	*/
	function add_menu( ){
		
		if( empty( $this->opts['disablestore'] ) ){
			add_submenu_page( 'edit.php?post_type=ims_gallery',__( 'Sales', $this->domain ),__( 'Sales', $this->domain ),
				'ims_read_sales', 'ims-sales', array( $this, 'show_menu') );
			add_submenu_page( 'edit.php?post_type=ims_gallery',__( 'Pricing', $this->domain ),__( 'Pricing', $this->domain ),
				'ims_change_pricing', 'ims-pricing', array( $this, 'show_menu') );
			add_submenu_page( 'edit.php?post_type=ims_gallery',__( 'Customers', $this->domain ),__( 'Customers', $this->domain ),
				'ims_manage_customers', 'ims-customers', array( $this, 'show_menu') );
		}
		
		add_submenu_page( 'edit.php?post_type=ims_gallery',__( 'Settings', $this->domain ),__( 'Settings', $this->domain ),
			'ims_change_settings', 'ims-settings', array( $this, 'show_menu') );
		
		global $current_user;
		if( isset( $current_user->allcaps['ims_read_galleries'] ) )
			add_users_page(__( 'Image Store', $this->domain ),__( 'Galleries', $this->domain ),
				'ims_read_galleries', 'user-galleries', array( $this, 'show_menu') );
	}
	
	/**
	*Display additional customer roloe
	*profile fields in edit profile screens
	*
	*@param obj $profileuser
	*@return void
	*@since 2.0.0
	*/
	function profile_fields( $profileuser ){
		
		if( empty( $profileuser->caps['customer']) ) 
			return;
		
		echo '<h3>',__( 'Address Information', $this->domain ), '</h3>';
		echo '<table class="form-table">';
		foreach( $this->user_fields as $key => $label )
		echo '<tr>
					<th><label for="',$key, '">',$label, '</label></th>
					<td><input type="text" name="',$key, '" id="',$key, '" value="', ( isset($profileuser->$key)? esc_attr( $profileuser->$key ) : '' ), '" class="regular-text" /></td>
				</tr>';
		echo '</table>';
	}
	
	/**
	*Allow wp_insert_post to ad expiration date 
	*on the custom "post_expire "column
	*
	*@param array $data
	*@param array $postarg
	*@return array
	*@since 0.5.0 
	*/
	function insert_post_data( $data ){
		if( $data['post_type'] == 'ims_gallery' ){
			if( empty( $data['post_content'] )) 
				$data['post_content'] = '[ims-gallery-content]';
			if( $this->pagenow !== 'post-new.php' ) 
				$data['post_expire'] = ( isset( $_POST['_ims_expire'] ) ) ? $_POST['_ims_expire'] : false ;
		}
		if( $data['post_type'] == 'ims_promo' ) 
			$data['post_expire'] = $_POST['expiration_date'];
		return $data;
	}
	
	/**
	*Save customer information using
	*wordpress edit profile screen
	*
	*@param unit $user_id
	*@return void
	*@since 3.0.0
	*/
	function update_user( $user_id ){
		if( empty($_REQUEST['role']) || $_REQUEST['role'] != 'customer' )
			return;
			
		foreach( $this->user_fields as $key => $label ){
			$data = isset( $_POST[$key] ) ? $_POST[$key] : '';
			update_user_meta( $user_id, $key, $data );
		}
			
		if( !get_user_meta( $user_id, 'ims_status' ) )
			update_user_meta( $user_id, 'ims_status', 'active' );
	}
	 
	/**
	*Return link count status by type
	*
	*@param string $type
	*@since 3.0.0
	*return bool
	*/
	function count_links( $stati = array( ), $args = array() ){
		global $wpdb;
		
		$default = array(
			'type'=> NULL,
			'default_status' => 'active'
		 ); 
		$args = wp_parse_args( $args, $default );
		extract( $args );
			
		if( $type == null ) return false;
			
		switch($type){ //, count(meta_key) count 
			case 'customer':
				$query = "SELECT um.meta_value status, count(um.meta_value) count 
				FROM $wpdb->usermeta um LEFT JOIN $wpdb->usermeta ur ON um.user_id = ur.user_id 
				WHERE um.meta_key = 'ims_status'  
				AND ( ur.meta_key =  '{$wpdb->prefix}capabilities' AND ur.meta_value LIKE '%customer%' ) GROUP by um.user_id";
				break;
			case 'order':
				$query = "SELECT post_status AS status, count(post_status) AS count FROM $wpdb->posts
				WHERE post_type = 'ims_{$type}'AND post_status != 'draft' GROUP by post_status";
				break;
			case 'image':
				$query = "SELECT post_status AS status, count(post_status) AS count FROM $wpdb->posts WHERE post_type = 'ims_image' 
				AND post_status != 'auto-draft' AND post_parent = $postid GROUP by post_status";
				break;
			default:
				$query = "";
		}
		
		$r = $wpdb->get_results( $query );
		if( empty( $r )) return false;
		
		$status	= ( isset($_GET['status']) ) ? $_GET['status'] : $default_status;	
		
		foreach($r as $obj){
			$current = ( $status == $obj->status) ? ' class="current"' : '';
			$links[] = '<li class="status-'.$obj->status.'">
				<a href="'.$this->pageurl . '&amp;status='.$obj->status.'"'.$current.'>'.$stati[$obj->status].' <span class="count">(<span>'.$obj->count.'</span>)</span></a>
			</li>';
		}
		
		$links = apply_filters( "ims_{$type}_status_links" , $links, $r, $this->pageurl );
		echo implode( ' | ',$links );
	}
	
	/**
	*Get all customers
	*
	*@since 0.5.0
	*return array
	*/
	function get_active_customers( ){
		$customers = wp_cache_get( 'ims_customers' );
		if ( false == $customers ){
			global $wpdb;

		 	$q = "SELECT DISTINCT ID, user_login FROM $wpdb->users AS u 
			LEFT JOIN $wpdb->usermeta um ON u.ID = um.user_id
			LEFT JOIN $wpdb->usermeta ur ON u.ID = ur.user_id 
			WHERE um.meta_key = 'ims_status' AND um.meta_value = 'active' 
			AND ( ur.meta_key = '{$wpdb->prefix}capabilities' AND ur.meta_value LIKE '%customer%' )";
		
			$customers = $wpdb->get_results( $q );
			wp_cache_set( 'ims_customers', $customers );
		}
		return $customers;
	}
	
	/**
	 *Delete folder
	 *
	 *@param string $dir 
	 *@since 2.0.0
	 *return boolean
	*/
	function delete_folder($dir){
		if( $dh = @opendir($dir)){
			while( false !== ( $obj = readdir($dh) ) ){
				if( $obj == '.' || $obj == '..') continue;
				if( is_dir( "$dir/$obj" ) ) 
					$this->delete_folder( "$dir/$obj" );
				else @unlink( "$dir/$obj" ); 
			}
			closedir( $dh );
			return rmdir( $dir );
		}
	}
	
	/**
	 *Delete image folder
	 *
	 *@param unit $postid
	 *@since 2.0.0
	 *return void
	*/
	function delete_post( $postid ){
		if( !current_user_can( 'ims_manage_galleries') 
		|| !$this->opts['deletefiles'] || 'ims_gallery' != get_post_type( $postid ) )
			return $postid;
					
		if( $folderpath = get_post_meta( $postid, '_ims_folder_path', true ))
			$this->delete_folder( $this->content_dir . $folderpath );
		return $postid;
	}
	
	/*Register screen columns
	*
	*@return void
	*@since 3.0.0
	*/
	function register_screen_columns( ){
		global $current_screen;
		
		if( empty( $current_screen ) ) return;
		
		switch( $current_screen->id ){
			case 'profile_page_user-galleries':
				register_column_headers( 'profile_page_user-galleries', array(
					'gallery'			=> __( 'Gallery', $this->domain ),
					'galleryid'		=> __( 'Gallery ID', $this->domain ),
					'password' 	=> __( 'Password', $this->domain ),
					'expire' 			=> __( 'Expires', $this->domain ),
					'images' 		=> __( 'Images', $this->domain ),
				) );
				break;
			default:
		}
	}
	
	/**
	*Add screen settings to 
	*image store screens
	*
	*@return void
	*@since 3.0.0
	*/
	function screen_settings( ){
		global $current_screen;
		
		$option = array( );
		switch( $current_screen->id ){
			case 'ims_gallery_page_ims-customers':
				$option['ims_sales_per_page'] = __( 'Customers', $this->domain );
				break;
			case 'ims_gallery':
				$option['ims_gallery'] = __( 'Images', $this->domain );
				break;
			case 'ims_gallery_page_ims-pricing':
				$option['ims_pricing_per_page'] = __( 'Promotions', $this->domain );
				break;
			case 'profile_page_user-galleries':
				$option['ims_user_galleries_per_page'] = __( 'Galleries', $this->domain );
				break;
			case 'ims_gallery_page_ims-sales':
				if( isset( $_REQUEST['details']) ) return;
				$option['ims_user_sales_per_page'] = __( 'Sales', $this->domain );
				break;
			default:
		}
		$out = '';
		
		foreach( $option as $key => $label ){
			$this->per_page = (int) get_user_option( $key );
			if( empty( $this->per_page ) || $this->per_page < 1 ) $this->per_page = 20;
			$out = "<div class='screen-options'>\n";
			$out .= '<h5>'.__( 'Show per page', $this->domain ).'</h5>';
			$out .= '<input type="text" class="screen-per-page" name="ims_screen_options[value]" id="' . $key . '" maxlength="3" value="' . esc_attr( $this->per_page ) . '" > ';
			$out .=	'<label for="' . $key . '">'. $label .'</label>';
			$out .=	'<input type="submit" class="button" value="'. esc_attr__( 'Apply', $this->domain ).'">';
			$out .= '<input type="hidden" name="ims_screen_options[option]" value="' . esc_attr( $key ) . '" />';
			$out .= "</div>\n";
		}
		return $out;
	}
 }
?>
