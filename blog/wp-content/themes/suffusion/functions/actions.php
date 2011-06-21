<?php
/**
 * Contains a list of all custom action hooks and corresponding functions defined for Suffusion.
 * This file is included in functions.php
 *
 * @package Suffusion
 * @subpackage Functions
 */

// First we will get all options from the database, then we will individually invoke the options within each function as required.
global $suffusion_unified_options;
foreach ($suffusion_unified_options as $id => $value) {
	$$id = $value;
}

function suffusion_document_header() {
	do_action('suffusion_document_header');
}

function suffusion_before_page() {
	do_action('suffusion_before_page');
}

function suffusion_before_begin_wrapper() {
	do_action('suffusion_before_begin_wrapper');
}

function suffusion_after_begin_wrapper() {
	do_action('suffusion_after_begin_wrapper');
}

function suffusion_page_header() {
	do_action('suffusion_page_header');
}

function suffusion_after_begin_container() {
	do_action('suffusion_after_begin_container');
}

function suffusion_before_begin_content() {
	do_action('suffusion_before_begin_content');
}

function suffusion_after_begin_content() {
	do_action('suffusion_after_begin_content');
}

function suffusion_content() {
	do_action('suffusion_content');
}

function suffusion_after_begin_post() {
	do_action('suffusion_after_begin_post');
}

function suffusion_after_content() {
	do_action('suffusion_after_content');
}

function suffusion_before_end_post() {
	do_action('suffusion_before_end_post');
}

function suffusion_before_end_content() {
	do_action('suffusion_before_end_content');
}

function suffusion_before_end_container() {
	do_action('suffusion_before_end_container');
}

function suffusion_after_end_container() {
	do_action('suffusion_after_end_container');
}

function suffusion_page_footer() {
	do_action('suffusion_page_footer');
}

function suffusion_document_footer() {
	do_action('suffusion_document_footer');
}

function suffusion_page_navigation() {
	do_action('suffusion_page_navigation');
}

function suffusion_query_posts() {
	do_action('suffusion_query_posts');
}

function suffusion_before_first_sidebar() {
	do_action('suffusion_before_first_sidebar');
}

function suffusion_between_first_sidebars() {
	do_action('suffusion_between_first_sidebars');
}

function suffusion_after_first_sidebar() {
	do_action('suffusion_after_first_sidebar');
}

function suffusion_before_second_sidebar() {
	do_action('suffusion_before_second_sidebar');
}

function suffusion_between_second_sidebars() {
	do_action('suffusion_between_second_sidebars');
}

function suffusion_after_second_sidebar() {
	do_action('suffusion_after_second_sidebar');
}

function suffusion_before_comment() {
	do_action('suffusion_before_comment');
}

function suffusion_after_comment() {
	do_action('suffusion_after_comment');
}

//
// This section defines the individual callback functions
//
function suffusion_include_dbx() {
	global $suf_sidebar_1_dnd, $suf_sidebar_2_dnd, $suf_sidebar_1_expcoll, $suf_sidebar_2_expcoll, $suf_sidebar_count, $suf_wa_sb2_style;
	if (suffusion_should_include_dbx()) {
		$expcoll_1 = $suf_sidebar_1_expcoll == "enabled" ? "yes" : "no";
		$expcoll_2 = $suf_sidebar_2_expcoll == "enabled" ? "yes" : "no";
?>
	<!-- Sidebar docking boxes (dbx) by Brothercake - http://www.brothercake.com/ -->
	<script type="text/javascript">
	/* <![CDATA[ */
	window.onload = function() {
		//initialise the docking boxes manager
		var manager = new dbxManager('main'); 	//session ID [/-_a-zA-Z0-9/]

<?php
		if ($suf_sidebar_1_dnd == "enabled") {?>
		//create new docking boxes group
		var sidebar = new dbxGroup(
			'sidebar', 		// container ID [/-_a-zA-Z0-9/]
			'vertical', 		// orientation ['vertical'|'horizontal']
			'7', 			// drag threshold ['n' pixels]
			'no',			// restrict drag movement to container axis ['yes'|'no']
			'10', 			// animate re-ordering [frames per transition, or '0' for no effect]
			'<?php echo $expcoll_1; ?>', 			// include open/close toggle buttons ['yes'|'no']
			'open', 		// default state ['open'|'closed']
			'open', 		// word for "open", as in "open this box"
			'close', 		// word for "close", as in "close this box"
			'click-down and drag to move this box', // sentence for "move this box" by mouse
			'click to %toggle% this box', // pattern-match sentence for "(open|close) this box" by mouse
			'use the arrow keys to move this box', // sentence for "move this box" by keyboard
			', or press the enter key to %toggle% it',  // pattern-match sentence-fragment for "(open|close) this box" by keyboard
			'%mytitle%  [%dbxtitle%]' // pattern-match syntax for title-attribute conflicts
		);
<?php
		}
		if (($suf_sidebar_count > 1 && $suf_sidebar_2_dnd == "enabled" && $suf_wa_sb2_style == "boxed" && !(is_page_template('1l-sidebar.php') || is_page_template('1r-sidebar.php'))) ||
				($suf_sidebar_2_dnd == "enabled" && $suf_wa_sb2_style == "boxed" && (is_page_template('1l1r-sidebar.php') || is_page_template('2l-sidebars.php') || is_page_template('2r-sidebars.php')))) {
?>
		var sidebar_2 = new dbxGroup(
			'sidebar-2', 		// container ID [/-_a-zA-Z0-9/]
			'vertical', 		// orientation ['vertical'|'horizontal']
			'7', 			// drag threshold ['n' pixels]
			'no',			// restrict drag movement to container axis ['yes'|'no']
			'10', 			// animate re-ordering [frames per transition, or '0' for no effect]
			'<?php echo $expcoll_2; ?>', 			// include open/close toggle buttons ['yes'|'no']
			'open', 		// default state ['open'|'closed']
			'open', 		// word for "open", as in "open this box"
			'close', 		// word for "close", as in "close this box"
			'click-down and drag to move this box', // sentence for "move this box" by mouse
			'click to %toggle% this box', // pattern-match sentence for "(open|close) this box" by mouse
			'use the arrow keys to move this box', // sentence for "move this box" by keyboard
			', or press the enter key to %toggle% it',  // pattern-match sentence-fragment for "(open|close) this box" by keyboard
			'%mytitle%  [%dbxtitle%]' // pattern-match syntax for title-attribute conflicts
		);
<?php
		}
		?>
	};
	/* ]]> */
	</script>

<?php
	}
}

function suffusion_include_ie_fixes() {?>
<!--[if lt IE 7]>
<script src="<?php echo get_template_directory_uri(); ?>/belatedpng.js"></script>
<script>
	//Drew Diller's Belated PNG: http://dillerdesign.wordpress.com/2009/07/02/belatedpng-img-nodes-javascript-event-handling/
  	DD_belatedPNG.fix('img, .suf-widget ul li, #sidebar ul li, #sidebar-2 ul li, .sidebar-tab-content ul li, li.suf-mag-catblock-post, input, .searchform .searchsubmit, #right-header-widgets .searchsubmit, #left-header-widgets .searchsubmit, #top-bar-left-widgets .searchsubmit,  #top-bar-right-widgets .searchsubmit, submit, .searchsubmit, .postdata .category, .postdata .comments, .postdata .edit, .previous-entries a, .next-entries a, .post-nav .next a, .post-nav .previous a, .post .date, h3#comments, h3.comments, #h3#respond, h3.respond, blockquote, blockquote div');
 </script>
<![endif]-->
<?php
}

function suffusion_include_custom_header_js() {
	global $suf_custom_header_js;
	if (isset($suf_custom_header_js) && trim($suf_custom_header_js) != "") {?>
<!-- Custom JavaScript for header defined in options -->
<script type="text/javascript">
/* <![CDATA[ */
<?php
		$strip = stripslashes($suf_custom_header_js);
		$strip = wp_specialchars_decode($strip, ENT_QUOTES);
		echo $strip."\n";
?>
/* ]]> */
</script>
<!-- /Custom JavaScript for header defined in options -->
<?php
	}
}

function suffusion_display_header() {
	global $suf_sub_header_vertical_alignment, $suf_header_fg_image_type, $suf_header_fg_image, $suf_header_alignment;
	if ($suf_header_alignment == 'right') {
		suffusion_display_widgets_in_header();
	}
	if (!is_singular()) {
		$header_tag = "h1";
	}
	else {
		$header_tag = "h2";
	}
?>
	<div id="header" class="fix">
	<?php
	$header = ($suf_header_fg_image_type == 'image' && trim($suf_header_fg_image) != '') ? "<img src='$suf_header_fg_image' alt='".esc_attr(get_bloginfo('name'))."'/>" : get_bloginfo('name', 'display');
	$home_link = home_url();
	if (function_exists('icl_get_home_url')) {
		$home_link = icl_get_home_url();
	}
	if ($suf_sub_header_vertical_alignment == "above") {
		?>
		<div class="description"><?php bloginfo('description');?></div>
		<<?php echo $header_tag?> class="blogtitle"><a href="<?php echo $home_link;?>"><?php echo $header;?></a></<?php echo $header_tag?>>
	<?php
	}
	else {
		?>
		<<?php echo $header_tag?> class="blogtitle"><a href="<?php echo $home_link;?>"><?php echo $header;?></a></<?php echo $header_tag?>>
		<div class="description"><?php bloginfo('description');?></div>
<?php
	}
	?>
    </div><!-- /header -->
<?php
	if ($suf_header_alignment != 'right') {
		suffusion_display_widgets_in_header();
	}
}

function suffusion_display_main_navigation() {
	global $suf_nav_contents, $suf_nav_item_type;
	$display = apply_filters('suffusion_can_display_main_navigation', true);
	if ($display) {
?>
 	<div id="nav" class="<?php echo $suf_nav_item_type; ?> fix">
		<div class='col-control'>
<?php
	suffusion_display_left_header_widgets();
	//Two options using native WP functionality:
	//1. wp_list_pages('title_li=&sort_column=menu_order&depth=3'); // This will need you to add the starting and ending <ul> tags
	//2. wp_page_menu('show_home=Home&menu_class=nav'); // This needs nothing and even creates the div. Works only for WP 2.7+
	//I am using a custom function here because I want to show the ">" for items with a dropdown. Also, page exclusions don't work as desired with standard functionality.
	if ($suf_nav_contents == "pages") {
		suffusion_create_navigation_html(true, $suf_nav_contents, 'main', 'suf_nav_pages', 'suf_nav_cats', 'suf_nav_links', 'suf_nav_menus');
	}
	suffusion_display_right_header_widgets();
?>
		</div><!-- /col-control -->
	</div><!-- /nav -->
<?php
	}
}

