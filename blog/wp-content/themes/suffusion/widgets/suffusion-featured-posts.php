<?php
/**
 * This creates a widget that displays featured posts. This is independent of the "Featured Content" section of the options because you can
 * place this in any widget area. This behaves similar to the featured-posts.php file, but since this is a widget it can be placed in any widget area.
 *
 * @package Suffusion
 * @subpackage Widgets
 */

class Suffusion_Featured_Posts extends WP_Widget {
	function Suffusion_Featured_Posts() {
		$widget_ops = array('classname' => 'widget-suf-featured-posts',
			'description' => __("A widget for displaying featured posts.", "suffusion"));
		$control_ops = array('width' => 750);
		$this->WP_Widget("suf-featured-posts", __("Featured Content", "suffusion"), $widget_ops, $control_ops);
	}

	function form($instance) {
		$defaults = array("title" => "",
            'show_sticky' => true,
            'latest_posts' => false,
            "number_of_posts" => 20,
            "number_of_latest_posts" => 5,
			"icon_height" => "100px",
            "icon_width" => "100px",
            'transition_effect' => 'fade',
            'frame_time' => 4000,
            'frame_delay' => 1000,
            'featured_height' => '250px',
            'custom_image_size' => false,
            'custom_img_height' => '250px',
            'custom_img_width' => '250px',
            'text_display' => 'title-excerpt',
            'excerpt_position' => 'rotate',
            'text_width' => '200px',
            'text_bg_color' => '222222',
            'text_color' => 'FFFFFF',
            'link_color' => 'FFFFFF',
            'show_index' => true,
            'show_controls' => true,
            'prev_text' => __('Previous Post', 'suffusion'),
            'pause_text' => __('Pause', 'suffusion'),
            'next_text' => __('Next Post', 'suffusion'),
		);
		$instance = wp_parse_args((array)$instance, $defaults);
?>
<div style='display: inline-block; clear: both;'>
	<div style='float: left; width: 48%; margin-right: 10px;'>
<?php
		_e("<p>This widget displays a featured posts slider.</p>", "suffusion");
?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'suffusion'); ?></label>
			<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'number_of_posts' ); ?>"><?php _e('Maximum number of posts / pages to display:', 'suffusion'); ?></label>
			<select id="<?php echo $this->get_field_id( 'number_of_posts' ); ?>" name="<?php echo $this->get_field_name( 'number_of_posts' ); ?>">
<?php
		for ($i = 1; $i <= 20; $i++) {
?>
				<option <?php if ( $i == $instance['number_of_posts'] ) echo 'selected="selected"'; ?>><?php echo $i; ?></option>
<?php
		}
?>
			</select>
		</p>

		<p>
			<input id="<?php echo $this->get_field_id('show_sticky'); ?>" name="<?php echo $this->get_field_name('show_sticky'); ?>" type="checkbox" <?php checked($instance['show_sticky'], 'on'); ?>  class="checkbox" />
			<label for="<?php echo $this->get_field_id('show_sticky'); ?>"><?php _e('Include sticky posts', 'suffusion'); ?></label>
		</p>

		<p>
			<input id="<?php echo $this->get_field_id('latest_posts'); ?>" name="<?php echo $this->get_field_name('latest_posts'); ?>" type="checkbox" <?php checked($instance['latest_posts'], 'on'); ?>  class="checkbox" />
			<label for="<?php echo $this->get_field_id('latest_posts'); ?>"><?php _e('Include latest posts', 'suffusion'); ?></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('number_of_latest_posts'); ?>"><?php _e('Number of "Latest Posts":', 'suffusion'); ?></label>
			<select id="<?php echo $this->get_field_id('number_of_latest_posts'); ?>" name="<?php echo $this->get_field_name('number_of_latest_posts'); ?>">
<?php
		for ($i = 1; $i <= 20; $i++) {
?>
				<option <?php if ( $i == $instance['number_of_latest_posts'] ) echo 'selected="selected"'; ?>><?php echo $i; ?></option>
<?php
		}
