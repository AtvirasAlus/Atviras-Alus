<?php 
/*
Plugin Name: Image Store
Plugin URI: http://imstore.xparkmedia.com
Description: Your very own image store within wordpress "ImStore"
Author: Hafid R. Trujillo Huizar
Version: 2.0.9
Author URI:http://www.xparkmedia.com
Requires at least: 3.0.0
Tested up to: 3.1.0

Copyright 2010-2011 by Hafid Trujillo http://www.xparkmedia.com

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License,or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not,write to the Free Software
Foundation,Inc.,51 Franklin St,Fifth Floor,Boston,MA 02110-1301 USA
*/ 


// Stop direct access of the file
if(preg_match('#'.basename(__FILE__).'#',$_SERVER['PHP_SELF'])) 
	die();
	
if(!class_exists('ImStore')){

class ImStore{
	
	/**
	*Variables
	*
	*@param $domain plugin Gallery IDentifier
	*Make sure that new language(.mo) files have 'ims-' as base name
	*/
	const domain	= 'ims';
	const version	= '2.0.9';
	
	/**
	*Constructor
	*
	*@return void
	*@since 0.5.0 
	*/
	function __construct(){
		global $wp_version;
		
		$this->load_text_domain();
		$this->define_constant();
		$this->load_dependencies();
		
		$this->pages[1] = __('Photos',ImStore::domain);
		$this->pages[2] = __('Slideshow',ImStore::domain);
		$this->pages[3] = __('Price List',ImStore::domain);
		$this->pages[4] = __('Favorites',ImStore::domain);
		$this->pages[5] = __('Shopping Cart',ImStore::domain);
		$this->pages[6] = __('Receipt',ImStore::domain);
		$this->pages[7] = __('Shipping',ImStore::domain);
		
		// register hooks
		//if($wp_version >= 3.1) register_update_hook(IMSTORE_FILE_NAME,array(&$this,'update'));
		register_activation_hook(IMSTORE_FILE_NAME,array(&$this,'activate'));
		register_deactivation_hook(IMSTORE_FILE_NAME,array(&$this,'deactivate'));
		
		add_action('init',array(&$this,'int_actions'),40);
		add_action('wp_logout',array(&$this,'logout_ims_user'),10);
		add_action('imstore_expire',array(&$this,'expire_galleries'));
		add_filter('post_type_link',array(&$this,'gallery_permalink'),10,3);
		add_filter('wp_insert_post_data',array(&$this,'insert_post_data'),20,2);
		add_action('generate_rewrite_rules',array(&$this,'add_rewrite_rules'),10,1);
	}

	/**
	*Define contant variables
	*
	*@return void
	*@since 0.5.0 
	*/
	function define_constant(){
		ob_start(); //fix redirection problems
		define('IMSTORE_FILE_NAME',plugin_basename(__FILE__));
		define('IMSTORE_FOLDER',plugin_basename(dirname(__FILE__)));
		define('IMSTORE_ABSPATH',str_replace("\\","/",dirname(__FILE__)));
		define('IMSTORE_URL',WP_PLUGIN_URL."/".IMSTORE_FOLDER."/");
		define('IMSTORE_ADMIN_URL',IMSTORE_URL.'admin/');
		if(!defined('WP_SITE_URL')) define('WP_SITE_URL',get_bloginfo('url'));
		if(!defined('WP_EDIT_URL')) define('WP_EDIT_URL',admin_url()."/post.php?post=");
		if(!defined('WP_CONTENT_URL')) define('WP_CONTENT_URL',WP_SITE_URL.'/wp-content');
		if(!defined('WP_TEMPLATE_DIR')) define('WP_TEMPLATE_DIR',get_template_directory());
	}
			
	/**
	*Register localization/language file
	*
	*@return void
	*@since 0.5.0 
	*/
	function load_text_domain(){
		if(function_exists('load_plugin_textdomain')){
			$plugin_dir = basename(dirname(__FILE__)).'/langs';
			load_plugin_textdomain(ImStore::domain,WP_CONTENT_DIR.'/plugins/'.$plugin_dir,$plugin_dir);
		}
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
	function insert_post_data($data,$args){
		if($data['post_type'] == 'ims_gallery'){
			$data['post_expire'] = ($_POST['_ims_expire'] != '0000-00-00 00:00:00' && !empty($_POST['imsexpire'])) 
			? $_POST['_ims_expire']:'';
			$data['post_content'] = '[ims-gallery-content]';
		}
		if($data['post_type'] == 'ims_promo') $data['post_expire'] = $_POST['expiration_date'];
		return $data;
	}
	
	/**
	*logout user 
	*
	*@return void
	*@since 0.5.0 
	*/
	function logout_ims_user(){
		setcookie('ims_galid_'.COOKIEHASH,' ',time() - 31536000,COOKIEPATH,COOKIE_DOMAIN);
		setcookie('wp-postpass_'.COOKIEHASH,' ',time() - 31536000,COOKIEPATH,COOKIE_DOMAIN);
	}
	
	/**
	*Deactivate 
	*
	*@return void
	*@since 0.5.0 
	*/
	function deactivate(){
		 wp_clear_scheduled_hook('imstore_expire');
	}
	
	/**
	*Activite and save default options
	*Activite the expire cron 
	*
	*@return void
	*@since 0.5.0 
	*/
	function activate(){
		wp_schedule_event(strtotime("tomorrow 1 hours"),'twicedaily','imstore_expire');
		include_once(dirname(__FILE__).'/admin/install.php');
	}
	
	/**
	*Run plugin updates
	*
	*@return void
	*@since 2.0.8 
	*/
	function update(){
		include_once(dirname(__FILE__).'/admin/install.php');
	}
	
	/**
	*Fast in_array function
	*
	*@parm string $elem
	*@parm array $array
	*@return bool
	*@since 1.2.0
	*/
	function fast_in_array($elem,$array){ 
		foreach($array as $val){
			if($val==$elem) return true; 
		} return false; 
	} 
	
	/**
	 *Create image feeds
	 *
	 *@return void
	 *@since 0.5.3 
	 */
	function create_feed(){
		require_once(dirname(__FILE__).'/includes/image-rss.php');
	}
	
	/**
	*Rewrites for custom page managers
	*
	*@param array $wp_rewrite
	*@return array
	*@since 0.5.0 
	*/
	 function add_rewrite_rules($wp_rewrite){
		 
		$wp_rewrite->add_rewrite_tag("%gallery%",'([^/]+)',"ims_gallery=");
		$wp_rewrite->add_rewrite_tag('%paypalipn%','([^/]+)','paypalipn=');
		$wp_rewrite->add_rewrite_tag('%imslogout%','([^/]+)','imslogout=');
		$wp_rewrite->add_rewrite_tag('%imsmessage%','([0-9]+)','imsmessage=');
		$wp_rewrite->add_permastruct('ims_gallery',__('galleries',ImStore::domain).'/%ims_gallery%/%imspage%',false);
	
		$new_rules = array(
			__('galleries',ImStore::domain)."/imspaypalipn/?([0-9]+)/?$" =>
			"index.php&paypalipn=".$wp_rewrite->preg_index(1),
			__('galleries',ImStore::domain)."/([^/]+)/logout/?([^/]+)?$" => 
			"index.php&ims_gallery=".$wp_rewrite->preg_index(1).
			'&imslogout='.$wp_rewrite->preg_index(2),
		);

		foreach($this->pages as $id => $page){
			$slug = sanitize_title($page);
			if($id == 1)
				$new_rules[__('galleries',ImStore::domain)."/([^/]+)/$slug/page/?([0-9]+)/?$"] = 
				"index.php?ims_gallery=".$wp_rewrite->preg_index(1). "&imspage=$id".
				'&page='.$wp_rewrite->preg_index(2);
			
			$new_rules[__('galleries',ImStore::domain)."/([^/]+)/$slug/logout/?([^/]+)?$"] = 
			"index.php?ims_gallery=".$wp_rewrite->preg_index(1).
			'&imslogout='.$wp_rewrite->preg_index(2);
			
			$new_rules[__('galleries',ImStore::domain)."/([^/]+)/$slug/ms/?([0-9]+)/?$"] = 
			"index.php?ims_gallery=".$wp_rewrite->preg_index(1). "&imspage=$id".
			'&imsmessage='.$wp_rewrite->preg_index(2);
			
			$new_rules[__('galleries',ImStore::domain)."/([^/]+)/$slug/?$"] = 
			"index.php?ims_gallery=".$wp_rewrite->preg_index(1)."&imspage=$id";

			$new_rules[__('galleries',ImStore::domain)."/([^/]+)/$slug/feed/(feed|rdf|rss|rss2|atom|imstore)/?$"] = 
			"index.php?ims_gallery=".$wp_rewrite->preg_index(1)."&imspage=$id&feed=".$wp_rewrite->preg_index(2);

		}
		$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
		$wp_rewrite->rules["/page/?([0-9]+)/?$"] =  "index.php?paged=".$wp_rewrite->preg_index(1);  //print_r($wp_rewrite);
		return $wp_rewrite;
	}
	
		
	function gallery_permalink($permalink, $post, $leavename){
		if($post->post_type != 'ims_gallery') return $permalink;
		$page = ($p = get_query_var('imspage')) ? $this->pages[$p] : $this->pages[1];
		return str_replace('%imspage%',sanitize_title($page),$permalink);
	}
	

	/**
	*Initial actions
	*
	*@return void
	*@since 0.5.0 
	*/
	function int_actions(){
		add_feed('imstore',array(&$this,'create_feed'));
		$searchable = (get_option('ims_searchable'))? false : true;
		register_post_type('ims_gallery',array(
			'labels' => array(
				'name' 			=> _x('Galleries','post type general name',ImStore::domain),
				'singular_name' => _x('Gallery','post type singular name',ImStore::domain),
				'add_new' 		=> _x('Add New','Gallery',ImStore::domain),
				'add_new_item'	=> __('Add New Gallery',ImStore::domain),
				'edit_item' 	=> __('Edit Gallery',ImStore::domain),
				'new_item' 		=> __('New Gallery',ImStore::domain),
				'view_item' 	=> __('View Gallery',ImStore::domain),
				'search_items' 	=> __('Search galleries',ImStore::domain),
				'not_found' 	=> __('No galleries found',ImStore::domain),
				'not_found_in_trash' => __('No galleries found in Trash',ImStore::domain),
			),
			'public' 			=> true,
			'show_ui' 			=> true,
			'menu_position' 	=> 33,
			'publicly_queryable'=> true,
			'exclude_from_search'=> $searchable,
			'hierarchical' 		=> false,
			'revisions'			=> false,
			'show_in_nav_menus' => false,
			'capability_type' 	=> 'page',
			'query_var'			=> 'ims_gallery',
			'menu_icon' 		=> IMSTORE_URL.'_img/imstore.png',
			'rewrite' 			=> array('slug' => __('galleries',ImStore::domain),'with_front'=>false),
			'supports' 			=> array('title','comments','author'),
			'taxonomies'		=> array('ims_album')
		));
		
		register_taxonomy('ims_album',array('ims_gallery'),array(
			'labels' => array(
				'name' 			=> _x( 'Albums', 'taxonomy general name',ImStore::domain),
				'singular_name' => _x( 'Album', 'taxonomy singular name',ImStore::domain),
				'search_items' 	=> __( 'Search Albums',ImStore::domain),
				'all_items' 	=> __( 'All Albums',ImStore::domain),
				'parent_item' 	=> __( 'Parent Album',ImStore::domain),
				'parent_item_colon' => __( 'Parent Album:',ImStore::domain),
				'edit_item' 	=> __( 'Edit Album',ImStore::domain), 
				'update_item' 	=> __( 'Update Album',ImStore::domain),
				'add_new_item' 	=> __( 'Add New Album',ImStore::domain),
				'new_item_name' => __( 'New Album Name',ImStore::domain),
				'menu_name' 	=> __( 'Album',ImStore::domain),
			 ),
			'show_ui' 		=> true,
			'query_var' 	=> true,
			'hierarchical' 	=> true,
			'show_in_nav_menus' => true,
			'rewrite' 		=> array('slug' =>__('albums',ImStore::domain)),
		));
		
		$statuses = array(
			'expire' 	=> __('Expired',ImStore::domain),
			'active' 	=> __('Active',ImStore::domain),
			'inative'	=> __('Inative',ImStore::domain),
		);
		
		foreach($statuses as $status => $label){
			register_post_status($status,array(
				'protected' 	=> true,
				'label' 		=> $status,
				'label_count' 	=> _n_noop("{$label} <span class='count'>(%s)</span>","{$label} <span class='count'>(%s)</span>")
			));
		}
		flush_rewrite_rules();
		$this->permalinks = get_option('permalink_structure');
	}

	/**
	*Set galleries to expired
	*and delete unprocess orders
	*
	*@return void
	*@since 0.5.0 
	*/
	function expire_galleries(){
		global $wpdb;
		$wpdb->query(
			"UPDATE $wpdb->posts SET post_status = 'expire' 
			WHERE post_expire <= '".date('Y-m-d',current_time('timestamp'))."'
			AND post_expire != '0000-00-00 00:00:00'
			AND post_type = 'ims_gallery'"
		);
		$wpdb->query(
			"DELETE p,pm FROM $wpdb->posts p 
			LEFT JOIN $wpdb->postmeta pm ON(p.ID = pm.post_id) 
			WHERE post_expire <='".date('Y-m-d',current_time('timestamp'))."'
			AND post_type = 'ims_order' AND post_status = 'draft'"
		);
		$wpdb->query("OPTIMIZE TABLE $wpdb->terms,$wpdb->postmeta,$wpdb->posts,$wpdb->term_relationships,$wpdb->term_taxonomy");
	}
	
	/**
	*Load what is needed where is needed
	*
	*@return void
	*@since 0.5.0 
	*/
	function load_dependencies(){
		if(is_admin() && !class_exists('ImStoreAdmin')){
			require_once(dirname(__FILE__).'/admin/admin.php');
		}elseif(!class_exists('ImStoreFront')){
			require_once(dirname(__FILE__).'/includes/store.php');
			require_once(dirname(__FILE__).'/includes/image-rss.php');
			require_once(dirname(__FILE__).'/includes/shortcode.php');
		}
		if($this->admin->opts['imswidget'] || $this->store->opts['imswidget']) 
			include_once(dirname(__FILE__).'/admin/widget.php');		
	}

}

// Do that thing you do!!!
global $ImStore;
$ImStore = new ImStore();
	
}
?>