<?php
/**
 * Template Name: Magazine
 *
 * Creates a page with a magazine-style layout. If you have a magazine-themed
 * blog you should can use this to define your front page.
 *
 * @package Suffusion
 * @subpackage Templates
 */

get_header();

global $suffusion_unified_options, $post;
foreach ($suffusion_unified_options as $id => $value) {
	$$id = $value;
}

function suffusion_get_headlines() {
	global $post, $wpdb, $suf_mag_headline_limit;
	$headlines = array();
	$solos = array();
	$suf_mag_headline_limit = (int)$suf_mag_headline_limit;
	$quota_full = false;

	// Previously the script was loading all posts into memory using get_posts and checking the meta field. This causes the code to crash if the # posts is high.
	$querystr = "SELECT wposts.*
		FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta
		WHERE wposts.ID = wpostmeta.post_id
	    AND wpostmeta.meta_key = 'suf_magazine_headline'
	    AND wpostmeta.meta_value = 'on'
	    AND wposts.post_status = 'publish'
	    AND wposts.post_type = 'post'
	    ORDER BY wposts.post_date DESC
	 ";

	$head_posts = $wpdb->get_results($querystr, OBJECT);
	foreach ($head_posts as $post) {
		setup_postdata($post);
		$headlines[] = $post;
		$solos[] = $post->ID;
		if (count($headlines) == $suf_mag_headline_limit) {
			$quota_full = true;
			break;
		}
	}

	if ($quota_full) {
		return $headlines;
	}

	$headline_categories = suffusion_get_allowed_categories('suf_mag_headline_categories');
	if (is_array($headline_categories) && count($headline_categories) > 0) {
		$query_cats = array();
		foreach ($headline_categories as $headline_category) {
			$query_cats[] = $headline_category->cat_ID;
		}
		$query_posts = implode(",", array_values($query_cats));
		$cat_query = new WP_query(array('cat' => $query_posts, 'post__not_in' => $solos));
	}

	if (isset($cat_query->posts) && is_array($cat_query->posts)) {
		while ($cat_query->have_posts()) {
			$cat_query->the_post();
			$headlines[] = $post;
			if (count($headlines) == $suf_mag_headline_limit) {
				$quota_full = true;
				break;
			}
		}
	}
	return $headlines;
}

function suffusion_get_mag_section_queries($args = array()) {
	global $post, $wpdb, $suf_mag_total_excerpts;
	$meta_check_field = $args['meta_check_field'];
	$solos = array();
	$queries = array();

	if ($meta_check_field) {
		// Previously the script was loading all posts into memory using get_posts and checking the meta field. This causes the code to crash if the # posts is high.
		$querystr = "SELECT wposts.*
			FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta
			WHERE wposts.ID = wpostmeta.post_id
		    AND wpostmeta.meta_key = '$meta_check_field'
		    AND wpostmeta.meta_value = 'on'
		    AND wposts.post_status = 'publish'
		    AND wposts.post_type = 'post'
		    ORDER BY wposts.post_date DESC
		 ";

		$post_results = $wpdb->get_results($querystr, OBJECT);
		foreach ($post_results as $post) {
			setup_postdata($post);
			$solos[] = $post->ID;
		}
	}
	if (count($solos) > 0) {
		$solo_query = new WP_query(array('post__in' => $solos, 'caller_get_posts' => 1));
		$queries[] = $solo_query;
	}

	$category_prefix = $args['category_prefix'];
	if ($category_prefix) {
		$categories = suffusion_get_allowed_categories($category_prefix);
		if (is_array($categories) && count($categories) > 0) {
			$query_cats = array();
			foreach ($categories as $category) {
				$query_cats[] = $category->cat_ID;
			}
			$query_posts = implode(",", array_values($query_cats));
			$cat_query = new WP_query(array('cat' => $query_posts, 'post__not_in' => $solos, 'posts_per_page' => (int)$suf_mag_total_excerpts));
			$queries[] = $cat_query;
		}
	}
	return $queries;
}