function suffusion_display_top_navigation() {
	global $suf_navt_contents, $suf_wa_tbrh_style, $suf_wa_tbrh_open_text, $suf_wa_tbrh_close_text, $suf_navt_item_type;
	if ($suf_navt_contents == "pages" || (has_nav_menu('top'))) {
		if (!suffusion_is_sidebar_empty(7)) {
			if ($suf_wa_tbrh_style == 'sliding-panel' || $suf_wa_tbrh_style == 'spanel-flat' || $suf_wa_tbrh_style == 'spanel-boxed') {
				$display = apply_filters('suffusion_can_display_sliding_panel', true);
				if ($display) {
?>
	<!-- #top-bar-right-spanel -->
	<div id="top-bar-right-spanel" class='warea fix'>
		<div class='col-control'>
			<div class='spanel'>
				<div class='spanel-content fix'>
<?php
					dynamic_sidebar('Top Bar Right Widgets');
?>
				</div>
			</div>
		</div>
	</div>
	<!-- #top-bar-right-spanel -->
<?php
				}
			}
		}
		$display = apply_filters('suffusion_can_display_top_navigation', true);
		if ($display) {
?>
	<div id='nav-top' class='<?php echo $suf_navt_item_type; ?> fix'>
		<div class='col-control'>
<?php
		if (!suffusion_is_sidebar_empty(6)) {
?>
			<!-- #top-bar-left-widgets -->
			<div id="top-bar-left-widgets" class="warea">
<?php
			dynamic_sidebar('Top Bar Left Widgets');
?>
			</div>
			<!-- /#top-bar-left-widgets -->
<?php
		}
		if (!suffusion_is_sidebar_empty(7)) {
			if ($suf_wa_tbrh_style == 'sliding-panel' || $suf_wa_tbrh_style == 'spanel-flat' || $suf_wa_tbrh_style == 'spanel-boxed') {
?>
		<div id="top-bar-right-spanel-tab">
			<div class="toggle">
				<a class="open" href="#"><?php echo $suf_wa_tbrh_open_text; ?></a>
				<a style="display: none;" class="close" href="#"><?php echo $suf_wa_tbrh_close_text; ?></a>
			</div>
		</div> <!-- /#top-bar-right-spanel-tab -->
<?php
			}
			else {
?>
		<!-- #top-bar-right-widgets -->
		<div id="top-bar-right-widgets" class="warea">
<?php
				dynamic_sidebar('Top Bar Right Widgets');
?>
		</div>
		<!-- /#top-bar-right-widgets -->
<?php
			}
		}
		suffusion_create_navigation_html(true, "pages", 'top', 'suf_navt_pages', 'suf_navt_cats', 'suf_navt_links', 'suf_navt_menus');
?>
		</div><!-- /.col-control -->
	</div><!-- /#nav-top -->
<?php
		}
	}
}

function suffusion_display_left_header_widgets() {
	if (!suffusion_is_sidebar_empty('8')) {?>
		<!-- left-header-widgets -->
		<div id="left-header-widgets" class='warea fix'>
		<?php
			dynamic_sidebar('Left Header Widgets');
		?>
		</div>
		<!-- /left-header-widgets -->
<?php
	}
}

function suffusion_display_right_header_widgets() {
	global $suf_show_search;
	if ($suf_show_search == "show" || !suffusion_is_sidebar_empty(3)) {?>
		<!-- right-header-widgets -->
		<div id="right-header-widgets" class="warea">
		<?php
			if (!dynamic_sidebar('Right Header Widgets')) {
				if ($suf_show_search == "show") {
					get_search_form();
				}
			}
		?>
		</div>
		<!-- /right-header-widgets -->
<?php
	}
}

/*
 * Displays the widget area below the header, if it is enabled.
 */
function suffusion_print_widget_area_below_header() {
	global $suf_widget_area_below_header_enabled, $suf_ns_wabh_enabled, $suf_wa_wabh_style;
	$display = apply_filters('suffusion_can_display_widget_area_below_header', true);
	if (!$display) {
		return;
	}
	if ($suf_widget_area_below_header_enabled == "enabled") {
		if ((is_page_template('no-sidebars.php') && $suf_ns_wabh_enabled == 'not-enabled')) {
		}
		else if (!suffusion_is_sidebar_empty(4)) { ?>
	<!-- horizontal-outer-widgets-1 Widget Area -->
	<div id="horizontal-outer-widgets-1" class="dbx-group <?php echo $suf_wa_wabh_style;?> warea fix">
		<?php
			dynamic_sidebar('Widget Area Below Header');
		?>
	</div>
	<!-- /horizontal-outer-widgets-1 --><?php
		}
	}
}

/**
 * Displays all left sidebars. This shows the following:
 *  1. Static tabbed sidebar, if enabled and either explicitly positioned left or if positioned right but there are no right sidebars.
 *  2. A wide sidebar at the top, if there are 2 left sidebars
 *  3. Sidebar 1 & Sidebar 1 - bottom, if applicable
 *  4. Sidebar 2 & Sidebar 2 - bottom, if applicable
 *  5. A wide sidebar at the bottom, if there are 2 left sidebars.
 *
 * @return
 */
function suffusion_print_left_sidebars() {
	global $suffusion;
	if (!isset($suffusion) || is_null($suffusion)) {
		$suffusion = new Suffusion();
	}
	$context = $suffusion->get_context();

	$left_count = suffusion_get_left_sidebar_count($context);
	if ($left_count == 0) {
		return;
	}

	global $suf_sidebar_alignment, $suf_sbtab_alignment, $suffusion_tabs_alignment, $suf_sbtab_enabled, $suf_wa_sb1b_style, $suf_wa_sb2b_style, $suf_wa_wst_style, $suf_wa_wsb_style;
	$right_count = suffusion_get_right_sidebar_count($context);

	// Show static tabbed sidebar if:
	//  1. It is enabled and positioned on the left
	//  2. Or it is enabled and there is only one sidebar and that is to the left
	//  3. Or it is enabled and there are 2 left sidebars.
	if ($suf_sbtab_enabled == 'enabled' && ($suf_sbtab_alignment == 'left' || ($left_count == 1 && $right_count == 0) || $left_count == 2)) {
		echo "<div id='sidebar-container' class='sidebar-container-left fix'>";
		$suffusion_tabs_alignment = 'left';
		get_template_part('sidebar-tabs');
	}

	// Show Wide Sidebar Top if there are 2 left sidebars
	if ($left_count == 2) {
		echo "<div id='sidebar-wrap' class='sidebar-wrap sidebar-wrap-left fix'>";
		if (!suffusion_is_sidebar_empty(18)) {
			suffusion_print_sidebar(18, 'wsidebar-top', 'Wide Sidebar (Top)', $suf_wa_wst_style, 'left');
		}
	}

	// Show Sidebar-1 if:
	//  1. There is only 1 sidebar to be shown and that's on the left
	//  2. Or there are 2 sidebars to be shown, one on the left and one on the right, and sidebar-1 has left alignment
	//  3. Or there are 2 sidebars to be shown, both on the left
	if ((($left_count == 1 && $right_count == 0) || ($left_count == 1 && $right_count == 1 && $suf_sidebar_alignment == 'left') || $left_count == 2)) {
		echo "<div id='sidebar-shell-1' class='sidebar-shell sidebar-shell-left'>\n";
		suffusion_before_first_sidebar();
		get_sidebar();
		suffusion_between_first_sidebars();
		suffusion_print_sidebar(9, 'sidebar-b', 'Sidebar 1 (Bottom)', $suf_wa_sb1b_style, "left");
		suffusion_after_first_sidebar();
		echo "</div>\n";
	}

	// Show Sidebar-2 if:
	//  1. There is 1 sidebar on the left and one on the right, and sidebar-2 has left alignment and sidebar-1 has right alignment
	//  2. Or there are 2 sidebars to be shown, both on the left
	if ((($left_count == 1 && $right_count == 1 && $suf_sidebar_alignment == "right") || $left_count == 2)) {
		echo "<div id='sidebar-shell-2' class='sidebar-shell sidebar-shell-left'>\n";
		suffusion_before_second_sidebar();
		get_sidebar(2);
		suffusion_between_second_sidebars();
		suffusion_print_sidebar(10, 'sidebar-2-b', 'Sidebar 2 (Bottom)', $suf_wa_sb2b_style, "left");
		suffusion_after_second_sidebar();
		echo "</div>\n";
	}

	// Show Wide Sidebar Bottom if there are 2 left sidebars. Consequently close #sidebar-wrap.
	if ($left_count == 2) {
		if (!suffusion_is_sidebar_empty(19)) {
			suffusion_print_sidebar(19, 'wsidebar-bottom', 'Wide Sidebar (Bottom)', $suf_wa_wsb_style, 'left');
		}
		echo "</div><!-- #sidebar-wrap -->\n";
	}

	// Close container, created if there is a static tabbed sidebar.
	if ($suf_sbtab_enabled == 'enabled' && ($suf_sbtab_alignment == 'left' || ($left_count == 1 && $right_count == 0) || $left_count == 2)) {
		echo "</div> <!-- /#sidebar-container -->";
	}
}

/**
 * Displays all right sidebars. This shows the following:
 *  1. Static tabbed sidebar, if enabled and either explicitly positioned right or if positioned left but there are no left sidebars.
 *  2. A wide sidebar at the top, if there are 2 right sidebars
 *  3. Sidebar 1 & Sidebar 1 - bottom, if applicable
 *  4. Sidebar 2 & Sidebar 2 - bottom, if applicable
 *  5. A wide sidebar at the bottom, if there are 2 right sidebars.
 *
 * @return
 */
