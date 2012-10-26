<?php

/**
 * ImStoreFront - widget
 * 
 * @package Image Store
 * @author Hafid Trujillo
 * @copyright 20010-2012
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
		$widget_ops = array(
			'classname' => 'ims-widget',
			'description' => __("Display images from unsecure galleries", 'ims')
		);
		$this->WP_Widget('ims-widget', __('Image Store', 'ims'), $widget_ops);
	}

	/**
	 * Display widget.
	 *
	 * @return void
	 * @since 0.5.3
	 */
	function widget($args, $instance) {
		extract($args);
		extract($instance);

		echo $before_widget . "\n";

		if ( $title ) echo $before_title . $title . $after_title . "\n";
		
		$this->filmstrip  = $filmstrip;
		$this->display_images(
			$this->get_widget_images($instance)
		);

		echo $after_widget . "\n";
	}

	/**
	 * Configuration form.
	 *
	 * @return void
	 * @since 0.5.3
	 */
	function form($instance) {

		$default = array(
			'title' => NULL, 'limit' => false, 'filmstrip' => 1,
			'galid' => false, 'show' => false, 'orderby' => false,
		);
		$instance = wp_parse_args($instance, $default);

		extract($instance);

		$order_options = array(
			'post_date' => __('Date', 'ims'),
			'post_title' => __('Title', 'ims'),
			'menu_order' => __('Custom', 'ims'),
			'post_excerpt' => __('Caption', 'ims'),
		);

		$show_options = apply_filters('ims_widget_display_options', array(
			'gal' => __('Gallery', 'ims'),
			'ASC' => __('Oldest images', 'ims'),
			'DESC' => __('Latest images', 'ims'),
			'rand' => __('Random images', 'ims'),
		));
		?>

		<p>
			<label for="<?php echo $this->get_field_id('title') ?>"><?php _e('Title', 'ims') ?> <input class="widefat" id="<?php echo $this->get_field_id('title') ?>" name="<?php echo $this->get_field_name('title') ?>" type="text" value="<?php echo $title ?>" /></label>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('filmstrip')?>"> <?php _e( 'Filmstrip mode', 'ims')?> 
				<input id="<?php echo $this->get_field_id('filmstrip')?>" name="<?php echo $this->get_field_name('filmstrip')?>" type="checkbox" <?php checked( 'on', $filmstrip ) ?> /> 
				<br /><small><?php _e( 'Display images in film strip mode', 'ims')?></small>
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('orderby') ?>"><?php _e('Order by', 'ims') ?></label>
			<select id="<?php echo $this->get_field_id('orderby') ?>" name="<?php echo $this->get_field_name('orderby') ?>">
				<?php foreach ($order_options as $value => $label) { ?>
					<option value="<?php echo esc_attr($value) ?>" <?php echo selected($value, $orderby) ?> ><?php echo esc_html($label) ?></option> 
				<?php } ?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('show') ?>"><?php _e('Show', 'ims') ?></label>
			<select id="<?php echo $this->get_field_id('show') ?>" name="<?php echo $this->get_field_name('show') ?>">
				<?php foreach ($show_options as $value => $label) { ?>
					<option value="<?php echo esc_attr($value) ?>" <?php echo selected($value, $show) ?> ><?php echo esc_html($label) ?></option> 
				<?php } ?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e('How many images', 'ims'); ?> <input id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" size="4" type="text" value="<?php echo esc_attr($limit) ?>"/></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('galid'); ?>"><?php _e('Gallery ID', 'ims'); ?><input id="<?php echo $this->get_field_id('galid'); ?>" name="<?php echo $this->get_field_name('galid'); ?>" class="widefat" type="text" value="<?php echo esc_attr($galid) ?>"/></label>
			<small><?php _e('To be use with "show" gallery option', 'ims'); ?></small>
		</p>
		<?php
	}

	/**
	 * Get recent images
	 * From unsecure galleries
	 *
	 * @param array $instance
	 * @return array
	 * @since 0.5.3 
	 */
	function get_widget_images($instance) {

		if (empty($instance))
			return;

		global $wpdb;
		extract($instance);

		$gallery_id = $wpdb->get_var($wpdb->prepare(
				"SELECT post_id FROM $wpdb->postmeta 
			 WHERE meta_key = '_ims_gallery_id'
			 AND meta_value = %s", $galid
		));

		if ($gallery_id && $show == 'gal')
			$parent = " = $gallery_id";
		else
			$parent = " IN ( 
			SELECT ID FROM $wpdb->posts 
			WHERE post_type = 'ims_gallery' AND post_status = 'publish' AND post_password = ''
		)";

		if (empty($parent))
			return;

		if ($show == 'gal') {
			$order = " DESC";
		} elseif ($show == 'rand') {
			$orderby = '';
			$order = " RAND( )";
		} else {
			$order = $wpdb->escape($show);
		}

		if ($limit)
			$limit = "LIMIT $limit";

		$images = wp_cache_get('ims_widget_' . $this->number);
		if (false == $images) {
			$images = $wpdb->get_results($wpdb->prepare(
				"SELECT ID , post_title, guid, post_parent, post_excerpt, meta_value meta
				FROM $wpdb->posts p LEFT JOIN $wpdb->postmeta pm
				ON p.ID = pm.post_id WHERE post_type = 'ims_image' 
				AND post_status = 'publish' AND post_parent $parent
				AND pm.meta_key = '_wp_attachment_metadata'
				ORDER BY $orderby $order $limit "
			));
			wp_cache_set('ims_widget_' . $this->number, $images);
		}

		if (empty($images))
			return;

		foreach ($images as $image) {
			$image->meta = maybe_unserialize($image->meta);
			$this->attachments[] = $image;
		}

		return true;
	}

	/**
	 * Display galleries
	 *
	 * @param array $images
	 * @return array
	 * @since 0.5.3 
	 */
	function display_images() {

		if (empty($this->attachments))
			return;

		global $ImStore;
		$output = apply_filters('ims_widget_images', false, $this->attachments);

		if (false != $output) {
			echo output;
			return true;
		}

		$tags = apply_filters('ims_gallery_tags', array(
			'itemtag' => 'div',
			'imagetag' => 'figure',
			'icontag' => 'figcaption'
		), $this);
		extract($tags);
		
		$css = 'ims-gallery';
		
		if( $this->filmstrip )
			$css .=" ims-filmstrip";
		
		$output = "<{$itemtag} class='$css'>
		<div class='ims-gal-innner'>";
		foreach ($this->attachments as $image) {

			if (!isset($image->meta['sizes']['mini']))
				continue;

			$mini = $image->meta['sizes']['mini'];
			$link = get_permalink($image->post_parent);
			$size = ' width="' . $mini['width'] . '" height="' . $mini['height'] . '"';

			$imgattr = ' role="img" class="photo ims-image" alt="' . esc_attr($image->post_title) . '"' . $size;
			$imgtag = '<img src="' . IMSTORE_URL . '/_img/1x1.trans.gif" itemprop="thumbnail"  data-ims-src="' . $ImStore->get_image_url($image->ID, 3) . '" ' . $imgattr . '/>';

			$output .= "<{$imagetag} class='hmedia ims-img' itemscope itemtype='http://schema.org/ImageObject'>";
			$output .= '<a href="' . $link . '" rel="enclosure" itemprop="contentURL">' . $imgtag . '</a>';
			$output .= "<{$icontag} class='gallery-caption'><span class='fn ims-img-name'  itemprop='caption'>" . wptexturize($image->post_excerpt) . "</span></{$icontag}>";
			$output .= "</{$imagetag}>";
		}
		$output .= "</div></{$itemtag}><!--.hmedia-->";
		echo $output .= '<div class="ims-cl"></div>';
	}

}

add_action('widgets_init', create_function('', 'return register_widget("ImStoreWidget");'));
