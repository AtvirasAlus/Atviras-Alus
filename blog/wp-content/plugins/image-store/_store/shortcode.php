<?php

/**
 * ImStoreFront - single gallery shorcode 
 *
 * @package Image Store
 * @author Hafid Trujillo
 * @copyright 20010-2012
 * @since 0.5.3 
 */

// Stop direct access of the file
if (!defined('ABSPATH'))
	die();


class ImStoreShortCode {

	/**
	 * Constructor
	 *
	 * @return void
	 * @since 0.5.3 
	 */
	function ImStoreShortCode() {
		global $ImStore;
		$this->opts = $ImStore->opts;
		add_shortcode('ims-gallery', array(&$this, 'ims_gallery_shortcode'), 50);
	}

	/**
	 * Core function display gallery
	 *
	 * @param array $atts
	 * @return void
	 * @since 0.5.0 
	 */
	function ims_gallery_shortcode($atts) {
		if (!is_singular())
			return;

		extract($atts = shortcode_atts(array(
			'id' => '',
			'caption' => 1,
			'filmstrip' => 1,
			'number' => false,
			'order' => false,
			'linkto' => 'file',
			'orderby' => false,
			'slideshow' => false,
			'size' => 'thumbnail',
			'layout' => 'lightbox',
		), $atts));

		if (empty($id))
			return;

		$sort = array(
			'date' => 'post_date',
			'title' => 'post_title',
			'custom' => 'menu_order',
			'caption' => 'post_excerpt',
		);

		global $wpdb;
		$this->galid = $wpdb->get_var($wpdb->prepare(
			"SELECT post_id FROM $wpdb->postmeta 
			 WHERE meta_key = '_ims_gallery_id'
			 AND meta_value = '%s'", $id
		));

		if (empty($this->galid))
			return;

		$this->order = ( $order ) ? $order : $this->opts['imgsortorder'];
		$this->limit = ( empty($number) || strtolower($number) == 'all' ) ? false : $number;
		$this->sortby = isset($sort[$orderby]) ? $sort[$orderby] : $this->opts['imgsortdirect'];
		
		$this->get_galleries();
		$slideshow = ( isset($layout) && strtolower($layout) == 'slideshow' ) ? true : false;
		
		if ($slideshow)
			return $this->display_slideshow($atts);

		return $this->display_galleries($atts);
	}

	/**
	 * Get gallery images
	 *
	 * @return array
	 * @since 2.0.0
	 */
	function get_galleries() {
		global $wpdb;

		$limit = ( empty($this->limit) ) ? '' : " LIMIT $this->limit ";
		$this->attachments = wp_cache_get('ims_shortcode_' . $this->galid);

		if (false == $this->attachments) {

			//$wpdb->show_errors( );
			$this->attachments = $wpdb->get_results($wpdb->prepare(
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

			if (empty($this->attachments))
				return;

			foreach ($this->attachments as $post) {
				$post->meta = unserialize($post->meta);
				$images[] = $post;
			} $this->attachments = $images;

			wp_cache_set('ims_shortcode_' . $this->galid, $this->attachments);
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
	function display_galleries($atts) {
		
		if( empty($this->attachments[0])) 
			return;
		
		$tags = apply_filters('ims_gallery_tags', array(
			'gallerytag' => 'div',
			'imagetag' => 'figure',
			'captiontag' => 'figcaption'
		), $this);

		extract($atts);
		extract($tags);

		global $ImStore;
		$tagatts = ( $layout == 'lightbox' ) ? ' class="ims-colorbox"' : ' class="ims-' . $layout . '"';
		$galid = wp_get_post_parent_id($this->attachments[0]->ID);

		$output = "<{$gallerytag} id='ims-gallery-" . $galid . "' class='ims-gallery'>";
		foreach ($this->attachments as $image) {

			$title = get_the_title($image->ID);
			$thmb = $image->meta['sizes'][$size];

			$cap = ( $caption && $image->post_excerpt ) ? $image->post_excerpt : $title;

			$url = $ImStore->get_image_url($image->ID, 2);
			$link = ( $linkto == 'attachment' ) ? get_attachment_link($image->ID) :
					$ImStore->get_image_url($image->ID);

			$isize = ' width="' . $thmb['width'] . '" height="' . $thmb['height'] . '"';
			$img = '<img role="img" src="' . $url . '" title="' . esc_attr($cap) . '" class="colorbox-2" alt="' . esc_attr($title) . '"' . $isize . ' />';

			$output .= "<{$imagetag} class='ims-img'>";
			$output .= '<a href="' . $link . '"' . $tagatts . ' rel="bookmark" title="' . esc_attr($title) . '">' . $img . '</a>';
			if ($caption)
				$output .= "<{$captiontag} class='gallery-caption'>" . wptexturize($cap) . "</{$captiontag}>";

			$output .= apply_filters('ims_shortcode_after_image', '', $image, $atts);
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
	function display_slideshow($atts) {

		extract($atts);

		global $ImStore;
		$this->baseurl = $ImStore->baseurl;
		
		$output = '';
		
		if(!$ImStore->opts['bottommenu'])
			$output .= $ImStore->slide_show_nav($this->attachments );

		$output .= '<div class="ims-slideshow-box">';
		$output .= '
			<div class="ims-preview">
				<div class="ims-slideshow-row">
					<div id="ims-slideshow" class="ims-slideshow" ></div>
				</div>
			</div><!--.ims-preview-->';


		$output .= '<div class="ims-slideshow-tools-box">' . "\n";
		$output .= '<div id="ims-caption" class="ims-caption"></div>' . "\n";
		
		if($ImStore->opts['bottommenu'])
			$output .= $ImStore->slide_show_nav($this->attachments );
		
		$output .= '<form method="post" class="ims-slideshow-tools">' . "\n";
		$output .= '<div id="ims-player" class="ims-player">' . "\n";
		$output .= '<a href="#" class="bk" rel="nofollow">' . __('Back', 'ims') . '</a>';
		$output .= '<a href="#" class="py" rel="nofollow">' . __('Play', 'ims') . '</a>';
		$output .= '<a href="#" class="nx" rel="nofollow">' . __('Next', 'ims') . '</a>';
		$output .= '</div><!--#ims-player-->' . "\n";

		//color options
		$output .= '<div class="image-color">' . "\n";
		if (!empty($ImStore->listmeta['colors']) ){
			$output .= '<span class="ims-color-label">' . __('Color Options:', 'ims') . '</span>' . "\n";
			foreach ($ImStore->listmeta['colors'] as $key => $color){
				if($color['code'])
					$output .= '<label><input type="checkbox" name="ims-color[]" value="'.$color['code'].'" class="ims-color ims-color-'.$color['code'].'" /> ' . $color['name'] . '</label>	' . "\n";
			}
		}

		$output .= apply_filters('ims_color_options', '');
		$output .= '</div><!--.image-color-->' . "\n";
		
		$output .= '</form><!--.ims-slideshow-tools-->' . "\n";
		$output .= apply_filters('ims_after_slideshow', '');

		$output .= '</div><!--.ims-slideshow-tools-box-->' . "\n";
		$output .= '<div class="ims-cl"></div>' . "\n";
		$output .= '</div><!--.ims-slideshow-box-->' . "\n";

		return $output;
	}

}

new ImStoreShortCode( );