function suffusion_print_right_sidebars() {
	global $suffusion;
	if (!isset($suffusion) || is_null($suffusion)) {
		$suffusion = new Suffusion();
	}
	$context = $suffusion->get_context();

	$right_count = suffusion_get_right_sidebar_count($context);
	if ($right_count == 0) {
		return;
	}

	global $suf_sidebar_alignment, $suf_sbtab_alignment, $suffusion_tabs_alignment, $suf_sbtab_enabled, $suf_wa_sb1b_style, $suf_wa_sb2b_style, $suf_wa_wst_style, $suf_wa_wsb_style;
	$left_count = suffusion_get_left_sidebar_count($context);

	// Show static tabbed sidebar if it is enabled:
	//  1. And positioned on the right
	//  2. Or there is only one sidebar and that is to the right
	//  3. Or there are 2 right sidebars.
	if ($suf_sbtab_enabled == 'enabled' && ($suf_sbtab_alignment == 'right' || ($right_count == 1 && $left_count == 0) || $right_count == 2)) {
		echo "<div id='sidebar-container' class='sidebar-container-right fix'>";
		$suffusion_tabs_alignment = 'right';
		get_template_part('sidebar-tabs');
	}

	// Show Wide Sidebar Top if there are 2 right sidebars
	if ($right_count == 2) {
		echo "<div id='sidebar-wrap' class='sidebar-wrap sidebar-wrap-right fix'>";
		if (!suffusion_is_sidebar_empty(18)) {
			suffusion_print_sidebar(18, 'wsidebar-top', 'Wide Sidebar (Top)', $suf_wa_wst_style, 'right');
		}
	}

	// Show Sidebar-1 if:
	//  1. There is only 1 sidebar to be shown and that's on the right
	//  2. Or there are 2 sidebars to be shown, one on the left and one on the right, and sidebar-1 has right alignment
	//  3. Or there are 2 sidebars to be shown, both on the right
	if ((($right_count == 1 && $left_count == 0) || ($right_count == 1 && $left_count == 1 && $suf_sidebar_alignment == 'right') || $right_count == 2)) {
		echo "<div id='sidebar-shell-1' class='sidebar-shell sidebar-shell-right'>\n";
		suffusion_before_first_sidebar();
		get_sidebar();
		suffusion_between_first_sidebars();
		suffusion_print_sidebar(9, 'sidebar-b', 'Sidebar 1 (Bottom)', $suf_wa_sb1b_style, "right");
		suffusion_after_first_sidebar();
		echo "</div>\n";
	}

	// Show Sidebar-2 if:
	//  1. There is 1 sidebar on the left and one on the right, and sidebar-2 has right alignment
	//  2. Or there are 2 sidebars to be shown, both on the right
	if ((($right_count == 1 && $left_count == 1 && $suf_sidebar_alignment == "left") || $right_count == 2)) {
		echo "<div id='sidebar-shell-2' class='sidebar-shell sidebar-shell-right'>\n";
		suffusion_before_second_sidebar();
		get_sidebar(2);
		suffusion_between_second_sidebars();
		suffusion_print_sidebar(10, 'sidebar-2-b', 'Sidebar 2 (Bottom)', $suf_wa_sb2b_style, "right");
		suffusion_after_second_sidebar();
		echo "</div>\n";
	}

	// Show Wide Sidebar Bottom if there are 2 right sidebars. Consequently close #sidebar-wrap.
	if ($right_count == 2) {
		if (!suffusion_is_sidebar_empty(19)) {
			suffusion_print_sidebar(19, 'wsidebar-bottom', 'Wide Sidebar (Bottom)', $suf_wa_wsb_style, 'right');
		}
		echo "</div>";
	}

	// Close container, created if there is a static tabbed sidebar.
	if ($suf_sbtab_enabled == 'enabled' && ($suf_sbtab_alignment == 'right' || ($right_count == 1 && $left_count == 0) || $right_count == 2)) {
		echo "</div> <!-- /#sidebar-container -->";
	}
}

/**
 * Computes and returns the number of sidebars to show on the left. The result is passed through the filter "suffusion_left_sidebar_count".
 *
 * @return mixed|void
 */
function suffusion_get_left_sidebar_count($context = array()) {
	global $suf_sidebar_count, $suf_sidebar_alignment, $suf_sidebar_2_alignment;
	$display = apply_filters('suffusion_can_display_left_sidebars', true); // Custom templates can use this hook to avoid sidebars
	if (!$display) {
		return 0;
	}

	if (is_page_template('no-sidebars.php') || is_page_template('1r-sidebar.php') || is_page_template('2r-sidebars.php')) {// These templates have 0 left sidebars
		$ret = 0;
	}
	else if (is_page_template('1l-sidebar.php') || is_page_template('1l1r-sidebar.php')) {// These templates have 1 left sidebar
		$ret = 1;
	}
	else if (is_page_template('2l-sidebars.php')) {// This template has 2 left sidebars
		$ret = 2;
	}
	else if ($suf_sidebar_count == 0 || ($suf_sidebar_count == 1 && $suf_sidebar_alignment == 'right') ||
			($suf_sidebar_count == 2 && $suf_sidebar_alignment == 'right' && $suf_sidebar_2_alignment == 'right')) {// These non-template layouts have 0 left sidebars
		$ret = 0;
	}
	else if (($suf_sidebar_count == 1 && $suf_sidebar_alignment == 'left') ||
			($suf_sidebar_count == 2 && (($suf_sidebar_alignment == 'left' && $suf_sidebar_2_alignment == 'right') ||
					($suf_sidebar_alignment == 'right' && $suf_sidebar_2_alignment == 'left')))) {// These non-template layouts have 1 left sidebar
		$ret = 1;
	}
	else if ($suf_sidebar_count == 2 && $suf_sidebar_alignment == 'left' && $suf_sidebar_2_alignment == 'left') {// These non-template layouts have 2 left sidebars
		$ret = 2;
	}
	return apply_filters('suffusion_left_sidebar_count', $ret, 'left', $context);
}

/**
 * Computes and returns the number of sidebars to show on the right. The result is passed through the filter "suffusion_right_sidebar_count".
 *
 * @return mixed|void
 */
function suffusion_get_right_sidebar_count($context = array()) {
	global $suf_sidebar_count, $suf_sidebar_alignment, $suf_sidebar_2_alignment;
	$display = apply_filters('suffusion_can_display_right_sidebars', true); // Custom templates can use this hook to avoid sidebars
	if (!$display) {
		return 0;
	}

	if (is_page_template('no-sidebars.php') || is_page_template('1l-sidebar.php') || is_page_template('2l-sidebars.php')) {
		$ret = 0;
	}
	else if (is_page_template('1r-sidebar.php') || is_page_template('1l1r-sidebar.php')) {
		$ret = 1;
	}
	else if (is_page_template('2r-sidebars.php')) {
		$ret = 2;
	}
	else if ($suf_sidebar_count == 0 || ($suf_sidebar_count == 1 && $suf_sidebar_alignment == 'left') ||
			($suf_sidebar_count == 2 && $suf_sidebar_alignment == 'left' && $suf_sidebar_2_alignment == 'left')) {
		$ret = 0;
	}
	else if (($suf_sidebar_count == 1 && $suf_sidebar_alignment == 'right') ||
			($suf_sidebar_count == 2 && (($suf_sidebar_alignment == 'left' && $suf_sidebar_2_alignment == 'right') || ($suf_sidebar_alignment == 'right' && $suf_sidebar_2_alignment == 'left')))) {
		$ret = 1;
	}
	else if ($suf_sidebar_count == 2 && $suf_sidebar_alignment == 'right' && $suf_sidebar_2_alignment == 'right') {
		$ret = 2;
	}
	return apply_filters('suffusion_right_sidebar_count', $ret, 'right', $context);
}

/**
 * For a given page this returns the number of sidebars to display on a particular side. In Version 3.7.4 this supports just the blog page,
 * but will subsequently be extended to other views.
 *
 * @param  $count
 * @param string $position
 * @param array $context
 * @return
 */
function suffusion_get_sidebar_count_for_view($count, $position = 'right', $context = array()) {
	global $suffusion_sidebar_context_presets;
	foreach ($suffusion_sidebar_context_presets as $preset) {
		if (in_array($preset, $context)) {
			$count_option = "suf_{$preset}_sidebar_count";
			global $$count_option;
		}
	}
/*	if (in_array('blog', $context)) {
		$count_option = "suf_blog_sidebar_count";
		global $$count_option;
	}*/

	if (!isset($$count_option)) {
		return $count;
	}
	
	$inherit = $$count_option;

	$position_count = array('default' => $count, '0' => 0,
		'1l' => array('left' => 1, 'right' => 0), '1r' => array('left' => 0, 'right' => 1), '1l1r' => array('left' => 1, 'right' => 1),
		'2l' => array('left' => 2, 'right' => 0), '2r' => array('left' => 0, 'right' => 2), );
	if (!isset($position_count[$inherit])) {
		return $position_count['default'];
	}
	$new_count = $position_count[$inherit];
	if (is_array($new_count)) {
		return $new_count[$position];
	}
	return $new_count;
}

/**
 * Returns the class corresponding to the template that a particular page is supposed to mimic.
 * 
 * @return string
 */
function suffusion_get_pseudo_template_class() {
	global $suffusion, $suffusion_sidebar_context_presets;
	if (!isset($suffusion) || is_null($suffusion)) {
		$suffusion = new Suffusion();
	}
	$context = $suffusion->get_context();

	foreach ($suffusion_sidebar_context_presets as $preset) {
		if (in_array($preset, $context)) {
			$count_option = "suf_{$preset}_sidebar_count";
			global $$count_option;
		}
	}
/*	if (in_array('blog', $context)) {
		$count_option = "suf_blog_sidebar_count";
		global $$count_option;
	}*/

	if (!isset($$count_option)) {
		return '';
	}

	switch ($$count_option) {
		case 'default':
			$template = '';
	        break;
		case '0':
			$template = 'no-sidebars.php';
	        break;
		case '1l':
			$template = '1l-sidebar.php';
			break;
		case '1r':
			$template = '1r-sidebar.php';
			break;
		case '1l1r':
			$template = '1l1r-sidebar.php';
			break;
		case '2l':
			$template = '2l-sidebars.php';
			break;
		case '2r':
			$template = '2r-sidebars.php';
			break;
		default:
			$template = '';
			break;
	}
	if ($template != '') {
		$template = 'page-template-'.str_replace('.', '-', $template);
	}

	return apply_filters('suffusion_pseudo_template_class', $template);
}

/*
 * Displays the widget area above the footer, if it is enabled.
 */
