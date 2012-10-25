<?php 

/**
*ImStoreFront - single gallery shorcode 
*
*@package Image Store
*@author Hafid Trujillo
*@copyright 20010-2012
*@since 0.5.3 
*/

// Stop direct access of the file
if(preg_match('#'.basename(__FILE__).'#',$_SERVER['PHP_SELF'])) 
	die();

class ImStoreShortCode{
	
	/**
	 * Constructor
	 *
	 * @return void
	 * @since 0.5.3 
	 */
	function ImStoreShortCode( ){
		global $ImStore;
		$this->opts  = $ImStore->opts;
		add_shortcode( 'ims-gallery', array( $this, 'ims_gallery_shortcode' ),50 );
	}

	/**
	 * Core function display gallery
	 *
	 * @param array $atts
	 * @return void
	 * @since 0.5.0 
	 */
	function ims_gallery_shortcode($atts) {
		if( !is_singular( ) ) return; 
	
		extract( $atts = shortcode_atts(array(
			'id' 			=> '',
			'caption' 	=> 1,
			'number' 	=> false,
			'order' 		=> false,
			'linkto'		=> 'file',
			'orderby' 	=> false,
			'slideshow' => false,
			'size'			=> 'thumbnail',
			'layout' 	=> 'lightbox',
		), $atts ));
		
		if( empty( $id ) ) return;
		
		$sort = array(
			'date' 		=> 'post_date',
			'title' 		=> 'post_title',
			'custom' 	=> 'menu_order',
			'caption' 	=> 'post_excerpt',
		);
		
		global $wpdb;
		$this->galid = $wpdb->get_var( $wpdb->prepare(
			"SELECT post_id FROM $wpdb->postmeta 
			 WHERE meta_key = '_ims_gallery_id'
			 AND meta_value = '%s'", $id 
		));
		
		$this->order 	= ( $order ) ? $order : $this->opts['imgsortorder'];
		$this->sortby 	= ( isset($sort[$orderby]) ) ? $sort[$orderby] : $this->opts['imgsortdirect'];
		$this->limit 	= ( empty($number) ||  strtolower($number) == 'all' ) ?  false : $number;
		$this->get_galleries( );
		
		$slideshow = ( isset( $layout ) && strtolower($layout) == 'slideshow' ) ? true : false;
		if( $slideshow ) return $this->display_slideshow( $atts );
		
		return $this->display_galleries( $atts );
	}
	
	/**
	*Get gallery images
	*
	*@return array
	*@since 2.0.0
	*/
	function get_galleries( ){
		global $wpdb;
		
		$limit = ( empty( $this->limit) )  ?  '' : " LIMIT $this->limit " ;
		$this->attachments = wp_cache_get( 'ims_shortcode_' . $this->galid );
		
		if ( false == $this->attachments ){
			
			//$wpdb->show_errors( );
			$this->attachments = $wpdb->get_results( $wpdb->prepare(
				"SELECT ID, post_title ,guid, post_author,
				meta_value meta, post_excerpt, post_expire
				FROM $wpdb->posts AS p 
				LEFT JOIN $wpdb->postmeta AS pm
				ON p.ID = pm.post_id
				WHERE post_type = 'ims_image'
				AND meta_key = '_wp_attachment_metadata'
				AND post_status = 'publish' AND post_parent = %d
				ORDER BY $this->order $this->sortby $limit", $this->galid 
			));
			
			if( empty( $this->attachments ) )  return;
			
			foreach( $this->attachments as $post ){
				$post->meta = unserialize( $post->meta );
				$images[] = $post;
			} $this->attachments = $images;
			
			wp_cache_set( 'ims_shortcode_' . $this->galid, $this->attachments );
			return;
		}
	}
	
