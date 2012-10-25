<?php 

/**
*Image store - admin settings
*
*@package Image Store
*@author Hafid Trujillo
*@copyright 20010-2012
*@since 3.0.0
*/

class ImStoreSet extends ImStoreAdmin{
 	
	/**
	*Constructor
	*
	*@return void
	*@since 3.0.0
	*/
	function ImStoreSet( ){
		
		parent::ImStoreAdmin( );
		
		//speed up ajax we don't need this
		if( defined( 'DOING_AJAX' ) || defined( 'DOING_AUTOSAVE' || defined( 'SHORTINIT')) ) 
			return;
		
		ob_start( );
		add_action( 'admin_init', array( $this, 'save_settings' ), 10 );
		add_action( 'ims_settings', array( $this, 'watermark_location' ),2, 1 );
		add_action( 'admin_print_styles', array( $this, 'register_screen_columns' ), 0 );
		
		add_filter( 'paginate_links', array( $this, 'user_page_links' ), 20,2 ); 
		add_filter( 'screen_settings', array( $this, 'screen_settings' ), 15,2 ); 
		add_filter( 'pre_user_search', array( $this, 'customer_search_query' ), 20 );
	}
	
	/**
	* Add watermark location option
	*
	*@return void
	*@since 3.0.3
	*/
	function watermark_location( $boxid ){
		if( $boxid != 'image' )
			return;
		
		$option = get_option( 'ims_wlocal' );
		$wlocal = empty( $option ) ? 5 : $option;
		
		echo '<tr class="row-wlocal" valign="top"><td><label>'. __('Watermark location', $this->domain ) .'</label></td><td>';
		echo '<div class="row">
			<label><input name="wlocal" type="radio" value="1" ' . checked( 1, $wlocal , false) . ' /></label>
			<label><input name="wlocal" type="radio" value="2" ' . checked( 2, $wlocal , false) . '/></label>
			<label><input name="wlocal" type="radio" value="3" ' . checked( 3, $wlocal , false) . '/></label>
			</div>';
		echo '<div class="row">
			<label><input name="wlocal" type="radio" value="4" ' . checked( 4, $wlocal , false) . '/></label>
			<label><input name="wlocal" type="radio" value="5" ' . checked( 5, $wlocal , false) . '/></label>
			<label><input name="wlocal" type="radio" value="6" ' . checked( 6, $wlocal , false) . '/></label>
			</div>';
		echo '<div class="row">
			<label><input name="wlocal" type="radio" value="7" ' . checked( 7, $wlocal , false) . '/></label>
			<label><input name="wlocal" type="radio" value="8" ' . checked( 8, $wlocal , false) . '/></label>
			<label><input name="wlocal" type="radio" value="9" ' . checked( 9, $wlocal , false) . '/></label>
			</div>';
		echo '</td></tr>';
	}
		
	/**
	*Get all user except customers
	*and administrators
	*
	*@return void
	*@since 3.0.0
	*/
	function get_users( ){
		
		$users = wp_cache_get( 'ims_users' );
		
		if ( false == $users ){
			global $wpdb;
		
			$q = "SELECT ID, user_login name FROM $wpdb->users u JOIN $wpdb->usermeta um ON ( u.ID = um.user_id ) 
			WHERE meta_key = '{$wpdb->prefix}capabilities' AND meta_value NOT LIKE '%customer%' AND meta_value NOT LIKE '%administrator%'";
		
			$users = $wpdb->get_results( $q );
			wp_cache_set( 'ims_users', $users );
		}
		
		if( empty($users) )
			return array( '0' => __( 'No users to manage', $this->domain ) );
		$list = array();
		$list[0] = __( 'Select user', $this->domain );
		foreach( $users as $user)
			$list[$user->ID] = $user->name;
			
		return $list;
	}
	
	/**
	*Return Image Store options
	*
	*@parm string $option
	*@parm unit $userid 
	*@return string/int
	*@since 3.0.0
	*/
	function vr( $option, $userid = 0 ){
		if( $userid){
			$usermeta = get_user_meta( $userid, 'ims_user_caps', true );
			if( isset($usermeta[$option]) )
				return true;
			return false;
		}
		if( isset($this->opts[$option]) ) 
			return esc_attr( $this->opts[$option] );
		elseif( $o = get_option($option)) 
			return $o;
		return false;
	}
	
	/**
	*Check if it's a checkbox
	*or radio box
	*
	*@parm string $elem
	*@return bool
	*@since 3.0.0
	*/
	function is_checkbox( $type ){ 
		if( $this->in_array( $type, array( 'checkbox', 'radio')) )
			return true;
		return false;
	} 
	
