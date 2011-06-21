<?php
/**
 * This creates a widget that shows the posts of a particular category. You can use this if you are designing a site based on a magazine layout.
 *
 * @package Suffusion
 * @subpackage Widgets
 *
 */

class Suffusion_Category_Posts extends WP_Widget {
	function Suffusion_Category_Posts() {
		$widget_ops = array('classname' => 'widget-suf-cat-posts',
			'description' => __("A widget for displaying posts in a category. You can use this to selectively display posts.", "suffusion"));

		$control_ops = array('width' => 600);
		$this->WP_Widget("suf-cat-posts", __("Category Posts", "suffusion"), $widget_ops, $control_ops);
	}

	function widget( $args, $instance ) {
		extract($args);

		$selected_category = $instance["selected_category"];
		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title']);
		$icon_type = $instance["icon_type"];
		$cat_icon_url = $instance["cat_icon_url"];
		$post_style = $instance["post_style"];
		$number_of_posts = $instance["number_of_posts"];
		$icon_height = $instance['icon_height'];
        $icon_width = $instance['icon_width'];
        $all_posts_text = $instance['all_posts_text'];

		echo $before_widget;
		echo $before_title;
		echo $title;
		echo $after_title;

        $ret = "";
        if ($icon_type == 'plugin') {
            if (function_exists('get_cat_icon')) {
                $cat_icon = get_cat_icon('echo=false&cat='.$selected_category);
                if (trim($cat_icon) != '') {
                    $ret .= "\t\t<div class='suf-cat-posts-widget-image' style='height: $icon_height;'>";
                    $ret .= $cat_icon;
                    $ret .= "</div>\n";
                }
            }
        }
        else if ($icon_type == 'custom') {
            if (trim($cat_icon_url) != '') {
                $ret .= "\t\t<div class='suf-cat-posts-widget-image' style='height: $icon_height;'>";
                $ret .= "<a href='".get_category_link($selected_category)."'>";
                $ret .= "<img src='$cat_icon_url' alt='".esc_attr($title)."' title='".esc_attr($title)."' style='width: $icon_width; height: $icon_height;'/>";
                $ret .= "</a>\n";
                $ret .= "</div>\n";
            }
        }
		$query = new WP_query(array('cat' => $selected_category, 'posts_per_page' => $number_of_posts));
		if (isset($query->posts) && is_array($query->posts) && count($query->posts) > 0) {
            if ($post_style == 'magazine') {
                $ret .= "<ul class='suf-cat-posts-list'>\n";
            }
            else {
                $ret .= "<ul>\n";
            }
			while ($query->have_posts())  {
				$query->the_post();
                if ($post_style == 'magazine') {
                    $ret .= "<li class='suf-cat-post'><a href='".get_permalink()."' class='suf-cat-post'>".get_the_title()."</a></li>\n";
                }
                else {
                    $ret .= "<li><a href='".get_permalink()."'>".get_the_title()."</a></li>\n";
                }
			}
			$ret .= "</ul>";
		}

        if (trim($all_posts_text)) {
            $ret .= "\t<div class='suf-mag-category-footer'>\n";
            $ret .= "\t\t<a href='".get_category_link($selected_category)."' class='suf-mag-category-all-posts'>$all_posts_text</a>";
            $ret .= "\t</div>\n";
        }
        echo $ret;

		echo $after_widget;
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance["selected_category"] = $new_instance["selected_category"];
		$cat = get_category($instance['selected_category']);
		$title = stripslashes(trim($new_instance["title"]));
		$instance["title"] = strip_tags($title) == '' ? $cat->cat_name : $title;
		$instance["icon_type"] = $new_instance["icon_type"];
		$instance["post_style"] = $new_instance["post_style"];
		$instance["number_of_posts"] = $new_instance["number_of_posts"];
		$instance["icon_height"] = $new_instance["icon_height"] == '' ? '100px' : $new_instance["icon_height"];
        $instance["icon_width"] = $new_instance["icon_width"] == '' ? '100px' : $new_instance["icon_width"];
		$instance["cat_icon_url"] = strip_tags($new_instance["cat_icon_url"]);
        $instance["all_posts_text"] = stripslashes($new_instance["all_posts_text"]);

		return $instance;
	}

