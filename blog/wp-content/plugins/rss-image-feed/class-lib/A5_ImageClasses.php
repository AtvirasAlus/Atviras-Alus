<?php

/**
 *
 * Class A5 Image Tags
 *
 * @ A5 Plugin Framework
 *
 * Gets the alt and title tag for attachments
 *
 */

class A5_ImageTags {
	
	var $tags;
	
	function get_tags($post, $language_file) {
	
		setup_postdata($post);
		
		$args = array(
		'post_type' => 'attachment',
		'numberposts' => 1,
		'post_status' => null,
		'post_parent' => $post->ID
		);
		
		$title_tag = __('Permalink to', $language_file).' '.esc_attr($post->post_title);
		
		$attachments = get_posts( $args );
		
		if ( $attachments ) :
		
			$attachment = $attachments[0];
			  
			$image_alt = trim(strip_tags( get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true) ));
			
			$image_title = trim(strip_tags( $attachment->post_title ));
		
		endif;
		
		$image_alt = (empty($image_alt)) ? esc_attr($post->post_title) : esc_attr($image_alt);
		$image_title = (empty($image_title)) ? esc_attr($post->post_title) : esc_attr($image_title);
		
		$this->tags = array(
		'image_alt' => $image_alt,
		'image_title' => $image_title,
		'title_tag' => $title_tag
		);
		
		return $this->tags;
	
	}
	
}


/**
 *
 * Class A5 Thumbnail
 *
 * @ A5 Plugin Framework
 *
 * Gets all image related stuff
 *
 */

class A5_Thumbnail {
	
	var $image_info;
	
	// getting the first image of a post with available sizes as the post thumbnail
	
	function get_thumbnail($args) {
		
		extract($args);
	
		$image = preg_match_all('/<\s*img[^>]+src\s*=\s*["\']?([^\s"\']+)["\']?[\s\/>]+/', do_shortcode($content), $matches);
		$thumb = $matches [1] [0];
		
		if (empty($thumb)) return false;
		
		$thumb_width = preg_match_all('/width\s*=\s*["\']?([^\s"\']+)["\']/', $matches [0] [0], $size);
		$thumb_width = $size[1] [0];
		
		$thumb_height = preg_match_all('/height\s*=\s*["\']?([^\s"\']+)["\']/', $matches [0] [0], $size);
		$thumb_height = $size[1] [0];
		
		if (!$thumb_width) : 
		
			$size=$this->get_size($thumb);
			
			$thumb_width = $size['width'];
			
			$thumb_height = $size['height'];
			
			if (!$thumb_width) :
			
				$this->image_info = array (
				'thumb' => $thumb,
				);
				
				return $this->image_info;
			
			endif;
			
			$ratio = $thumb_width/$thumb_height;
			
		endif;
		
		if ($thumb_width && $height) :
		
			if ($ratio > 1) :
					
				$thumb_height = intval($thumb_height/($thumb_width/$width));
				
				$thumb_width = $width;
					
				else :
				
				$thumb_width = intval($thumb_width/($thumb_height/$height));
				
				$thumb_height = $height;
				
			endif;
			
		else :
		
			$thumb_width = $width;
			
			$thumb_height = intval($thumb_width/$ratio);
	
		endif;
	
		$this->image_info = array (
		'thumb' => $thumb,
		'thumb_width' => $thumb_width,
		'thumb_height' => $thumb_height
		);
		
		return $this->image_info;
	
	}
	
	// getting the image size if having no tags in the image string
	
	function get_size($img) {
	
		$uploaddir = wp_upload_dir();
		
		$img = str_replace($uploaddir['baseurl'], $uploaddir['basedir'], $img);
		
		$imgsize = @getimagesize($img);
		
		if (empty($imgsize)) :
		
			if ( ! function_exists( 'download_url' ) ) require_once ABSPATH.'/wp-admin/includes/file.php';
		
			$tmp_image = download_url($image);
			
			if (!is_wp_error($tmp_image)) $imgsize = @getimagesize($img);
			
			@unlink($tmp_image);
			
		endif;
		
		$size = array ( 'width' => $imgsize[0], 'height' => $imgsize[1] );
		
		return $size;
	
	}
	
}


?>