function suffusion_print_widget_area_above_footer() {
	global $suf_widget_area_above_footer_enabled, $suf_ns_waaf_enabled,  $suf_wa_waaf_style;
	$display = apply_filters('suffusion_can_display_widget_area_above_footer', true);
	if (!$display) {
		return;
	}
	if ($suf_widget_area_above_footer_enabled == "enabled") {
		if (is_page_template('no-sidebars.php') && ($suf_ns_waaf_enabled == 'not-enabled')) {
		}
		else if (!suffusion_is_sidebar_empty(5)) { ?>
	<!-- horizontal-outer-widgets-2 Widget Area -->
	<div id="horizontal-outer-widgets-2" class="<?php echo $suf_wa_waaf_style; ?> warea fix">
		<?php
			dynamic_sidebar('Widget Area Above Footer');
		?>
	</div>
	<!-- /horizontal-outer-widgets-2 -->
<?php
		}
	}
}

function suffusion_display_footer() {
	global $suf_footer_left, $suf_footer_center, $suf_footer_layout_style;
	$display = apply_filters('suffusion_can_display_site_footer', true);
	if (!$display) {
		return;
	}
	if ($suf_footer_layout_style != 'in-align') {
	?>
	<div id='page-footer'>
		<div class='col-control'>
	<?php
	}
	?>
	<div id="cred">
		<table>
			<tr>
				<td class="cred-left"><?php $strip = stripslashes($suf_footer_left); $strip = wp_specialchars_decode($strip, ENT_QUOTES); echo do_shortcode($strip); ?></td>
				<td class="cred-center"><?php $strip = stripslashes($suf_footer_center); $strip = wp_specialchars_decode($strip, ENT_QUOTES); echo do_shortcode($strip); ?></td>
				<td class="cred-right"><a href="http://www.aquoid.com/news/themes/suffusion/">Suffusion theme by Sayontan Sinha</a></td>
			</tr>
		</table>
	</div>
	<?php
	if ($suf_footer_layout_style != 'in-align') {
	?>
		</div>
	</div>
	<?php
	}
	?>
	<!-- <?php echo get_num_queries(); ?> queries, <?php suffusion_get_memory_usage(); ?> in <?php timer_stop(1); ?> seconds. -->
	<?php
 }

function suffusion_include_custom_footer_js() {
	global $suf_custom_footer_js;
	if (isset($suf_custom_footer_js) && trim($suf_custom_footer_js) != "") {
?>
<!-- Custom JavaScript for footer defined in options -->
<script type="text/javascript">
/* <![CDATA[ */
	<?php
	$strip = stripslashes($suf_custom_footer_js);
	$strip = wp_specialchars_decode($strip, ENT_QUOTES);
	echo $strip."\n";
	?>
/* ]]> */
</script>
<!-- /Custom JavaScript for footer defined in options -->
<?php }
}

function suffusion_get_siblings_in_nav($ancestors, $index, $exclusion_list, $exclude, $echo = 1) {
	if (count($ancestors) <= $index || $index < 0) {
		return;
	}
	$exclusion_query = $exclude == "hide" ? "&exclude=".$exclusion_list : "";
	$children = wp_list_pages("title_li=&child_of=".$ancestors[$index]."&echo=".$echo.$exclusion_query);
	return $children;
}

function suffusion_display_hierarchical_navigation() {
	$display = apply_filters('suffusion_can_display_hierarchical_navigation', true);
	if (!$display) {
		return;
	}
	global $post, $suf_nav_breadcrumb, $suf_nav_exclude_in_breadcrumb;
	$ancestors = get_post_ancestors($post);
	$exclusion_list = suffusion_get_excluded_pages("suf_nav_pages");
	$num_ancestors = count($ancestors);

	if ($suf_nav_breadcrumb == "all") {
		for ($anc_index = 1; $num_ancestors - $anc_index >= 0; $anc_index++) {
			$style = ($anc_index == 1) ? "subnav" : "l".($anc_index + 1)."nav";
			$class = ($anc_index == 1) ? "" : "hier-nav";
?>
	<div id="<?php echo $style;?>" class="<?php echo $class; ?> fix">
		<ul>
			<?php suffusion_get_siblings_in_nav($ancestors, $num_ancestors - $anc_index, $exclusion_list, $suf_nav_exclude_in_breadcrumb); ?>
		</ul>
	</div><?php echo "<!-- /".$style."-->"; ?>
<?php
		}
		$exclusion_query = $suf_nav_exclude_in_breadcrumb == "hide" ? "&exclude_tree=".$exclusion_list : "";
		$style = ($num_ancestors == 0) ? "subnav" : "l".($num_ancestors + 2)."nav";
		$class = ($num_ancestors == 0) ? "" : "hier-nav";
		$children = wp_list_pages("title_li=&child_of=".$post->ID."&echo=0".$exclusion_query);
		if ($children) {
	?>
	<div id="<?php echo $style;?>" class="<?php echo $class; ?> fix">
		<ul>
			<?php echo $children; ?>
		</ul>
	</div><!-- /sub nav -->
<?php
		}
	}
}

function suffusion_create_navigation_breadcrumb() {
	$display = apply_filters('suffusion_can_display_breadcrumb_navigation', true);
	if (!$display) {
		return;
	}
	global $suf_nav_breadcrumb, $suf_breadcrumb_separator, $post;

	$ancestors = get_post_ancestors($post);
	$num_ancestors = count($ancestors);
	if ($suf_nav_breadcrumb == "breadcrumb") {
		if ($num_ancestors > 0) {
	?>
	<div id="subnav" class="fix">
		<div class="breadcrumb">
	<?php
			for ($i = $num_ancestors-1; $i>=0; $i--) {
				$anc_page = get_page($ancestors[$i]);
				echo "<a href='".get_permalink($ancestors[$i])."'>".$anc_page->post_title."</a> ".$suf_breadcrumb_separator." ";
			}
			echo $post->post_title;
	?>
		</div>
	</div><!-- /sub nav -->
	<?php
		}
	}
}

function suffusion_excerpt_or_content() {
	global $post, $suf_category_excerpt, $suf_tag_excerpt, $suf_archive_excerpt, $suf_index_excerpt, $suf_search_excerpt, $suf_author_excerpt, $suf_show_excerpt_thumbnail, $suf_show_content_thumbnail, $full_content_post_counter, $full_post_count;
	if (($full_content_post_counter > $full_post_count) && ((is_category() && $suf_category_excerpt == "excerpt") ||
		(is_tag() && $suf_tag_excerpt == "excerpt") ||
		(is_search() && $suf_search_excerpt == "excerpt") ||
		(is_author() && $suf_author_excerpt == "excerpt") ||
		((is_date() || is_year() || is_month() || is_day() || is_time())&& $suf_archive_excerpt == "excerpt") ||
		(!(is_singular() || is_category() || is_tag() || is_search() || is_author() || is_date() || is_year() || is_month() || is_day() || is_time()) && $suf_index_excerpt == "excerpt"))) {
		$show_image = $suf_show_excerpt_thumbnail == "show" ? true : false;
		suffusion_excerpt($show_image);
	}
	else {
		if (has_post_thumbnail($post->ID) && $suf_show_content_thumbnail == 'show') {
			global $suf_excerpt_thumbnail_alignment, $suf_excerpt_thumbnail_size;
			//Could use suffusion_get_image(), but the theme uploader recommends use of the_post_thumbnail at least once in the theme...
			//echo suffusion_get_image(array('no-link' => true));
			if ($suf_excerpt_thumbnail_size == 'custom') {
				$thumbnail = get_the_post_thumbnail(null, 'excerpt-thumbnail');
			}
			else {
				$thumbnail = get_the_post_thumbnail(null, $suf_excerpt_thumbnail_size);
			}
			echo "<div class='$suf_excerpt_thumbnail_alignment-thumbnail'>".$thumbnail."</div>";
		}
		the_content(__('Continue reading', 'suffusion').' &raquo;');
	}
}

function suffusion_excerpt($show_image = false) {
	if ($show_image) {
		echo suffusion_get_image(array());
	}
	the_excerpt();
}

function suffusion_print_author_byline() {
	global $suf_page_posted_by_format, $suf_post_posted_by_format;
?>
<span class="author">
	<?php
	if (is_page()) {
		$format = $suf_page_posted_by_format;
	}
	else {
		$format = $suf_post_posted_by_format;
	}
	switch ($format) {
		case 'by':
			printf(__('Posted by %1$s', 'suffusion'), '<a href="'.get_author_posts_url(get_the_author_meta('ID')).'">'.get_the_author_meta('display_name').'</a>');
			break;
		case 'by-at':
			printf(__('Posted by %1$s at %2$s', 'suffusion'), '<a href="'.get_author_posts_url(get_the_author_meta('ID')).'">'.get_the_author_meta('display_name').'</a>', sprintf(get_the_time(get_option('time_format'))));
			break;
		case 'by-on':
			printf(__('Posted by %1$s on %2$s', 'suffusion'), '<a href="'.get_author_posts_url(get_the_author_meta('ID')).'">'.get_the_author_meta('display_name').'</a>', sprintf(get_the_time(get_option('date_format'))));
			break;
		case 'by-on-at':
			printf(__('Posted by %1$s on %2$s at %3$s', 'suffusion'), '<a href="'.get_author_posts_url(get_the_author_meta('ID')).'">'.get_the_author_meta('display_name').'</a>', sprintf(get_the_time(get_option('date_format'))), sprintf(get_the_time(get_option('time_format'))));
			break;
		case 'by-at-on':
			printf(__('Posted by %1$s at %2$s on %3$s', 'suffusion'), '<a href="'.get_author_posts_url(get_the_author_meta('ID')).'">'.get_the_author_meta('display_name').'</a>', sprintf(get_the_time(get_option('time_format'))), sprintf(get_the_time(get_option('date_format'))));
			break;
	}
	?>
</span>
<?php
}

