<?php 

/**
*ImStoreFront - single gallery shorcode 
*
*@package Image Store
*@author Hafid Trujillo
*@copyright 20010-2011
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
	function __construct(){
		$this->opts = get_option('ims_front_options');
		add_shortcode('ims-gallery',array($this,'ims_gallery_shortcode'),50);
	}
	
	/**
	*Encrypt image ID for downlaods
	*
	*@return void
	*@since 2.0.4
	*/
	function encrypt_id($int) {
    	$HashedChecksum = substr(sha1("imstore".$int.SECURE_AUTH_KEY),0,6);
    	$hex = dechex($int);
    	return urlencode(base64_encode($HashedChecksum.$hex));
    }
	
	/**
	 * Core function display gallery
	 *
	 * @param array $atts
	 * @return void
	 * @since 0.5.0 
	 */
	function ims_gallery_shortcode($atts) {
		global $wpdb;
		extract($atts = shortcode_atts(array(
			'id' 		=> '',
			'caption' 	=> 1,
			'lightbox' 	=> 1,
			'number' 	=> false,
			'order' 	=> false,
			'orderby' 	=> false,
			'slideshow' => false,
		),$atts));
		
		if(empty($id)) return;
		$sort = array(
			'date' 		=> 'post_date',
			'title' 	=> 'post_title',
			'custom' 	=> 'menu_order',
			'caption' 	=> 'post_excerpt',
		);

		$this->gallery_id = $wpdb->get_var($wpdb->prepare(
			"SELECT post_id FROM $wpdb->postmeta 
			 WHERE meta_key = '_ims_gallery_id'
			 AND meta_value = '%s'",$id 
		)) ;
		
		$this->limit 	= $number;
		$this->order 	= ($order)?$order:$this->opts['imgsortdirect'];
		$this->sortby 	= ($sort[$orderby])?$sort[$orderby]:$this->opts['imgsortorder'];
		$this->get_galleries();
		
		if($slideshow) return $this->display_slideshow($atts) ;
		else return $this->display_galleries($atts);
	}
	
	/**
	*Get gallery images
	*
	*@return array
	*@since 2.0.0
	*/
	function get_galleries($album=''){
		global $wpdb;
		
		$wpdb->show_errors();
		if($this->limit) $limit = " LIMIT $this->limit ";
		$this->attachments = $wpdb->get_results($wpdb->prepare(
			"SELECT ID,post_title,guid,
			meta_value,post_excerpt,post_expire
			FROM $wpdb->posts AS p 
			LEFT JOIN $wpdb->postmeta AS pm
			ON p.ID = pm.post_id
			WHERE post_type = 'ims_image'
			AND meta_key = '_wp_attachment_metadata'
			AND post_status = 'publish'
			AND post_parent = %d
			ORDER BY $this->sortby $this->order $limit" 
		,$this->gallery_id));
		if(empty($this->attachments)) return;
		foreach($this->attachments as $post){
			$post->meta_value = unserialize($post->meta_value);
			$images[] = $post;
		} $this->attachments = $images;
	}
	
	/**
	 * Display galleries
	 *
	 * @param array $atts
	 * @return array
	 * @since 0.5.3 
	 */
	function display_galleries($atts){ 
		
		extract($atts);
		
		$itemtag 	= 'ul';
		$icontag 	= 'li';
		$captiontag = 'div';
		$nonce 		= '_wpnonce='.wp_create_nonce('ims_secure_img');
		
		$output = "<{$itemtag} class='ims-gallery'>";
		foreach($this->attachments as $image){

			$link 		= IMSTORE_URL."image.php?$nonce&amp;img=".$this->encrypt_id($image->ID);
			$title_att 	= ($caption)? 'title="'.esc_attr($image->post_title).'" name="'.esc_attr($image->post_excerpt).'"':' ' ;
			$tagatts 	= ($lightbox)?' class="ims-colorbox" rel="gallery" ':' class="ims-image" rel="image" ';
			$imagetag 	= '<img src="'.$image->meta_value['sizes']['thumbnail']['url'].'" alt="'.$image->post_title.'" />'; 
			
			$output .= "<{$icontag}>";
			$output .= '<a href="'.$link.'"'. $tagatts.$title_att .' >'.$imagetag.'</a>';
			$output .= "</{$icontag}>";
		}
		return $output .= "</{$itemtag}>";
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
		
		//navigation
		$output .= '<div class="ims-imgs-nav">';
		$output .= '<div id="ims-thumbs">
						<ul class="thumbs">';
		if(!empty($this->attachments)){
			$nonce = '_wpnonce='.wp_create_nonce('ims_secure_img');
			foreach($this->attachments as $image){
				$title = $image->post_title;
				$w = $image->meta_value['sizes']['mini']['width'];
				$h = $image->meta_value['sizes']['mini']['height'];
				$imagetag = '<img src="'.$image->meta_value['sizes']['mini']['url'].'" width="'.$w.'" height="'.$h.'" alt="'. $title.'" />'; 
				$output .= '<li class="ims-thumb"><a class="thumb" href="'.IMSTORE_URL."image.php?$nonce&amp;img=".$this->encrypt_id($image->ID).'" title="'.esc_attr($image->post_title).'" rel="nofollow">'.$imagetag.'</a>';
				if($caption) $output .= '<span class="caption">'.$image->post_excerpt.'</span>';
				$output .= '</li>';
			}
		}
		$output .= '</ul>
					</div>';
		$output .= '</div>';	
		
		//slideshow
		$output .= '<div class="ims-slideshow-box">';
		$output .= '<div class="ims-preview"><div class="ims-slideshow-row">
						<div id="ims-slideshow" class="ims-slideshow" ></div>
					</div></div>';
		$output .= '<div class="ims-slideshow-tools-box">
					<form method="post" class="ims-slideshow-tools">';
		if(!$this->opts['disablebw'] || !$this->opts['disablesepia']){
			$output .=	'<div class="image-color">';
			if(!$this->opts['disablebw'])
				$output .=	'<label><input type="checkbox" name="ims-color" id="ims-color-bw" value="bandw" /> '.__('Black &amp; White',ImStore::domain). ' </label>';
			if(!$this->opts['disablesepia'])		
				$output .=	'<label><input type="checkbox" name="ims-color" id="ims-color-sepia" value="sepia" /> '.__('Sepia',ImStore::domain). ' </label>';
			$output .= '</div>';
		}
		$output .=	'<div id="ims-player" class="ims-player">
							<a href="#" class="bk" rel="nofollow" role="button">'.__('Back',ImStore::domain).' </a> 
							<a href="#" class="py" rel="nofollow" role="button">'.__('Play',ImStore::domain).' </a> 
							<a href="#" class="nx" rel="nofollow" role="button">'.__('Next',ImStore::domain).' </a>
						</div>
					</form>
					<div id="ims-caption" class="ims-caption"></div>
					</div>';				
		$output .= '<div class="cl"></div></div>';	
		echo $output;
	}
}

new ImStoreShortCode();

?>