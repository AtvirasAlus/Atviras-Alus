<?php 
/*
Plugin Name: Image Store
Plugin URI: http://imstore.xparkmedia.com
Description: Your very own image store within wordpress "ImStore"
Author: Hafid R. Trujillo Huizar
Version: 3.0.6
Author URI:http://www.xparkmedia.com
Requires at least: 3.1.0
Tested up to: 3.4.0

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
if( preg_match( '#'.basename(__FILE__).'#',$_SERVER['PHP_SELF']) ) 
	die( );

if( !class_exists( 'ImStore') && !defined( 'IMSTORE_ABSPATH') ){
	
	//define constants
	define( 'IMSTORE_FILE_NAME', plugin_basename( __FILE__ ) );
	define( 'IMSTORE_FOLDER', plugin_basename( dirname( __FILE__ ) ) );
	define( 'IMSTORE_ABSPATH', str_replace("\\","/", dirname( __FILE__ ) ) );

	include(dirname(__FILE__)."/_inc/core.php");
	
	//admin
	if( is_admin( ) ){
		
		global $pagenow, $ImStore;
		include( IMSTORE_ABSPATH . "/_inc/admin.php" );

		if ( empty(  $pagenow ) )  
			$pagenow = basename( $_SERVER['SCRIPT_NAME'] );
		$post_type = isset( $_GET['post_type'] ) ? $_GET['post_type'] : false;		

		//load what is needed where is needed
		if( isset( $_GET['taxonomy'] ) && isset( $_GET['taxonomy'] ) == 'ims_album' ){
			//taxonomy
			$ImStore = new ImStoreAdmin( ); 
		}elseif( ( $pagenow == "post-new.php" && $post_type == 'ims_gallery' ) ||
			in_array( $pagenow , array( 'post.php', 'upload-img.php' ) ) ){
			//galleries
			include( IMSTORE_ABSPATH . "/_inc/galleries.php" ); 
			$ImStore = new ImStoreGallery( );
		}elseif( $post_type == 'ims_gallery' ){
			 //settings
			include( IMSTORE_ABSPATH . "/_inc/set.php" );
			$ImStore = new ImStoreSet( );
		}else{
			 //all others
			$ImStore = new ImStoreAdmin( );
		}
	
	//front end
	}else{
		
		global  $ImStore;
		include( IMSTORE_ABSPATH . "/_inc/store.php" );
		$ImStore = new ImStoreFront( );
		
	}
	
}?>