function suffusion_post_footer() {
	global $suf_post_show_posted_by, $suf_page_show_posted_by, $suf_post_show_tags, $suf_post_show_cats, $suf_post_show_comment, $suf_page_show_comment, $post, $suf_post_meta_position, $suf_page_meta_position;
?>
	<div class="post-footer fix">
	<?php
	if ((!is_page() && $suf_post_meta_position == 'corners' && ($suf_post_show_posted_by == 'show' || $suf_post_show_posted_by == 'show-bright')) ||
		(is_page() && $suf_page_meta_position == 'corners' && ($suf_page_show_posted_by == 'show' || $suf_page_show_posted_by == 'show-bright'))) {
		suffusion_print_author_byline();
	}
	if (!is_page() && $suf_post_meta_position == 'corners' && ($suf_post_show_cats == 'show-bleft' || $suf_post_show_cats == 'show-bright')) {
?>
			<span class="category"><?php the_category(', ') ?></span>
<?php
	}
	if (!is_page()) {
		if ($suf_post_meta_position == 'corners') {
			if (is_singular()) {
				if ('open' == $post->comment_status && ($suf_post_show_comment == 'show-bleft' || $suf_post_show_comment == 'show-bright')) {
?>
			<span class="comments"><a href="#respond"><?php _e('Add comments', 'suffusion'); ?></a></span>
<?php
				}
			}
			else if ($suf_post_show_comment == 'show-bleft' || $suf_post_show_comment == 'show-bright') { ?>
			<span class="comments"><?php comments_popup_link(__('No Responses', 'suffusion').' &#187;', __('1 Response', 'suffusion').' &#187;', __('% Responses', 'suffusion').' &#187;'); ?></span>
<?php
			}
		}
	}
	else {
		if ('open' == $post->comment_status && $suf_page_meta_position == 'corners' && ($suf_page_show_comment == 'show-bleft' || $suf_page_show_comment == 'show-bright')) {
?>
			<span class="comments"><a href="#respond"><?php _e('Add comments', 'suffusion'); ?></a></span>
<?php
		}
	}
	if (!is_page() && $suf_post_meta_position == 'corners' && ($suf_post_show_tags == 'show' ||  $suf_post_show_tags == 'show-bleft')) { ?>
			<span class="tags"><?php the_tags(__('Tagged with: ', 'suffusion'),', ','<br />'); ?></span>
<?php
	}
?>
	</div><!-- .post-footer -->
<?php
}

function suffusion_disable_plugin_styles() {
	wp_deregister_style('wp-pagenavi');
}

function suffusion_pagination() {
	global $suf_pagination_type, $suf_pagination_index, $suf_pagination_prev_next, $suf_pagination_show_all;
	if (is_singular()) {
		return;
	}
    if (suffusion_show_page_nav()) {
        if (function_exists("wp_pagenavi")) {
			// If the user has wp_pagenavi installed, we will use that for pagination
?>
		<div class="page-nav fix">
<?php
			wp_pagenavi();
?>
		</div><!-- page nav -->
<?php
		}
		else if ($suf_pagination_type == "numbered") {
			// The user doesn't have WP-PageNavi, but still wants pagination
			global $wp_query, $paged;
			$max_page = $wp_query->max_num_pages;
			$prev_next = $suf_pagination_prev_next == "show";
			$show_all = $suf_pagination_show_all == "all";
			if (!$paged && $max_page >= 1) {
				$current_page = 1;
			}
			else {
				$current_page = $paged;
			}
?>
		<div class="page-nav fix">
			<div class="suf-page-nav fix">
<?php
			if ($suf_pagination_index == "show") {
?>
				<span class="page-index"><?php printf(__('Page %1$s of %2$s', 'suffusion'), $current_page, $max_page); ?></span>
<?php
			}
			echo paginate_links(array(
				"base" => add_query_arg("paged", "%#%"),
				"format" => '',
				"type" => "plain",
				"total" => $max_page,
				"current" => $current_page,
				"show_all" => $show_all,
				"end_size" => 2,
				"mid_size" => 2,
				"prev_next" => $prev_next,
				"next_text" => __('Older Entries', 'suffusion'),
				"prev_text" => __('Newer Entries', 'suffusion'),
			));
?>
			</div><!-- suf page nav -->
		</div><!-- page nav -->
<?php
		}
		else {
?>
		<div class="page-nav fix">
			<span class="previous-entries"><?php next_posts_link(__('Older Entries', 'suffusion')); ?></span>
			<span class="next-entries"><?php previous_posts_link(__('Newer Entries', 'suffusion')); ?></span>
		</div><!-- page nav -->
<?php
		}
    }
}

function suffusion_comment_pagination() {
	global $suf_cpagination_type, $suf_cpagination_index, $suf_cpagination_prev_next, $suf_cpagination_show_all;
	if ($suf_cpagination_type == "numbered") {
		// The user wants pagination
		global $wp_query, $paged;
		$max_page = $wp_query->max_num_pages;
		$prev_next = $suf_cpagination_prev_next == "show";
		$show_all = $suf_cpagination_show_all == "all";
		if (!$paged && $max_page >= 1) {
			$current_page = 1;
		}
		else {
			$current_page = $paged;
		}
?>
		<div class="page-nav fix">
			<div class="suf-page-nav fix">
<?php
		if ($suf_cpagination_index == "show") {
?>
				<span class="page-index"><?php printf(__('Page %1$s of %2$s', 'suffusion'), $current_page, $max_page); ?></span>
<?php
		}
		echo paginate_comments_links(array(
			"base" => add_query_arg("cpage", "%#%"),
			"format" => '',
			"type" => "plain",
			"total" => $max_page,
			"current" => $current_page,
			"show_all" => $show_all,
			"end_size" => 2,
			"mid_size" => 2,
			"prev_next" => $prev_next,
			"next_text" => __('Older Entries', 'suffusion'),
			"prev_text" => __('Newer Entries', 'suffusion'),
		));
?>
			</div><!-- suf page nav -->
		</div><!-- page nav -->
<?php
	}
	else {
?>
		<div class="page-nav fix">
			<span class="previous-entries"><?php next_posts_link(__('Older Entries', 'suffusion')); ?></span>
			<span class="next-entries"><?php previous_posts_link(__('Newer Entries', 'suffusion')); ?></span>
		</div><!-- page nav -->
<?php
	}
}

function suffusion_featured_posts() {
	global $suf_featured_category_view, $suf_featured_tag_view, $suf_featured_search_view, $suf_featured_author_view, $suf_featured_time_view, $suf_featured_index_view;
	global $suf_mag_featured_enabled, $suf_featured_pages_with_fc, $post;
    $pages_with_fc = explode(',', $suf_featured_pages_with_fc);
	if ((is_category() && $suf_featured_category_view == "enabled") || (is_tag() && $suf_featured_tag_view == "enabled") ||
		(is_search() && $suf_featured_search_view == "enabled") || (is_author() && $suf_featured_author_view == "enabled") ||
		(is_page_template('magazine.php') && $suf_mag_featured_enabled == 'enabled') ||
        (is_page() && isset($pages_with_fc) && is_array($pages_with_fc) && isset($post) && isset($post->ID) && in_array($post->ID, $pages_with_fc)) ||
		((is_date() || is_year() || is_month() || is_day() || is_time()) && $suf_featured_time_view == "enabled") ||
		(!(is_category() || is_tag() || is_search() || is_author() || is_date() || is_year() || is_month() || is_day() || is_time() || is_page_template('magazine.php') || is_page())
			&& $suf_featured_index_view == "enabled")) {
		locate_template(array("featured-posts.php"), true);
		suffusion_display_featured_posts();
	}
}

function suffusion_include_featured_js() {
	global $suf_featured_category_view, $suf_featured_tag_view, $suf_featured_search_view, $suf_js_in_footer;
	global $suf_featured_author_view, $suf_featured_time_view, $suf_featured_index_view, $suf_mag_featured_enabled, $suf_featured_pages_with_fc;
	$footer = $suf_js_in_footer == 'footer' ? true : false;
	if ((is_category() && $suf_featured_category_view == "enabled") || (is_tag() && $suf_featured_tag_view == "enabled") ||
		(is_search() && $suf_featured_search_view == "enabled") || (is_author() && $suf_featured_author_view == "enabled") ||
		(is_page_template('magazine.php') && $suf_mag_featured_enabled == 'enabled') ||
        (is_page() && $suf_featured_pages_with_fc != '') ||
		((is_date() || is_year() || is_month() || is_day() || is_time()) && $suf_featured_time_view == "enabled") ||
		(!(is_category() || is_tag() || is_search() || is_author() || is_date() || is_year() || is_month() || is_day() || is_time() || is_page_template('magazine.php') || is_page()) && $suf_featured_index_view == "enabled")) {
		wp_enqueue_script('jquery-cycle');
        wp_enqueue_script('slider-init', get_template_directory_uri() . '/scripts/slider-init.js', array('jquery-cycle'), null, $footer);
	}
    if (!is_admin() && is_active_widget('Suffusion_Featured_Posts', false, 'suf-featured-posts', true)) {
	    wp_enqueue_script('jquery-cycle');
    }
}

function suffusion_template_specific_header() {
	global $suf_cat_info_enabled, $suf_author_info_enabled, $suf_tag_info_enabled, $suf_search_info_enabled;
	if (is_category() && ($suf_cat_info_enabled == 'enabled')) { ?>
		<div class="info-category fix">
			<h2 class="category-title"><?php single_cat_title(); ?></h2>
<?php echo suffusion_get_category_information(); ?>
		</div><!-- .info-category -->
<?php
	}
	else if (is_author() && ($suf_author_info_enabled == 'enabled')) {
		$id = get_query_var('author'); ?>
		<div id="author-profile-<?php the_author_meta('user_nicename', $id); ?>" class="author-profile author-even fix">
			<h2 class="author-title"><?php the_author_meta('display_name', $id); ?></h2>
			<?php echo suffusion_get_author_information();?>
		</div><!-- /.author-profile -->
<?php
	}
	else if (is_tag() && ($suf_tag_info_enabled == 'enabled')) { ?>
		<div class="info-tag fix">
			<h2 class="tag-title"><?php single_tag_title(); ?></h2>
		<?php echo tag_description(get_query_var('tag_id')); ?>
		</div><!-- .info-tag -->
<?php
	}
	else if (is_search() && $suf_search_info_enabled == 'enabled') {
		if (have_posts()) {	?>
		<div class='post fix'>
			<h2 class='posttitle'><?php $title = wp_title(':', false); $title = trim($title); if (substr($title, 0, 1) == ':') { $title = substr($title, 1);} echo $title; ?></h2>
			<form method="get" action="<?php echo home_url(); ?>/" class='search-info' id='search-info'>
				<input class="search-hl checkbox" name="search-hl" id="search-hl" type="checkbox"/>
				<label class='search-hl' for='search-hl'><?php _e('Highlight matching results below', 'suffusion');?></label>
				<input type='hidden' name='search-term' id='search-term' value="<?php $search_term = get_search_query(); echo esc_attr($search_term);?>"/>
			</form>
			<?php get_search_form(); ?>
		</div>
<?php
		}
	}
}

