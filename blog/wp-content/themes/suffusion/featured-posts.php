<?php
/**
 * Creates a "Featured Posts" section for your blog.
 * Depending on the criteria you set, your featured posts can be picked
 * from the "Sticky Posts", or based on a category that you define
 *
 * @package Suffusion
 * @subpackage Template
 */

global $suffusion_unified_options, $suffusion_duplicate_posts;
foreach ($suffusion_unified_options as $id => $value) {
	$$id = $value;
}

function suffusion_display_featured_pager() {
	global $suf_featured_pager, $suf_featured_controller;
	$ret = "";
	if ($suf_featured_pager == 'show' || $suf_featured_controller == 'show') {
		$ret .= "<div id='sliderIndex' class='fix'>";
		if ($suf_featured_pager == 'show') {
			$ret .= "<div id=\"sliderPager\">";
			$ret .= "</div>";
		}
		if ($suf_featured_controller == 'show') {
			$ret .= "<div id=\"sliderControl\">";
			$ret .= "\t<a class='sliderPrev' href='#'>&laquo; ". __('Previous Post', 'suffusion')."</a>";
			$ret .= "\t<a class='sliderPause' href='#'>". __('Pause', 'suffusion')."</a>";
			$ret .= "\t<a class='sliderNext' href='#'>". __('Next Post', 'suffusion'). " &raquo;</a>";
			$ret .= "</div>";
		}
		$ret .= "</div>";
	}
	return $ret;
}

function suffusion_display_single_featured_post($position, $excerpt_position) {
	global $suf_featured_excerpt_type, $post;
	$ret = "<li class=\"sliderImage sliderimage-$position\">";
	$ret .= suffusion_get_image(array('featured' => true, 'excerpt_position' => $excerpt_position, 'default' => true));
	if ($suf_featured_excerpt_type != 'none') {
		$ret .= "<div class=\"$excerpt_position\">";
		$ret .= "<p><ins>";
		$ret .= "<a href=\"".get_permalink($post->ID)."\"  style='font-weight: bold;'>";
		if ($suf_featured_excerpt_type != 'excerpt') {
			$ret .= get_the_title($post->ID);
		}
		$ret .= "</a>";
		$ret .= "</ins></p>";
		if ($suf_featured_excerpt_type != 'title') {
			$excerpt = get_the_excerpt();
			$ret .= apply_filters('the_excerpt', $excerpt);
		}
		$ret .= "</div>";
	}
	$ret .= "</li>";
	return $ret;
}

