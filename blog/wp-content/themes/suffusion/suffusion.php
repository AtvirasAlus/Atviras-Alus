<?php
/**
 * Core class for Suffusion. This holds the options for the theme.
 *
 * @package Suffusion
 * @subpackage Functions
 */
class Suffusion {
	var $context;

	function init() {
		$this->set_translatable_fields();
		$this->set_image_sizes();
	}

	function get_context() {
		if (is_array($this->context)) {
			return $this->context;
		}

		$this->context = array();
		if (is_front_page()) {
			$this->context[] = 'home';
		}

		if (is_home()) {
			$this->context[] = 'blog';
		}

		if (is_singular()) {
			global $post;
			$this->context[] = 'singular';
			$this->context[] = "{$post->post_type}";
			if ($post->post_type == 'page') {
				$page_template = get_page_template();
				$path = TEMPLATEPATH;
				if (is_child_theme() && strlen($page_template) > strlen(STYLESHEETPATH) && substr($page_template, 0, strlen(STYLESHEETPATH)) == STYLESHEETPATH) {
					$path = STYLESHEETPATH;
				}
				if (strlen($page_template) > strlen($path)) {
					$page_template = substr($page_template, strlen($path) + 1);
					$this->context[] = 'page-template';
					$this->context[] = $page_template;
				}
			}
		}
		else if (is_archive()) {
			$this->context[] = 'archive';
			if (is_date()) {
				$this->context[] = 'date';
			}
			else if (is_category()) {
				$this->context[] = 'taxonomy';
				$this->context[] = 'category';
			}
			else if (is_tag()) {
				$this->context[] = 'taxonomy';
				$this->context[] = 'tag';
			}
			else if (is_author()) {
				$this->context[] = 'author';
			}
		}
		else if (is_search()) {
			$this->context[] = 'search';
		}
		else if (is_404()) {
			$this->context[] = '404';
		}

		return $this->context;
	}

	/**
	 * Adds WPML support for translating fields set in the admin panel.
	 *
	 * @return void
	 */
	function set_translatable_fields() {
		global $suffusion_translatable_fields, $suffusion_unified_options;
		if (function_exists('wpml_register_string')) {
			$suffusion_translatable_fields = array(
				"suf_home_text",
				"suf_nav_page_tab_title",
				"suf_nav_cat_tab_title",
				"suf_nav_links_tab_title",
				"suf_breadcrumb_separator",
				"suf_navt_home_text",
				"suf_navt_page_tab_title",
				"suf_navt_cat_tab_title",
				"suf_navt_links_tab_title",
				"suf_excerpt_custom_more_text",
				"suf_seo_title_separator",
				"suf_comment_label_name",
				"suf_comment_label_name_req",
				"suf_comment_label_email",
				"suf_comment_label_email_req",
				"suf_comment_label_uri",
				"suf_comment_label_your_comment",
				"suf_sbtab_categories_title",
				"suf_sbtab_archives_title",
				"suf_sbtab_Links_title",
				"suf_sbtab_meta_title",
				"suf_sbtab_pages_title",
				"suf_sbtab_recent_comments_title",
				"suf_sbtab_recent_posts_title",
				"suf_sbtab_search_title",
				"suf_sbtab_tag_cloud_title",
				"suf_sbtab_custom_tab_1_title",
				"suf_sbtab_custom_tab_2_title",
				"suf_sbtab_custom_tab_3_title",
				"suf_sbtab_custom_tab_4_title",
				"suf_sbtab_custom_tab_5_title",
				"suf_sbtab_custom_tab_6_title",
				"suf_sbtab_custom_tab_7_title",
				"suf_sbtab_custom_tab_8_title",
				"suf_sbtab_custom_tab_9_title",
				"suf_sbtab_custom_tab_10_title",
				"suf_custom_rss_title_1",
				"suf_custom_rss_title_2",
				"suf_custom_rss_title_3",
				"suf_custom_atom_title_1",
				"suf_custom_atom_title_2",
				"suf_custom_atom_title_3",

				"suf_wa_tbrh_open_text",
				"suf_wa_tbrh_close_text",

				"suf_mag_headline_title",
				"suf_mag_excerpts_title",
				"suf_mag_excerpt_full_story_text",
				"suf_mag_catblocks_title",
				"suf_mag_catblocks_see_all_text",
				"suf_sitemap_label_pages",
				"suf_sitemap_label_categories",
				"suf_sitemap_label_authors",
				"suf_sitemap_label_yarchives",
				"suf_sitemap_label_marchives",
				"suf_sitemap_label_warchives",
				"suf_sitemap_label_darchives",
				"suf_sitemap_label_tags",
				"suf_sitemap_label_posts",
				"suf_nr_lib_title",
				"suf_nr_lib_curr_title",
				"suf_nr_lib_unread_title",
				"suf_nr_lib_completed_title",
				"suf_nr_single_added_text",
				"suf_nr_single_started_text",
				"suf_nr_single_finished_text",
				"suf_nr_wid_curr_title",
				"suf_nr_wid_unread_title",
				"suf_nr_wid_completed_title",
				"suf_404_title"
			);

			if (is_admin()) {
				global $suffusion_interactive_text_fields;
				foreach ($suffusion_translatable_fields as $field) {
					$display = $suffusion_interactive_text_fields[$field]."|$field";
					wpml_register_string('suffusion-interactive', $display, $suffusion_unified_options[$field]);
				}
			}
		}
	}