function suffusion_show_mag_excerpts_table($queries, $total) {
	global $suf_mag_excerpts_per_row, $suf_mag_excerpts_title;
	$ret = "";
	$ret .= "<table class='suf-mag-excerpts'>\n";
	for ($i = 0; $i < (int)$suf_mag_excerpts_per_row - 1; $i++) {
		$ret .= "\t<col class='suf-mag-excerpt'/>\n";
	}
	$ret .= "\t<col/>\n";
	if (trim($suf_mag_excerpts_title) != '') {
		$ret .= "\t<tr>\n";
		$ret .= "\t\t<th colspan='$suf_mag_excerpts_per_row'>".stripslashes($suf_mag_excerpts_title)."</th>\n";
		$ret .= "\t</tr>\n";
	}
	$ret .= suffusion_show_mag_excerpts($queries, $total);
	$ret .= "</table>\n";
	return $ret;
}

function suffusion_show_mag_excerpts($queries, $total) {
	global $suf_mag_excerpts_per_row, $suf_mag_total_excerpts;
	$ctr = 0;
	$ret = "";
	foreach ($queries as $query) {
		if (isset($query->posts) && is_array($query->posts)) {
			while ($query->have_posts()) {
                if ($ctr >= $suf_mag_total_excerpts) {
                    break;
                }
				$query->the_post();
				if ($ctr%$suf_mag_excerpts_per_row == 0) {
					$ret .= "<tr>\n";
				}
				$ret .= suffusion_show_mag_single_excerpt();
				if ($ctr == $total - 1 || $ctr%$suf_mag_excerpts_per_row == $suf_mag_excerpts_per_row - 1) {
					$ret .= "</tr>\n";
				}
				$ctr++;
			}
		}
	}
	return $ret;
}

function suffusion_show_mag_single_excerpt() {
	global $post, $suf_mag_excerpt_full_story_text, $suf_mag_excerpts_images_enabled;
	$ret = "";
	$ret .= "<td>\n";
	$ret .= "\t<div class='suf-mag-excerpt entry-content'>\n";

	$image_link = suffusion_get_image(array('mag-excerpt' => true));
	if (($suf_mag_excerpts_images_enabled == 'show') || ($suf_mag_excerpts_images_enabled == 'hide-empty' && $image_link != '')) {
		$ret .= "\t\t<div class='suf-mag-excerpt-image'>".$image_link."</div>\n";
	}
	$ret .= "\t\t<h2  class='suf-mag-excerpt-title'><a class='entry-title' rel='bookmark' href='".get_permalink($post->ID)."'>".get_the_title($post->ID)."</a></h2>\n";
	$ret .= "\t\t<div class='suf-mag-excerpt-text entry-content'>\n";
	$excerpt = get_the_excerpt();
	$ret .= apply_filters('the_excerpt', $excerpt);
	$ret .= "\t\t</div>\n";
	if (trim($suf_mag_excerpt_full_story_text)) {
		$ret .= "\t\t<a href='".get_permalink($post->ID)."' class='suf-mag-excerpt-full-story'>$suf_mag_excerpt_full_story_text</a>";
	}

	$ret .= "\t</div>\n";
	$ret .= "</td>\n";
	return $ret;
}

function suffusion_show_mag_catblocks_table($categories, $total) {
	global $suf_mag_catblocks_per_row, $suf_mag_catblocks_title;
	$ret = "";
	$ret .= "<table class='suf-mag-categories'>\n";
	for ($i = 0; $i < (int)$suf_mag_catblocks_per_row - 1; $i++) {
		$ret .= "\t<col class='suf-mag-category'/>\n";
	}
	$ret .= "\t<col/>\n";
	if (trim($suf_mag_catblocks_title) != '') {
		$ret .= "\t<tr>\n";
		$ret .= "\t\t<th colspan='$suf_mag_catblocks_per_row'>".stripslashes($suf_mag_catblocks_title)."</th>\n";
		$ret .= "\t</tr>\n";
	}
	$ret .= suffusion_show_mag_catblocks($categories, $total);
	$ret .= "</table>\n";
	return $ret;
}