?>
			</select>
			<i><?php _e("If you are including latest posts and you select 5 here, the latest 5 posts will be included in the featured content. If this number is higher than the maximum number of posts, this setting will be ignored", "suffusion"); ?></i>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('featured_categories'); ?>"><?php _e('Include categories:', 'suffusion'); ?></label>
			<input id="<?php echo $this->get_field_id('featured_categories'); ?>" name="<?php echo $this->get_field_name('featured_categories'); ?>" value="<?php if (isset($instance['featured_categories'])) echo $instance['featured_categories']; ?>" class="widefat" />
			<i><?php _e("Fill in a comma-separated list of category ids. E.g. 3,8,17", "suffusion"); ?></i>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('featured_posts'); ?>"><?php _e('Include posts:', 'suffusion'); ?></label>
			<input id="<?php echo $this->get_field_id('featured_posts'); ?>" name="<?php echo $this->get_field_name('featured_posts'); ?>" value="<?php if (isset($instance['featured_posts'])) echo $instance['featured_posts']; ?>" class="widefat" />
			<i><?php _e("Fill in a comma-separated list of post ids. E.g. 7,9,13", "suffusion"); ?></i>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('featured_pages'); ?>"><?php _e('Include pages:', 'suffusion'); ?></label>
			<input id="<?php echo $this->get_field_id('featured_pages'); ?>" name="<?php echo $this->get_field_name('featured_pages'); ?>" value="<?php if (isset($instance['featured_pages'])) echo $instance['featured_pages']; ?>" class="widefat" />
			<i><?php _e("Fill in a comma-separated list of page ids. E.g. 7,9,13", "suffusion"); ?></i>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('transition_effect'); ?>"><?php _e('Featured content transition effect:', 'suffusion'); ?></label>
			<select id="<?php echo $this->get_field_id('transition_effect'); ?>" name="<?php echo $this->get_field_name( 'transition_effect' ); ?>">
<?php
        $effects = array("fade" => __("Fade In", 'suffusion'), "scrollUp" => __("Scroll Up", 'suffusion'), "scrollDown" => __("Scroll Down", 'suffusion'),
            "scrollLeft" => __("Scroll Left", 'suffusion'), "scrollRight" => __("Scroll Right", 'suffusion'), "scrollHorz" => __("Scroll Horizontally", 'suffusion'),
            "scrollVert" => __("Scroll Vertically", 'suffusion'), "slideX" => __("Slide in and back horizontally", 'suffusion'),
            "slideY" => __("Slide in and back vertically", 'suffusion'), "turnUp" => __("Turn Upwards", 'suffusion'), "turnDown" => __("Turn Downwards", 'suffusion'),
            "turnLeft" => __("Turn Leftwards", 'suffusion'), "turnRight" => __("Turn Rightwards", 'suffusion'), "zoom" => __("Zoom", 'suffusion'),
            "fadeZoom" => __("Zoom and Fade", 'suffusion'), "blindX" => __("Vertical Blinds", 'suffusion'), "blindY" => __("Horizontal Blinds", 'suffusion'),
            "blindZ" => __("Diagonal Blinds", 'suffusion'), "growX" => __("Grow horizontally from center", 'suffusion'),
            "growY" => __("Grow vertically from center", 'suffusion'), "curtainX" => __("Squeeze in both edges horizontally", 'suffusion'),
            "curtainY" => __("Squeeze in both edges vertically", 'suffusion'), "cover" => __("Current post is covered by next post", 'suffusion'),
            "uncover" => __("Current post moves off next post", 'suffusion'), "wipe" => __("Wipe", 'suffusion'),
			);
        foreach ($effects as $option => $text) {
            ?>
                 <option value='<?php echo $option; ?>' <?php if ($option == $instance['transition_effect'] ) echo 'selected="selected"'; ?>><?php echo $text; ?></option>
            <?php
        }
