<?php 

/**
*Image store - admin settings
*
*@package Image Store
*@author Hafid Trujillo
*@copyright 20010-2011
*@since 0.5.0
*/

class ImStoreAdmin{
		
	/**
	*Constructor
	*
	*@return void
	*@since 0.5.0 
	*/
	function __construct(){
		global $pagenow;
		
		//ad a unique Gallery IDentifier to make sure that the actions come from this plugin
		if(('media-upload.php' == $pagenow || 'async-upload.php' == $pagenow) && isset($_GET['imstore'])){
			add_filter('media_send_to_editor',array($this,'media_send_to_editor'),1,3);
			add_filter('media_upload_form_url',array($this,'media_upload_form_url'),1,1); 
		}
		
		$this->useropts = get_option('ims_user_options');
		$this->opts = (array)get_option('ims_front_options'); 
		add_filter('intermediate_image_sizes',array($this,'image_sizes'),15,1);
		add_filter('get_attached_file',array($this,'load_ims_image_path'),15,2);
		add_filter('load_image_to_edit_path',array($this,'load_ims_image_path'),15,2);
		add_filter('wp_update_attachment_metadata',array($this,'update_attachment_metadata'),15,2);
		
		add_filter('intermediate_image_sizes',array($this,'ims_image_sizes'),15,1);
		add_filter('manage_edit-ims_gallery_columns',array($this,"add_columns"),10);
		add_filter('manage_posts_custom_column',array($this,'add_columns_val_gal'),15,2);
		
		//speed up ajax we don't need this
		if(defined('DOING_AJAX') || defined('DOING_AUTOSAVE')) return;
		
		add_action('admin_menu',array($this,'add_menu'),20);	
		add_action('save_post',array($this,'save_post'),1,2);
		add_action('admin_init',array($this,'int_actions'),1);	
		add_action('delete_post',array($this,'delete_post'),1);
		add_action('edit_user_profile',array($this,'profile_fields'),1);
		add_action('show_user_profile',array($this,'profile_fields'),1);
		add_action('post_edit_form_tag',array($this,'multidata_form'),20);	
		add_action('admin_print_styles',array($this,'load_admin_styles'),1);
		add_action('ims_album_pre_add_form',array($this,'add_album_link'),1);
		add_action('admin_print_scripts',array($this,'load_admin_scripts'),1);
		add_action('admin_print_styles',array($this,'register_screen_columns'),10);
		
		add_filter('screen_settings',array($this,'register_screen_columns'),15,2); 		
		add_filter('manage_edit-ims_order_columns',array($this,"add_columns"),10);
		add_filter('manage_users_columns',array($this,'add_columns'),10);
		add_filter('redirect_post_location',array($this,'post_messeges'),25);
		add_filter('post_updated_messages',array($this,'add_auto_password'),1);

		$this->units = array('in' => __('in',ImStore::domain),'cm' => __('cm',ImStore::domain),'px' => __('px',ImStore::domain));
	}