function suffusion_show_mag_catblocks($categories, $total) {
	global $suf_mag_catblocks_per_row, $category;
	$ctr = 0;
	$ret = "";
	if (is_array($categories)) {
		foreach ($categories as $category) {
			if ($ctr%$suf_mag_catblocks_per_row == 0) {
				$ret .= "<tr>\n";
			}

			$ret .= suffusion_show_mag_single_catblock();
			if ($ctr == $total - 1 || $ctr%$suf_mag_catblocks_per_row == $suf_mag_catblocks_per_row - 1) {
				$ret .= "</tr>\n";
			}
			$ctr++;
		}
	}
	return $ret;
}

function suffusion_show_mag_single_catblock() {
	global $category, $suf_mag_catblocks_images_enabled, $suf_mag_catblocks_desc_enabled, $suf_mag_catblocks_posts_enabled, $suf_mag_catblocks_num_posts;
	global $suf_mag_catblocks_see_all_text, $suf_mag_catblocks_post_style;
	$ret = "";
	$ret .= "<td>\n";
	$ret .= "\t<h2 class='suf-mag-category-title'>".$category->cat_name;
	//$rss_url = get_bloginfo('rss2_url');
	//$cat_rss = add_query_arg('cat', $category->cat_ID, $rss_url);
	//$ret .= " <a href='".$cat_rss."'>Subscribe</a>";
	$ret .= "</h2>";

	$ret .= "\t<div class='suf-mag-category'>\n";
	if ($suf_mag_catblocks_images_enabled != 'hide') {
		if (function_exists('get_cat_icon')) {
			$cat_icon = get_cat_icon('echo=false&cat='.$category->cat_ID);
			if (($suf_mag_catblocks_images_enabled == 'hide-empty' && trim($cat_icon) != '') || $suf_mag_catblocks_images_enabled == 'show') {
				$ret .= "\t\t<div class='suf-mag-category-image'>";
				$ret .= $cat_icon;
				$ret .= "</div>\n";
			}
		}
	}
	if ($suf_mag_catblocks_desc_enabled == 'show') {
		$ret .= $category->category_description;
	}
	if ($suf_mag_catblocks_posts_enabled == 'show') {
		$cat_args = array('cat' => $category->cat_ID, 'posts_per_page' => $suf_mag_catblocks_num_posts);
		if (function_exists('mycategoryorder')) {
		    $cat_args['orderby'] = 'order';
		}

		$query = new WP_query($cat_args);
		if (isset($query->posts) && is_array($query->posts) && count($query->posts) > 0) {
            if ($suf_mag_catblocks_post_style == 'magazine') {
                $ul_class = " class='suf-mag-catblock-posts' ";
                $li_class = " class='suf-mag-catblock-post' ";
            }
			$ret .= "<ul $ul_class>\n";
			while ($query->have_posts())  {
				$query->the_post();
				$ret .= "<li $li_class><a href='".get_permalink()."' class='suf-mag-catblock-post'>".get_the_title()."</a></li>\n";
			}
			$ret .= "</ul>";
		}
	}
	if (trim($suf_mag_catblocks_see_all_text)) {
		$ret .= "\t<div class='suf-mag-category-footer'>\n";
		$ret .= "\t\t<a href='".get_category_link($category->cat_ID)."' class='suf-mag-category-all-posts'>$suf_mag_catblocks_see_all_text</a>";
		$ret .= "\t</div>\n";
	}

	$ret .= "\t</div>\n";
	$ret .= "</td>\n";
	return $ret;
}

?>

    <div id="main-col">