?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('frame_time'); ?>"><?php _e('Time for each post display:', 'suffusion'); ?></label>
			<input id="<?php echo $this->get_field_id('frame_time'); ?>" name="<?php echo $this->get_field_name('frame_time'); ?>" value="<?php echo $instance['frame_time']; ?>" class="widefat" />
			<i><?php _e("Enter time in milliseconds. E.g. 4000", "suffusion"); ?></i>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('frame_delay'); ?>"><?php _e('Time for each post display:', 'suffusion'); ?></label>
			<input id="<?php echo $this->get_field_id('frame_delay'); ?>" name="<?php echo $this->get_field_name('frame_delay'); ?>" value="<?php echo $instance['frame_delay']; ?>" class="widefat" />
			<i><?php _e("Enter time in milliseconds. E.g. 1000", "suffusion"); ?></i>
		</p>

	</div>
	<div style='float: right; width: 48%;'>
		<p>
			<label for="<?php echo $this->get_field_id('featured_height'); ?>"><?php _e('Height of featured content section:', 'suffusion'); ?></label>
			<input id="<?php echo $this->get_field_id('featured_height'); ?>" name="<?php echo $this->get_field_name('featured_height'); ?>" value="<?php echo $instance['featured_height']; ?>" class="widefat" />
			<i><?php _e("E.g. 250px", "suffusion"); ?></i>
		</p>

		<p>
			<input id="<?php echo $this->get_field_id('custom_image_size'); ?>" name="<?php echo $this->get_field_name('custom_image_size'); ?>" type="checkbox" <?php checked($instance['custom_image_size'], 'on'); ?>  class="checkbox" />
			<label for="<?php echo $this->get_field_id('custom_image_size'); ?>"><?php _e('Enable custom image size for featured images', 'suffusion'); ?></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('custom_img_height'); ?>"><?php _e('Custom image height:', 'suffusion'); ?></label>
			<input id="<?php echo $this->get_field_id('custom_img_height'); ?>" name="<?php echo $this->get_field_name('custom_img_height'); ?>" value="<?php echo $instance['custom_img_height']; ?>" class="widefat" />
			<i><?php _e("This is applicable only if custom image size is enabled", "suffusion"); ?></i>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('custom_img_width'); ?>"><?php _e('Custom image width:', 'suffusion'); ?></label>
			<input id="<?php echo $this->get_field_id('custom_img_width'); ?>" name="<?php echo $this->get_field_name('custom_img_width'); ?>" value="<?php echo $instance['custom_img_width']; ?>" class="widefat" />
			<i><?php _e("This is applicable only if custom image size is enabled", "suffusion"); ?></i>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'text_display' ); ?>"><?php _e('Text display options:', 'suffusion'); ?></label>
			<select id="<?php echo $this->get_field_id( 'text_display' ); ?>" name="<?php echo $this->get_field_name( 'text_display' ); ?>">
<?php
        $text_options = array("title-excerpt" => __("Show post title and excerpt", 'suffusion'), "title" => __("Show post title only (no excerpt)", 'suffusion'),
				"excerpt" => __("Show excerpt only (no title)", 'suffusion'), "none" => __("Don't show any text", 'suffusion'));
        foreach ($text_options as $option => $text) {
            ?>
                 <option value='<?php echo $option; ?>' <?php if ($option == $instance['text_display'] ) echo 'selected="selected"'; ?>><?php echo $text; ?></option>
            <?php
        }
?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'excerpt_position' ); ?>"><?php _e('Position of the excerpt:', 'suffusion'); ?></label>
			<select id="<?php echo $this->get_field_id( 'excerpt_position' ); ?>" name="<?php echo $this->get_field_name( 'excerpt_position' ); ?>">
<?php
        $excerpt_positions = array("top" => __("Top", 'suffusion'), "bottom" => __("Bottom", 'suffusion'), "right" => __("Right", 'suffusion'), "left" => __("Left", 'suffusion'),
				"alttb" => __("Alternate excerpt between top and bottom", 'suffusion'), "altlr" => __("Alternate excerpt between left and right", 'suffusion'),
				"rotate" => __("Rotate between the four positions", 'suffusion'));
        foreach ($excerpt_positions as $position => $text) {
            ?>
                 <option value='<?php echo $position; ?>' <?php if ($position == $instance['excerpt_position'] ) echo 'selected="selected"'; ?>><?php echo $text; ?></option>
            <?php
        }
