<?php

/*
  Plugin Name: Image Store
  Plugin URI: http://imstore.xparkmedia.com
  Description: Your very own image store within wordpress "ImStore"
  Author: Hafid R. Trujillo Huizar
  Version: 3.1.7
  Author URI:http://www.xparkmedia.com
  Requires at least: 3.0.0
  Tested up to: 3.5.0
  Text Domain: ims

  Copyright 2010-2012 by Hafid Trujillo http://www.xparkmedia.com

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
if (!defined('ABSPATH'))
	die();
	
if (!class_exists('ImStore') && !defined('IMSTORE_ABSPATH')) {

	//define constants
	define('IMSTORE_FILE_NAME', plugin_basename(__FILE__));
	define('IMSTORE_FOLDER', plugin_basename(dirname(__FILE__)));
	define('IMSTORE_ABSPATH', str_replace("\\", "/", dirname(__FILE__)));

	include( IMSTORE_ABSPATH . "/_inc/core.php");

	if (is_admin()) { //admin
	
		global $pagenow, $ImStore;
		include( IMSTORE_ABSPATH . "/_inc/admin.php" );

		if (empty($pagenow))
			$pagenow = basename($_SERVER['SCRIPT_NAME']);

		$page = isset($_GET['page']) ? $_GET['page'] : false;
		$post_type = isset($_GET['post_type']) ? $_GET['post_type'] : false;

		//load what is needed where is needed
		if (( $pagenow == "post-new.php" && $post_type == 'ims_gallery' ) ||
			in_array($pagenow, array('post.php', 'upload-img.php'))) {
			
			include( IMSTORE_ABSPATH . "/_inc/galleries.php" );
			$ImStore = new ImStoreGallery( ); //galleries
			
		} elseif ($post_type == 'ims_gallery' || $page == 'ims-settings') {
			
			include( IMSTORE_ABSPATH . "/_inc/set.php" );
			$ImStore = new ImStoreSet( ); //settings
			
		} else {
			
			$ImStore = new ImStoreAdmin( );
			
		}
		
	} else { //front end
	
		global $ImStore;
		include( IMSTORE_ABSPATH . "/_inc/store.php" );
		$ImStore = new ImStoreFront( );
		
	}
}