<?php suffusion_before_begin_content(); ?>
      <div id="content" class="hfeed">
	<?php suffusion_after_begin_content(); ?>
	<?php if (have_posts() && $suf_mag_content_enabled == "show") : ?>

		<?php while (have_posts()) : the_post(); ?>

        <div class="post fix" id="post-<?php the_ID(); ?>">
<?php suffusion_after_begin_post(); ?>

          <div class="entry fix">
			<?php suffusion_content(); ?>
          </div><!--entry -->
		<?php suffusion_before_end_post(); ?>
		</div><!--post -->
		<?php endwhile; ?>
		<?php suffusion_before_end_content(); ?>
	<?php endif; ?>

<?php
if (is_array($suf_mag_entity_order)) {
	$sequence = array();
	foreach ($suf_mag_entity_order as $key => $value) {
		$sequence[] = $value['key'];
	}
}
else {
	$sequence = explode(',', $suf_mag_entity_order);
}

foreach ($sequence as $entity) {
	if ($suf_mag_headlines_enabled == 'show' && $entity == 'headlines') {
		if (trim($suf_mag_headline_title)) {
	?>
		<h2 class='suf-mag-headlines-title fix'><?php echo stripslashes($suf_mag_headline_title); ?></h2>
	<?php
		}
	?>
	<div class='suf-mag-headlines fix'>
		<div class='suf-mag-headline-block'>
	<?php
		$headlines = suffusion_get_headlines();
		$headline_ctr = 0;
		if (count($headlines) > 0) {
	?>
			<ul class='mag-headlines'>
	<?php
			$headline_ctr = 0;
			foreach ($headlines as $post) {
				$headline_ctr++;
				if ($headline_ctr == 1) {
					$first_class = 'suf-mag-headline-first';
				}
				else {
					$first_class = '';
				}
	?>
				<li class='suf-mag-headline-<?php echo $post->ID?> suf-mag-headline <?php echo $first_class; ?>'>
					<a href="<?php echo get_permalink($post->ID); ?>" class='suf-mag-headline-<?php echo $post->ID?> suf-mag-headline'><?php echo get_the_title($post->ID); ?></a>
				</li>
	<?php
			}
	?>
			</ul>
	<?php
		}
	?>
		</div>
		<div class='suf-mag-headline-photo-box'>
	<?php
		$headline_ctr = 0;
		foreach ($headlines as $post) {
			$headline_ctr++;
			if ($headline_ctr == 1) {
				$first_class = 'suf-mag-headline-photo-first';
			}
			else {
				$first_class = '';
			}
	?>
			<div class='suf-mag-headline-photo-<?php echo $post->ID?> suf-mag-headline-photo <?php echo $first_class;?>'>
	<?php
			echo suffusion_get_image(array('mag-headline' => true));
	?>
			</div>
	<?php
		}
	?>
		</div>
	</div>
	<?php
	}
	else if ($suf_mag_excerpts_enabled == 'show' && $entity == 'excerpts') {
		$queries = suffusion_get_mag_section_queries(array('meta_check_field' => 'suf_magazine_excerpt', 'category_prefix' => 'suf_mag_excerpt_categories'));
		$total = 0;
		foreach ($queries as $query) {
			if (isset($query->posts) && is_array($query->posts)) {
				$total += count($query->posts);
			}
		}
		if ($total > 0) {
			echo suffusion_show_mag_excerpts_table($queries, $total);
		}
	}
	else if ($suf_mag_categories_enabled == 'show' && $entity == 'categories') {
		$categories = suffusion_get_allowed_categories('suf_mag_catblock_categories');
		if ($categories != null && is_array($categories) && count($categories) > 0) {
			$total = count($categories);
			echo suffusion_show_mag_catblocks_table($categories, $total);
		}
	}
}
?>
      </div><!-- content -->
    </div><!-- main col -->
	<?php get_footer(); ?>