function suffusion_get_category_information() {
	$ret = "<div class=\"category-description\">\n";
	if (function_exists('get_cat_icon')) {
		$ret .= get_cat_icon('echo=false');
	}
	$ret .= category_description()."\n";
	$ret .= "</div><!-- .category-description -->\n";
	$ret = apply_filters('suffusion_category_information', $ret);
	return $ret;
}

function suffusion_get_author_information() {
	$id = get_query_var('author');
	$ret = "<div class=\"author-description\">\n";
	$ret .= get_avatar(get_the_author_meta('user_email', $id), '96')."\n";
	$ret .= "<p class=\"author-bio\">\n";
	$ret .= get_the_author_meta('description', $id)."\n";
	$ret .= "</p><!-- /.author-bio -->\n";
	$ret .= "</div><!-- /.author-description -->\n";
	$ret = apply_filters('suffusion_author_information', $ret);
	return $ret;
}

function suffusion_print_post_page_title() {
	global $post, $suf_post_show_cats, $suf_post_show_comment, $suf_page_show_comment, $suf_post_show_posted_by, $suf_page_show_posted_by, $suf_post_show_tags, $suf_post_meta_position, $suf_page_meta_position;
	if (is_singular()) {
		$header_tag = "h1";
	}
	else {
		$header_tag = "h2";
	}

	if ($post->post_type == 'post') {
?>
		<div class='title-container fix'>
			<div class="title">
			<?php
/*			if (!function_exists('has_post_format') ||
				(function_exists('has_post_format') &&
						(has_post_format('chat') || has_post_format('gallery') || has_post_format('image') || has_post_format('link') || has_post_format('video') || has_post_format('audio') || !get_post_format()))) {*/
			?>
				<<?php echo $header_tag;?>  class="posttitle"><?php echo suffusion_get_post_title_and_link(); ?></<?php echo $header_tag;?>>
<?php

/*			}*/
			if ($suf_post_meta_position == 'corners') {
?>
				<div class="postdata fix">
		<?php
		if (($suf_post_show_posted_by == 'show-tleft' || $suf_post_show_posted_by == 'show-tright') && $suf_post_meta_position == 'corners') {
			suffusion_print_author_byline();
		}
		if ($suf_post_show_cats == 'show' || $suf_post_show_cats == 'show-tright') {
?>
			<span class="category"><?php the_category(', ') ?></span>
<?php
		}
		if (is_singular()) {
			if ('open' == $post->comment_status && ($suf_post_show_comment == 'show' || $suf_post_show_comment == 'show-tleft')) {
?>
			<span class="comments"><a href="#respond"><?php _e('Add comments', 'suffusion'); ?></a></span>
<?php
			}
		}
		else if ($suf_post_show_comment == 'show' || $suf_post_show_comment == 'show-tleft') { ?>
			<span class="comments"><?php comments_popup_link(__('No Responses', 'suffusion').' &#187;', __('1 Response', 'suffusion').' &#187;', __('% Responses', 'suffusion').' &#187;'); ?></span>
<?php	}
		if (is_singular() && get_edit_post_link() != '') { ?>
   			<span class="edit"><?php edit_post_link(__('Edit', 'suffusion'), '', ''); ?></span>
<?php
		}
		if ($suf_post_show_tags == 'show-tleft' ||  $suf_post_show_tags == 'show-tright') { ?>
			<span class="tags"><?php the_tags(__('Tagged with: ', 'suffusion'),', ','<br />'); ?></span>
<?php
		}
?>
				</div><!-- /.postdata -->
<?php
	}
?>
			</div><!-- /.title -->
<?php
		if ("post" == $post->post_type) {
?>
			<div class="date"><span class="month"><?php the_time('M'); ?></span> <span class="day"><?php the_time('d'); ?></span><span class="year"><?php the_time('Y'); ?></span></div>
<?php
		}
?>
		</div><!-- /.title-container -->
<?php
	}
	else {
		if (!is_singular()) {
?>
		<<?php echo $header_tag;?> class="posttitle"><?php echo suffusion_get_post_title_and_link(); ?></<?php echo $header_tag;?>>
<?php
		}
		else {
?>
        <<?php echo $header_tag;?> class="posttitle"><?php the_title(); ?></<?php echo $header_tag;?>>
<?php
		}

		if ($suf_page_meta_position == 'corners') {
?>
        <div class="postdata fix">
		<?php
		if ($suf_page_show_posted_by == 'show-tleft' || $suf_page_show_posted_by == 'show-tright') {
			suffusion_print_author_byline();
		}
		if ('open' == $post->comment_status && ($suf_page_show_comment == 'show' || $suf_page_show_comment == 'show-tleft')) {
?>
			<span class="comments"><a href="#respond"><?php _e('Add comments', 'suffusion'); ?></a></span>
<?php
		}
		if (get_edit_post_link() != '') {
?>
			<span class="edit"><?php edit_post_link(__('Edit', 'suffusion'), '', ''); ?></span>
<?php
		}
?>
        </div>
<?php
		}
	}
}

function suffusion_include_custom_js_files() {
	global $suf_custom_js_file_1, $suf_custom_js_file_2, $suf_custom_js_file_3, $suf_js_in_footer;
	$footer = $suf_js_in_footer == 'footer' ? true : false;
	if ($suf_custom_js_file_1) {
		wp_enqueue_script('suffusion-js-1', $suf_custom_js_file_1, array(), null, $footer);
	}
	if ($suf_custom_js_file_2) {
		wp_enqueue_script('suffusion-js-2', $suf_custom_js_file_2, array(), null, $footer);
	}
	if ($suf_custom_js_file_3) {
		wp_enqueue_script('suffusion-js-3', $suf_custom_js_file_3, array(), null, $footer);
	}
}

function suffusion_include_jqfix_js() {
	global $suf_js_in_footer;
	$footer = $suf_js_in_footer == 'footer' ? true : false;
	$version = suffusion_get_current_version();
    wp_enqueue_script('suffusion', get_template_directory_uri() . '/scripts/suffusion.js', array('jquery'), $version, $footer);
}

function suffusion_set_title() {
	global $suf_seo_enabled, $suf_seo_title_style, $suf_seo_title_separator, $suf_seo_show_subtitle, $suf_seo_show_page_num, $paged, $page;
	if ($suf_seo_enabled != 'enabled') {
		if (is_home() || is_front_page()) {
			echo "\t<title>".get_bloginfo('name')."</title>\n";
		}
		else if (function_exists('bp_is_group')) {
			bp_page_title();
		}
		else {
			echo "\t<title>".wp_title('&raquo;', false)."</title>\n";
		}
		return;
	}

	if (function_exists('bp_is_group')) {
		// If this is not set, BP titles come out wrong.
		$page_title = bp_get_page_title();
	}
	else {
		$page_title = wp_title('', false);
	}
	$blog_title = get_bloginfo('name');

	if (is_home() || is_front_page()) {
		$title = $blog_title;
		if ($suf_seo_show_subtitle == 'show') {
			$blog_subtitle = get_bloginfo('description');
			$title .= " ".$suf_seo_title_separator." ".$blog_subtitle;
		}
	}
	else {
		if ($suf_seo_title_style == 'page-blog') {
			$title = $page_title." ".$suf_seo_title_separator." ".$blog_title;
		}
		else if ($suf_seo_title_style == 'blog-page') {
			$title = $blog_title." ".$suf_seo_title_separator." ".$page_title;
		}
		else {
			$title = $page_title;
		}
	}
	if ($suf_seo_show_page_num == 'show' && ($paged >= 2 || $page >= 2)) {
		$title .= ' '.$suf_seo_title_separator.' '.sprintf(__('Page %s', 'suffusion'), max($paged, $page));
	}
    $title = apply_filters('suffusion_set_title', $title);
    echo "\t<title>{$title}</title>\n";
}

function suffusion_include_meta() {
    global $suf_seo_enabled, $suf_seo_all_settings;
    if ($suf_seo_enabled == 'enabled') {
        $seo_settings = explode(',', $suf_seo_all_settings);
        suffusion_include_meta_generator($seo_settings);
        suffusion_include_meta_theme($seo_settings);
        suffusion_include_meta_robots($seo_settings);
        suffusion_include_meta_author($seo_settings);
        suffusion_include_meta_copyright($seo_settings);
        suffusion_include_meta_revised($seo_settings);

        suffusion_include_meta_description();
        suffusion_include_meta_keywords();
    }
}

function suffusion_include_ie7_compatibility_mode() {
	global $suf_ie7_compatibility;
	if ($suf_ie7_compatibility == 'force') {
		echo "\t<meta http-equiv=\"X-UA-Compatible\" content=\"IE=EmulateIE7\" />\n";
	}
}

function suffusion_include_meta_generator($seo_settings) {
    if ($seo_settings && in_array('generator', $seo_settings)) {
        wp_generator();
    }
}

function suffusion_include_meta_theme($seo_settings) {
    if ($seo_settings && in_array('theme', $seo_settings)) {
        $theme_data = get_theme_data(TEMPLATEPATH.'/style.css');
        echo "\t".'<meta name="template" content="'.esc_attr("{$theme_data['Title']} {$theme_data['Version']}").'" />'."\n";
    }
}

function suffusion_include_meta_robots($seo_settings) {
    if ($seo_settings && in_array('robots', $seo_settings) && get_option('blog_public')) {
        echo "\t".'<meta name="robots" content="noindex,nofollow" />' . "\n";
    }
}

function suffusion_include_meta_author($seo_settings) {
    global $wp_query;
    if ($seo_settings && in_array('author', $seo_settings)) {
        if (is_singular()) {
            $author = get_the_author_meta('display_name', $wp_query->post->post_author);
        }
        else {
            $posts_on_page = $wp_query->posts;
            $author_array = array();
            foreach ($posts_on_page as $single_post) {
                $single_author = get_the_author_meta('display_name', $single_post->post_author);
                if (!in_array($single_author, $author_array)) {
                    $author_array[] = get_the_author_meta('display_name', $single_post->post_author);
                }
            }
            $author = implode(',',$author_array);
        }

        if ($author) {
            echo "\t".'<meta name="author" content="'.esc_attr($author).'" />' . "\n";
        }
    }
}

function suffusion_include_meta_copyright($seo_settings) {
    if ($seo_settings && in_array('copyright', $seo_settings)) {
        if (is_singular()) {
            $copy_date = sprintf(get_the_time(get_option('date_format')));
        }
        else {
            $copy_date = date('Y');
        }
        echo "\t".'<meta name="copyright" content="'.sprintf(esc_attr__('Copyright (c) %1$s', 'suffusion'), $copy_date).'" />'."\n";
    }
}