	/**
	*Display unit sizes
	*
	*@return void
	*@since 1.1.0
	*/
	function dropdown_units( $name, $selected ){
		$output = '<select name="'. $name.'" class="unit">';
		foreach($this->units as $unit => $label){
			$select = ($selected == $unit) ?' selected="selected"':'';
			$output .= '<option value="'. esc_attr( $unit ).'" '.$select.'>'.$label.'</option>';
		}
		echo $output .= '</select>';
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
			case 'ims_gallery_page_ims-customers':
				$columns = array(
					'cb' 			=> '<input type="checkbox">', 'name' 		=> __( 'First Name', $this->domain ),
					'lastname' 	=> __( 'Last Name', $this->domain ), 'email' => __( 'E-Mail', $this->domain ),
					'phone' 		=> __( 'Phone', $this->domain ), 'city' 			=> __( 'City', $this->domain ), 'state' 	=> __( 'State', $this->domain ),
				 ); 
				
				if( class_exists( 'MailPress') )
					$columns['newsletter'] = __( 'eNewsletter', $this->domain );
					
				register_column_headers( 'ims_gallery_page_ims-customers', $columns );
				break;
			case 'ims_gallery_page_ims-pricing':
				register_column_headers( 'ims_gallery_page_ims-pricing', array(
					'cb' 		=> '<input type="checkbox">',
					'name' 	=> __( 'Name', $this->domain ), 'code' 	=> __( 'Code', $this->domain ),
					'starts' 	=> __( 'Starts', $this->domain ), 'expires' => __( 'Expires', $this->domain ),
					'type'		=> __( 'Type', $this->domain ), 'discount'	=> __( 'Discount', $this->domain ),
				) );
				break;
			case 'ims_gallery_page_ims-sales':
				if( isset( $_REQUEST['details']) ) return;
				register_column_headers( 'ims_gallery_page_ims-sales' ,array(
					'cb' 		=> '<input type="checkbox">',	
					'ordernum'	=> __( 'Order number', $this->domain ),'orderdate'=> __( 'Date', $this->domain ),
					'amount' 	=> __( 'Amount', $this->domain ),'customer' 	=> __( 'Customer', $this->domain ),
					'images' 	=> __( 'Images', $this->domain ),'paystatus' => __( 'Payment status', $this->domain ),
					'orderstat' => __( 'Order Status', $this->domain ),
				));
				break;
			default:
		}
	}
	
	/**
	*Modify paging link for customers
	*
	*@return string
	*@since 3.0.0
	*/	
	function user_page_links( $link ){
		if( $this->page != 'ims-customers')
			return $link;
		return str_replace( 'users.php?', 'edit.php?post_type=ims_gallery&page=ims-customers&', $link );
	}
	
	/**
	*Filter user results by status in 
	*the image store customer screen.
	*
	*@param obj $query
	*@return void
	*@since 3.0.0
	*/
	function customer_search_query( &$query ){
		global $wpdb;
		
		if( $this->page != 'ims-customers' ) 
			return;
			
		$s = " meta_value LIKE '%$query->search_term%' OR display_name";
		$status = isset($_GET['status']) ? $_GET['status'] : 'active';	

		if( $query->search_term )
			$query->query_where = str_ireplace( 'display_name',$s, $query->query_where );
		else $query->query_where .= " AND $wpdb->usermeta.user_id IN (
			SELECT u.user_id FROM $wpdb->usermeta u 
			WHERE meta_key = 'ims_status' AND meta_value = '$status' GROUP by u.user_id 
		)";
		
		if( isset( $this->per_page ) ){
			$page = ( $query->page - 1 );
			$limit = ( $page ) ? ($this->per_page * $page) : $this->per_page;
			$query->query_limit = " LIMIT $page, $limit";
			$query->users_per_page = $this->per_page;
		}
	}
	
	
	/**
	*Create package
	*
	*@return array on error
	*@since 3.0.0
	*/
	function create_package( ){		
		$errors = new WP_Error( );
		if( empty($_POST['package_name']) ){
			$errors->add( 'empty_name',__( 'A name is required.', $this->domain ) );
			return $errors;
		}
		
		$price_list = array(
				'post_status'	=> 'publish',
				'post_type' 	=> 'ims_package',
				'post_title' 	=> $_POST['package_name'],
		 );
		
		$list_id = wp_insert_post( $price_list );
		if( empty( $list_id ) ){
			$errors->add( 'list_error',__( 'There was a problem creating the package.', $this->domain ) );
			return $errors;
		}
		
		wp_redirect( $this->pageurl . "&ms=35#packages" );
		die( );
	}
	
	
	/**
	*Create new list
	*
	*@return array on error
	*@since 3.0.0
	*/
	function create_pricelist( ){
		$errors = new WP_Error( );
		
		if( empty($_POST['pricelist_name'])){
			$errors->add( 'empty_name', __( 'A name is required.', $this->domain ) );
			return $errors;
		}
		$price_list = array(
				'post_status'	=> 'publish',
				'post_type' 	=> 'ims_pricelist',
				'post_title' 		=> $_POST['pricelist_name'],
		 );
		
		$list_id = wp_insert_post( $price_list );
		if( empty($list_id)){
			$errors->add( 'list_error', __( 'There was a problem creating the list.', $this->domain ) );
			return $errors;
		}
		
		add_post_meta( $list_id, '_ims_list_opts', 
			 array( 'ims_bw' => 0, 'ims_sepia' => 0, 'ims_ship_local' => 0, 'ims_ship_inter' => 0 ) 
		);
		wp_redirect( $this->pageurl . "&ms=38" );
		die( );
	}
	
	
	/**
	*Update list
	*
	*@return array on error
	*@since 3.0.0
	*/
	function update_pricelist( ){
		if( empty($_POST['listid']) ) 
			return;
		
		$errors = new WP_Error( );
		if( empty($_POST['list_name'])){
			$errors->add( 'empty_name',__( 'A name is required.', $this->domain ) );
			return $errors;
		}
		
		// price list
		$options = array(
			'ims_bw' 			=> $_POST['_ims_bw'],
			'ims_sepia' 		=> $_POST['_ims_sepia'],
			'ims_ship_local' => $_POST['_ims_ship_local'],
			'ims_ship_inter' => $_POST['_ims_ship_inter']
		 );
		
		//print_r(  $_POST['sizes'] ); die();
		update_post_meta( $_POST['listid'], '_ims_list_opts', $options );
		update_post_meta( $_POST['listid'], '_ims_sizes', $_POST['sizes'] );
		
		wp_update_post( array( 'ID' => $_POST['listid'], 'post_title' => $_POST['list_name']) );
		wp_redirect($this->pageurl."&ms=34" );
		die( );
	}

	/**
	*Update package
	*
	*@return array on error
	*@since 3.0.0
	*/
	function update_package( ){
		if( empty($_POST['packageid']) ) 
			return;
			
		$errors = new WP_Error( );
		if( empty($_POST['packagename'])){
			$errors->add( 'empty_name',__( 'A name is required.', $this->domain ) );
			return $errors;
		}
		$sizes = array();
		foreach($_POST['sizes'] as $size){
			$sizes[$size['name']]['unit'] = $size['unit'];
			$sizes[$size['name']]['count'] = $size['count'];
		}
		
		$id = intval($_POST['packageid'] );
		update_post_meta($id, '_ims_sizes',$sizes );
		update_post_meta($id, '_ims_price',$_POST['packageprice'] );
		wp_update_post(array( 'ID' => $id, 'post_title' => $_POST['packagename']) );
		
		wp_redirect($this->pageurl."&ms=33#packages" );
		die( );
	}
	
	/**
	*Add/update promotions
	*
	*@return void
	*@since 3.0.0
	*/
	function add_promotion( ){
		$errors = new WP_Error( );
		if( empty($_POST['promo_name']))
			$errors->add( 'empty_name',__( 'A promotion name is required.', $this->domain ) );
		
		if( empty($_POST['discount']) && $_POST['promo_type'] != 3)
			$errors->add( 'discount',__( 'A discount is required', $this->domain ) );	
			
		if( !empty($errors->errors)) return $errors;
		
		$promotion = array(
			'post_status'	=> 'publish',
			'post_type' 	=> 'ims_promo',
			'post_title' 		=> $_POST['promo_name'],
			'post_date'		=> $_POST['start_date'],	 
			'post_expire'	=> $_POST['expiration_date'],
		 );
		
		$promotion['ID'] = ( empty($_POST['promotion_id'] ) ) ? false : $_POST['promotion_id'];
		$promo_id = ( $promotion['ID'] ) ? wp_update_post( $promotion ) : wp_insert_post( $promotion );
		
		if( empty( $promo_id) ){
			$errors->add( 'promo_error',__( 'There was a problem creating the promotion.', $this->domain ) );
			return $errors;
		}
                
		$data = array();
		foreach( array( 'promo_code', 'promo_type', 'free-type', 'discount', 'items', 'rules' ) as $key ){
			if( isset( $_POST[$key] ) ) 	$data[$key] = $_POST[$key];
		}
		
		$a = ( $promotion['ID'] ) ? 30 : 32; update_post_meta( $promo_id, '_ims_promo_data', $data );
		if( isset( $_POST['promo_code'] ) ) update_post_meta($promo_id, '_ims_promo_code',$_POST['promo_code'] );
		
		wp_redirect($this->pageurl."&ms=$a#promotions" );
		die( );
	}
	
	/**
	*delete promotions
	*
	*@return void
	*@since 3.0.0
	*/
	function delete_promotions( ){
		global $wpdb;
		$errors = new WP_Error( );
		
		if( empty($_GET['delete'] ) && empty($_POST['promo']) ){
			$errors->add( 'nothing_checked',__( 'Please select a promo to be deleted.', $this->domain ) );
			return $errors;
		}
		
		$ids = ( is_numeric( $_GET['delete'] ) ) ? (array)$_GET['delete'] : $_POST['promo'] ;
		$ids = $wpdb->escape( implode( ', ', $ids ) );
		
		$count = $wpdb->query( "DELETE FROM $wpdb->posts WHERE ID IN ($ids) " );
		if( !empty( $count) ) $wpdb->query("DELETE FROM $wpdb->postmeta WHERE post_id IN ($ids) " );
		$a = ( $count< 2 ) ? 31 : 39;
		
		wp_redirect($this->pageurl . "&ms=$a&c=$count#promotions" );
		die( );
	}


	/**
	*Save settings
	*
	*@return void
	*@since 3.0.0
	*/
	function save_settings( ){
		
		if( empty($_POST) || $this->page != 'ims-settings')
			return;

		//reset settings
		if( isset( $_POST['resetsettings'] ) ){
			
			check_admin_referer( 'ims_settings' );
			include(IMSTORE_ABSPATH.'/admin/install.php' );
			
			ImStoreInstaller::imstore_default_options( );
			wp_redirect( $this->pageurl .'&ms=3' );
			die( );
		
		//uninstall
		}elseif( isset( $_POST['uninstall'] ) ){
			
			check_admin_referer( 'ims_settings' );
			include( IMSTORE_ABSPATH.'/admin/install.php' );
			ImStoreInstaller::imstore_uninstall( );
		
		//save options
		}elseif( isset( $_POST['ims-action'] ) ){
			$action = $_POST['ims-action'];
	
			check_admin_referer( 'ims_settings' );
			include( IMSTORE_ABSPATH . "/admin/settings-fields.php" );
			
			//clear image cache data
			@touch( IMSTORE_ABSPATH . "/admin/_key/{$this->key}.txt" );
			
			if( empty( $action ) || empty( $settings[$action] ) ){
				wp_redirect( $this->pageurl );
				die( );
			}
			
			if( 'permissions' == $action ){
				if( !is_numeric( $_POST['userid'] ) ){
					wp_redirect( $this->pageurl );
					die( );
				}
				
				$newcaps = array( );
				$userid = (int)$_POST['userid'];
				foreach( $this->uopts['caplist'] as $cap => $label )
					if( !empty($_POST['ims_'][$cap])) $newcaps['ims_'.$cap] = 1;
				update_user_meta($userid, 'ims_user_caps', $newcaps );	
				
				do_action( 'ims_user_permissions', $action, $userid, $this->uopts );
				wp_redirect( $this->pageurl . "&userid=" .$userid );
				die( );
			}
			
			foreach( $settings[$action] as $key => $val ){
				if( isset( $val['col'] ) ){ 
					foreach( $val['opts'] as $k2 => $v2 ){
						if( empty( $_POST[$k2] ) )
							$this->opts[$k2] = false;
						else $this->opts[$k2] = $_POST[$k2];
					}
				}elseif( isset( $val['multi']) ){
					foreach( $val['opts'] as $k2 => $v2 ){
						if( get_option($key.$k2) )
							update_option($key.$k2, $_POST[$key][$k2] );
						elseif( !empty( $_POST[$key][$k2] ) )
							$this->opts[$key.$k2] = $_POST[$key][$k2];
						else unset( $this->opts[$key.$k2] );
					}
				}elseif( isset($_POST[$key]) ) 
				$this->opts[$key] = $_POST[$key];
				else unset( $this->opts[$key] );
			}
			
			//multisite support
			if( is_multisite( ) && $this->sync == true ) 
				switch_to_blog( 1 );
				
			update_option( $this->optionkey, $this->opts );
			if( isset( $_POST['wlocal'] ) )
				update_option( 'ims_wlocal', $_POST['wlocal'] );
				
			if( isset( $_POST['album_template'] ) ){
				update_option( 'ims_searchable',  
					( empty( $_POST['ims_searchable'] ) ) ? false : $_POST['ims_searchable']
				 );  
			}
		
			do_action( 'ims_save_settings', $action, $settings );
			
			wp_redirect( $this->pageurl .'&ms=4' );
			die( );
		}
	}

	
}

?>