	function form($instance) {
		$defaults = array("title" => "",
			"icon_height" => "100px",
            "icon_width" => "100px",
			"number_of_posts" => 5,
		);
		$instance = wp_parse_args((array)$instance, $defaults);
?>
<div style='display: inline-block; clear: both;'>
	<div style='float: left; width: 48%; margin-right: 10px;'>
<?php
		_e("<p>This widget lets you display the latest posts from a category.</p>", "suffusion");
?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'suffusion'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
			<i><?php _e("This will default to the name of the selected category if left blank", "suffusion"); ?></i>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('selected_category'); ?>"><?php _e('Select category to show:', 'suffusion'); ?></label>
<?php
		$cat_args = array('hierarchical' => 1,
			'name' => $this->get_field_name('selected_category'),
			'class' => 'widefat',
		);
		if (isset($instance['selected_category'])) $cat_args['selected'] = $instance['selected_category'];
		wp_dropdown_categories($cat_args);
?>
			<i><?php _e("Posts from the selected category will show in the widget", "suffusion"); ?></i>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'number_of_posts' ); ?>"><?php _e('Number of posts to display:', 'suffusion'); ?></label>
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
			<label for="<?php echo $this->get_field_id( 'post_style' ); ?>"><?php _e('Post display style:', 'suffusion'); ?></label>
			<select id="<?php echo $this->get_field_id( 'post_style' ); ?>" name="<?php echo $this->get_field_name( 'post_style' ); ?>" class='widefat'>
<?php
		$post_styles = array('sidebar' => __('Underline upon hover (sidebar-style)', 'suffusion'),
				'magazine' => __('Box upon hover (magazine-style)', 'suffusion'),
			);
		foreach ($post_styles as $type_id => $type_name) {
?>
				<option <?php if (isset($instance['post_style']) && $type_id == $instance['post_style']) echo 'selected="selected"'; ?> value='<?php echo $type_id;?>'><?php echo $type_name; ?></option>
<?php
		}
?>
			</select>
			<i><?php _e("You can choose to display an icon for the category based on the Category Icons plugin, or a custom image", "suffusion"); ?></i>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'all_posts_text' ); ?>"><?php _e('Text for "All Posts":', 'suffusion'); ?></label>
			<input id="<?php echo $this->get_field_id( 'all_posts_text' ); ?>" name="<?php echo $this->get_field_name('all_posts_text'); ?>" value="<?php if (isset($instance['all_posts_text'])) echo stripslashes($instance['all_posts_text']); ?>" class="widefat" />
			<i><?php _e("The text you enter here will be the displayed in the button for \"All Posts\". If you leave this field blank the button will not be shown.", "suffusion"); ?></i>
		</p>
	</div>
	<div style='float: right; width: 48%;'>
		<p>
			<label for="<?php echo $this->get_field_id( 'icon_type' ); ?>"><?php _e('Category Icon:', 'suffusion'); ?></label>
			<select id="<?php echo $this->get_field_id( 'icon_type' ); ?>" name="<?php echo $this->get_field_name( 'icon_type' ); ?>" class='widefat'>
<?php
		$icon_types = array('none' => __('No category icon', 'suffusion'),
				'plugin' => __('Use Category Icons Plugin', 'suffusion'),
				'custom' => __('Use custom image', 'suffusion'),
			);
		foreach ($icon_types as $type_id => $type_name) {
?>
				<option <?php if (isset($instance['icon_type']) && $type_id == $instance['icon_type']) echo 'selected="selected"'; ?> value='<?php echo $type_id;?>'><?php echo $type_name; ?></option>
<?php
		}
?>
			</select>
			<i><?php _e("You can choose to display an icon for the category based on the Category Icons plugin, or a custom image", "suffusion"); ?></i>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('cat_icon_url'); ?>"><?php _e('Category icon custom image link:', 'suffusion'); ?></label>
			<input id="<?php echo $this->get_field_id('cat_icon_url'); ?>" name="<?php echo $this->get_field_name('cat_icon_url'); ?>" value="<?php if (isset($instance['cat_icon_url'])) echo $instance['cat_icon_url']; ?>" class="widefat" />
			<i><?php _e("If you have chosen to use a custom image in the previous option, please enter the complete URL of the category icon here, including http://", "suffusion"); ?></i>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'icon_height' ); ?>"><?php _e('Set the height for the category icon:', 'suffusion'); ?></label>
			<input id="<?php echo $this->get_field_id( 'icon_height' ); ?>" name="<?php echo $this->get_field_name( 'icon_height' ); ?>"
				value="<?php echo $instance['icon_height']; ?>"/>
			<br />
			<i><?php _e("The image you have picked will be scaled to this height.", "suffusion"); ?></i>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('icon_width'); ?>"><?php _e('Set the width for the category icon:', 'suffusion'); ?></label>
			<input id="<?php echo $this->get_field_id( 'icon_width' ); ?>" name="<?php echo $this->get_field_name( 'icon_width' ); ?>"
				value="<?php echo $instance['icon_width']; ?>"/>
			<br />
			<i><?php _e("The image you have picked will be scaled to this width.", "suffusion"); ?></i>
		</p>
	</div>
</div>
<?php
	}
}
?>