function suffusion_include_meta_revised($seo_settings) {
    if ($seo_settings && in_array('revised', $seo_settings)) {
        if (is_singular()) {
            $mod_time = sprintf(get_the_modified_time(get_option('date_format')." ".get_option('time_format')));
            echo "\t".'<meta name="revised" content="'.$mod_time.'" />'."\n";
        }
    }
}

function suffusion_include_default_feed() {
	global $suf_custom_default_rss_enabled, $wp_version;
	if ($suf_custom_default_rss_enabled == 'enabled') {
		if ($wp_version < 3) {
			echo "\t".'<link rel="alternate" type="application/rss+xml" title="'.esc_attr(get_bloginfo('name')).' RSS Feed" href="'.get_feed_link('rss2').'" />'."\n";
		}
	}
}

function suffusion_include_meta_description() {
    global $suf_seo_meta_description, $wp_query;
    if (is_home()) {
        $description = $suf_seo_meta_description;
    }
    else if (is_singular()) {
        $description = get_post_meta($wp_query->post->ID, "meta_description", true);
        if (empty($description) && is_front_page()) {
            $description = $suf_seo_meta_description;
        }
    }
    else if (is_category() || is_tag() || is_tax()) {
        $description = term_description('', get_query_var('taxonomy'));
    }
    else if (is_author()) {
        $description = get_the_author_meta('description', get_query_var('author'));
    }
    if (!empty($description)) {
        $description = stripslashes($description);
        $description = strip_tags($description);
        $description = str_replace(array("\r", "\n", "\t"), '', $description);
        $description = "\t".'<meta name="description" content="' . $description . '" />' . "\n";
        echo $description;
    }
}

function suffusion_include_meta_keywords() {
    global $suf_seo_meta_keywords, $wp_query;
    if (is_home() || is_category() || is_tag() || is_tax() || is_author()) {
        $keywords = $suf_seo_meta_keywords;
    }
    else if (is_singular()) {
        $keywords = get_post_meta($wp_query->post->ID, "meta_keywords", true);
        if (empty($keywords)) {
            $keywords = $suf_seo_meta_keywords;
        }
    }

    if (!empty($keywords)) {
        $keywords = stripslashes($keywords);
        $keywords = strip_tags($keywords);
        $keywords = str_replace(array("\r", "\n", "\t"), '', $keywords);
        $keywords = str_replace(array(", ", " ,"), ',', $keywords);
        $keywords = "\t".'<meta name="keywords" content="' . $keywords . '" />' . "\n";
        echo $keywords;
    }
}

function suffusion_js_initializer() {
    global $suf_nav_delay, $suf_nav_effect, $suf_navt_delay, $suf_navt_effect, $suf_featured_interval, $suf_featured_fx, $suf_featured_transition_speed, $suf_featured_sync, $suf_jq_masonry_enabled;
    global $suf_featured_category_view, $suf_featured_tag_view, $suf_featured_search_view;
    global $suf_featured_author_view, $suf_featured_time_view, $suf_featured_index_view, $suf_featured_pages_with_fc, $suf_mag_featured_enabled;

    if ($suf_nav_delay == '') {
        $delay = "0";
    }
    else {
        $delay = $suf_nav_delay;
    }

	if ($suf_navt_delay == '') {
		$delay_top = "0";
	}
	else {
		$delay_top = $suf_navt_delay;
	}
?>
    <script type='text/javascript'>
        //Menu effects
        var suf_nav_delay = <?php echo $delay; ?>;
        var suf_nav_effect = "<?php echo $suf_nav_effect;?>";
        var suf_navt_delay = <?php echo $delay_top; ?>;
        var suf_navt_effect = "<?php echo $suf_navt_effect;?>";
        var suf_jq_masonry_enabled = "<?php echo $suf_jq_masonry_enabled; ?>";
<?php
	if ((is_category() && $suf_featured_category_view == "enabled") || (is_tag() && $suf_featured_tag_view == "enabled") ||
		(is_search() && $suf_featured_search_view == "enabled") || (is_author() && $suf_featured_author_view == "enabled") ||
		(is_page_template('magazine.php') && $suf_mag_featured_enabled == 'enabled') ||
        (is_page() && $suf_featured_pages_with_fc != '') ||
		((is_date() || is_year() || is_month() || is_day() || is_time()) && $suf_featured_time_view == "enabled") ||
		(!(is_category() || is_tag() || is_search() || is_author() || is_date() || is_year() || is_month() || is_day() || is_time() || is_page_template('magazine.php') || is_page()) && $suf_featured_index_view == "enabled")) {
        $pause = __('Pause', 'suffusion');
        $resume = __('Resume', 'suffusion');
?>
        //Featured content
        var suf_featured_interval = <?php echo $suf_featured_interval; ?>;
        var suf_featured_transition_speed = <?php echo $suf_featured_transition_speed; ?>;
        var suf_featured_fx = '<?php echo $suf_featured_fx; ?>';
        var suf_featured_pause = '<?php echo $pause; ?>';
        var suf_featured_resume = '<?php echo $resume; ?>';
		var suf_featured_sync = <?php echo $suf_featured_sync; ?>;
<?php
    }
?>
    </script>
<?php
}

function suffusion_author_information() {
    global $suf_uprof_post_info_enabled, $suf_uprof_post_info_header, $suf_uprof_post_info_content, $suf_uprof_post_info_gravatar;
    global $suf_uprof_post_info_gravatar_size;
	if (is_singular()) {
		$ret = "";
		if ($suf_uprof_post_info_enabled == 'bottom' || (is_page() && $suf_uprof_post_info_enabled == 'pages') || (!is_page() && $suf_uprof_post_info_enabled == 'posts')) {
			$ret = "<div class='author-info fix'>\n";
			if (trim($suf_uprof_post_info_header) != '') {
				$header = stripslashes($suf_uprof_post_info_header);
				$header = wp_specialchars_decode($header, ENT_QUOTES);
				$header = do_shortcode($header);
				$ret .= "<h4>".$header."</h4>\n";
			}
			if ($suf_uprof_post_info_gravatar == 'show') {
				$ret .= get_avatar(get_the_author_meta('user_email'), "$suf_uprof_post_info_gravatar_size");
			}
			if (trim($suf_uprof_post_info_content) != '') {
				$body = stripslashes($suf_uprof_post_info_content);
				$body = wp_specialchars_decode($body, ENT_QUOTES);
				$body = do_shortcode($body);
				$ret .= $body;
			}
			$ret .= "</div>\n";
		}
		echo $ret;
	}
}

function suffusion_include_favicon() {
	global $suf_favicon_path;
	if (trim($suf_favicon_path) != '') {
		echo "<link rel='shortcut icon' href='$suf_favicon_path' />\n";
	}
}

function suffusion_is_sidebar_empty($index) {
	$sidebars = wp_get_sidebars_widgets();
	if (!isset($sidebars['sidebar-'.$index]) || $sidebars['sidebar-'.$index] == null || (is_array($sidebars['sidebar-'.$index]) && count($sidebars['sidebar-'.$index]) == 0)) {
		return true;
	}
	return false;
}

function suffusion_sidebar_widget_count($index) {
	$sidebars = wp_get_sidebars_widgets();
	if (!isset($sidebars['sidebar-'.$index]) || $sidebars['sidebar-'.$index] == null || (is_array($sidebars['sidebar-'.$index]) && count($sidebars['sidebar-'.$index]) == 0)) {
		return 0;
	}
	return count($sidebars['sidebar-'.$index]);
}

function suffusion_display_open_header() {
	global $suf_header_layout_style;
	$display = apply_filters('suffusion_can_display_open_header', true);
	if (!$display) {
		return;
	}
	if ($suf_header_layout_style != 'in-align') {
		if ($suf_header_layout_style  == 'out-hcalign' || $suf_header_layout_style  == 'out-cfull-halign') {
			suffusion_display_top_navigation();
			suffusion_display_widgets_above_header();
		}
?>
		<div id="header-container" class="fix">
			<div class='col-control fix'>
<?php
		if ($suf_header_layout_style  == 'out-hcfull') {
			suffusion_display_top_navigation();
			suffusion_display_widgets_above_header();
		}
		suffusion_display_header();
		if ($suf_header_layout_style  == 'out-hcfull') {
			suffusion_display_main_navigation();
		}
	?>
			</div>
		</div><!-- //#header-container -->
<?php
		if ($suf_header_layout_style  == 'out-hcalign' || $suf_header_layout_style  == 'out-cfull-halign') {
			suffusion_display_main_navigation();
		}
	}
	else {
		suffusion_display_top_navigation();
		suffusion_display_widgets_above_header();
	}
}

function suffusion_display_closed_header() {
	global $suf_header_layout_style;
	$display = apply_filters('suffusion_can_display_closed_header', true);
	if (!$display) {
		return;
	}
	if ($suf_header_layout_style == 'in-align') {
?>
			<div id="header-container" class="fix">
				<?php
					suffusion_page_header();
				?>
			</div><!-- //#header-container -->
<?php
	}
}

/**
 * Based on the Image Rotator script by Matt Mullenweg > http://photomatt.net
 * Inspired by Dan Benjamin > http://hiveware.com/imagerotator.php
 * Latest version always at: http://photomatt.net/scripts/randomimage
 *
 * Make the folder the relative path to the images, like "../img" or "random/images/".
 *
 * Modifications by Sayontan Sinha, to dynamically pass the folder for images.
 * This cannot exist as a standalone file, because it loads outside the context of WP, so variables such as folder names cannot be fetched by the file automatically.
 */
function suffusion_get_rotating_image($folder) {
	// Space seperated list of extensions, you probably won't have to change this.
	$exts = 'jpg jpeg png gif';

	$files = array(); $i = -1; // Initialize some variables
//	if ('' == $folder) $folder = './';
	$content_folder = WP_CONTENT_DIR."/".$folder;

	$handle = opendir($content_folder);
	$exts = explode(' ', $exts);
	while (false !== ($file = readdir($handle))) {
		foreach($exts as $ext) { // for each extension check the extension
			if (preg_match('/\.'.$ext.'$/i', $file, $test)) { // faster than ereg, case insensitive
				$files[] = $file; // it's good
				++$i;
			}
		}
	}
	closedir($handle); // We're not using it anymore
	mt_srand((double)microtime()*1000000); // seed for PHP < 4.2
	$rand = mt_rand(0, $i); // $i was incremented as we went along
	return WP_CONTENT_URL."/".$folder."/".$files[$rand];
}