	/**
	 *Save gallery data and images
	 *
	 *@param unit $postid
	 *@param array $post
	 *@since 2.0.0
	 *return unit|string
	*/
	function save_post($postid,$post){
		
		if($post->post_type != 'ims_gallery') return $postid;
		if(!wp_verify_nonce($_POST['ims_save_post'],'ims_save_post') 
		|| !current_user_can('ims_add_galleries')) return $postid;
		
		$ID	= (int)$_POST['ID'];
		@ini_set('memory_limit','256M');
		@ini_set('post_max_size','16M');
		@ini_set('max_execution_time',1000);
		$folderpath	= (empty($_POST['_ims_folder_path']))?"gallery-$ID":$_POST['_ims_folder_path'];
		
		//unload remote zip
		if(!empty($_POST['zipurl'])){
			if(!preg_match('/^http(s)?:\/\//i',$_POST['zipurl'])) 
				return $this->error = 2;
			if(!preg_match('/(zip)$/i',$_POST['zipurl'])) 
				return $this->error = 3;
			
			$filename 	 	= basename($_POST['zipurl']);
			$download_file 	= download_url($_POST['zipurl']);
			if(is_wp_error($download_file)) return $this->error = 1;
		
		//upload zip
		}elseif($filename = $_FILES['zipfile']['name']){
			if(!preg_match('/(zip)$/i',$_FILES['zipfile']['name']))
				return $this->error = 3;
			if($_FILES['zipfile']['error'] != '0' || $_FILES['zipfile']['size'] == 0)
				return $this->error = 5;
			$download_file = $_FILES['zipfile']['tmp_name'];
		}
		
		//scan folder
		global $wpdb;
		if(!empty($_POST['scannfolder']) && !empty($_POST['galleryfolder'])){
			if(!preg_match('/^\//',trim($_POST['galleryfolder']))) 
				$folderpath	 = "/".trim($_POST['galleryfolder']);
			else $folderpath = trim($_POST['galleryfolder']);
			$galpath = WP_CONTENT_DIR.$folderpath;
			@rename($galpath,WP_CONTENT_DIR.$this->sanitize_path($folderpath));
			$wpdb->query(
				"DELETE p,pm FROM $wpdb->posts p 
				LEFT JOIN $wpdb->postmeta pm ON(p.ID = pm.post_id) 
				WHERE post_parent IN($postid)"
			);
			if($dh = @opendir($galpath)){
				$x=0;
				while(false !== ($obj = readdir($dh))){
					if($obj{0} == '.' || !preg_match('/(png|jpg|jpeg|gif)$/i',$obj)) continue;
					$archive[$x]['filename'] = $obj;
					$archive[$x]['status'] = 'ok';
					$x++;
				}
				@closedir($dh);
			}
		}

		// update post info
		update_post_meta($ID,'_ims_order',$_POST['_ims_order']);
		update_post_meta($ID,'_ims_customer',$_POST['customers']);	
		update_post_meta($ID,'_ims_sortby',$_POST['_ims_sortby']);
		update_post_meta($ID,'_ims_visits',$_POST['_ims_visits']);	
		update_post_meta($ID,'_ims_customer',$_POST['customers']);	
		update_post_meta($ID,'_ims_tracking',$_POST['_ims_tracking']);
		update_post_meta($ID,'_ims_downloads',$_POST['_ims_downloads']);	
		update_post_meta($ID,'_ims_price_list',$_POST['_ims_price_list']);
		update_post_meta($ID,'_ims_gallery_id',$_POST['_ims_gallery_id']);
		update_post_meta($ID,'_ims_folder_path',$this->sanitize_path($folderpath));
		
		//update image information
		foreach((array)$_POST['img_title'] as $key => $val){
			$img['ID'] 				= $key;
			$img['post_title']		= $_POST['img_title'][$key];
			$img['menu_order']		= $_POST['menu_order'][$key];
			$img['post_excerpt']	= $_POST['img_excerpt'][$key];
			wp_update_post($img);
		}
		
		//change status or delete files
		global $wpdb;
		if(isset($_POST['doactions']) && !empty($_POST['galleries'])){
			if($_POST['actions'] == 'delete'){
				foreach((array)$_POST['galleries'] as $d){
					if($this->opts['deletefiles']){
						$metadata = get_post_meta($d,'_wp_attachment_metadata');
						if($metadata[0]['sizes']){
							foreach($metadata[0]['sizes'] as $size)
								@unlink(WP_CONTENT_DIR.$folder.'/'.$size['file']);
							@unlink(WP_CONTENT_DIR.$metadata[0]['file']);
							@unlink(WP_CONTENT_DIR.str_replace('_resized/','',$metadata[0]['file']));
						}
					}
					wp_delete_post($d,true);
				}
			}else{
				$wpdb->query(
					"UPDATE $wpdb->posts 
					SET post_status = '".$wpdb->escape($_POST['actions'])."' 
					WHERE ID IN(".$wpdb->escape(implode(',',$_POST['galleries'])).")"
				);
			}
		}
		
		//update image data
		if($download_file||$archive){
			
			if(!$archive){
				include_once(ABSPATH.'wp-admin/includes/class-pclzip.php');
				$PclZip = new PclZip($download_file);
				if(false == ($archive = $PclZip->extract(PCLZIP_OPT_EXTRACT_AS_STRING)))
					return $this->error = 3;
			}else{ $scan = true; }
			
			$img_sizes = get_option('ims_dis_images');
			$img_sizes['thumbnail']['name'] = "thumbnail";
			$img_sizes['thumbnail']['crop'] = '1';
			$img_sizes['thumbnail']['q'] 	= '95';
			$img_sizes['thumbnail']['w'] 	= get_option("thumbnail_size_w");
			$img_sizes['thumbnail']['h'] 	= get_option("thumbnail_size_h");
			
			$downloadsizes = get_option('ims_download_sizes');
			if(is_array($downloadsizes)) $img_sizes += $downloadsizes;
		
			foreach($archive as $file){
				if('__MACOSX/' === substr($file['filename'],0,9) || $file['folder']
				|| !preg_match('/(png|jpg|jpeg|gif)$/i',$file['filename'])) continue;
				
				$filename 	= basename($file['filename']);
				if(preg_match('(^._)',$filename)) continue;
				
				$filename 	= wp_unique_filename($gallerypath,sanitize_file_name($filename));
				$filetype 	= wp_check_filetype($filename);
				$galpath 	= WP_CONTENT_DIR.$folderpath;
				$relative 	= "$folderpath/_resized/$filename";
				$guid		= WP_CONTENT_URL.$relative;
				$filepath	= str_replace('\\','/',"$galpath/$filename");
				$despath 	= str_replace('\\','/',"$galpath/_resized");
				
				if($wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_parent	= $ID AND guid = %s",$guid))) continue;
				if(!file_exists($despath)) @mkdir($despath,0775,true);
				if($scan) @rename("$galpath/".$file['filename'],$filepath);
				if(!$scan) file_put_contents($filepath,$file['content']); @chmod($filepath,0777);
				
				@copy($filepath,"$despath/$filename"); 
				$orininfo = @getimagesize($filepath);
				
				$metadata['file'] 	= $relative;
				$metadata['width'] 	= $orininfo[0];
				$metadata['height'] = $orininfo[1];
				$metadata['url'] 	= $guid;
				$metadata['path'] 	= "$despath/$filename";
				
				list($uwidth,$uheight) = wp_constrain_dimensions($metadata['width'],$metadata['height'],100,100);
				$metadata['hwstring_small'] = "height='$uheight' width='$uwidth'";
				
				switch($orininfo['channels']){ 
					case 1:$metadata['color'] = 'BW'; break;
					case 3:$metadata['color'] = 'RGB'; break;
					case 4:$metadata['color'] = 'CMYK'; break;
					default:$metadata['color'] = __('Unknown',ImStore::domain);
				}
				
				foreach($img_sizes as $size){
					$resized = image_resize($filepath,$size['w'],$size['h'],$size['crop'],NULL,$despath,$size['q']);
					if(!is_wp_error($resized) && $resized && $info = getimagesize($resized)){
						$imgname 	= basename($resized);
					}else{
						$info 		= getimagesize($filepath);
						$imgname 	= basename($filepath);
					}
					$data = array(
						'file'	=>$imgname,
						'width'	=>$info[0],
						'height'=>$info[1],
						'url'	=> dirname($guid)."/$imgname",
						'path'	=> dirname($filepath)."/$imgname",
					);
					$metadata['sizes'][$size['name']] = $data;
					$metadata['image_meta'] = wp_read_image_metadata($filepath);
				}
				
				$title = ($metadata['image_meta']['title']) ? $metadata['image_meta']['title'] : $filename;
				$attachment = array(
					'guid' => $guid,
					'post_parent' => $ID,
					'post_title' => $title,
					'post_type' => 'ims_image',
					'post_status' => 'publish',
					'post_mime_type'=> $filetype['type'],
					'post_excerpt' => $metadata['image_meta']['caption'],
				);
				
				$attach_id = wp_insert_post($attachment);
				if(empty($attach_id)) continue;
				
				wp_update_attachment_metadata($attach_id,$metadata);
			}
		}
	}
	
	/**
	 *Add url and path to the attachment data for "ims_image" post type
	 *
	 *@param array $data
	 *@param unit $postid
	 *@return array
	 *@since 0.5.0 
	*/	
	function update_attachment_metadata($data,$postid){
		$contdir 		= str_replace("\\","/",WP_CONTENT_DIR);
		$data['file'] 	= str_replace($contdir,"",dirname($data['file'])."/".basename($data['file'])); 
		if($data['sizes']['mini'] && !stristr($data['file'],date('Y/m')) && !$data['sizes']['thumbnail']['url']){
			foreach($data['sizes'] as $size => $filedata){
				$data['sizes'][$size]['path'] = dirname($data['path'])."/".$filedata['file'];
				$data['sizes'][$size]['url'] = dirname($data['url'])."/".$filedata['file'];
			}
		}
		return $data;
	}
	
	/**
	*ImStore admin menu	
	*
	*@return void
	*@since 0.5.0 
	*/
	function add_menu(){ 
		global $menu,$submenu; 
		//if store is enable
		if(!$this->opts['disablestore']){
			add_submenu_page('edit.php?post_type=ims_gallery',__('Sales',ImStore::domain),__('Sales',ImStore::domain),
				'ims_read_sales','ims-sales',array($this,'show_menu'));
			add_submenu_page('edit.php?post_type=ims_gallery',__('Pricing',ImStore::domain),__('Pricing',ImStore::domain),
				'ims_change_pricing','ims-pricing',array($this,'show_menu'));
			add_submenu_page('edit.php?post_type=ims_gallery',__('Customers',ImStore::domain),__('Customers',ImStore::domain),
				'ims_manage_customers','ims-customers',array($this,'show_menu'));
		}
		add_submenu_page('edit.php?post_type=ims_gallery',__('Settings',ImStore::domain),__('Settings',ImStore::domain),
			'ims_change_settings','ims-settings',array($this,'show_menu'));
		add_users_page(__('Image Store',ImStore::domain),__('Galleries',ImStore::domain),
			'ims_read_galleries','user-galleries',array($this,'show_menu'));
	}
	
	/**
	 * Add ims mages sizes to 
	 * be updated by ajax image edit.
	 *
	 * @param array $sizes
	 * @return array
	 * @since 0.5.0 
	 */
	function ims_image_sizes($sizes){
		if(!defined('DOING_AJAX')) return $sizes;
		$img_sizes = get_option('ims_dis_images');
		$downloadsizes = get_option('ims_download_sizes');
		if(is_array($downloadsizes)) $img_sizes += $downloadsizes;
		foreach($img_sizes as $name => $values) $sizes[] = $name;
		return $sizes;
	}
	
	/**
	*Add $imstore to the media upload url
	*
	*@param string $form_action_url default url 
	*@return string $imstore and default url 
	*@since 0.5.0 
	*/	
	function media_upload_form_url($form_action_url){
		return str_replace('media-upload.php?','media-upload.php?imstore=1&',$form_action_url);
	}
	
	/**
	*Return Image Store options
	*
	*@parm string $option option name
	*@parm string $key key name if option value is an array
	*@return string/int
	*@since 0.5.0
	*/
	function _vr($option,$key = ''){
		global $ImStore;
		if(!empty($key)) return esc_attr($this->opts[$option][$key]);
		else return esc_attr($this->opts[$option]);
	}
	
	/**
	*display Image Store options
	*
	*@parm string $option option name
	*@parm string $key key name if option value is an array
	*@return void
	*@since 0.5.0
	*/
	function _v($option,$key = ''){
		if(!empty($key)) echo esc_attr($this->opts[$option][$key]);
		else echo esc_attr($this->opts[$option]);
	}
	
	/**
	*Add ims mages sizes to 
	*be updated by ajax image edit.
	*
	*@param array $sizes
	*@return array
	*@since 2.0.0
	*/
	function image_sizes($sizes){
		$img_sizes = get_option('ims_dis_images');
		$downloadsizes = get_option('ims_download_sizes');
		if(is_array($downloadsizes)) $img_sizes += $downloadsizes;
		foreach($img_sizes as $name => $values) $sizes[] = $name;
		return $sizes;
	}
	
	/**
	*Return image path for ims_images to be edited
	*
	*@param string $filepath
	*@param unit $postid
	*@return string
	*@since 0.5.0 
	*/	
	function load_ims_image_path($filepath,$postid){
		global $wpdb;
		if('ims_image' == $wpdb->get_var($wpdb->prepare("SELECT post_type FROM $wpdb->posts WHERE ID = %s",$postid))){
			$imagedata = get_post_meta($postid,'_wp_attachment_metadata'); 
			$filepath = str_replace("\\","/",WP_CONTENT_DIR.$imagedata[0]['file']);
		}
		return $filepath;
	}
	
	/**
	*Add example album link
	*
	*@return void
	*@since 2.0.0
	*/
	function add_album_link(){
		if($this->permalinks) printf(__('To view albums go to: %s/%s/%s',ImStore::domain),
		"<br /> ".WP_SITE_URL,__('albums',ImStore::domain),__('term-slug',ImStore::domain));
		else printf(__('To view albums go to: %s/?taxonomy=ims_album&term=%s',ImStore::domain)
		,"<br /> ".WP_SITE_URL,__('term-slug',ImStore::domain));
	}
	
	/**
	*Get all price list
	*
	*@return array
	*@since 0.5.0
	*/
	function get_ims_pricelists(){
		global $wpdb; return $wpdb->get_results("SELECT DISTINCT ID,post_title FROM $wpdb->posts WHERE post_type = 'ims_pricelist'");
	}
	
	/**
	*Use a custom js function to to process the send image request
	*to prevent conficts with other plugins,accept images only
	*
	*@param string $html link to image file(html formated)
	*@param unit $id image/post id
	*@param array $attachment image data
	*@return string html formated
	*@since 0.5.0 
	*/
	function media_send_to_editor($html,$id,$attachment){
		//$imagurl = str_ireplace(get_option('home'),'',$attachment['url']);
		?><script type="text/javascript">
			/*<![CDATA[*/
			var win = window.dialogArguments || opener || parent || top;
			win.add_watermark_url('<?php echo addslashes($attachment['url']);?>');
			/*]]>*/
		</script><?php
		return $html;
	}
	
	/**
	*Change media upload icons links
	*
	*@param string $src 
	*@return string url 
	*@since 0.5.0 
	*/
	function change_media_upload_src($src){
		global $post_type;
		if($post_type == 'ims_gallery')
			return str_replace('media-upload.php?','media-upload.php?imstore=1&',$src);
		else return $src;
	}
	
	/**
	 *Create a Gallery ID 
	 *for user to login
	 *
	 *@param unit $length
	 *@return string
	 *@since 2.0.0
	*/
	function unique_id($length = 12){
		$salt		= "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$len		= strlen($salt);
		$makepass	= '';
		mt_srand(10000000 *(double) microtime());
		for($i = 0; $i < $length; $i++) $makepass .= $salt[mt_rand(0,$len - 1)];
		return $makepass;
	}
	
	/**
	 *Clean file path
	 *
	 *@param $loc string
	 *@since 2.0.0
	 *return string
	*/
	function sanitize_path($path) {
       	$path = strtolower($path);
		$path = preg_replace('/&.+?;/','',$path); // kill entities
		$path = str_replace('.','-',$path);
		$path = preg_replace('/\s+/','-',$path);
		$path = preg_replace('|-+|','-',$path);
		return trim($path,'-');
	}

	/**
	 *Display message after post 
	 *has been saved
	 *
	 *@param $loc string
	 *@since 2.0.0
	 *return string
	*/
	function post_messeges($loc){
		if(empty($this->errors))
			return add_query_arg('error',$this->error,$loc);
		return $loc;
	}
	
	/**
	 *Make post edit form multidata
	 *
	 *@since 2.0.0
	 *return void
	*/
	function multidata_form(){
		global $current_screen;
		if($current_screen->id == 'ims_gallery') echo 'enctype="multipart/form-data"';
	}

	/**
	 *Remove empty entries form array recursively
	 *
	 *@parm array $input 
	 *@return array
	 *@since 0.5.0
	*/
	function array_filter_recursive($input){ 
		foreach($input as &$value){ 
			if(is_array($value)) 
				$value = $this->array_filter_recursive($value); 
		} 
		return array_filter($input); 
	} 
	
		
	/**
	 *Delete folder
	 *
	 *@param string $dir 
	 *@since 2.0.0
	 *return boolean
	*/
	function delete_folder($dir){
		if($dh = @opendir($dir)){
			while(false !== ($obj = readdir($dh))){
				if($obj == '.' || $obj == '..') continue;
				if(is_dir("$dir/$obj")) $this->delete_folder("$dir/$obj");
				else @unlink("$dir/$obj"); 
			}
			closedir($dh);
			return rmdir($dir);
		}
	}
	
	/**
	*Dencrypt image ID for downlaods
	*
	*@return void
	*@since 2.0.1
	*/
	function decrypt_id($string) {
   		$parts 	= base64_decode(urldecode($string));
		$hex 	= substr($parts,6);
		$int 	= hexdec($hex);
		$part1  = substr($parts,0,6);
		return (substr(sha1("imstore".$int.SECURE_AUTH_KEY),0,6) === $part1) ? $int : false;
    }
	
	/**
	 *Delete image folder
	 *
	 *@param unit $postid
	 *@since 2.0.0
	 *return void
	*/
	function delete_post($postid){
		if($this->opts['deletefiles']){
			global $wpdb;
			if('ims_gallery' == $wpdb->get_var($wpdb->prepare("SELECT post_type FROM $wpdb->posts WHERE ID = %d",$postid))){
				if($folderpath = get_post_meta($postid,'_ims_folder_path',true))
					$this->delete_folder(WP_CONTENT_DIR.$folderpath);
			}
			$wpdb->query(
				"DELETE p,pm FROM $wpdb->posts p 
				LEFT JOIN $wpdb->postmeta pm ON(p.ID = pm.post_id) 
				WHERE post_parent IN($postid)"
			);
		}
	}

	/**
	*Display unit sizes
	*
	*@return void
	*@since 1.1.0
	*/
	function dropdown_units($name,$selected){
		$output = '<select name="'. $name.'" class="unit">';
		foreach($this->units as $unit => $label){
			$select = ($selected == $unit) ?' selected="selected"':'';
			$output .= '<option value="'.$unit.'" '.$select.'>'.$label.'</option>';
		}
		echo $output .= '</select>';
	}

	/**
	*Display images
	*
	*@return void
	*@since 2.0.0
	*/
	function dis_images(){
		global $wpdb,$pagenowurl,$pagenow;
		$pagenowurl = admin_url().$pagenow;
		include_once(dirname(__FILE__).'/gallery.php');
	}
	
	/**
	*Hacky way to add auto-genarated
	*password to new galleries
	*
	*@return array|null
	*@since 2.0.0
	*/
	function add_auto_password($messages){
		global $post,$pagenow;
		if($this->opts['securegalleries'] && $pagenow == 'post-new.php' && $post->post_type == "ims_gallery" ) 
			$post->post_password = wp_generate_password(8);
		return $messages;
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
	function add_columns_val($null,$column_name,$user_id){
		$data = get_userdata($user_id);
		$status = array("active" => __('Active',ImStore::domain),"inactive" => __('Inactive',ImStore::domain));
		switch($column_name){
			case 'status':
				return $status[$data->ims_status];
				break;
			case 'fistname':
				return $data->first_name;
				break;
			case 'lastname':
				return $data->last_name;
				break;
			case 'city':
				return $data->ims_city;
				break;
			case 'phone':
				return $data->ims_phone;
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
				echo get_post_meta($postid,'_ims_gallery_id',true);
				break;
			case 'visits':
				echo get_post_meta($postid,'_ims_visits',true);
				break;
			case 'tracking':
				echo get_post_meta($postid,'_ims_tracking',true);
				break;
			case 'images':
				echo $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_parent = $postid AND post_status = 'publish'");
				break;	
			case 'expire':
				echo($post->post_expire == '0000-00-00 00:00:00')?'':date_i18n($this->dformat,strtotime($post->post_expire));
				break;
			case 'phone':
				return $data->ims_phone;
				break;
			default:
		}
	}
	
	/**
	*ImStore admin menu	
	*
	*@return void
	*@since 2.0.0
	*/
	function add_columns($columns){ 
		global $current_screen; //print_r($current_screen);
		switch($current_screen->id){
			case "edit-ims_gallery":
				return array(
					'cb' 		=> '<input type="checkbox">',	
					'title'		=> __('Gallery',ImStore::domain),'galleryid' => __('Gallery ID',ImStore::domain),
					'visits' 	=> __('Visits',ImStore::domain),'tracking'	=> __('Tracking',ImStore::domain),
					'images' 	=> __('Images',ImStore::domain),'author' => __('Author',ImStore::domain),
					'expire' 	=> __('Expires',ImStore::domain),'date' => __('Date',ImStore::domain) 
				);
				break;
			case "ims_gallery":
				return array(
					'cb' 		=> '<input type="checkbox">',	
					'title'		=> __('Gallery',ImStore::domain),'galleryid' => __('Gallery ID',ImStore::domain),
					'tracking'	=> __('Tracking',ImStore::domain),'images' => __('Images',ImStore::domain),	
					'visits' 	=> __('Visits',ImStore::domain),'expire' => __('Expires',ImStore::domain),	
					'date' 		=> __('Date',ImStore::domain),//'author' => __('Author',ImStore::domain) 
				);
				break;
			case "edit-ims_order":
				return array(
					'cb' 		=> '<input type="checkbox">',	
					'ordernum'	=> __('Order number',ImStore::domain),'orderdate'=> __('Date',ImStore::domain),
					'amount' 	=> __('Amount',ImStore::domain),'customer' => __('Customer',ImStore::domain),
					'images' 	=> __('Images',ImStore::domain),'paystatus' => __('Payment status',ImStore::domain),
					'orderstat' => __('Order Status',ImStore::domain),
				);
				break;
			default:
			if($_GET['role']=='customer')
				return array(
					'cb' 		=> '<input type="checkbox">','username' => __('Username',ImStore::domain),
					'fistname'	=> __('First Name',ImStore::domain),'lastname' => __('Last Name',ImStore::domain),
					'email' 	=> __('E-mail',ImStore::domain),'city' => __('City',ImStore::domain),
					'phone' 	=> __('Phone',ImStore::domain),'status' => __('Status',ImStore::domain)
				);
			else return $columns;
		}
	}
	
	/**
	*Get all customers
	*
	*@since 0.5.0
	*return array
	*/
	function get_active_customers(){
		global $wpdb;
		$users = $wpdb->get_results(
			"SELECT DISTINCT ID,user_login FROM $wpdb->users AS u
			INNER JOIN $wpdb->usermeta AS um ON u.ID = um.user_id 
			WHERE um.meta_key = 'ims_status' 
			AND um.meta_value IN('active',
				(SELECT DISTINCT meta_value 
				 FROM $wpdb->usermeta 
				 WHERE meta_value LIKE '%customer%') 
			)"
		);
		return $users;
	}
	
	/**
	*Load admin styles
	*
	*@return void
	*@since 2.0.0
	*/
	function load_admin_styles(){
		global $current_screen;
		if($current_screen->id == 'ims_gallery' || $_GET['page'] == 'ims-settings' 
			|| $_GET['page'] == 'ims-pricing' || $_GET['page'] == 'ims-sales' || $_GET['page'] == 'ims-customers'){
			wp_enqueue_style('thickbox');
			wp_enqueue_style('adminstyles',IMSTORE_URL.'_css/admin.css',false,ImStore::version,'all');
			wp_enqueue_style('datepicker',IMSTORE_URL.'_css/jquery-datepicker.css',false,ImStore::version,'all');
		}
	}
	
	/**
	*Load admin scripts
	*
	*@return void
	*@since 2.0.0
	*/
	function load_admin_scripts(){
		global $current_screen;
		
		if($current_screen->id == 'ims_gallery' || $_GET['page'] == 'ims-settings' || $_GET['page'] == 'ims-pricing'){
			wp_enqueue_script('postbox');
			wp_enqueue_script('thickbox');
			wp_enqueue_script('swfobject');		
			wp_enqueue_script('jquery-ui-sortable');
			wp_enqueue_script('jquery-ui-draggable');
			wp_enqueue_script('uploadify',IMSTORE_URL.'_js/jquery.uploadify.js',array('jquery','swfobject'),'2.2.0');
			wp_enqueue_script('datepicker',IMSTORE_URL.'_js/jquery-ui-datepicker.js',array('jquery'),ImStore::version);
			wp_enqueue_script('ims-admin',IMSTORE_URL.'_js/admin.js',array('jquery','postbox','datepicker','uploadify'),ImStore::version,true);
			
			$jquery = array('dd','D','d','DD','*','*','*','o','*','MM','mm','M','m','*','*','*','yy','y');
			$php 	= array('/d/','/D/','/j/','/l/','/N/','/S/','/w/','/z/','/W/','/F/','/m/','/M/','/n/','/t/','/L/','/o/','/Y/','/y/');
			$format = preg_replace($php,$jquery,get_option('date_format'));
			
			$user = wp_get_current_user();
			wp_localize_script('ims-admin','imslocal',array(
				'dateformat'	=> $format,
				'userid'		=> $user->ID,
				'imsurl'		=> IMSTORE_URL,
				'dformat'		=> $this->dformat,
				'imsajax' 		=> IMSTORE_ADMIN_URL.'ajax.php',
				'deletefile'	=> $this->opts['deletefiles'],
				'trash'			=> __('Trash',ImStore::domain),
				'remove'		=> __('Remove',ImStore::domain),
				'pixels'		=> __('Pixels',ImStore::domain),
				'publish'		=> __('Published',ImStore::domain),
				'exists'		=> __('File Exist.',ImStore::domain),
				'flastxt'		=> __('Select files.',ImStore::domain),
				'selectgal' 	=> __('Please,select a gallery!',ImStore::domain),
				'hiddengal' 	=> '.column-'.implode(',.column-',(array)get_hidden_columns('ims_gallery')),
				'deletelist' 	=> __('Are you sure that you want to delete this list?',ImStore::domain),
				'deletepackage' => __('Are you sure that you want to delete this package?',ImStore::domain),
				'deleteentry'	=> __('Are you sure that you want to delete this entry?',ImStore::domain),
				'nonceajax'		=> wp_create_nonce('ims_ajax')
			));
		}
	}

	/**
	*Register screen columns
	*
	*@return void
	*@since 0.5.0 
	*/
	function register_screen_columns(){
		global $current_screen;
		switch($current_screen->id){
			case 'profile_page_user-galleries':
				register_column_headers('profile_page_user-galleries',array(
					'gallery'	=> __('Gallery',ImStore::domain),
					'galleryid' => __('Gallery ID',ImStore::domain),
					'password' 	=> __('Password',ImStore::domain),
					'expire' 	=> __('Expires',ImStore::domain),
					'images' 	=> __('Images',ImStore::domain),
				));
				$option = 'user_galleries_per_page';
				$per_page_label = __('Galleries',ImStore::domain);
				break;
			case 'ims_gallery':
				register_column_headers('ims_gallery',array(
					'cb' => '<input type="checkbox">',
					'imthumb' => __('Thumbnail',ImStore::domain),'immetadata' => __('Metadata',ImStore::domain),
					'imtitle' => __('Title/Caption',ImStore::domain),//'imdate' => __('Date',ImStore::domain),
					'imauthor' => __('Author',ImStore::domain),'imorder'=> __('Order',ImStore::domain),
					'imageid' => __('ID',ImStore::domain),
				));
				$option = 'ims_gallery_per_page';
				$per_page_label = __('Images',ImStore::domain);
				break;//return;
			case 'ims_gallery_page_ims-pricing':
				register_column_headers('ims_gallery_page_ims-pricing',array(
					'cb' 		=> '<input type="checkbox">',
					'name' 		=> __('Name',ImStore::domain),'code' 		=> __('Code',ImStore::domain),
					'starts' 	=> __('Starts',ImStore::domain),'expires' 	=> __('Expires',ImStore::domain),
					'type'		=> __('Type',ImStore::domain),'discount'	=> __('Discount/Items',ImStore::domain),
				));
				$option = 'ims_pricing_per_page';
				$per_page_label = __('Promotions',ImStore::domain);
				break;
			case 'ims_gallery_page_ims-customers':
				$columns = array(
					'cb' => '<input type="checkbox">','name' => __('First Name',ImStore::domain),
					'lastname' => __('Last Name',ImStore::domain),'email' => __('E-Mail',ImStore::domain),
					'phone' => __('Phone',ImStore::domain),'city' => __('City',ImStore::domain),
					'state' => __('State',ImStore::domain),'newsletter' => __('eNewsletter',ImStore::domain));
				if(!class_exists('MailPress')) unset($columns['newsletter']);
				register_column_headers('ims_gallery_page_ims-customers',$columns);
				$option = 'ims_sales_per_page';
				$per_page_label = __('Customers',ImStore::domain);
				break;
			case 'ims_gallery_page_ims-sales':
				if($_REQUEST['details']) return;
				register_column_headers('ims_gallery_page_ims-sales',array(
					'cb' 		=> '<input type="checkbox">',	
					'ordernum'	=> __('Order number',ImStore::domain),'orderdate'=> __('Date',ImStore::domain),
					'amount' 	=> __('Amount',ImStore::domain),'customer' 	=> __('Customer',ImStore::domain),
					'images' 	=> __('Images',ImStore::domain),'paystatus' => __('Payment status',ImStore::domain),
					'orderstat' => __('Order Status',ImStore::domain),
				));
				$option = 'ims_customers_per_page';
				$per_page_label = __('Sales',ImStore::domain);
				break;
			default:
				return;
		}
		$this->per_page = (int) get_user_option($option);
		if(empty($this->per_page) || $this->per_page < 1) $this->per_page = 20;
		
		$return = "<div class='screen-options'>\n";
		$return .= '<h5>'.__('Show per page',ImStore::domain).'</h5>';
		$return .= "<label>$per_page_label:<input type='text' class='inputxm' name='screen_options[value]' value='$this->per_page' /></label>\n";
		$return .= "<input type='submit' class='button' value='".esc_attr__('Apply')."' />";
		$return .= "<input type='hidden' name='screen_options[option]' value='".esc_attr($option)."' />";
		$return .= "</div>\n";
		return $return;
	}
	
	/**
	*Initial actions
	*
	*@return void
	*@since 2.0.0
	*/
	function int_actions(){
		$this->gallery 		= get_post($_GET['post']);
		$this->dformat 		= get_option('date_format');
		$this->meta 		= get_post_custom($_GET['post']);
		$this->permalinks 	= get_option('permalink_structure');
		add_meta_box("ims_customers_box",__('Customers',ImStore::domain),array($this,"dis_customers"),"ims_gallery","side","low");
		add_meta_box("ims_info_box",__('Gallery Information',ImStore::domain),array($this,"dis_info"),"ims_gallery","normal","high");
		add_meta_box("ims_import_box",__('Import Images',ImStore::domain),array($this,"import_img"),"ims_gallery","normal","high");
		add_meta_box("ims_images_box",__('Images',ImStore::domain),array($this,"dis_images"),"ims_gallery","normal","high");
		$this->color 		= array(
			'ims_sepia' => __('Sepia + ',ImStore::domain),
			'color' => __('Full Color',ImStore::domain),
			'ims_bw' => __('B &amp; W + ',ImStore::domain
		));
	}
	
	/**
	*Display Customers
	*
	*@return void
	*@since 2.0.0
	*/
	function dis_customers(){
		$customers = $this->get_active_customers();
		$this->meta['_ims_customer'] = maybe_unserialize($this->meta['_ims_customer'][0]);
		echo '<div class="categorydiv"><div class="tabs-panel">
			<ul class="categorychecklist form-no-clear">';
			if(is_array($this->meta['_ims_customer'])){
				foreach($customers as $customer){
					$checked = (ImStore::fast_in_array($customer->ID,$this->meta['_ims_customer']))?' checked="checked"':'';
					echo '<li><label>
					<input type="checkbox" name="customers[]" value="'.$customer->ID.'"'.$checked.' /> '.
					$customer->user_login.'</label></li>';
				}
			}else{
				foreach($customers as $customer){
					$checked = ($customer->ID == $this->meta['_ims_customer'])?' checked="checked"':'';
					echo '<li><label>
					<input type="checkbox" name="customers[]" value="'.$customer->ID.'"'.$checked.' /> '.
					$customer->user_login.'</label></li>';
				}
			}
		echo'</ul>
		</div></div>';
	}
	
	/**
	 *paging function for events reports
	 *
	 *@return void
	 *@since 0.5.0 
	*/
	function imstore_paging($perpage,$all){
		global $wpdb,$pagenowurl;
		
		$all	= ($all)?$all:1;
		$s		= $wpdb->escape($_GET['s']);	
		$page	= (empty($_GET['p']))?'1':intval($wpdb->escape($_GET['p']));
		$from	= (($page - 1) *$perpage) + 1;
		$last	= ceil($all / $perpage);
		
		if(isset($_REQUEST['s'])) $pagenowurl .= "&amp;s=$s";
		if(isset($_REQUEST['status'])) $pagenowurl .= "&amp;status=$status";
		
		if($all > $perpage){
			echo '<div class="tablenav-pages">';
			echo '<span class="displaying-num">'." Displaying $from &#8211; $to of $all".'</span>';
			//prev
			if(($p = $page-1) >= 1) 
				echo '<a href="'."$pagenowurl&amp;p=$p".'" class="next page-numbers">&laquo;</a>';
			//first
			if($page != 1) echo '<a href="'."$pagenowurl&amp;p=1".'" class="next page-numbers">1</a>';
			if($page > 4) echo '<span class="page-numbers dots">...</span>';
			
			for($i = $page-2; $i < $page; $i++){
				if($i < $page && $i >1)
					echo '<a href="'."$pagenowurl&amp;p=$i".'" class="next page-numbers">'.$i.'</a>';
			}
			//current
			echo '<span class="current page-numbers">'.$page.'</span>';
			
			for($i = $page+1; $i <($page + 3); $i++){
				if($i < $last)
					echo '<a href="'."$pagenowurl&amp;p=$i".'" class="next page-numbers">'.$i.'</a>';
			}
			if($i < $last)echo '<span class="page-numbers dots">...</span>';
			//last
			if($page != $last)
				echo '<a href="'."$pagenowurl&amp;p=$last".'" class="next page-numbers">'.$last.'</a>';
			//next
			if(($p = $page + 1) <= $last)
				echo '<a href="'."$pagenowurl&amp;p=$p".'" class="next page-numbers">&raquo;</a>';
			echo '</div>';
		}
	}
	
	/**
	*Display gallery 
	*information metabox
	*
	*@return void
	*@since 2.0.0
	*/
	function dis_info(){
		global $post,$pagenow;
		$type = ($pagenow == 'post-new.php')?'text':'hidden';
		$gallery_id = (empty($this->meta['_ims_gallery_id'][0]))?$this->unique_id():esc_attr($this->meta['_ims_gallery_id'][0]);
		$folder_path = (empty($this->meta['_ims_folder_path'][0]))?"{$this->opts['galleriespath']}/gallery-{$post->ID}":esc_attr($this->meta['_ims_folder_path'][0]);
		
		if($pagenow == 'post-new.php') $expire = ($this->opts['galleryexpire'])?date_i18n($this->dformat,(current_time('timestamp'))+($this->opts['galleryexpire'] *86400)):'';
		else $expire = ($this->gallery->post_expire == '0000-00-00 00:00:00')?'':date_i18n($this->dformat,strtotime($this->gallery->post_expire));
	   	$ims_expire  = ($this->gallery->post_expire) ? esc_attr($this->gallery->post_expire) : date_i18n('Y-m-d H:i',(current_time('timestamp'))+($this->opts['galleryexpire'] *86400));
	   ?>
	<table class="ims-table" >
		<tr>
			<td width="23%" scope="row"><label for="_ims_folder_path"><?php _e('Folder path',ImStore::domain)?></label></td>
			<td width="25%" class="type-<?php echo $type?>">
				<span><?php echo $folder_path?></span>
				<input type="<?php echo $type?>" name="_ims_folder_path" id="_ims_folder_path" value="<?php echo $folder_path?>" <?php echo $disable?>/>
			</td>
			<td width="23%"><label for="gallery_id"><?php _e('Gallery ID',ImStore::domain)?></label></td>
			<td width="25%"><input type="text" name="_ims_gallery_id" id="gallery_id" value="<?php echo $gallery_id?>"/></td>
		</tr>
		<?php if(!$this->opts['disablestore']){?>
		<tr>
			<td scope="row"><label for="_ims_tracking"><?php _e('Tracking Number',ImStore::domain)?></label></td>
			<td><input type="text" name="_ims_tracking" id="_ims_tracking" value="<?php echo esc_attr($this->meta['_ims_tracking'][0])?>" /></td>
			<td><label for="_ims_price_list"><?php _e('Price List',ImStore::domain)?></label></td>
			<td>
			<select name="_ims_price_list" id="_ims_price_list" >
				<?php foreach($this->get_ims_pricelists() as $list):?>
				<option value="<?php echo $list->ID?>" <?php selected($list->ID,$this->meta['_ims_price_list'][0])?>><?php echo $list->post_title?></option>
				<?php endforeach?>
			</select>
			</td>
		</tr>
		<?php }?>
		<tr>
			<td scope="row"><label for="_ims_visits"><?php _e('Visits',ImStore::domain)?></label></td>
			<td><input type="text" name="_ims_visits" id="_ims_visits" value="<?php echo esc_attr($this->meta['_ims_visits'][0])?>" /></td>
			<td><label for="expire" class="date-icon"><?php _e('Expiration Date',ImStore::domain)?></label></td>
			<td>
			<input type="text" name="imsexpire" id="imsexpire" value="<?php echo $expire?>" />
			<input type="hidden" name="_ims_expire" id="_ims_expire" value="<?php echo $ims_expire ?>"/>
			</td>
		</tr>
		<tr>
			<td scope="row"><label for="sortby"><?php _e('Sort Order',ImStore::domain)?></label></td>
			<td colspan="3">
				<select name="_ims_sortby" id="sortby">
					<option value="0"><?php _e('Default',ImStore::domain)?></option> 
					<option value="menu_order"<?php selected('menu_order',$this->meta['_ims_sortby'][0])?>><?php _e('Custom order',ImStore::domain)?></option> 
					<option value="post_excerpt"<?php selected('post_excerpt',$this->meta['_ims_sortby'][0])?>><?php _e('Caption',ImStore::domain)?></option>
					<option value="post_title"<?php selected('post_title',$this->meta['_ims_sortby'][0])?>><?php _e('Image title',ImStore::domain)?></option>
					<option value="post_date"<?php selected('post_date',$this->meta['_ims_sortby'][0])?>><?php _e('Image date',ImStore::domain)?></option>
				</select>
				<select name="_ims_order">
					<option value="0"><?php _e('Default',ImStore::domain)?></option> 
					<option value="ASC"<?php selected('ASC',$this->meta['_ims_order'][0])?>><?php _e('Ascending',ImStore::domain)?></option>
					<option value="DESC"<?php selected('DESC',$this->meta['_ims_order'][0])?>><?php _e('Descending',ImStore::domain)?></option> 
				</select>
				<?php wp_nonce_field('ims_save_post','ims_save_post');?>
			</td>
		</tr>
	</table>
	<?php
	}
	
	/**
	*Import images metabox
	*
	*@return void
	*@since 2.0.0
	*/	
	function import_img(){
		global $pagenow;
		$class = ($pagenow == 'post.php')?' class="folderscan"':'';
	?>
		<ul class="ims-tabs add-menu-item-tabs">
			<li class="tabs"><a href="#upload-images"><?php _e('Upload Images',ImStore::domain)?></a></li>
			<li class="tabs"><a href="#upload-zip"><?php _e('Upload zip file',ImStore::domain)?></a></li>
			<li class="tabs"><a href="#import-folder"><?php _e('Scan folder',ImStore::domain)?></a></li>
		</ul>
		<div class="ims-box flash-upload">
			<label id="disableflash" for="imagefiles"><?php _e('Upload images',ImStore::domain)?></label>
			<input id="imagefiles" name="imagefiles" type="file" />
		</div>
		<div class="ims-box">
			<p><label for="zipfile"><?php _e('Zip file',ImStore::domain)?> <input type="file" name="zipfile" id="zipfile" /></label></p>
			<p><label for="zipurl"><?php _e('Or enter zip file URL',ImStore::domain)?></label><br />
			<input type="text" name="zipurl" id="zipurl" class="inputxl"/><br />
			<small><?php printf(__("Import a zip file with images from a url. Your server's maximum file size upload is <strong>%sB</strong>. Publish or update gallery to upload images.",ImStore::domain),ini_get('upload_max_filesize'))?></small></p>
		</div>
		<div class="ims-box">
			<p><label for="galleryfolder"><?php _e('Import From Server Path',ImStore::domain)?></label></p>
			<p<?php echo $class?>>
				<input type="text" name="dis" value="<?php echo $this->meta['_ims_folder_path'][0]?>" disabled="disabled" class="inputlg"/>
				<input type="text" name="galleryfolder" value="<?php echo $this->meta['_ims_folder_path'][0]?>" class="inputlg"/>
				<input type="submit" name="scannfolder" value="<?php _e('Scan',ImStore::domain)?>" class="button" /><br />
				<small><?php _e("Path relative to the wp-content folder.",ImStore::domain)?></small>
			</p>
		</div>

	<?php 
	}
	
	/**
	*Display images 
	*
	*@return void
	*@since 2.0.0
	*/
	function profile_fields($profileuser){
		if(!$profileuser->caps['customer']) return;
	?>
		<h3><?php _e('Address Information',ImStore::domain)?></h3>
		<table class="form-table">
			<tr>
				<th><label for="<?php echo $profileuser->ims_address?>"><?php _e('First Name',ImStore::domain)?></label></th>
				<td><input type="text" name="<?php echo $profileuser->ims_address?>" id="<?php echo $profileuser->ims_address?>" value="<?php echo esc_attr($profileuser->ims_address)?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th><label for="<?php echo $profileuser->ims_city?>"><?php _e('Last Name',ImStore::domain)?></label></th>
				<td><input type="text" name="<?php echo $profileuser->ims_city?>" id="<?php echo $profileuser->ims_city?>" value="<?php echo esc_attr($profileuser->ims_city)?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th><label for="<?php echo $profileuser->ims_state?>"><?php _e('State',ImStore::domain)?></label></th>
				<td><input type="text" name="<?php echo $profileuser->ims_state?>" id="<?php echo $profileuser->ims_state?>" value="<?php echo esc_attr($profileuser->ims_state)?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th><label for="<?php echo $profileuser->ims_zip?>"><?php _e('Zip',ImStore::domain)?></label></th>
				<td><input type="text" name="<?php echo $profileuser->ims_zip?>" id="<?php echo $profileuser->ims_zip?>" value="<?php echo esc_attr($profileuser->ims_zip)?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th><label for="<?php echo $profileuser->ims_phone?>"><?php _e('Phone',ImStore::domain)?></label></th>
				<td><input type="text" name="<?php echo $profileuser->ims_phone?>" id="<?php echo $profileuser->ims_phone?>" value="<?php echo esc_attr($profileuser->ims_phone)?>" class="regular-text" /></td>
			</tr>			
		</table>
	<?php
	}
	
	/**
	*Display the pages 
	*
	*@return void
	*@since 0.5.0 
	*/
	function show_menu(){
		global $wpdb,$pagenowurl,$pagenow,$user_ID;
		$page = $_GET['page'];
		$this->sym 	= $this->opts['symbol']; 
		$this->loc 	= $this->opts['clocal'];
		$pagenowurl = admin_url().$pagenow.'?post_type=ims_gallery&page='.$_GET['page'];
		include_once(dirname(__FILE__).'/template.php');
	}
}
$this->admin = new ImStoreAdmin();
?>