function suffusion_display_featured_posts($echo = true) {
	global $suf_featured_allow_sticky, $suf_featured_show_latest, $suf_featured_num_posts, $suf_featured_num_latest_posts, $suf_featured_excerpt_position, $feautred_excerpt_position, $featured_post_counter, $excerpt_position, $suffusion_duplicate_posts, $suf_featured_show_dupes, $suf_featured_selected_posts, $suf_featured_selected_tags;
	global $rotation, $alttb, $altlr;
	$ret = "";

    $stickies = get_option('sticky_posts');
    $featured_categories = suffusion_get_allowed_categories('suf_featured_selected_categories');
    $featured_pages = suffusion_get_allowed_pages('suf_featured_selected_pages');
    if (is_array($stickies) && count($stickies) > 0 && $suf_featured_allow_sticky == "show") {
        $sticky_query = new WP_query(array('post__in' => $stickies));
    }
    if ($suf_featured_show_latest == 'show') {
        if (!$suf_featured_num_latest_posts) {
            $number_of_latest_posts = 5;
        }
        else {
            $number_of_latest_posts = $suf_featured_num_latest_posts;
        }
        $latest_query = new WP_query(array('post__not_in' => $stickies, 'posts_per_page' => $number_of_latest_posts, 'order' => 'DESC', 'caller_get_posts' => 1));
    }
    if (is_array($featured_categories) && count($featured_categories) > 0) {
        $query_cats = array();
        foreach ($featured_categories as $featured_category) {
            $query_cats[count($query_cats)] = $featured_category->cat_ID;
        }
        $query_posts = implode(",", array_values($query_cats));
        $cat_query = new WP_query(array('cat' => $query_posts, 'post__not_in' => $stickies, 'posts_per_page' => $suf_featured_num_posts));
    }
    if (is_array($featured_pages) && count($featured_pages) > 0) {
        $query_pages = array();
        foreach ($featured_pages as $featured_page) {
            $query_pages[count($query_pages)] = $featured_page->ID;
        }
        $page_query = new WP_query(array('post_type' => 'page', 'post__in' => $query_pages, 'posts_per_page' => $suf_featured_num_posts, 'caller_get_posts' => 1, 'orderby' => 'menu_order', 'order' => 'ASC'));
    }
	if (isset($suf_featured_selected_posts) && trim($suf_featured_selected_posts) != '') {
		$trim_featured_posts = str_replace(' ', '', $suf_featured_selected_posts);
		$query_posts = explode(',', $trim_featured_posts);
		$post_query = new WP_query(array('post_type' => 'post', 'post__in' => $query_posts, 'posts_per_page' => $suf_featured_num_posts, 'caller_get_posts' => 1));
	}
	if (isset($suf_featured_selected_tags) && trim($suf_featured_selected_tags) != '') {
		$featured_tags = explode(',', trim($suf_featured_selected_tags));
		$trim_featured_tags = array();
		foreach ($featured_tags as $tag) {
			$tag = str_replace('  ', ' ', $tag);
			$trim_featured_tags[] = str_replace(' ', '-', $tag);
		}
		$trim_featured_tags = implode(',',$trim_featured_tags);
		$tag_query = new WP_query(array('tag' => $trim_featured_tags, 'posts_per_page' => $suf_featured_num_posts));
	}

    $total_count = 0;
    if (isset($sticky_query->posts) && is_array($sticky_query->posts)) {
        $total_count += count($sticky_query->posts);
    }
    if (isset($latest_query->posts) && is_array($latest_query->posts)) {
        $total_count += count($latest_query->posts);
    }
    if (isset($cat_query->posts) && is_array($cat_query->posts)) {
        $total_count += count($cat_query->posts);
    }
    if (isset($page_query->posts) && is_array($page_query->posts)) {
        $total_count += count($page_query->posts);
    }
	if (isset($post_query) && isset($post_query->posts) && is_array($post_query->posts)) {
		$total_count += count($post_query->posts);
	}
	if (isset($tag_query) && isset($tag_query->posts) && is_array($tag_query->posts)) {
		$total_count += count($tag_query->posts);
	}
	if ($total_count > 0) {
        $alttb = array("top", "bottom");
        $altlr = array("left", "right");
        $rotation = array("top", "bottom", "left", "right");
        if (in_array($suf_featured_excerpt_position, $rotation)) {
            $excerpt_position = $suf_featured_excerpt_position;
        }
        $feautred_excerpt_position = 0;
        $featured_post_counter = 0;
        $ret .= "<div id=\"featured-posts\" class=\"fix\">";
        $ret .= "\t<div id=\"slider\" class=\"fix clear\">";
        $ret .= "\t\t<ul id=\"sliderContent\" class=\"fix clear\">";
        $do_not_duplicate = array();
        if (isset($sticky_query)) {
	        $ret .= suffusion_parse_featured_query_results($sticky_query, &$do_not_duplicate);
        }
        if (isset($latest_query)) {
            $ret .= suffusion_parse_featured_query_results($latest_query, &$do_not_duplicate);
        }
        if (isset($cat_query)) {
	        $ret .= suffusion_parse_featured_query_results($cat_query, &$do_not_duplicate);
        }
        if (isset($page_query)) {
	        $ret .= suffusion_parse_featured_query_results($page_query, &$do_not_duplicate);
        }
        if (isset($post_query)) {
	        $ret .= suffusion_parse_featured_query_results($post_query, &$do_not_duplicate);
        }
        if (isset($tag_query)) {
	        $ret .= suffusion_parse_featured_query_results($tag_query, &$do_not_duplicate);
        }
        $ret .= "\t\t</ul>";
        $ret .= "\t</div>";
        $ret .= suffusion_display_featured_pager($echo);
        $ret .= "</div>";
        if ($suf_featured_show_dupes == 'hide') {
	        $suffusion_duplicate_posts = $do_not_duplicate;
        }
	    else {
		    $suffusion_duplicate_posts = array();
	    }
    }

	if ($echo) {
		echo $ret;
	}
	return $ret;
}

function suffusion_parse_featured_query_results($query, $do_not_duplicate) {
	global $feautred_excerpt_position, $featured_post_counter, $suf_featured_num_posts, $suf_featured_excerpt_position, $excerpt_position, $rotation, $alttb, $altlr;
    global $post;
	$ret = "";
	if (isset($query->posts) && is_array($query->posts)) {
		while ($query->have_posts())  {
			if ($featured_post_counter >= $suf_featured_num_posts) {
				break;
			}
			$query->the_post();
            if (in_array($post->ID, $do_not_duplicate)) {
                continue;
            }
            else {
                $do_not_duplicate[count($do_not_duplicate)] = $post->ID;
            }
			if ($suf_featured_excerpt_position == "rotate") {
				$excerpt_position = $rotation[$feautred_excerpt_position%4];
			}
			else if ($suf_featured_excerpt_position == "alttb") {
				$excerpt_position = $alttb[$feautred_excerpt_position%2];
			}
			else if ($suf_featured_excerpt_position == "altlr") {
				$excerpt_position = $altlr[$feautred_excerpt_position%2];
			}
			$feautred_excerpt_position++;
			$ret .= suffusion_display_single_featured_post($feautred_excerpt_position, $excerpt_position);
			$featured_post_counter++;
		}
	}
	return $ret;
}
?>