	/**
	 * Display galleries
	 *
	 * @param array $atts
	 * @return array
	 * @since 0.5.3 
	 */
	function display_galleries($atts){ 
		
		$tags = apply_filters( 'ims_gallery_tags', array(
			'gallerytag' => 'div', 
			'imagetag' => 'figure', 
			'captiontag' => 'figcaption'
		 ), $this ); 
		
		extract($atts); extract( $tags );
		
		global $ImStore;
		$tagatts	= ( $layout == 'lightbox' ) ?  ' class="ims-colorbox"' : ' class="ims-'.$layout.'"';
		$galid 		= wp_get_post_parent_id( $this->attachments[0]->ID ); 

		$output = "<{$gallerytag} id='ims-gallery-".$galid."' class='ims-gallery'>";
		foreach( $this->attachments as $image ){
			
			$title	= get_the_title( $image->ID );
			$thmb = $image->meta['sizes'][$size];
			
			$cap = ( $caption && $image->post_excerpt ) ? $image->post_excerpt : $title;
			
			$url = $ImStore->get_image_url( $image, $size );
			$link = ( $linkto == 'attachment' ) ? get_attachment_link( $image->ID )  :
			$ImStore->get_image_url( $image );
			
			$isize = ' width="'.$thmb['width'].'" height="'.$thmb['height'].'"';
			$img = '<img src="' . $url . '" title="' . esc_attr( $cap ) . '" class="colorbox-2" alt="' . esc_attr( $title ) . '"'. $isize . ' />'; 
			
			$output .= "<{$imagetag} class='ims-img'>";
			$output .= '<a href="' . $link . '"' . $tagatts . '  rel="gallery" title="' . esc_attr( $title ) . '">' . $img . '</a>';
			if( $caption ) $output .= "<{$captiontag} class='gallery-caption'>" . wptexturize( $cap ) . "</{$captiontag}>";
			
			$output .= apply_filters( 'ims_shortcode_after_image', '', $image , $atts );
			$output .= "</{$imagetag}>";

		}
		$output .= "<div class='ims-cl'></div>";
		return $output .= "</{$gallerytag}>";
	
	}
	
	
		/**
	 * Display slideshow
	 *
	 * @param array $atts
	 * @return array
	 * @since 0.5.3 
	 */
	function display_slideshow($atts){ 
		
		extract($atts);
		
		global $ImStore;
		$this->baseurl = $ImStore->baseurl;
		
		//navigation
		$output = '<div class="ims-imgs-nav">';
		$output .= '<div id="ims-thumbs"> <ul class="thumbs">';

		foreach( $this->attachments as $image ){
			
			$title	= get_the_title( $image->ID );
			$mini = $image->meta['sizes']['mini'];

			$size = ' width="'.$mini['width'].'" height="'.$mini['height'].'"';
			$cap = ( $caption && $image->post_excerpt ) ? $image->post_excerpt : $title;
			
			$link = $ImStore->get_image_url( $image  );
			$url = $ImStore->get_image_url( $image, 'thumbnail' );
			
			if( $caption ) $caption =  '<span class="caption">'. $cap .'</span>';
			$image = '<img src="' . $url . '" title="' . esc_attr( $cap ) . '" class="colorbox-2" alt="' . esc_attr( $title ) . '"'. $size . ' />';
			$output .= '<li class="ims-thumb"><a class="thumb" href="'.$link.'" title="'.esc_attr( $title ).'">'. $image.'</a>'. $caption .'</li>';
		}
			
		$output .= '</ul></div>';
		$output .= '</div>';	
		
		$output .= '<div class="ims-slideshow-box">';
		$output .= '
			<div class="ims-preview">
				<div class="ims-slideshow-row">
					<div id="ims-slideshow" class="ims-slideshow" ></div>
				</div>
			</div><!--.ims-preview-->';
			
			
		$output .= '<div class="ims-slideshow-tools-box">'."\n";
		$output .= '<div class="zoom">&nbsp;</div>'."\n";
		$output .= '<form method="post" class="ims-slideshow-tools">'."\n";
		
		$output .= '<div class="image-color">'."\n";
		if( empty( $this->opts['disablebw'] ))
			$output .= '<label><input type="checkbox" name="ims-color" id="ims-color-bw" value="bandw" /> '. __( 'Black &amp; White', $ImStore->domain ) . '</label>	'."\n";
		if( empty( $this->opts['disablesepia'] ))
			$output .= '<label><input type="checkbox" name="ims-color" id="ims-color-sepia" value="sepia" /> '. __( 'Sepia', $ImStore->domain ) . '</label>	'."\n";
		
		$output .= apply_filters( 'ims_color_options', '' );
		$output .= '</div><!--.image-color-->'."\n";
		
		$output .= '<div id="ims-player" class="ims-player">'."\n";
		$output .= '<a href="#" class="bk" rel="nofollow">' . __( 'Back', $ImStore->domain ) . '</a>';
		$output .= '<a href="#" class="py" rel="nofollow">' . __( 'Play', $ImStore->domain ) . '</a>';
		$output .= '<a href="#" class="nx" rel="nofollow">' . __( 'Next', $ImStore->domain ) . '</a>';
		$output .= '</div><!--#ims-player-->'."\n";
		
		
		$output .= '</form><!--.ims-slideshow-tools-->'."\n";
		$output .= '<div id="ims-caption" class="ims-caption"></div>'."\n";
		$output .= apply_filters( 'ims_after_slideshow', '' );
		
		$output .= '</div><!--.ims-slideshow-tools-box-->'."\n";
		$output .= '<div class="ims-cl"></div>'."\n";
		$output .= '</div><!--.ims-slideshow-box-->'."\n";
		
		
		return $output;
	}
	
	
}
new ImStoreShortCode( );

?>
