<?php

/**
 * ImStoreFront - Tools Widget
 * 
 * @package Image Store
 * @author Hafid Trujillo
 * @copyright 20010-2012
 * @since 3.1.0
 */
class ImStoreWidgetTools extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 * @since 3.1.0
	 */
	function ImStoreWidgetTools() {
		$widget_ops = array(
			'classname' => 'ims-widget-tools',
			'description' => __("Display Image Store tools and navigation", 'ims')
		);
		$this->WP_Widget('ims-widget-tools', __('Image Store Tools', 'ims'), $widget_ops);
	}

	/**
	 * Display widget.
	 *
	 * @since 3.1.0
	 * @return void
	 */
	function widget($args, $instance) {

		global $ImStore;
		extract($args);
		extract($instance);
		
		echo $before_widget . "\n";

		if ($title)
			echo $before_title . $title . $after_title . "\n";

		echo '<div class="ims-innner-widget">';

		if (is_singular('ims_gallery')) {
			echo $ImStore->store_nav(false);
			if (in_array($ImStore->imspage, array('photos', 'favorites')))
				echo $ImStore->store_subnav(false);
		}

		$parent_id = false;

		//display thumbnail
		if (isset($ImStore->cart['images']) && is_array($ImStore->cart['images'])) {
			echo '<div class="ims-gallery ims-tools-gal"><div class="ims-gal-innner">';
			foreach ($ImStore->cart['images'] as $id => $image) {

				$title = esc_attr(get_the_title($id));
				$parent_id = wp_get_post_parent_id($id);

				$image = get_post_meta($id, '_wp_attachment_metadata', true);
				$size = ' width="' . $image['sizes']['mini']['width'] . '" height="' . $image['sizes']['mini']['height'] . '"';

				echo '<figure class="hmedia ims-img"><a href="' . get_permalink(wp_get_post_parent_id($id)) . '" title="' . $title . '" rel="enclosure">';
				echo '<img class="photo" src="' . IMSTORE_URL . '/_img/1x1.trans.gif" data-ims-src="' . $ImStore->get_image_url($id, 3) . '" alt="' . $title . '" ' . $size . ' role="img" />';
				echo '</a></figure>';
			}
			echo '</div><!--.ms-tools-gal-inner--></div><!--.ims-tools-gal-->';
		}

		echo '<div class="ims-tools">';
		$link = '<a href="' . $this->get_permalink($parent_id, 'shopping-cart') . '" role="link" class="ims-checkout" title="' . __('Checkout', 'ims') . '">%s</a>';

		//care items
		if (isset($ImStore->cart['items']))
			echo '<div class="ims-items"><span class="ims-label">' . __('Total Items:', 'ims') . ' </span>' . sprintf($link, $ImStore->cart['items']) . "</div>\n";

		if (isset($ImStore->cart['total']))
			echo '<div class="ims-total"><span class="ims-label">' . __('Total:', 'ims') . ' </span>' . sprintf($link, $ImStore->format_price($ImStore->cart['total'])) . "</div>\n";

		echo '</div><!--.ims-tools-->';
		echo '</div><!--.ims-innner-widget-->';

		echo $after_widget . "\n";
	}

	/**
	 * Get imstore permalink
	 *
	 * @param string $page
	 * @since 3.1.0
	 * return string
	 */
	function get_permalink($id, $page = false) {
		global $wp_rewrite, $ImStore;
		$link = '';
		if ($wp_rewrite->using_permalinks()) {
			if ($page && isset($ImStore->pages[$page])) {
				if (preg_match('/[^\\p{Common}\\p{Latin}]/u', $ImStore->pages[$page]))
					$link .= '/' . $page;
				else
					$link .= '/' . sanitize_title($ImStore->pages[$page]);
			}
		} else if ($page) {
			$link .= '&imspage=' . $page;
		}
		return get_permalink($id) . $link;
	}

	/**
	 * Configuration form.
	 *
	 * @since 3.1.0
	 * @return void
	 */
	function form($instance) {
		$default = array('title' => '');
		$instance = wp_parse_args($instance, $default);
		extract($instance);
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title') ?>"><?php _e('Title', 'ims') ?> <input class="widefat" id="<?php echo $this->get_field_id('title') ?>" name="<?php echo $this->get_field_name('title') ?>" type="text" value="<?php echo $title ?>" /></label>
		</p>
		<?php
	}

}

add_action('widgets_init', create_function('', 'return register_widget("ImStoreWidgetTools");'));