?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('text_width'); ?>"><?php _e('Width of excerpt section:', 'suffusion'); ?></label>
			<input id="<?php echo $this->get_field_id('text_width'); ?>" name="<?php echo $this->get_field_name('text_width'); ?>" value="<?php echo $instance['text_width']; ?>" class="widefat" />
			<i><?php _e("This is only applicable if your excerpt is at the right or at the left of your featured posts.", "suffusion"); ?></i>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('text_bg_color'); ?>"><?php _e('Excerpt background color:', 'suffusion'); ?></label>
			<input id="<?php echo $this->get_field_id('text_bg_color'); ?>" name="<?php echo $this->get_field_name('text_bg_color'); ?>" value="<?php echo $instance['text_bg_color']; ?>" class="color" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('link_color'); ?>"><?php _e('Excerpt title color:', 'suffusion'); ?></label>
			<input id="<?php echo $this->get_field_id('link_color'); ?>" name="<?php echo $this->get_field_name( 'link_color' ); ?>" value="<?php echo $instance['link_color']; ?>" class="color" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('text_color'); ?>"><?php _e('Excerpt font color:', 'suffusion'); ?></label>
			<input id="<?php echo $this->get_field_id('text_color'); ?>" name="<?php echo $this->get_field_name( 'text_color' ); ?>" value="<?php echo $instance['text_color']; ?>" class="color" />
		</p>

		<p>
			<input id="<?php echo $this->get_field_id('show_index'); ?>" name="<?php echo $this->get_field_name('show_index'); ?>" type="checkbox" <?php checked($instance['show_index'], 'on'); ?>  class="checkbox" />
			<label for="<?php echo $this->get_field_id('show_index'); ?>"><?php _e('Show a numbered post index', 'suffusion'); ?></label>
		</p>

		<p>
			<input id="<?php echo $this->get_field_id( 'show_controls' ); ?>" name="<?php echo $this->get_field_name( 'show_controls' ); ?>" type="checkbox" <?php checked( $instance['show_controls'], 'on' ); ?>  class="checkbox" />
			<label for="<?php echo $this->get_field_id( 'show_controls' ); ?>"><?php _e('Show Previous, Pause and Next buttons', 'suffusion'); ?></label>
		</p>

        <p>
			<i><?php _e("The following three settings only apply if you show the previous, pause and next buttons:", "suffusion"); ?></i>
        </p>

		<p>
			<label for="<?php echo $this->get_field_id('prev_text'); ?>"><?php _e('Text for "Previous Post":', 'suffusion'); ?></label>
			<input id="<?php echo $this->get_field_id('prev_text'); ?>" name="<?php echo $this->get_field_name('prev_text'); ?>" value="<?php echo $instance['prev_text']; ?>" class="widefat" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('pause_text'); ?>"><?php _e('Text for "Pause":', 'suffusion'); ?></label>
			<input id="<?php echo $this->get_field_id('pause_text'); ?>" name="<?php echo $this->get_field_name('pause_text'); ?>" value="<?php echo $instance['pause_text']; ?>" class="widefat" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('next_text'); ?>"><?php _e('Text for "Next Post":', 'suffusion'); ?></label>
			<input id="<?php echo $this->get_field_id('next_text'); ?>" name="<?php echo $this->get_field_name('next_text'); ?>" value="<?php echo $instance['next_text']; ?>" class="widefat" />
		</p>
	</div>