function suffusion_register_jquery() {
	global $suf_featured_use_lite, $suf_js_in_footer;
	$footer = $suf_js_in_footer == 'footer' ? true : false;
	if ($suf_featured_use_lite == 'lite') {
		wp_register_script('jquery-cycle', get_template_directory_uri() . '/scripts/jquery.cycle.lite.min.js', array('jquery'), null, $footer);
	}
	else {
		wp_register_script('jquery-cycle', get_template_directory_uri() . '/scripts/jquery.cycle.all.min.js', array('jquery'), null, $footer);
	}
}

function suffusion_include_bp_admin_css() {
	if (function_exists('bp_is_group')) {
		wp_enqueue_style('bp-admin-bar', apply_filters('bp_core_admin_bar_css', WP_PLUGIN_URL.'/buddypress/bp-themes/bp-default/_inc/css/adminbar.css'), array(), null);
	}
}

function suffusion_pad_signup_form_start() {
?>
<div id="main-col">
<?php
}

function suffusion_pad_signup_form_end() {
?>
</div><!-- #main-col -->
<?php
}

function suffusion_register_custom_types() {
	global $suffusion_post_type_labels, $suffusion_post_type_args, $suffusion_post_type_supports, $suffusion_taxonomy_labels, $suffusion_taxonomy_args;
	$suffusion_post_types = get_option('suffusion_post_types');
	$suffusion_taxonomies = get_option('suffusion_taxonomies');
	if (is_array($suffusion_post_types)) {
		foreach ($suffusion_post_types as $id => $suffusion_post_type) {
			$args = array();
			$labels = array();
			$supports = array();
			foreach ($suffusion_post_type_labels as $label) {
				if (isset($suffusion_post_type['labels'][$label['name']]) && $suffusion_post_type['labels'][$label['name']] != '') {
					$labels[$label['name']] = $suffusion_post_type['labels'][$label['name']];
				}
			}
			foreach ($suffusion_post_type_supports as $support) {
				if (isset($suffusion_post_type['supports'][$support['name']])) {
					if ($suffusion_post_type['supports'][$support['name']] == '1') {
						$supports[] = $support['name'];
					}
				}
			}
			foreach ($suffusion_post_type_args as $arg) {
				if (isset($suffusion_post_type['args'][$arg['name']])) {
					if ($arg['type'] == 'checkbox' && $suffusion_post_type['args'][$arg['name']] == '1') {
						$args[$arg['name']] = true;
					}
					else if ($arg['type'] != 'checkbox') {
						$args[$arg['name']] = $suffusion_post_type['args'][$arg['name']];
					}
				}
			}
			$args['labels'] = $labels;
			$args['supports'] = $supports;
			register_post_type($suffusion_post_type['post_type'], $args);
		}
	}

	if (is_array($suffusion_taxonomies)) {
		foreach ($suffusion_taxonomies as $id => $suffusion_taxonomy) {
			$labels = array();
			$args = array();
			foreach ($suffusion_taxonomy_labels as $label) {
				if (isset($suffusion_taxonomy['labels'][$label['name']]) && $suffusion_taxonomy['labels'][$label['name']] != '') {
					$labels[$label['name']] = $suffusion_taxonomy['labels'][$label['name']];
				}
			}
			foreach ($suffusion_taxonomy_args as $arg) {
				if (isset($suffusion_taxonomy['args'][$arg['name']])) {
					if ($arg['type'] == 'checkbox' && $suffusion_taxonomy['args'][$arg['name']] == '1') {
						$args[$arg['name']] = true;
					}
					else if ($arg['type'] != 'checkbox') {
						$args[$arg['name']] = $suffusion_taxonomy['args'][$arg['name']];
					}
				}
			}
			$args['labels'] = $labels;
			$object_type_str = $suffusion_taxonomy['object_type'];
			$object_type_array = explode(',',$object_type_str);
			$object_types = array();
			foreach ($object_type_array as $object_type) {
				if (post_type_exists(trim($object_type))) {
					$object_types[] = trim($object_type);
				}
			}
			register_taxonomy($suffusion_taxonomy['taxonomy'], $object_types, $args);
		}
	}
}

function suffusion_display_widgets_above_header() {
	$display = apply_filters('suffusion_can_display_widgets_above_header', true);
	if (!$display) {
		return;
	}
	if (!suffusion_is_sidebar_empty(11)) {
?>
		<!-- #widgets-above-header -->
		<div id="widgets-above-header" class='warea fix'>
			<div class='col-control'>
<?php
		dynamic_sidebar('Widgets Above Header');
?>
			</div>
		</div>
		<!-- /#widgets-above-header -->
<?php
	}
}

function suffusion_display_widgets_in_header() {
	if (!suffusion_is_sidebar_empty(12)) {
?>
		<!-- #header-widgets -->
		<div id="header-widgets" class="warea">
<?php
		dynamic_sidebar('Header Widgets');
?>
		</div>
		<!-- /#header-widgets -->
<?php
	}
}

/**
 * Function to support meus from the Menu dashboard.
 * Strictly speaking this is not required. You could select these same menus from the Main Navigation Bar Setup or Top Navigation Bar Setup.
 *
 * @return void
 */
function suffusion_register_menus() {
	register_nav_menu('top', 'Top Navigation Bar');
	register_nav_menu('main', 'Main Navigation Bar');
}

function suffusion_include_google_translate_js() {
    if (!is_admin() && is_active_widget('Suffusion_Google_Translator', false, 'suf-google-translator', true)) {
	    // For some reason the translation widget fails if we load the JS in the header. Hence we are overriding the header/footer JS setting
	    wp_register_script('google-translate', 'http://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit', array(), null, true);
	    wp_enqueue_script('google-translate');
    }
}

function suffusion_should_include_dbx() {
	global $suffusion, $suf_sidebar_1_dnd, $suf_sidebar_2_dnd;
	if (!isset($suffusion) || is_null($suffusion)) {
		$suffusion = new Suffusion();
	}
	$context = $suffusion->get_context();

	$left_count = suffusion_get_left_sidebar_count($context);
	$right_count = suffusion_get_right_sidebar_count($context);
	$total_count = $left_count + $right_count;
	if ($total_count == 0 || ($total_count == 1 && $suf_sidebar_1_dnd != "enabled") || ($total_count == 2 && $suf_sidebar_1_dnd != "enabled" && $suf_sidebar_2_dnd != "enabled")) {
		return false;
	}
	else {
		return true;
	}
}

function suffusion_include_bp_js() {
	global $suf_js_in_footer;
	$footer = $suf_js_in_footer == 'footer' ? true : false;

	if (!is_admin() && function_exists('bp_is_group')) {
		wp_enqueue_script('suffusion-bp-ajax-js', WP_PLUGIN_URL . '/buddypress/bp-themes/bp-default/_inc/global.js', array('jquery'), null, $footer);
	}
}

function suffusion_meta_pullout() {
	global $post, $suf_post_meta_position, $suf_page_meta_position;
	if ($suf_post_meta_position != 'corners' && ((!is_singular() && $post->post_type != 'page') || (is_singular() && !is_page()))) {
		suffusion_meta_pullout_for_post();
	}

	if ($suf_page_meta_position != 'corners' && (is_page() || (!is_singular() && $post->post_type == 'page'))) {
		suffusion_meta_pullout_for_page();
	}
}

function suffusion_meta_pullout_for_post() {
	global $post, $suf_post_meta_position, $suf_post_show_cats, $suf_post_show_posted_by, $suf_post_show_tags, $suf_date_box_show, $suf_post_show_comment;
	echo "<div class='meta-pullout meta-$suf_post_meta_position'>\n";
	echo "<ul>\n";

	if ($suf_date_box_show != 'hide' || ($suf_date_box_show == 'hide-search' && !is_search())) {
		echo "<li><span class='pullout-date'>".get_the_time(get_option('date_format'))."</span></li>\n";
	}

	if ($suf_post_show_posted_by != 'hide') {
		echo "<li>";
		suffusion_print_author_byline();
		echo "</li>\n";
	}

	if ($suf_post_show_comment != 'hide') {
		if ('open' == $post->comment_status && is_singular()) {
			echo "<li><span class='comments'><a href='#respond'>".__('Add comments', 'suffusion')."</a></span></li>\n";
		}
		else {
			echo "<li><span class='comments'>";
			comments_popup_link(__('No Responses', 'suffusion'), __('1 Response', 'suffusion'), __('% Responses', 'suffusion'));
			echo "</span></li>\n";
		}
	}

	if ($suf_post_show_cats != 'hide') {
		$categories = get_the_category($post->ID);
		if ($categories) {
			echo "<li><span class='category'>";
			the_category(', ');
			echo "</span></li>\n";
		}
	}

	if ($suf_post_show_tags != 'hide') {
		$tags = get_the_tags($post->ID);
		if ($tags != '') {
			echo "<li><span class='tags'>";
			the_tags(__('Tagged with: ', 'suffusion'),', ');
			echo "</span></li>\n";
		}
	}

	if (is_singular() && get_edit_post_link() != '') {
		echo "<li><span class='edit'>";
?>
		   <?php edit_post_link(__('Edit', 'suffusion'), '', ''); ?>
<?php
		echo "</span></li>\n";
	}

	echo "</div>\n";
}

function suffusion_meta_pullout_for_page() {
	global $post, $suf_page_meta_position, $suf_page_show_posted_by, $suf_page_show_comment;
	echo "<div class='meta-pullout meta-$suf_page_meta_position'>\n";
	echo "<ul>\n";

	if ($suf_page_show_posted_by != 'hide') {
		echo "<li>";
		suffusion_print_author_byline();
		echo "</li>\n";
	}

	if ($suf_page_show_comment != 'hide') {
		if ('open' == $post->comment_status) {
			echo "<li><span class='comments'><a href='#respond'>".__('Add comments', 'suffusion')."</a></span></li>\n";
		}
	}

	if (get_edit_post_link() != '') {
		echo "<li><span class='edit'>";
?>
		   <?php edit_post_link(__('Edit', 'suffusion'), '', ''); ?>
<?php
		echo "</span></li>\n";
	}

	echo "</div>\n";
}
?>