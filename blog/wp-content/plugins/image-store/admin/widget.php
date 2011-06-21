<?php 
/**
 * ImStoreFront - widget
 * 
 * @package Image Store
 * @author Hafid Trujillo
 * @copyright 20010-2011
 * @since 0.5.3
*/
class ImStoreWidget extends WP_Widget {
	
	/**
	 * Constructor
	 *
	 * @return void
	 * @since 0.5.3
	 */
	function ImStoreWidget() {
		$widget_ops = array('classname' => 'ims-widget','description' => __("Display images from unsecure galleries",$this->domain));
		$this->WP_Widget('ims-widget',__('Image Store',$this->domain),$widget_ops);
	}
	
	
	/**
	 * Display widget.
	 *
	 * @return void
	 * @since 0.5.3
	 */
	function widget($args,$instance) {
		extract($args); extract($instance); 
		echo $before_widget."\n";
		echo $before_title.$title.$after_title."\n";
		$this->display_images($this->get_widget_images($instance));
		echo $after_widget."\n";
	}
	
	
	/**
	 * Configuration form.
	 *
	 * @return void
	 * @since 0.5.3
	 */
	function form($instance) {
		extract($instance); $title = esc_attr($title);
		$order_options = array(
			'post_date' 	=> __('Date',ImStore::domain),
			'post_title' 	=> __('Title',ImStore::domain),
			'menu_order' 	=> __('Custom',ImStore::domain),
			'post_excerpt' 	=> __('Caption',ImStore::domain),
		);
		$show_options = array(
			'DESC'	 	=> __('Latest images',ImStore::domain),
			'ASC'	 	=> __('Oldest images',ImStore::domain),
			'rand' 		=> __('Random images',ImStore::domain),
			'gal' 		=> __('Gallery',ImStore::domain),
		);
	 ?>
		<p>
	 		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title',ImStore::domain); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('orderby'); ?>"><?php _e('Order by',ImStore::domain); ?></label>
			<select id="<?php echo $this->get_field_id('orderby'); ?>" name="<?php echo $this->get_field_name('orderby'); ?>">
			 <?php foreach($order_options as $value => $label) { ?>
				<option value="<?php echo $value?>" <?php echo selected($value,$orderby) ?> ><?php echo $label ?></option> 
			   <?php } ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('show'); ?>"><?php _e('Show',ImStore::domain); ?></label>
			<select id="<?php echo $this->get_field_id('show'); ?>" name="<?php echo $this->get_field_name('show'); ?>">
			<?php foreach($show_options as $value => $label) { ?>
			<option value="<?php echo $value?>" <?php echo selected($value,$show) ?> ><?php echo $label ?></option> 
			 <?php } ?>
			 </select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e('How many images',ImStore::domain); ?> <input id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" size="4" type="text" value="<?php echo $limit; ?>"/></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('galid'); ?>"><?php _e('Gallery ID',ImStore::domain); ?><input id="<?php echo $this->get_field_id('galid'); ?>" name="<?php echo $this->get_field_name('galid'); ?>" class="widefat" type="text" value="<?php echo $galid; ?>"/></label>
		<small><?php _e('To be use with show gallery option',ImStore::domain); ?></small>
		</p>

	<?php }


	/**
	 * Get recent images
	 * From unsecure galleries
	 *
	 * @return array
	 * @since 0.5.3 
	 */
	function get_widget_images($instance){
		global $wpdb;
		extract($instance);
		$gallery_id = $wpdb->get_var($wpdb->prepare(
			"SELECT post_id  FROM $wpdb->postmeta 
			 WHERE meta_key = '_ims_gallery_id'
			 AND meta_value = '%s'",$galid
		)) ;
		if($gallery_id && $show == 'gal') $parent = " = $gallery_id";
		else $parent = " IN(SELECT ID FROM $wpdb->posts WHERE post_type = 'ims_gallery' AND post_status = 'publish' AND post_password = '')";
		if($show == 'gal'){ $order = " DESC";
		}elseif($show == 'rand'){ $order = " RAND()"; $orderby = '';
		}else{ $order = $show; }
		if($limit) $limit = "LIMIT $limit";
		$result = $wpdb->get_results($wpdb->prepare(
			"SELECT ID,post_title,guid,post_excerpt
			FROM $wpdb->posts AS p 
			WHERE post_type = 'ims_image'
			AND post_status = 'publish'
			AND post_parent $parent
			ORDER BY $orderby $order $limit"
		));
		if(empty($result)) return;
		foreach($result as $post)$images[] = $post;
		return $images;
	}
	
	
	/**
	 * Display galleries
	 *
	 * @return array
	 * @since 0.5.3 
	 */
	function display_images($images){ 
		global $ImStore; 
		$itemtag 	= 'ul';
		$icontag 	= 'li';
		$captiontag = 'div';
		$nonce 		= '_wpnonce='.wp_create_nonce('ims_secure_img');
		$output = "<{$itemtag} class='ims-gallery'>";
		foreach((array)$images as $image){
			$enc = $ImStore->store->encrypt_id($image->ID);	
			$output .= "<{$icontag}>";
			$output .= '<img src="'.IMSTORE_URL."image.php?$nonce&amp;img={$enc}&amp;mini=1".'" class="ims-widget-img" alt="'.$image->post_title .'" />';			$output .= "</{$icontag}>";
		}
		echo $output .= "</{$itemtag}>";
	}
}
add_action('widgets_init',create_function('','return register_widget("ImStoreWidget");'));
?>