</div>
<?php
	}

    function widget( $args, $instance ) {
        extract($args);

        $title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title']);
        $number_of_posts = $instance['number_of_posts'];
        $show_sticky = $instance['show_sticky'];
        $featured_categories = $instance['featured_categories'];
        $latest_posts = $instance['latest_posts'];
        $number_of_latest_posts = $instance['number_of_latest_posts'];
        $featured_posts = $instance['featured_posts'];
        $featured_pages = $instance['featured_pages'];
        $transition_effect = $instance['transition_effect'];
        $frame_time = $instance['frame_time'];
        $frame_delay = $instance['frame_delay'];
        $featured_height = $instance['featured_height'];
        $custom_image_size = $instance['custom_image_size'];
        $custom_img_height = $instance['custom_img_height'];
        $custom_img_width = $instance['custom_img_width'];
        $text_display = $instance['text_display'];
        $excerpt_position = $instance['excerpt_position'];
        $text_width = $instance['text_width'];
        $text_bg_color = $instance['text_bg_color'];
        $text_color = $instance['text_color'];
        $link_color = $instance['link_color'];
        $show_index = $instance['show_index'];
        $show_controls = $instance['show_controls'];
        $prev_text = $instance['prev_text'];
        $pause_text = $instance['pause_text'];
        $next_text = $instance['next_text'];

        echo $before_widget;
        if ($title != '') {
            echo $before_title;
            echo $title;
            echo $after_title;
        }

        $widget_id = $args['widget_id'];
        $ret = $this->get_widget_featured_content($widget_id, $number_of_posts, $show_sticky, $latest_posts, $number_of_latest_posts,
            $featured_categories, $featured_posts, $featured_pages, $excerpt_position, $text_display, $text_width, $text_bg_color, $text_color, $link_color,
            $featured_height, $custom_image_size,$custom_img_height, $custom_img_width, $show_index, $show_controls, $prev_text, $pause_text, $next_text);
        echo $ret;

        echo $after_widget;
        ?>
    <script type="text/javascript">
        /* <![CDATA[ */
        $j = jQuery.noConflict();
        $j(document).ready(function() {
            $j('#<?php echo $widget_id; ?>-sliderContent').cycle({
                fx: '<?php echo $transition_effect; ?>',
                timeout: <?php echo $frame_time; ?>,
                speed: <?php echo $frame_delay; ?>,
                pause: 1,
                sync: 0,
                pager: '#<?php echo $widget_id; ?>-sliderPager',
                prev: 'a.sliderPrev',
                next: 'a.sliderNext'
            });

            $j('#<?php echo $widget_id; ?>-fc a.sliderPause').click(
                function() {
                    if ($j(this).text() == 'Pause') {
                        $j('#<?php echo $widget_id; ?>-sliderContent').cycle('pause');
                        $j('#<?php echo $widget_id; ?>-fc a.sliderPause').addClass('activeSlide');
                        $j(this).text('<?php echo "Resume";?>');
                    }
                    else {
                        $j('#<?php echo $widget_id; ?>-sliderContent').cycle('resume');
                        $j('#<?php echo $widget_id; ?>-fc a.sliderPause').removeClass('activeSlide');
                        $j(this).text('<?php echo "Pause";?>');
                    }
                    return false;
                }
            );
        });
        /* ]]> */
    </script>
        <?php
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = stripslashes(trim($new_instance['title']));
        $instance['number_of_posts'] = $new_instance['number_of_posts'];
        $instance['show_sticky'] = $new_instance['show_sticky'];
        $instance['featured_categories'] = strip_tags($new_instance['featured_categories']);
        $instance['latest_posts'] = $new_instance['latest_posts'];
        $instance['number_of_latest_posts'] = $new_instance['number_of_latest_posts'];
        $instance['featured_posts'] = strip_tags($new_instance['featured_posts']);
        $instance['featured_pages'] = strip_tags($new_instance['featured_pages']);
        $instance['transition_effect'] = $new_instance['transition_effect'];
        $instance['frame_time'] = $new_instance['frame_time'];
        $instance['frame_delay'] = $new_instance['frame_delay'];
        $instance['featured_height'] = $new_instance['featured_height'];
        $instance['custom_image_size'] = $new_instance['custom_image_size'];
        $instance['custom_img_height'] = $new_instance['custom_img_height'];
        $instance['custom_img_width'] = $new_instance['custom_img_width'];
        $instance['text_display'] = $new_instance['text_display'];
        $instance['excerpt_position'] = $new_instance['excerpt_position'];
        $instance['text_width'] = $new_instance['text_width'];
        $instance['text_bg_color'] = $new_instance['text_bg_color'];
        $instance['text_color'] = $new_instance['text_color'];
        $instance['link_color'] = $new_instance['link_color'];
        $instance['show_index'] = $new_instance['show_index'];
        $instance['show_controls'] = $new_instance['show_controls'];
        $instance['prev_text'] = $new_instance['prev_text'];
        $instance['pause_text'] = $new_instance['pause_text'];
        $instance['next_text'] = $new_instance['next_text'];

        return $instance;
    }

    function get_widget_featured_content($widget_id, $number_of_posts, $show_sticky, $latest_posts, $number_of_latest_posts,
            $featured_categories, $featured_posts, $featured_pages, $excerpt_position, $text_display, $text_width, $text_bg_color, $text_color, $link_color,
            $featured_height, $custom_image_size,$custom_img_height, $custom_img_width, $show_index, $show_controls, $prev_text, $pause_text, $next_text) {
        $featured_categories = str_replace(" ", "", $featured_categories);
        $featured_posts = str_replace(" ", "", $featured_posts);
        $featured_pages = str_replace(" ", "", $featured_pages);

        $stickies = get_option('sticky_posts');
        if (is_array($stickies) && count($stickies) > 0 && $show_sticky) {
            $sticky_query = new WP_query(array('post__in' => $stickies));
        }
        if ($latest_posts) {
            $latest_query = new WP_query(array('post__not_in' => $stickies, 'posts_per_page' => $number_of_latest_posts, 'order' => 'DESC', 'caller_get_posts' => 1));
        }
        if ($featured_categories && trim($featured_categories) != '') {
            $cat_query = new WP_query(array('cat' => $featured_categories, 'post__not_in' => $stickies, 'posts_per_page' => $number_of_posts));
        }
        if ($featured_posts && trim($featured_posts) != '') {
            $query_posts = explode(',', $featured_posts);
            if ($query_posts) {
                $post_query = new WP_query(array('post_type' => 'post', 'post__in' => $query_posts, 'posts_per_page' => $number_of_posts, 'caller_get_posts' => 1));
            }
        }
        if ($featured_pages && trim($featured_pages) != '') {
            $query_pages = explode(',', $featured_pages);
            if ($query_pages) {
                $page_query = new WP_query(array('post_type' => 'page', 'post__in' => $query_pages, 'posts_per_page' => $number_of_posts, 'caller_get_posts' => 1));
            }
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
        if (isset($post_query->posts) && is_array($post_query->posts)) {
            $total_count += count($post_query->posts);
        }
        if (isset($page_query->posts) && is_array($page_query->posts)) {
            $total_count += count($page_query->posts);
        }

        $ret = "";
        if ($total_count > 0) {
            $alttb = array("top", "bottom");
            $altlr = array("left", "right");
            $rotation = array("top", "bottom", "left", "right");
            if (in_array($excerpt_position, $rotation)) {
                $position = $excerpt_position;
            }
            $featured_excerpt_position = 0;
            $featured_post_counter = 0;
            $ret .= "<div id=\"$widget_id-fc\" class=\"featured-content fix\">";
            $ret .= "\t<div id=\"$widget_id-slider\" class=\"slider fix clear\" style='height: $featured_height;'>";
            $ret .= "\t\t<ul id=\"$widget_id-sliderContent\" class=\"sliderContent fix clear\" style='height: $featured_height;'>";
            $do_not_duplicate = array();
            if (isset($sticky_query)) {
	            $ret .= $this->widget_parse_featured_query_results($sticky_query, $do_not_duplicate, $featured_excerpt_position, $featured_post_counter,
	                $number_of_posts, $excerpt_position, $position, $rotation, $alttb, $altlr, $text_display, $text_width, $text_bg_color, $text_color, $link_color,
	                $featured_height, $custom_image_size,$custom_img_height, $custom_img_width);
            }
            if (isset($latest_query)) {
	            $ret .= $this->widget_parse_featured_query_results($latest_query, $do_not_duplicate, $featured_excerpt_position, $featured_post_counter,
	                $number_of_posts, $excerpt_position, $position, $rotation, $alttb, $altlr, $text_display, $text_width, $text_bg_color, $text_color, $link_color,
	                $featured_height, $custom_image_size,$custom_img_height, $custom_img_width);
            }
            if (isset($cat_query)) {
	            $ret .= $this->widget_parse_featured_query_results($cat_query, $do_not_duplicate, $featured_excerpt_position, $featured_post_counter,
	                $number_of_posts, $excerpt_position, $position, $rotation, $alttb, $altlr, $text_display, $text_width, $text_bg_color, $text_color, $link_color,
	                $featured_height, $custom_image_size,$custom_img_height, $custom_img_width);
            }
            if (isset($post_query)) {
	            $ret .= $this->widget_parse_featured_query_results($post_query, $do_not_duplicate, $featured_excerpt_position, $featured_post_counter,
	                $number_of_posts, $excerpt_position, $position, $rotation, $alttb, $altlr, $text_display, $text_width, $text_bg_color, $text_color, $link_color,
	                $featured_height, $custom_image_size,$custom_img_height, $custom_img_width);
            }
            if (isset($page_query)) {
	            $ret .= $this->widget_parse_featured_query_results($page_query, $do_not_duplicate, $featured_excerpt_position, $featured_post_counter,
	                $number_of_posts, $excerpt_position, $position, $rotation, $alttb, $altlr, $text_display, $text_width, $text_bg_color, $text_color, $link_color,
	                $featured_height, $custom_image_size,$custom_img_height, $custom_img_width);
            }
            $ret .= "\t\t</ul>";
            $ret .= "\t</div>";
            $ret .= $this->widget_display_featured_pager($widget_id, $show_index, $show_controls, $prev_text, $pause_text, $next_text);
            $ret .= "</div>";
        }
        return $ret;
    }

    function widget_parse_featured_query_results($query, &$do_not_duplicate, &$featured_excerpt_position, &$featured_post_counter,
            $number_of_posts, $excerpt_position, &$position, $rotation, $alttb, $altlr, $text_display, $text_width, $text_bg_color, $text_color, $link_color,
            $featured_height, $custom_image_size,$custom_img_height, $custom_img_width) {
        global $post;
        $ret = "";
        if (isset($query->posts) && is_array($query->posts)) {
            while ($query->have_posts())  {
                if ($featured_post_counter >= $number_of_posts) {
                    break;
                }
                $query->the_post();
                if (in_array($post->ID, $do_not_duplicate)) {
                    continue;
                }
                else {
                    $do_not_duplicate[count($do_not_duplicate)] = $post->ID;
                }
                if ($excerpt_position == "rotate") {
                    $position = $rotation[$featured_excerpt_position%4];
                }
                else if ($excerpt_position == "alttb") {
                    $position = $alttb[$featured_excerpt_position%2];
                }
                else if ($excerpt_position == "altlr") {
                    $position = $altlr[$featured_excerpt_position%2];
                }
                $featured_excerpt_position++;
                $ret .= $this->widget_display_single_featured_post($featured_excerpt_position, $position, $text_display, $text_width, $text_bg_color,
                    $text_color, $link_color, $featured_height, $custom_image_size, $custom_img_height, $custom_img_width);
                $featured_post_counter++;
            }
        }
        return $ret;
    }

    function widget_display_single_featured_post($featured_excerpt_position, $position, $text_display, $text_width,
        $text_bg_color, $text_color, $link_color, $featured_height, $custom_image_size, $custom_img_height, $custom_img_width) {
        global $post;
        $ret = "<li class=\"sliderImage sliderimage-$position\" style='height: $featured_height; '>";
        $ret .= suffusion_get_image(array('featured-widget' => true, 'excerpt_position' => $position, 'default' => true,
            'featured-image-custom-size' => $custom_image_size, 'featured-height' => $custom_img_height, 'featured-width' => $custom_img_width));
        if ($text_display != 'none') {
            $style = " background-color: #$text_bg_color; color: #$text_color; ";
            if ($position == 'left' || $position == 'right') {
                $style .= " width: $text_width; ";
            }
            $style = " style='".$style."' ";
            $ret .= "<div class=\"$position\" $style>";
            $ret .= "<p><ins>";
            $ret .= "<a href=\"".get_permalink($post->ID)."\" style='color: #$link_color; font-weight: bold;'>";
            if ($text_display != 'excerpt') {
                $ret .= get_the_title($post->ID);
            }
            $ret .= "</a>";
			$ret .= "</ins></p>";
            if ($text_display != 'title') {
                $excerpt = get_the_excerpt();
                $ret .= apply_filters('the_excerpt', $excerpt);
            }
            $ret .= "</div>";
        }
        $ret .= "</li>";
        return $ret;
    }

    function widget_display_featured_pager($widget_id, $show_index, $show_controls, $prev_text, $pause_text, $next_text) {
        $ret = "";
        if ($show_index || $show_controls == 'show') {
            $ret .= "<div id='$widget_id-sliderIndex' class='sliderIndex fix'>";
            if ($show_index) {
                $ret .= "<div id=\"$widget_id-sliderPager\" class=\"sliderPager\">";
                $ret .= "</div>";
            }
            if ($show_controls) {
                $ret .= "<div id=\"$widget_id-sliderControl\" class=\"sliderControl\">";
                $ret .= "\t<a class='sliderPrev' href='#'>&laquo; ". $prev_text ."</a>";
                $ret .= "\t<a class='sliderPause' href='#'>". $pause_text ."</a>";
                $ret .= "\t<a class='sliderNext' href='#'>". $next_text . " &raquo;</a>";
                $ret .= "</div>";
            }
            $ret .= "</div>";
        }
        return $ret;
    }
}
?>