	function set_image_sizes() {
		global $suf_excerpt_thumbnail_size, $suf_excerpt_thumbnail_custom_width, $suf_excerpt_thumbnail_custom_height, $suf_excerpt_tt_zc;
		global $suf_featured_image_size, $suf_featured_image_custom_width, $suf_featured_image_custom_height, $suf_featured_zc;
		global $suf_mag_headline_image_size, $suf_mag_headline_image_custom_height, $suf_mag_headline_image_custom_width, $suf_mag_headline_zc;
		global $suf_mag_excerpt_image_size, $suf_mag_excerpt_image_custom_height, $suf_mag_excerpt_image_custom_width, $suf_mag_excerpt_zc;
		if ($suf_excerpt_thumbnail_size == "custom") {
			$width = suffusion_admin_get_size_from_field($suf_excerpt_thumbnail_custom_width, '200px');
			$width = (int)(substr($width, 0, strlen($width) - 2));
			$height = suffusion_admin_get_size_from_field($suf_excerpt_thumbnail_custom_height, '200px');
			$height = (int)(substr($height, 0, strlen($height) - 2));
			$zc = $suf_excerpt_tt_zc == "0" ? false: true;
			add_image_size('excerpt-thumbnail', $width, $height, $zc);
		}
		if ($suf_featured_image_size == "custom") {
			$width = suffusion_admin_get_size_from_field($suf_featured_image_custom_width, '200px');
			$width = (int)(substr($width, 0, strlen($width) - 2));
			$height = suffusion_admin_get_size_from_field($suf_featured_image_custom_height, '200px');
			$height = (int)(substr($height, 0, strlen($height) - 2));
			$zc = $suf_featured_zc == "default" ? $suf_excerpt_tt_zc : $suf_featured_zc;
			$zc = $zc == "0" ? false: true;
			add_image_size('featured', $width, $height, $zc);
		}
		if ($suf_mag_headline_image_size == "custom") {
			$width = suffusion_admin_get_size_from_field($suf_mag_headline_image_custom_width, '200px');
			$width = (int)(substr($width, 0, strlen($width) - 2));
			$height = suffusion_admin_get_size_from_field($suf_mag_headline_image_custom_height, '200px');
			$height = (int)(substr($height, 0, strlen($height) - 2));
			$zc = $suf_mag_headline_zc == "default" ? $suf_excerpt_tt_zc : $suf_mag_headline_zc;
			$zc = $zc == "0" ? false: true;
			add_image_size('mag-headline', $width, $height, $zc);
		}
		if ($suf_mag_excerpt_image_size == "custom") {
			$width = suffusion_admin_get_size_from_field($suf_mag_excerpt_image_custom_width, '200px');
			$width = (int)(substr($width, 0, strlen($width) - 2));
			$height = suffusion_admin_get_size_from_field($suf_mag_excerpt_image_custom_height, '200px');
			$height = (int)(substr($height, 0, strlen($height) - 2));
			$zc = $suf_mag_excerpt_zc == "default" ? $suf_excerpt_tt_zc : $suf_mag_excerpt_zc;
			$zc = $zc == "0" ? false: true;
			add_image_size('mag-excerpt', $width, $height, $zc);
		}
	}
}
?>