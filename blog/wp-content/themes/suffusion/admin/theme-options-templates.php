<?php
/**
 * Lists out all the options from the Templates Section of the theme options
 * This file is included in functions.php
 *
 * @package Suffusion
 * @subpackage Admin
 */

global $suffusion_404_title, $suffusion_404_content, $suffusion_all_sitemap_entities, $suffusion_sitemap_entities;
$suffusion_templates_options = array(
	array("name" => "Templates",
		"type" => "sub-section-2",
		"category" => "templates",
		"help" => "Configure how you want to use the templates bundled with Suffusion. Suffusion is packaged with the following templates:
			<table>
				<tr>
					<th>Automatic Templates</th>
				</tr>
				<tr>
					<td>These get attached to content automatically and cannot be assigned manually.</td>
				</tr>
				<tr>
					<td><em>Single Category</em> - Assigned whenever you are viewing a category</td>
				</tr>
				<tr>
					<td><em>Single Tag</em> - Assigned whenever you are viewing a tag</td>
				</tr>
				<tr>
					<td><em>Single Author</em> - Assigned whenever you are viewing an author</td>
				</tr>
				<tr>
					<td><em>Search</em> - Assigned whenever you are viewing a search page</td>
				</tr>
				<tr>
					<td><em>Attachments</em> - Assigned whenever you are viewing an attachment</td>
				</tr>
				<tr>
					<td><em>404</em> - Assigned whenever you are viewing a 404 (not found) page</td>
				</tr>
				<tr>
					<td><em>Now Reading</em> - Assigned if you have the \"Now Reading\" or \"Now Reading Reloaded\" plugin</td>
				</tr>

				<tr>
					<th>Manual Templates</th>
				</tr>
				<tr>
					<td>These can be assigned to a page from the \"Templates\" section of the \"Edit Page\" screen. You cannot use these templates
					for dynamic pages like the posts page assigned through <i>Settings &rarr; Reading</i>.</td>
				</tr>
				<tr>
					<td><em>Magazine</em> - Builds a magazine-styled page</td>
				</tr>
				<tr>
					<td><em>Page of Posts</em> - Shows a list of all your posts</td>
				</tr>
				<tr>
					<td><em>All Categories</em> - Shows all your categories</td>
				</tr>
				<tr>
					<td><em>Sitemap</em> - Creates an HTML sitemap for your site</td>
				</tr>
				<tr>
					<td><em>No Sidebars</em></td>
				</tr>
				<tr>
					<td><em>Single Left Sidebar</em></td>
				</tr>
				<tr>
					<td><em>Single Right Sidebar</em></td>
				</tr>
				<tr>
					<td><em>Single Left, Single Right Sidebar</em></td>
				</tr>
				<tr>
					<td><em>Double Left Sidebars</em></td>
				</tr>
				<tr>
					<td><em>Double Right Sidebars</em></td>
				</tr>
			</table>",
		"parent" => "root"
	),

	array("name" => "Magazine",
		"type" => "sub-section-3",
		"category" => "magazine-template",
		"parent" => "templates"
	),

	array("name" => "The \"Magazine\" template",
		"desc" => "The magazine template can be used as the landing page for magazine-style blogs. It displays featured content, headlines, excerpts and categories.
				Suffusion natively supports the <a href='http://wordpress.org/extend/plugins/category-icons/'>\"Category Icons\" plugin by Brahim Machkouri</a>.
				So if you have the plugin installed, the icon for the category will be automatically displayed for the categories in the magazine view. ",
		"parent" => "magazine-template",
		"type" => "blurb"
	),

	array("name" => "Order of entities in magazine template",
		"desc" => "You can define the order in which featured posts, headlines, excerpts and categories show up on the magazine template: ",
		"id" => "suf_mag_entity_order",
		"parent" => "magazine-template",
		"type" => "sortable-list",
		"std" => suffusion_entity_prepositions('mag-layout')),

	array("name" => "Enable Featured Posts on magazine template",
		"desc" => "You can enable the Featured Posts slider on your magazine template: ",
		"id" => "suf_mag_featured_enabled",
		"parent" => "magazine-template",
		"type" => "radio",
		"options" => array("enabled" => "Enabled", "disabled" => "Disabled"),
		"std" => "enabled"),

	array("name" => "Show main page content for the magazine template",
		"desc" => "You can show the content of the page on the magazine template. You can use this as an introduction to the page. By default it is hidden: ",
		"id" => "suf_mag_content_enabled",
		"parent" => "magazine-template",
		"type" => "radio",
		"options" => array("show" => "Show", "hide" => "Hide"),
		"std" => "hide"),

	array("name" => "Headlines",
		"desc" => "Control the headlines in the magazine layout",
		"category" => "mag-head",
		"parent" => "magazine-template",
		"type" => "sub-section-4",),

	array("name" => "Show headlines for the magazine template",
		"desc" => "You can show a section for headlines on the magazine template. Headlines can be displayed for all posts in a selected category by selecting from the list below.
			Additionally you can set individual posts up as headlines from the addtional options below each post: ",
		"id" => "suf_mag_headlines_enabled",
		"grouping" => "mag-head",
		"parent" => "magazine-template",
		"type" => "radio",
		"options" => array("show" => "Show headlines", "hide" => "Hide headlines"),
		"std" => "show"),

	array("name" => "Magazine Template - Main Title for headlines section",
		"desc" => "You can set the main title of the headline section: ",
		"id" => "suf_mag_headline_title",
		"grouping" => "mag-head",
		"parent" => "magazine-template",
		"type" => "text",
		"hint" => "Enter the title here (leave blank if you don't want a title).",
		"std" => 'Headlines'),

	array("name" => "Magazine Template - Main title alignment for headlines section",
		"desc" => "Where do you want your main title for the headlines section positioned? ",
		"id" => "suf_mag_headline_main_title_alignment",
		"grouping" => "mag-head",
		"parent" => "magazine-template",
		"type" => "radio",
		"options" => array("left" => "Left", "center" => "Center", "right" => "Right"),
		"std" => "left"),

	array("name" => "Magazine template - Height of headline section",
		"desc" => "You can set the height of the headline section here. Choose a larger number if you have more headlines: ",
		"id" => "suf_mag_headlines_height",
		"grouping" => "mag-head",
		"parent" => "magazine-template",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored.",
		"std" => "250"),

	array("name" => "Magazine template - Width of headline image box",
		"desc" => "You can set the width of the headline image section here. The image will be put inside a container of this width: ",
		"id" => "suf_mag_headline_image_container_width",
		"grouping" => "mag-head",
		"parent" => "magazine-template",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored.",
		"std" => "225"),

	array("name" => "Image preference order",
		"desc" => "You can change the order of preference for picking up images. If an image is not found for your first preference, the next one is looked for: ",
		"id" => "suf_mag_headline_img_pref",
		"grouping" => "mag-head",
		"parent" => "magazine-template",
		"type" => "sortable-list",
		"std" => suffusion_entity_prepositions('thumb-mag-headline')),

	array("name" => "Magazine template - Headline image scaling",
		"desc" => "Your can set a custom size for your headline images, or let the size be the same as that of the excerpt images: ",
		"id" => "suf_mag_headline_image_size",
		"grouping" => "mag-head",
		"parent" => "magazine-template",
		"type" => "radio",
		"options" => array("excerpt" => "Same size as excerpt images", "custom" => "Custom size (defined below)"),
		"std" => "excerpt"),

	array("name" => "Magazine template - Custom Height of headline image",
		"desc" => "If you have picked a custom size for the headline images above, you can set the height here: ",
		"id" => "suf_mag_headline_image_custom_height",
		"grouping" => "mag-head",
		"parent" => "magazine-template",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored.",
		"std" => "200"),

	array("name" => "Magazine template - Custom Width of headline image",
		"desc" => "If you have picked a custom size for the headline images above, you can set the width here: ",
		"id" => "suf_mag_headline_image_custom_width",
		"grouping" => "mag-head",
		"parent" => "magazine-template",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored.",
		"std" => "200"),

	array("name" => "Proportional resizing",
		"desc" => "If you are resizing an image 400x200 px to 250x150, the resizing is disproportionate. How do you want to handle the resize in such a scenario?",
		"id" => "suf_mag_headline_zc",
		"grouping" => "mag-head",
		"parent" => "magazine-template",
		"type" => "radio",
		"options" => array("default" => "Inherit setting from thumbnail (<em>Other Graphical Elements &rarr; Layout: Excerpt / List / Tile / Full &rarr; Thumbnail settings</em>)",
			"0" => "Preserve original proportions (final size might be different from desired size)",
			"1" => "Transform to desired proportions (image might get cropped)"),
		"std" => "default"),

	array("name" => "Magazine template - Select categories for headlines",
		"desc" => "You can pick categories to include in the headlines section. All posts in the selected categories will be shown.
			By default no category is selected: ",
		"id" => "suf_mag_headline_categories",
		"grouping" => "mag-head",
		"parent" => "magazine-template",
		"export" => "ne",
		"type" => "multi-select",
		"options" => suffusion_get_formatted_category_array("suf_mag_headline_categories")),

	array("name" => "Number of headlines",
		"desc" => "Enter the maximum number of headlines: ",
		"id" => "suf_mag_headline_limit",
		"grouping" => "mag-head",
		"parent" => "magazine-template",
		"type" => "text",
		"hint" => "Enter the number here. Please enter positive numeric values only",
		"std" => 10),

	array("name" => "Excerpts",
		"desc" => "Control the excerpts in the magazine layout",
		"category" => "mag-excerpt",
		"parent" => "magazine-template",
		"type" => "sub-section-4",),

	array("name" => "Show an excerpts section for the magazine template",
		"desc" => "You can show a section with specific excerpts: ",
		"id" => "suf_mag_excerpts_enabled",
		"grouping" => "mag-excerpt",
		"parent" => "magazine-template",
		"type" => "radio",
		"options" => array("show" => "Show excerpts", "hide" => "Hide excerpts"),
		"std" => "show"),

	array("name" => "Magazine Template - Main Title for excerpts section",
		"desc" => "You can set the main title of the excerpts section: ",
		"id" => "suf_mag_excerpts_title",
		"grouping" => "mag-excerpt",
		"parent" => "magazine-template",
		"type" => "text",
		"hint" => "Enter the title here (leave blank if you don't want a title).",
		"std" => 'Other Big Stories'),

	array("name" => "Magazine Template - Main title alignment for excerpts section",
		"desc" => "Where do you want your main title for the excerpts section positioned? ",
		"id" => "suf_mag_excerpts_main_title_alignment",
		"grouping" => "mag-excerpt",
		"parent" => "magazine-template",
		"type" => "radio",
		"options" => array("left" => "Left", "center" => "Center", "right" => "Right"),
		"std" => "left"),

	array("name" => "Magazine Template - Maximum number of excerpts",
		"desc" => "You can set the maximum number of excerpts to show: ",
		"id" => "suf_mag_total_excerpts",
		"grouping" => "mag-excerpt",
		"parent" => "magazine-template",
		"type" => "text",
		"hint" => "Enter a positive numeric value only",
		"std" => get_option('posts_per_page')),

	array("name" => "Magazine Template - Maximum Number of excerpts per row",
		"desc" => "You can define how many excerpts you want to show per row: ",
		"id" => "suf_mag_excerpts_per_row",
		"grouping" => "mag-excerpt",
		"parent" => "magazine-template",
		"type" => "radio",
		"options" => array("1" => "1 (One)", "2" => "2 (Two)", "3" => "3 (Three)", "4" => "4 (Four)", "5" => "5 (Five)", "6" => "6 (Six)", "7" => "7 (Seven)",
			"8" => "8 (Eight)", "9" => "9 (Nine)", "10" => "10 (Ten)"),
		"std" => "3"),

	array("name" => "Magazine template - Select categories for excerpts",
		"desc" => "You can pick categories to include in the headlines section. All posts in the selected categories will be shown.
			By default no category is selected: ",
		"id" => "suf_mag_excerpt_categories",
		"grouping" => "mag-excerpt",
		"parent" => "magazine-template",
		"export" => "ne",
		"type" => "multi-select",
		"options" => suffusion_get_formatted_category_array("suf_mag_excerpt_categories")),

	array("name" => "Magazine Template - Thumbnail container for excerpts",
		"desc" => "You can show thumbnails for excerpts in the magazine template: ",
		"id" => "suf_mag_excerpts_images_enabled",
		"grouping" => "mag-excerpt",
		"parent" => "magazine-template",
		"type" => "radio",
		"options" => array("show" => "Always show Thumbnail container", "hide" => "Always hide Thumbnail container",
			"hide-empty" => "Hide Thumbnail container if there is no thumbnail"),
		"std" => "show"),

	array("name" => "Magazine Template - Thumbnail container height for excerpts",
		"desc" => "For the purposes of visual consistency you can set the height of the box in which the thumbnail will be placed. Your thumbnail will be \"cropped\" to this height: ",
		"id" => "suf_mag_excerpts_image_box_height",
		"grouping" => "mag-excerpt",
		"parent" => "magazine-template",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored.",
		"std" => "100"),

	array("name" => "Image preference order",
		"desc" => "You can change the order of preference for picking up images. If an image is not found for your first preference, the next one is looked for: ",
		"id" => "suf_mag_excerpt_img_pref",
		"grouping" => "mag-excerpt",
		"parent" => "magazine-template",
		"type" => "sortable-list",
		"std" => suffusion_entity_prepositions('thumb-mag-excerpt')),

	array("name" => "Magazine template - Excerpt thumbnail image scaling",
		"desc" => "You can set a custom size for your excerpt thumbnail images, or let the size be the same as that of the regular excerpt images: ",
		"id" => "suf_mag_excerpt_image_size",
		"grouping" => "mag-excerpt",
		"parent" => "magazine-template",
		"type" => "radio",
		"options" => array("excerpt" => "Same size as excerpt images", "custom" => "Custom size (defined below)"),
		"std" => "excerpt"),

	array("name" => "Magazine template - Custom height of thumbnail image in excerpts",
		"desc" => "If you have picked a custom size for the excerpt thumbnail images above, you can set the height here: ",
		"id" => "suf_mag_excerpt_image_custom_height",
		"grouping" => "mag-excerpt",
		"parent" => "magazine-template",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored.",
		"std" => "200"),

	array("name" => "Magazine template - Custom width of thumbnail image in excerpts",
		"desc" => "If you have picked a custom size for the excerpt thumbnail images above, you can set the width here: ",
		"id" => "suf_mag_excerpt_image_custom_width",
		"grouping" => "mag-excerpt",
		"parent" => "magazine-template",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored.",
		"std" => "200"),

	array("name" => "Proportional resizing",
		"desc" => "If you are resizing an image 400x200 px to 250x150, the resizing is disproportionate. How do you want to handle the resize in such a scenario?",
		"id" => "suf_mag_excerpt_zc",
		"grouping" => "mag-excerpt",
		"parent" => "magazine-template",
		"type" => "radio",
		"options" => array("default" => "Inherit setting from thumbnail (<em>Other Graphical Elements &rarr; Layout: Excerpt / List / Tile / Full &rarr; Thumbnail settings</em>)",
			"0" => "Preserve original proportions (final size might be different from desired size)",
			"1" => "Transform to desired proportions (image might get cropped)"),
		"std" => "default"),

	array("name" => "Magazine template - Alignment of post title in Excerpts",
		"desc" => "You can set the alignment for the post title in the excerpts: ",
		"id" => "suf_mag_excerpt_title_alignment",
		"grouping" => "mag-excerpt",
		"parent" => "magazine-template",
		"type" => "radio",
		"options" => array("left" => "Left", "center" => "Center", "right" => "Right"),
		"std" => "left"),

	array("name" => "Magazine Template - Text for \"Full story\" in excerpts",
		"desc" => "You can set the text to show for the \"Full story\" link in excerpts: ",
		"id" => "suf_mag_excerpt_full_story_text",
		"grouping" => "mag-excerpt",
		"parent" => "magazine-template",
		"type" => "text",
		"hint" => "Enter the text here (leave blank for no link)",
		"std" => 'Full Story'),

	array("name" => "Category Blocks",
		"desc" => "Control the category blocks in the magazine layout",
		"category" => "mag-cat",
		"parent" => "magazine-template",
		"type" => "sub-section-4",),

	array("name" => "Show a categories section for the magazine template",
		"desc" => "You can show a section with lists of posts from specific categories: ",
		"id" => "suf_mag_categories_enabled",
		"grouping" => "mag-cat",
		"parent" => "magazine-template",
		"type" => "radio",
		"options" => array("show" => "Show categories", "hide" => "Hide categories"),
		"std" => "show"),

	array("name" => "Magazine Template - Main Title for categories section",
		"desc" => "You can set the main title for the categories section here: ",
		"id" => "suf_mag_catblocks_title",
		"grouping" => "mag-cat",
		"parent" => "magazine-template",
		"type" => "text",
		"hint" => "Enter the title here (leave blank if you don't want a title).",
		"std" => 'Other Stories'),

	array("name" => "Magazine Template - Main title alignment for categories section",
		"desc" => "Where do you want your main title for the categories section positioned? ",
		"id" => "suf_mag_catblocks_main_title_alignment",
		"grouping" => "mag-cat",
		"parent" => "magazine-template",
		"type" => "radio",
		"options" => array("left" => "Left", "center" => "Center", "right" => "Right"),
		"std" => "left"),

	array("name" => "Magazine template - Select category blocks to show",
		"desc" => "You can also show specific catagory blocks on the magazine template. A category block can include a category icon, the category description and some post titles.
			By default no category is selected: ",
		"id" => "suf_mag_catblock_categories",
		"grouping" => "mag-cat",
		"parent" => "magazine-template",
		"export" => "ne",
		"type" => "multi-select",
		"options" => suffusion_get_formatted_category_array("suf_mag_catblock_categories")),

	array("name" => "Magazine Template - Maximum Number of category blocks per row",
		"desc" => "You can define how many category blocks you want to show per row: ",
		"id" => "suf_mag_catblocks_per_row",
		"grouping" => "mag-cat",
		"parent" => "magazine-template",
		"type" => "radio",
		"options" => array("1" => "1 (One)", "2" => "2 (Two)", "3" => "3 (Three)", "4" => "4 (Four)", "5" => "5 (Five)", "6" => "6 (Six)", "7" => "7 (Seven)",
			"8" => "8 (Eight)", "9" => "9 (Nine)", "10" => "10 (Ten)"),
		"std" => "3"),

	array("name" => "Magazine Template - Category title alignment for each Category Block",
		"desc" => "Where do you want your category title positioned for each category block? ",
		"id" => "suf_mag_catblocks_title_alignment",
		"grouping" => "mag-cat",
		"parent" => "magazine-template",
		"type" => "radio",
		"options" => array("left" => "Left", "center" => "Center", "right" => "Right"),
		"std" => "left"),

	array("name" => "Magazine Template - Images for category blocks",
		"desc" => "If  you have the <a href='http://wordpress.org/extend/plugins/category-icons/'>\"Category Icons\" plugin by Brahim Machkouri</a> you can include an image at the top of each category block: ",
		"id" => "suf_mag_catblocks_images_enabled",
		"grouping" => "mag-cat",
		"parent" => "magazine-template",
		"type" => "radio",
		"options" => array("show" => "Always show Category Icons container", "hide" => "Always hide Category Icons container",
			"hide-empty" => "Hide Category Icons container if there is no icon"),
		"std" => "hide"),

	array("name" => "Magazine Template - Image container height for category blocks",
		"desc" => "For the purposes of visual consistency you can set the height of the box in which the category image will be placed. Your icon will be \"cropped\" to this height: ",
		"id" => "suf_mag_catblocks_image_box_height",
		"grouping" => "mag-cat",
		"parent" => "magazine-template",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored.",
		"std" => "100"),

	array("name" => "Magazine Template - Description for category blocks",
		"desc" => "You can show your category's description in each category block: ",
		"id" => "suf_mag_catblocks_desc_enabled",
		"grouping" => "mag-cat",
		"parent" => "magazine-template",
		"type" => "radio",
		"options" => array("show" => "Show Description", "hide" => "Hide Description"),
		"std" => "hide"),

	array("name" => "Magazine Template - Latest posts in category blocks",
		"desc" => "You can show your category's latest posts in each category block: ",
		"id" => "suf_mag_catblocks_posts_enabled",
		"grouping" => "mag-cat",
		"parent" => "magazine-template",
		"type" => "radio",
		"options" => array("show" => "Show Posts", "hide" => "Hide Posts"),
		"std" => "show"),

	array("name" => "Magazine Template - Maximum Number of posts in category blocks",
		"desc" => "You can the number of posts you want listed in each category block. By default this is set to 5: ",
		"id" => "suf_mag_catblocks_num_posts",
		"grouping" => "mag-cat",
		"parent" => "magazine-template",
		"type" => "text",
		"hint" => "Enter the number here. Enter -1 to show all posts.",
		"std" => '5'),

	array("name" => "Magazine Template - Text for \"See all posts\" in category blocks",
		"desc" => "You can set the text to show for the \"See all posts\" link in category blocks: ",
		"id" => "suf_mag_catblocks_see_all_text",
		"grouping" => "mag-cat",
		"parent" => "magazine-template",
		"type" => "text",
		"hint" => "Enter the text here (leave blank for no link)",
		"std" => 'See all posts'),

	array("name" => "Magazine Template - Post display style in category blocks",
		"desc" => "You can decide if you want to show your posts in the category blocks in a magazine-style (boxed) or in a sidebar-style (underlined): ",
		"id" => "suf_mag_catblocks_post_style",
		"grouping" => "mag-cat",
		"parent" => "magazine-template",
		"type" => "radio",
		"options" => array("magazine" => "Magazine Style (posts highlight with a box around them when you hover)", "sidebar" => "Sidebar Style (posts are underlined when you hover over them)"),
		"std" => 'magazine'),

	array("name" => "Page of Posts",
		"type" => "sub-section-3",
		"category" => "pop-template",
		"parent" => "templates"
	),

	array("name" => "The \"Page of Posts\" template",
		"desc" => "This template can be used to create a page of posts. This will follow the default sidebar layout for the theme.",
		"category" => "pop-settings",
		"parent" => "pop-template",
		"type" => "sub-section-4"
	),

	array("name" => "Layout Settings for the \"Page of Posts\" template",
		"desc" => "By default for all posts on this template, the complete contents are displayed.",
		"id" => "suf_pop_excerpt",
		"parent" => "pop-template",
		"grouping" => "pop-settings",
		"type" => "radio",
		"options" => array("content" => "Display full content", "excerpt" => "Display excerpt", "list" => "Display list", "tiles" => 'Display tiles'),
		"std" => "content"),

	array("name" => "Number of Full content posts on \"Page of Posts\" template",
		"desc" => "In the Excerpt, List and Tile display you can choose to show the first few posts with full content. Set the number of posts for which you want the full content displayed (ignored if you select full content above): ",
		"id" => "suf_pop_fc_number",
		"parent" => "pop-template",
		"grouping" => "pop-settings",
		"type" => "select",
		"options" => array("0" => "0 (Zero)", "1" => "1 (One)", "2" => "2 (Two)", "3" => "3 (Three)", "4" => "4 (Four)",
			"5" => "5 (Five)", "6" => "6 (Six)", "7" => "7 (Seven)", "8" => "8 (Eight)", "9" => "9 (Nine)", "10" => "10 (Ten)"),
		"std" => "0"),

	array("name" => "Single Category",
		"type" => "sub-section-3",
		"category" => "category-template",
		"parent" => "templates"
	),

	array("name" => "The \"Category\" template",
		"desc" => "The category template is applied whenever you open a category. It displays all posts associated with a category.
				Suffusion natively supports the <a href='http://wordpress.org/extend/plugins/category-icons/'>\"Category Icons\" plugin by Brahim Machkouri</a>.
				So if you have the plugin installed, the icon for the category will be automatically displayed. ",
		"parent" => "category-template",
		"type" => "blurb"
	),

	array("name" => "Enable Category Introduction?",
		"desc" => "By default the name of the category and its description are not shown on a category page. You can change it: ",
		"id" => "suf_cat_info_enabled",
		"parent" => "category-template",
		"type" => "radio",
		"options" => array("enabled" => "Category Information enabled",
			"not-enabled" => "Category Information not enabled (default)"),
		"std" => "not-enabled"),

	array("name" => "All Categories",
		"type" => "sub-section-3",
		"category" => "categories-template",
		"parent" => "templates"
	),

	array("name" => "The \"All Categories\" template",
		"desc" => "The \"All Categories\" template can be used if you want to list out all your categories on a single page.
				You can additionally decide to show the categories hierarchically, or show the RSS feed for each category or the number of posts in each category.",
		"parent" => "categories-template",
		"type" => "blurb"
	),

	array("name" => "List categories hierarchically?",
		"desc" => "You can decide if you want to list your categories in a hierarchical manner: ",
		"id" => "suf_temp_cats_hierarchical",
		"parent" => "categories-template",
		"type" => "radio",
		"options" => array("hierarchical" => "Categories listed hierarchically",
			"flat" => "Categories listed flat"),
		"std" => "hierarchical"),

	array("name" => "Show RSS feeds for each category?",
		"desc" => "You can display a link to an RSS feed for each category: ",
		"id" => "suf_temp_cats_rss",
		"parent" => "categories-template",
		"type" => "radio",
		"options" => array("show" => "Show RSS feed",
			"hide" => "Hide RSS feed"),
		"std" => "show"),

	array("name" => "Show post count for each category?",
		"desc" => "You can display the number of posts in each category. Categories with 0 posts are excluded: ",
		"id" => "suf_temp_cats_post_count",
		"parent" => "categories-template",
		"type" => "radio",
		"options" => array("show" => "Show Post Count",
			"hide" => "Hide Post Count"),
		"std" => "hide"),

	array("name" => "Single Tag",
		"type" => "sub-section-3",
		"category" => "tag-template",
		"parent" => "templates"
	),

	array("name" => "The \"Tag\" template",
		"desc" => "The tag template is applied whenever you open a tag. It displays all posts with a particular tag. ",
		"parent" => "tag-template",
		"type" => "blurb"
	),

	array("name" => "Show Tag Description",
		"desc" => "By default the name of the Tag and its description are not shown on a tag page. You can change it: ",
		"id" => "suf_tag_info_enabled",
		"parent" => "tag-template",
		"type" => "radio",
		"options" => array("enabled" => "Tag Description enabled",
			"not-enabled" => "Tag Description not enabled (default)"),
		"std" => "not-enabled"),

	array("name" => "Single Author",
		"type" => "sub-section-3",
		"category" => "author-template",
		"parent" => "templates"
	),

	array("name" => "The single \"Author\" template",
		"desc" => "The author template is applied whenever you open an author page. It displays all posts associated with a category.",
		"parent" => "author-template",
		"type" => "blurb"
	),

	array("name" => "Enable Author Introduction?",
		"desc" => "By default the name of the author and a the description are shown on an author page. You can change it: ",
		"id" => "suf_author_info_enabled",
		"parent" => "author-template",
		"type" => "radio",
		"options" => array("enabled" => "Author Information enabled (default)",
			"not-enabled" => "Author Information not enabled"),
		"std" => "enabled"),

	array("name" => "Search ",
		"type" => "sub-section-3",
		"category" => "search-template",
		"parent" => "templates"
	),

	array("name" => "The Search template",
		"desc" => "The search template is applied whenever you get search results.",
		"parent" => "search-template",
		"type" => "blurb"
	),

	array("name" => "Enable Search introduction?",
		"desc" => "This shows an introductory section with a search query and some options.
			Note that in some cases the search text highlighting doesn't work if the JS is in the footer, set in Blog Features &rarr; Site Optimization: ",
		"id" => "suf_search_info_enabled",
		"parent" => "search-template",
		"type" => "radio",
		"options" => array("enabled" => "Search Information enabled (default)",
			"not-enabled" => "Search Information not enabled"),
		"std" => "enabled"),

	array("name" => "Attachments",
		"type" => "sub-section-3",
		"category" => "att-template",
		"parent" => "templates"
	),

	array("name" => "Image Attachments",
		"desc" => "This is an inbuilt WP template that gets assigned when a user clicks an image attachment in a post.",
		"category" => "image-settings",
		"parent" => "att-template",
		"type" => "sub-section-4"
	),

	array("name" => "Display EXIF data?",
		"desc" => "You can display the EXIF data for your images",
		"id" => "suf_image_show_exif",
		"parent" => "att-template",
		"grouping" => "image-settings",
		"type" => "select",
		"options" => array("show" => "Display EXIF", "hide" => "Hide EXIF data"),
		"std" => "hide"),

	array("name" => "EXIF data to display",
		"desc" => "Select what you want to show for EXIF data",
		"id" => "suf_image_exif_pieces",
		"parent" => "att-template",
		"grouping" => "image-settings",
		"type" => "multi-select",
		"options" => suffusion_get_formatted_options_array("suf_image_exif_pieces", array('file' => 'File Name',
			'width' => 'Width',
			'height' => 'Height',
			'created_timestamp' => "Date taken",
			'copyright' => 'Copyright',
			'credit' => "Credit",
			'title' => "Title",
			'caption' => "Caption",
			'camera' => "Camera",
			'focal_length' => "Focal Length",
			'aperture' => "Aperture",
			'iso' => "ISO",
			'shutter_speed' => "Shutter Speed",
		)),
		"std" => ""),

	array("name" => "Audio Attachments",
		"desc" => "This is an inbuilt WP template that gets assigned when a user clicks an audio attachment in a post.",
		"category" => "audio-settings",
		"parent" => "att-template",
		"type" => "sub-section-4"
	),

	array("name" => "Audio template",
		"desc" => "How do you want the template to show an audio attachment?",
		"id" => "suf_audio_att_type",
		"parent" => "att-template",
		"grouping" => "audio-settings",
		"type" => "select",
		"options" => array("link" => "Display a link", "object" => "Display embedded content"),
		"std" => "link"),

	array("name" => "Height of content",
		"desc" => "You can set the height of the embedded content: ",
		"id" => "suf_audio_att_player_height",
		"grouping" => "audio-settings",
		"parent" => "att-template",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored.",
		"std" => "30"),

	array("name" => "Width of content",
		"desc" => "You can set the width of the embedded content: ",
		"id" => "suf_audio_att_player_width",
		"grouping" => "audio-settings",
		"parent" => "att-template",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored.",
		"std" => "300"),

	array("name" => "Application Attachments",
		"desc" => "This is an inbuilt WP template that gets assigned when a user clicks an application attachment in a post.",
		"category" => "app-settings",
		"parent" => "att-template",
		"type" => "sub-section-4"
	),

	array("name" => "Application template",
		"desc" => "How do you want the template to show an application attachment?",
		"id" => "suf_application_att_type",
		"parent" => "att-template",
		"grouping" => "app-settings",
		"type" => "select",
		"options" => array("link" => "Display a link", "object" => "Display embedded content"),
		"std" => "link"),

	array("name" => "Width of content",
		"desc" => "You can set the width of the embedded content: ",
		"id" => "suf_application_att_player_width",
		"grouping" => "app-settings",
		"parent" => "att-template",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored.",
		"std" => "300"),

	array("name" => "Text Attachments",
		"desc" => "This is an inbuilt WP template that gets assigned when a user clicks a text attachment in a post.",
		"category" => "text-settings",
		"parent" => "att-template",
		"type" => "sub-section-4"
	),

	array("name" => "Text template",
		"desc" => "How do you want the template to show an text attachment?",
		"id" => "suf_text_att_type",
		"parent" => "att-template",
		"grouping" => "text-settings",
		"type" => "select",
		"options" => array("link" => "Display a link", "object" => "Display embedded content"),
		"std" => "link"),

	array("name" => "Width of content",
		"desc" => "You can set the width of the embedded content: ",
		"id" => "suf_text_att_player_width",
		"grouping" => "text-settings",
		"parent" => "att-template",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored.",
		"std" => "300"),

	array("name" => "Video Attachments",
		"desc" => "This is an inbuilt WP template that gets assigned when a user clicks a video attachment in a post.",
		"category" => "video-settings",
		"parent" => "att-template",
		"type" => "sub-section-4"
	),

	array("name" => "Video template",
		"desc" => "How do you want the template to show an video attachment?",
		"id" => "suf_video_att_type",
		"parent" => "att-template",
		"grouping" => "video-settings",
		"type" => "select",
		"options" => array("link" => "Display a link", "object" => "Display embedded content"),
		"std" => "link"),

	array("name" => "Height of content",
		"desc" => "You can set the height of the embedded content: ",
		"id" => "suf_video_att_player_height",
		"grouping" => "video-settings",
		"parent" => "att-template",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored.",
		"std" => "225"),

	array("name" => "Width of content",
		"desc" => "You can set the width of the embedded content: ",
		"id" => "suf_video_att_player_width",
		"grouping" => "video-settings",
		"parent" => "att-template",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored.",
		"std" => "300"),

	array("name" => "Sitemap",
		"type" => "sub-section-3",
		"category" => "sitemap-template",
		"parent" => "templates"
	),

	array("name" => "The Sitemap template",
		"desc" => "The sitemap template can be used to show users a path around your site.",
		"parent" => "sitemap-template",
		"type" => "blurb"
	),

	array("name" => "Contents of Sitemap",
		"desc" => "What do you want to show on your sitemap page?",
		"id" => "suf_sitemap_contents",
		"parent" => "sitemap-template",
		"type" => "multi-select",
		"options" => $suffusion_sitemap_entities,
		'std' => $suffusion_all_sitemap_entities,
	),

	array("name" => "Label for Pages",
		"id" => "suf_sitemap_label_pages",
		"parent" => "sitemap-template",
		"type" => "text",
		"std" => "Pages"),

	array("name" => "Label for Categories",
		"id" => "suf_sitemap_label_categories",
		"parent" => "sitemap-template",
		"type" => "text",
		"std" => "Categories"),

	array("name" => "Label for Authors",
		"id" => "suf_sitemap_label_authors",
		"parent" => "sitemap-template",
		"type" => "text",
		"std" => "Authors"),

	array("name" => "Label for Yearly Archives",
		"id" => "suf_sitemap_label_yarchives",
		"parent" => "sitemap-template",
		"type" => "text",
		"std" => "Yearly Archives"),

	array("name" => "Label for Monthly Archives",
		"id" => "suf_sitemap_label_marchives",
		"parent" => "sitemap-template",
		"type" => "text",
		"std" => "Monthly Archives"),

	array("name" => "Label for Weekly Archives",
		"id" => "suf_sitemap_label_warchives",
		"parent" => "sitemap-template",
		"type" => "text",
		"std" => "Weekly Archives"),

	array("name" => "Label for Daily Archives",
		"id" => "suf_sitemap_label_darchives",
		"parent" => "sitemap-template",
		"type" => "text",
		"std" => "Daily Archives"),

	array("name" => "Label for Tags",
		"id" => "suf_sitemap_label_tags",
		"parent" => "sitemap-template",
		"type" => "text",
		"std" => "Tags"),

	array("name" => "Label for Blog Posts",
		"id" => "suf_sitemap_label_posts",
		"parent" => "sitemap-template",
		"type" => "text",
		"std" => "Blog Posts"),

	array("name" => "Sequence of Sitemap Contents",
		"desc" => "What order do you want your sitemap contents?",
		"id" => "suf_sitemap_entity_order",
		"parent" => "sitemap-template",
		"type" => "sortable-list",
		"std" => suffusion_entity_prepositions('sitemap'),),

	array("name" => "No Sidebars",
		"type" => "sub-section-3",
		"category" => "no-sidebars",
		"parent" => "templates"
	),

	array("name" => "The \"No Sidebars\" template",
		"desc" => "You can use the \"No Sidebars\" template if you have a page where you don't want sidebars to show up, but the rest of your blog has sidebars enabled.
				To set up pages with this template, select the \"No Sidebars\" template while creating or updating a page.",
		"parent" => "no-sidebars",
		"type" => "blurb"
	),

	array("name" => "Enable Widget Area Below Header?",
		"desc" => "By default the \"Widget Area Below Header\" is enabled in the \"No Sidebars\" template. You can change it: ",
		"id" => "suf_ns_wabh_enabled",
		"parent" => "no-sidebars",
		"type" => "radio",
		"options" => array("enabled" => "Widget Area below Header enabled (default)",
			"not-enabled" => "Widget Area below Header not enabled"),
		"std" => "enabled"),

	array("name" => "Enable Widget Area Above Footer?",
		"desc" => "By default the \"Widget Area Above Footer\" is enabled in the \"No Sidebars\" template. You can change it: ",
		"id" => "suf_ns_waaf_enabled",
		"parent" => "no-sidebars",
		"type" => "radio",
		"options" => array("enabled" => "Widget Area above Footer enabled (default)",
			"not-enabled" => "Widget Area above Footer not enabled"),
		"std" => "enabled"),

	array("name" => "Single Left Sidebar",
		"type" => "sub-section-3",
		"category" => "1l-sidebar",
		"parent" => "templates"
	),

	array("name" => "The \"Single Left Sidebar\" template",
		"desc" => "You can use this template if you have a page where you want a single sidebar to show up on the left.
				To set up pages with this template, select the \"Single Left Sidebar\" template while creating or updating a page.",
		"parent" => "1l-sidebar",
		"type" => "blurb"
	),

	array("name" => "Page Width Type",
		"desc" => "Your page can be fixed width or fluid/elastic width",
		"id" => "suf_1l_wrapper_width_type",
		"parent" => "1l-sidebar",
		"type" => "radio",
		"options" => array("fixed" => "Fixed width", "fluid" => "Fluid/Flexible width"),
		"std" => "fixed"),

	array("name" => "Fluid width settings",
		"desc" => "In the fluid width layout your sidebars have a fixed width, while the overall width of your page is a percentage of the browser window's width.",
		"category" => "1l-size-flexible",
		"parent" => "1l-sidebar",
		"type" => "sub-section-4",),

	array("name" => "Width of page",
		"id" => "suf_1l_wrapper_width_flex",
		"parent" => "1l-sidebar",
		"grouping" => "1l-size-flexible",
		"type" => "slider",
		"options" => array("range" => "min", "min" => 25, "max" => 100, "step" => 1, "size" => "400px", "unit" => "%"),
		"std" => 75),

	array("name" => "Maximum width",
		"desc" => "Set this value so that your typography stays consistent on large screens.",
		"id" => "suf_1l_wrapper_width_flex_max",
		"parent" => "1l-sidebar",
		"grouping" => "1l-size-flexible",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Value will be set to a default if blank or incompatible.",
		"std" => "1200"),

	array("name" => "Minimum width",
		"desc" => "Set this value so that your typography stays consistent on small screens.",
		"id" => "suf_1l_wrapper_width_flex_min",
		"parent" => "1l-sidebar",
		"grouping" => "1l-size-flexible",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Value will be set to a default if blank or incompatible.",
		"std" => "600"),

	array("name" => "Fixed width settings",
		"desc" => "In the fixed width layout the components of your page have widths fixed in pixels, irrespective of the size of your browser window.",
		"category" => "1l-size-fixed",
		"parent" => "1l-sidebar",
		"type" => "sub-section-4",),

	array("name" => "Overall Page Width",
		"desc" => "Suffusion comes with 3 preset page width options: 800px, 1000px and 1200px. You can also define a custom width if you please, or allow the width of the page to be determined by the width of its main components like the sidebars and the main content column.
				Due to difficulties with fitting things on the page, the minimum size allowed is 600px. If you enter something less than 600, it is considered to be 600.",
		"id" => "suf_1l_wrapper_width_preset",
		"parent" => "1l-sidebar",
		"grouping" => "1l-size-fixed",
		"type" => "radio",
		"options" => array("800" => "800px", "1000" => "1000px (Default)", "1200" => "1200px",
			"custom" => "Custom width (defined below)", "custom-components" => "Custom width, but constructed from individual components (defined below)"),
		"std" => "1000"),

	array("name" => "Custom value for page width",
		"desc" => "If you have selected \"Custom width\" above, you can set the width here. Please enter the width in pixels. <b>Do not enter \"px\".</b>
				Anything below 600 will be treated as 600. Note that this is a fixed width theme, not a fluid theme. What this means is that you cannot specify things like \"80%\" as the width.
				Also note that if you are setting a width over here with the \"Custom width\" selection in place, the widths of the individual components like the main column, the sidebars etc. are auto-calculated",
		"id" => "suf_1l_wrapper_width",
		"parent" => "1l-sidebar",
		"grouping" => "1l-size-fixed",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored. Incompatible values will be treated as 1000",
		"std" => "1000"),

	array("name" => "Custom component width - Custom value for main column width",
		"desc" => "If you have selected \"Custom width, but constructed from individual components\" above, you can set the width here for the main column.
				Please enter the width in pixels. <b>Do not enter \"px\".</b>
				Anything below 380 will be treated as 380. Note that this is a fixed width theme, not a fluid theme. What this means is that you cannot specify things like \"80%\" as the width. ",
		"id" => "suf_1l_main_col_width",
		"parent" => "1l-sidebar",
		"grouping" => "1l-size-fixed",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored. Incompatible values will be treated as 725",
		"std" => "725"),

	array("name" => "Custom component width - Custom value for width of sidebar",
		"desc" => "If you have selected \"Fluid width\" or \"Custom width, but constructed from individual components\" above, you can set the width here for the first sidebar.
				Please enter the width in pixels. <b>Do not enter \"px\".</b>
				Anything below 95 will be treated as 95. Note that this is a fixed width theme, not a fluid theme. What this means is that you cannot specify things like \"10%\" as the width. ",
		"id" => "suf_1l_sb_1_width",
		"parent" => "1l-sidebar",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored. Incompatible values will be treated as 260",
		"std" => "260"),

	array("name" => "Single Right Sidebar",
		"type" => "sub-section-3",
		"category" => "1r-sidebar",
		"parent" => "templates"
	),

	array("name" => "The \"Single Right Sidebar\" template",
		"desc" => "You can use this template if you have a page where you want a single sidebar to show up on the right.
				To set up pages with this template, select the \"Single Right Sidebar\" template while creating or updating a page.",
		"parent" => "1r-sidebar",
		"type" => "blurb"
	),

	array("name" => "Page Width Type",
		"desc" => "Your page can be fixed width or fluid/elastic width",
		"id" => "suf_1r_wrapper_width_type",
		"parent" => "1r-sidebar",
		"type" => "radio",
		"options" => array("fixed" => "Fixed width", "fluid" => "Fluid/Flexible width"),
		"std" => "fixed"),

	array("name" => "Fluid width settings",
		"desc" => "In the fluid width layout your sidebars have a fixed width, while the overall width of your page is a percentage of the browser window's width.",
		"category" => "1r-size-flexible",
		"parent" => "1r-sidebar",
		"type" => "sub-section-4",),

	array("name" => "Width of page",
		"id" => "suf_1r_wrapper_width_flex",
		"parent" => "1r-sidebar",
		"grouping" => "1r-size-flexible",
		"type" => "slider",
		"options" => array("range" => "min", "min" => 25, "max" => 100, "step" => 1, "size" => "400px", "unit" => "%"),
		"std" => 75),

	array("name" => "Maximum width",
		"desc" => "Set this value so that your typography stays consistent on large screens.",
		"id" => "suf_1r_wrapper_width_flex_max",
		"parent" => "1r-sidebar",
		"grouping" => "1r-size-flexible",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Value will be set to a default if blank or incompatible.",
		"std" => "1200"),

	array("name" => "Minimum width",
		"desc" => "Set this value so that your typography stays consistent on small screens.",
		"id" => "suf_1r_wrapper_width_flex_min",
		"parent" => "1r-sidebar",
		"grouping" => "1r-size-flexible",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Value will be set to a default if blank or incompatible.",
		"std" => "600"),

	array("name" => "Fixed width settings",
		"desc" => "In the fixed width layout the components of your page have widths fixed in pixels, irrespective of the size of your browser window.",
		"category" => "1r-size-fixed",
		"parent" => "1r-sidebar",
		"type" => "sub-section-4",),

	array("name" => "Overall Page Width",
		"desc" => "Suffusion comes with 3 preset page width options: 800px, 1000px and 1200px. You can also define a custom width if you please, or allow the width of the page to be determined by the width of its main components like the sidebars and the main content column.
				Due to difficulties with fitting things on the page, the minimum size allowed is 600px. If you enter something less than 600, it is considered to be 600.",
		"id" => "suf_1r_wrapper_width_preset",
		"parent" => "1r-sidebar",
		"grouping" => "1r-size-fixed",
		"type" => "radio",
		"options" => array("800" => "800px", "1000" => "1000px (Default)", "1200" => "1200px",
			"custom" => "Custom width (defined below)", "custom-components" => "Custom width, but constructed from individual components (defined below)"),
		"std" => "1000"),

	array("name" => "Custom value for page width",
		"desc" => "If you have selected \"Custom width\" above, you can set the width here. Please enter the width in pixels. <b>Do not enter \"px\".</b>
				Anything below 600 will be treated as 600. Note that this is a fixed width theme, not a fluid theme. What this means is that you cannot specify things like \"80%\" as the width.
				Also note that if you are setting a width over here with the \"Custom width\" selection in place, the widths of the individual components like the main column, the sidebars etc. are auto-calculated",
		"id" => "suf_1r_wrapper_width",
		"parent" => "1r-sidebar",
		"grouping" => "1r-size-fixed",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored. Incompatible values will be treated as 1000",
		"std" => "1000"),

	array("name" => "Custom component width - Custom value for main column width",
		"desc" => "If you have selected \"Custom width, but constructed from individual components\" above, you can set the width here for the main column.
				Please enter the width in pixels. <b>Do not enter \"px\".</b>
				Anything below 380 will be treated as 380. Note that this is a fixed width theme, not a fluid theme. What this means is that you cannot specify things like \"80%\" as the width. ",
		"id" => "suf_1r_main_col_width",
		"parent" => "1r-sidebar",
		"grouping" => "1r-size-fixed",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored. Incompatible values will be treated as 725",
		"std" => "725"),

	array("name" => "Custom component width - Custom value for width of sidebar",
		"desc" => "If you have selected \"Fluid width\" or \"Custom width, but constructed from individual components\" above, you can set the width here for the first sidebar.
				Please enter the width in pixels. <b>Do not enter \"px\".</b>
				Anything below 95 will be treated as 95. Note that this is a fixed width theme, not a fluid theme. What this means is that you cannot specify things like \"10%\" as the width. ",
		"id" => "suf_1r_sb_1_width",
		"parent" => "1r-sidebar",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored. Incompatible values will be treated as 260",
		"std" => "260"),

	array("name" => "Single Left, Single Right Sidebar",
		"type" => "sub-section-3",
		"category" => "1l1r-sidebar",
		"parent" => "templates"
	),

	array("name" => "The \"Single Left and Single Right Sidebar\" template",
		"desc" => "You can use this template if you have a page where you want a single sidebar to show up on the left and right sides.
				To set up pages with this template, select the \"Single Left and Single Right Sidebar\" template while creating or updating a page.",
		"parent" => "1l1r-sidebar",
		"type" => "blurb"
	),

	array("name" => "Page Width Type",
		"desc" => "Your page can be fixed width or fluid/elastic width",
		"id" => "suf_1l1r_wrapper_width_type",
		"parent" => "1l1r-sidebar",
		"type" => "radio",
		"options" => array("fixed" => "Fixed width", "fluid" => "Fluid/Flexible width"),
		"std" => "fixed"),

	array("name" => "Fluid width settings",
		"desc" => "In the fluid width layout your sidebars have a fixed width, while the overall width of your page is a percentage of the browser window's width.",
		"category" => "1l1r-size-flexible",
		"parent" => "1l1r-sidebar",
		"type" => "sub-section-4",),

	array("name" => "Width of page",
		"id" => "suf_1l1r_wrapper_width_flex",
		"parent" => "1l1r-sidebar",
		"grouping" => "1l1r-size-flexible",
		"type" => "slider",
		"options" => array("range" => "min", "min" => 25, "max" => 100, "step" => 1, "size" => "400px", "unit" => "%"),
		"std" => 75),

	array("name" => "Maximum width",
		"desc" => "Set this value so that your typography stays consistent on large screens.",
		"id" => "suf_1r_wrapper_width_flex_max",
		"parent" => "1l1r-sidebar",
		"grouping" => "1l1r-size-flexible",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Value will be set to a default if blank or incompatible.",
		"std" => "1200"),

	array("name" => "Minimum width",
		"desc" => "Set this value so that your typography stays consistent on small screens.",
		"id" => "suf_1l1r_wrapper_width_flex_min",
		"parent" => "1l1r-sidebar",
		"grouping" => "1l1r-size-flexible",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Value will be set to a default if blank or incompatible.",
		"std" => "600"),

	array("name" => "Fixed width settings",
		"desc" => "In the fixed width layout the components of your page have widths fixed in pixels, irrespective of the size of your browser window.",
		"category" => "1l1r-size-fixed",
		"parent" => "1l1r-sidebar",
		"type" => "sub-section-4",),

	array("name" => "Overall Page Width",
		"desc" => "Suffusion comes with 3 preset page width options: 800px, 1000px and 1200px. You can also define a custom width if you please, or allow the width of the page to be determined by the width of its main components like the sidebars and the main content column.
				Due to difficulties with fitting things on the page, the minimum size allowed is 600px. If you enter something less than 600, it is considered to be 600.",
		"id" => "suf_1l1r_wrapper_width_preset",
		"parent" => "1l1r-sidebar",
		"grouping" => "1l1r-size-fixed",
		"type" => "radio",
		"options" => array("800" => "800px", "1000" => "1000px (Default)", "1200" => "1200px",
			"custom" => "Custom width (defined below)", "custom-components" => "Custom width, but constructed from individual components (defined below)"),
		"std" => "1000"),

	array("name" => "Custom value for page width",
		"desc" => "If you have selected \"Custom width\" above, you can set the width here. Please enter the width in pixels. <b>Do not enter \"px\".</b>
				Anything below 600 will be treated as 600. Note that this is a fixed width theme, not a fluid theme. What this means is that you cannot specify things like \"80%\" as the width.
				Also note that if you are setting a width over here with the \"Custom width\" selection in place, the widths of the individual components like the main column, the sidebars etc. are auto-calculated",
		"id" => "suf_1l1r_wrapper_width",
		"parent" => "1l1r-sidebar",
		"grouping" => "1l1r-size-fixed",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored. Incompatible values will be treated as 1000",
		"std" => "1000"),

	array("name" => "Custom component width - Custom value for main column width",
		"desc" => "If you have selected \"Custom width, but constructed from individual components\" above, you can set the width here for the main column.
				Please enter the width in pixels. <b>Do not enter \"px\".</b>
				Anything below 380 will be treated as 380. Note that this is a fixed width theme, not a fluid theme. What this means is that you cannot specify things like \"80%\" as the width. ",
		"id" => "suf_1l1r_main_col_width",
		"parent" => "1l1r-sidebar",
		"grouping" => "1l1r-size-fixed",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored. Incompatible values will be treated as 725",
		"std" => "725"),

	array("name" => "Sidebar width settings",
		"desc" => "Sidebar widths are relevant in the fluid width layout and in the fixed width layout with the \"Custom width, but constructed from individual components\" selection.",
		"category" => "1l1r-size-sb",
		"parent" => "1l1r-sidebar",
		"type" => "sub-section-4",),

	array("name" => "Custom component width - Custom value for width of left sidebar",
		"desc" => "If you have selected \"Custom width, but constructed from individual components\" above, you can set the width here for the first sidebar.
				Please enter the width in pixels. <b>Do not enter \"px\".</b>
				Anything below 95 will be treated as 95. Note that this is a fixed width theme, not a fluid theme. What this means is that you cannot specify things like \"10%\" as the width. ",
		"id" => "suf_1l1r_sb_1_width",
		"parent" => "1l1r-sidebar",
		"grouping" => "1l1r-size-sb",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored. Incompatible values will be treated as 260",
		"std" => "260"),

	array("name" => "Custom component width - Custom value for width of right sidebar",
		"desc" => "If you have selected \"Custom width, but constructed from individual components\" above, you can set the width here for the second sidebar.
				Please enter the width in pixels. <b>Do not enter \"px\".</b>
				Anything below 95 will be treated as 95. Note that this is a fixed width theme, not a fluid theme. What this means is that you cannot specify things like \"10%\" as the width. ",
		"id" => "suf_1l1r_sb_2_width",
		"parent" => "1l1r-sidebar",
		"grouping" => "1l1r-size-sb",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored. Incompatible values will be treated as 260",
		"std" => "260"),

	array("name" => "Double Left Sidebars",
		"type" => "sub-section-3",
		"category" => "2l-sidebars",
		"parent" => "templates"
	),

	array("name" => "The \"Double Left Sidebars\" template",
		"desc" => "You can use this template if you have a page where you want two sidebars to show up on the left.
				To set up pages with this template, select the \"Double Left Sidebars\" template while creating or updating a page.",
		"parent" => "2l-sidebars",
		"type" => "blurb"
	),

	array("name" => "Page Width Type",
		"desc" => "Your page can be fixed width or fluid/elastic width",
		"id" => "suf_2l_wrapper_width_type",
		"parent" => "2l-sidebars",
		"type" => "radio",
		"options" => array("fixed" => "Fixed width", "fluid" => "Fluid/Flexible width"),
		"std" => "fixed"),

	array("name" => "Fluid width settings",
		"desc" => "In the fluid width layout your sidebars have a fixed width, while the overall width of your page is a percentage of the browser window's width.",
		"category" => "2l-size-flexible",
		"parent" => "2l-sidebars",
		"type" => "sub-section-4",),

	array("name" => "Width of page",
		"id" => "suf_2l_wrapper_width_flex",
		"parent" => "2l-sidebars",
		"grouping" => "2l-size-flexible",
		"type" => "slider",
		"options" => array("range" => "min", "min" => 25, "max" => 100, "step" => 1, "size" => "400px", "unit" => "%"),
		"std" => 75),

	array("name" => "Maximum width",
		"desc" => "Set this value so that your typography stays consistent on large screens.",
		"id" => "suf_2l_wrapper_width_flex_max",
		"parent" => "2l-sidebars",
		"grouping" => "2l-size-flexible",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Value will be set to a default if blank or incompatible.",
		"std" => "1200"),

	array("name" => "Minimum width",
		"desc" => "Set this value so that your typography stays consistent on small screens.",
		"id" => "suf_2l_wrapper_width_flex_min",
		"parent" => "2l-sidebars",
		"grouping" => "2l-size-flexible",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Value will be set to a default if blank or incompatible.",
		"std" => "600"),

	array("name" => "Fixed width settings",
		"desc" => "In the fixed width layout the components of your page have widths fixed in pixels, irrespective of the size of your browser window.",
		"category" => "2l-size-fixed",
		"parent" => "2l-sidebars",
		"type" => "sub-section-4",),

	array("name" => "Overall Page Width",
		"desc" => "Suffusion comes with 3 preset page width options: 800px, 1000px and 1200px. You can also define a custom width if you please, or allow the width of the page to be determined by the width of its main components like the sidebars and the main content column.
				Due to difficulties with fitting things on the page, the minimum size allowed is 600px. If you enter something less than 600, it is considered to be 600.",
		"id" => "suf_2l_wrapper_width_preset",
		"parent" => "2l-sidebars",
		"grouping" => "2l-size-fixed",
		"type" => "radio",
		"options" => array("800" => "800px", "1000" => "1000px (Default)", "1200" => "1200px",
			"custom" => "Custom width (defined below)", "custom-components" => "Custom width, but constructed from individual components (defined below)"),
		"std" => "1000"),

	array("name" => "Custom value for page width",
		"desc" => "If you have selected \"Custom width\" above, you can set the width here. Please enter the width in pixels. <b>Do not enter \"px\".</b>
				Anything below 600 will be treated as 600. Note that this is a fixed width theme, not a fluid theme. What this means is that you cannot specify things like \"80%\" as the width.
				Also note that if you are setting a width over here with the \"Custom width\" selection in place, the widths of the individual components like the main column, the sidebars etc. are auto-calculated",
		"id" => "suf_2l_wrapper_width",
		"parent" => "2l-sidebars",
		"grouping" => "2l-size-fixed",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored. Incompatible values will be treated as 1000",
		"std" => "1000"),

	array("name" => "Custom component width - Custom value for main column width",
		"desc" => "If you have selected \"Custom width, but constructed from individual components\" above, you can set the width here for the main column.
				Please enter the width in pixels. <b>Do not enter \"px\".</b>
				Anything below 380 will be treated as 380. Note that this is a fixed width theme, not a fluid theme. What this means is that you cannot specify things like \"80%\" as the width. ",
		"id" => "suf_2l_main_col_width",
		"parent" => "2l-sidebars",
		"grouping" => "2l-size-fixed",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored. Incompatible values will be treated as 725",
		"std" => "725"),

	array("name" => "Sidebar width settings",
		"desc" => "Sidebar widths are relevant in the fluid width layout and in the fixed width layout with the \"Custom width, but constructed from individual components\" selection.",
		"category" => "2l-size-sb",
		"parent" => "2l-sidebars",
		"type" => "sub-section-4",),

	array("name" => "Custom component width - Custom value for width of first sidebar",
		"desc" => "If you have selected \"Fluid width\" or \"Custom width, but constructed from individual components\" above, you can set the width here for the first sidebar.
				Please enter the width in pixels. <b>Do not enter \"px\".</b>
				Anything below 95 will be treated as 95. Note that this is a fixed width theme, not a fluid theme. What this means is that you cannot specify things like \"10%\" as the width. ",
		"id" => "suf_2l_sb_1_width",
		"parent" => "2l-sidebars",
		"grouping" => "2l-size-sb",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored. Incompatible values will be treated as 260",
		"std" => "260"),

	array("name" => "Custom component width - Custom value for width of second sidebar",
		"desc" => "If you have selected \"Fluid width\" or \"Custom width, but constructed from individual components\" above, you can set the width here for the second sidebar.
				Please enter the width in pixels. <b>Do not enter \"px\".</b>
				Anything below 95 will be treated as 95. Note that this is a fixed width theme, not a fluid theme. What this means is that you cannot specify things like \"10%\" as the width. ",
		"id" => "suf_2l_sb_2_width",
		"parent" => "2l-sidebars",
		"grouping" => "2l-size-sb",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored. Incompatible values will be treated as 260",
		"std" => "260"),

	array("name" => "Double Right Sidebars",
		"type" => "sub-section-3",
		"category" => "2r-sidebars",
		"parent" => "templates"
	),

	array("name" => "The \"Double Right Sidebars\" template",
		"desc" => "You can use this template if you have a page where you want two sidebars to show up on the right.
				To set up pages with this template, select the \"Double Right Sidebars\" template while creating or updating a page.",
		"parent" => "2r-sidebars",
		"type" => "blurb"
	),

	array("name" => "Page Width Type",
		"desc" => "Your page can be fixed width or fluid/elastic width",
		"id" => "suf_2r_wrapper_width_type",
		"parent" => "2r-sidebars",
		"type" => "radio",
		"options" => array("fixed" => "Fixed width", "fluid" => "Fluid/Flexible width"),
		"std" => "fixed"),

	array("name" => "Fluid width settings",
		"desc" => "In the fluid width layout your sidebars have a fixed width, while the overall width of your page is a percentage of the browser window's width.",
		"category" => "2r-size-flexible",
		"parent" => "2r-sidebars",
		"type" => "sub-section-4",),

	array("name" => "Width of page",
		"id" => "suf_2r_wrapper_width_flex",
		"parent" => "2r-sidebars",
		"grouping" => "2r-size-flexible",
		"type" => "slider",
		"options" => array("range" => "min", "min" => 25, "max" => 100, "step" => 1, "size" => "400px", "unit" => "%"),
		"std" => 75),

	array("name" => "Maximum width",
		"desc" => "Set this value so that your typography stays consistent on large screens.",
		"id" => "suf_2r_wrapper_width_flex_max",
		"parent" => "2r-sidebars",
		"grouping" => "2r-size-flexible",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Value will be set to a default if blank or incompatible.",
		"std" => "1200"),

	array("name" => "Minimum width",
		"desc" => "Set this value so that your typography stays consistent on small screens.",
		"id" => "suf_2r_wrapper_width_flex_min",
		"parent" => "2r-sidebars",
		"grouping" => "2r-size-flexible",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Value will be set to a default if blank or incompatible.",
		"std" => "600"),

	array("name" => "Fixed width settings",
		"desc" => "In the fixed width layout the components of your page have widths fixed in pixels, irrespective of the size of your browser window.",
		"category" => "2r-size-fixed",
		"parent" => "2r-sidebars",
		"type" => "sub-section-4",),

	array("name" => "Overall Page Width",
		"desc" => "Suffusion comes with 3 preset page width options: 800px, 1000px and 1200px. You can also define a custom width if you please, or allow the width of the page to be determined by the width of its main components like the sidebars and the main content column.
				Due to difficulties with fitting things on the page, the minimum size allowed is 600px. If you enter something less than 600, it is considered to be 600.",
		"id" => "suf_2r_wrapper_width_preset",
		"parent" => "2r-sidebars",
		"grouping" => "2r-size-fixed",
		"type" => "radio",
		"options" => array("800" => "800px", "1000" => "1000px (Default)", "1200" => "1200px",
			"custom" => "Custom width (defined below)", "custom-components" => "Custom width, but constructed from individual components (defined below)"),
		"std" => "1000"),

	array("name" => "Custom value for page width",
		"desc" => "If you have selected \"Custom width\" above, you can set the width here. Please enter the width in pixels. <b>Do not enter \"px\".</b>
				Anything below 600 will be treated as 600. Note that this is a fixed width theme, not a fluid theme. What this means is that you cannot specify things like \"80%\" as the width.
				Also note that if you are setting a width over here with the \"Custom width\" selection in place, the widths of the individual components like the main column, the sidebars etc. are auto-calculated",
		"id" => "suf_2r_wrapper_width",
		"parent" => "2r-sidebars",
		"grouping" => "2r-size-fixed",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored. Incompatible values will be treated as 1000",
		"std" => "1000"),

	array("name" => "Custom component width - Custom value for main column width",
		"desc" => "If you have selected \"Custom width, but constructed from individual components\" above, you can set the width here for the main column.
				Please enter the width in pixels. <b>Do not enter \"px\".</b>
				Anything below 380 will be treated as 380. Note that this is a fixed width theme, not a fluid theme. What this means is that you cannot specify things like \"80%\" as the width. ",
		"id" => "suf_2r_main_col_width",
		"parent" => "2r-sidebars",
		"grouping" => "2r-size-fixed",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored. Incompatible values will be treated as 725",
		"std" => "725"),

	array("name" => "Sidebar width settings",
		"desc" => "Sidebar widths are relevant in the fluid width layout and in the fixed width layout with the \"Custom width, but constructed from individual components\" selection.",
		"category" => "2r-size-sb",
		"parent" => "2r-sidebars",
		"type" => "sub-section-4",),

	array("name" => "Custom component width - Custom value for width of first sidebar",
		"desc" => "If you have selected \"Fluid width\" or \"Custom width, but constructed from individual components\" above, you can set the width here for the first sidebar.
				Please enter the width in pixels. <b>Do not enter \"px\".</b>
				Anything below 95 will be treated as 95. Note that this is a fixed width theme, not a fluid theme. What this means is that you cannot specify things like \"10%\" as the width. ",
		"id" => "suf_2r_sb_1_width",
		"parent" => "2r-sidebars",
		"grouping" => "2r-size-sb",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored. Incompatible values will be treated as 260",
		"std" => "260"),

	array("name" => "Custom component width - Custom value for width of second sidebar",
		"desc" => "If you have selected \"Fluid width\" or \"Custom width, but constructed from individual components\" above, you can set the width here for the second sidebar.
				Please enter the width in pixels. <b>Do not enter \"px\".</b>
				Anything below 95 will be treated as 95. Note that this is a fixed width theme, not a fluid theme. What this means is that you cannot specify things like \"10%\" as the width. ",
		"id" => "suf_2r_sb_2_width",
		"parent" => "2r-sidebars",
		"grouping" => "2r-size-sb",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored. Incompatible values will be treated as 260",
		"std" => "260"),

	array("name" => "Now Reading",
		"type" => "sub-section-3",
		"category" => "nr-template",
		"parent" => "templates"
	),

	array("name" => "The \"Now Reading\" plugin templates",
		"desc" => "If you have a site for book reviews the <a href='http://robm.me.uk/projects/plugins/wordpress/now-reading'>Now Reading plugin</a> is a gerat one to use.
		 	Suffusion has templates that support this plugin, which you can control here",
		"parent" => "nr-template",
		"type" => "blurb"
	),

	array("name" => "General Settings",
		"desc" => "Control the general display settings for all templates",
		"category" => "nr-general",
		"parent" => "nr-template",
		"type" => "sub-section-4",),

	array("name" => "Number of Books per row",
		"desc" => "For all pages displaying multiple books, how many books do you want to display per row? ",
		"id" => "suf_nr_books_per_row",
		"parent" => "nr-template",
		"grouping" => "nr-general",
		"type" => "select",
		"options" => array("1" => "1 (One)", "2" => "2 (Two)", "3" => "3 (Three)", "4" => "4 (Four)", "5" => "5 (Five)", "6" => "6 (Six)", "7" => "7 (Seven)",
			"8" => "8 (Eight)", "9" => "9 (Nine)", "10" => "10 (Ten)"),
		"std" => "4"),

	array("name" => "Width of book cover image",
		"desc" => "Set the width of the book cover image here",
		"id" => "suf_nr_main_cover_w",
		"parent" => "nr-template",
		"grouping" => "nr-general",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored. Incompatible values will be treated as 108",
		"std" => "108"),

	array("name" => "Height of book cover image",
		"desc" => "Set the height of the book cover image here",
		"id" => "suf_nr_main_cover_h",
		"parent" => "nr-template",
		"grouping" => "nr-general",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px'). Non-integers will be ignored. Incompatible values will be treated as 160",
		"std" => "160"),

	array("name" => "Text if no books are found for a section",
		"desc" => "If you are looking at a section that has no books what text would you like to display?",
		"id" => "suf_nr_no_books_text",
		"parent" => "nr-template",
		"grouping" => "nr-general",
		"type" => "textarea",
		"std" => "None"),

	array("name" => "Library Settings",
		"desc" => "Control the display of the Library view",
		"category" => "nr-lib",
		"parent" => "nr-template",
		"type" => "sub-section-4",),

	array("name" => "Title of Library Page",
		"id" => "suf_nr_lib_title",
		"parent" => "nr-template",
		"grouping" => "nr-lib",
		"type" => "text",
		"std" => "Library"),

	array("name" => "Display Currently Reading Books",
		"desc" => "Choose if you want to display the \"Currently Reading\" section",
		"id" => "suf_nr_lib_curr_show",
		"parent" => "nr-template",
		"grouping" => "nr-lib",
		"type" => "radio",
		"options" => array("show" => "Show", "hide" => "Hide"),
		"std" => "show"),

	array("name" => "Section title for Currently Reading Books",
		"id" => "suf_nr_lib_curr_title",
		"parent" => "nr-template",
		"grouping" => "nr-lib",
		"type" => "text",
		"std" => "Currently Reading"),

	array("name" => "Section text for Currently Reading Books",
		"desc" => "This text will be displayed at the top of the Currently Reading section",
		"id" => "suf_nr_lib_curr_text",
		"parent" => "nr-template",
		"grouping" => "nr-lib",
		"type" => "textarea",
		"std" => ""),

	array("name" => "Display Unread Books",
		"desc" => "Choose if you want to display the \"Unread\" section",
		"id" => "suf_nr_lib_unread_show",
		"parent" => "nr-template",
		"grouping" => "nr-lib",
		"type" => "radio",
		"options" => array("show" => "Show", "hide" => "Hide"),
		"std" => "show"),

	array("name" => "Section title for Unread Books",
		"id" => "suf_nr_lib_unread_title",
		"parent" => "nr-template",
		"grouping" => "nr-lib",
		"type" => "text",
		"std" => "Up Next"),

	array("name" => "Section text for Unread Books",
		"desc" => "This text will be displayed at the top of the Unread Books section",
		"id" => "suf_nr_lib_unread_text",
		"parent" => "nr-template",
		"grouping" => "nr-lib",
		"type" => "textarea",
		"std" => ""),

	array("name" => "Display Completed Books",
		"desc" => "Choose if you want to display the \"Completed\" section",
		"id" => "suf_nr_lib_completed_show",
		"parent" => "nr-template",
		"grouping" => "nr-lib",
		"type" => "radio",
		"options" => array("show" => "Show", "hide" => "Hide"),
		"std" => "show"),

	array("name" => "Section title for Completed Books",
		"id" => "suf_nr_lib_completed_title",
		"parent" => "nr-template",
		"grouping" => "nr-lib",
		"type" => "text",
		"std" => "Finished Reading"),

	array("name" => "Section text for Completed Books",
		"desc" => "This text will be displayed at the top of the Completed Books section",
		"id" => "suf_nr_lib_completed_text",
		"parent" => "nr-template",
		"grouping" => "nr-lib",
		"type" => "textarea",
		"std" => ""),

	array("name" => "Order of sections",
		"desc" => "You can define the order in which the Currently Reading, Unread and Completed sections appear: ",
		"id" => "suf_nr_lib_order",
		"parent" => "nr-template",
		"grouping" => "nr-lib",
		"type" => "sortable-list",
		"std" => suffusion_entity_prepositions('nr')),

	array("name" => "Single Book Settings",
		"desc" => "Control the display of the Single Book view",
		"category" => "nr-single",
		"parent" => "nr-template",
		"type" => "sub-section-4",),

	array("name" => "Reading statistics: Show when book was added to library",
		"desc" => "This text will be displayed below the book's image",
		"id" => "suf_nr_single_added_show",
		"parent" => "nr-template",
		"grouping" => "nr-single",
		"type" => "radio",
		"options" => array("show" => "Show", "hide" => "Hide"),
		"std" => "show"),

	array("name" => "Reading statistics: Tagline for when you added this book",
		"id" => "suf_nr_single_added_text",
		"parent" => "nr-template",
		"grouping" => "nr-single",
		"type" => "text",
		"std" => "Added on: "),

	array("name" => "Reading statistics: Show when you began reading this book",
		"desc" => "This text will be displayed below the book's image",
		"id" => "suf_nr_single_started_show",
		"parent" => "nr-template",
		"grouping" => "nr-single",
		"type" => "radio",
		"options" => array("show" => "Show", "hide" => "Hide"),
		"std" => "show"),

	array("name" => "Reading statistics: Tagline for when you began reading this book",
		"id" => "suf_nr_single_started_text",
		"parent" => "nr-template",
		"grouping" => "nr-single",
		"type" => "text",
		"std" => "Started: "),

	array("name" => "Reading statistics: Show when you finished reading this book",
		"desc" => "This text will be displayed below the book's image",
		"id" => "suf_nr_single_finished_show",
		"parent" => "nr-template",
		"grouping" => "nr-single",
		"type" => "radio",
		"options" => array("show" => "Show", "hide" => "Hide"),
		"std" => "show"),

	array("name" => "Reading statistics: Tagline for when you finished reading this book",
		"id" => "suf_nr_single_finished_text",
		"parent" => "nr-template",
		"grouping" => "nr-single",
		"type" => "text",
		"std" => "Finished: "),

	array("name" => "Show meta information about the book",
		"desc" => "This text will be displayed below the book's image. Meta tags are added through the Now Reading plugin.",
		"id" => "suf_nr_single_meta_show",
		"parent" => "nr-template",
		"grouping" => "nr-single",
		"type" => "radio",
		"options" => array("show" => "Show", "hide" => "Hide"),
		"std" => "show"),

	array("name" => "Now Reading Widget Settings",
		"desc" => "Control the display of the Widget",
		"category" => "nr-w",
		"parent" => "nr-template",
		"type" => "sub-section-4",),

	array("name" => "Display Library Search",
		"desc" => "Choose where you want the search field for the Now Reading plugin",
		"id" => "suf_nr_wid_search_show",
		"parent" => "nr-template",
		"grouping" => "nr-w",
		"type" => "select",
		"options" => array("top" => "Top of widget", "bottom" => "Bottom of widget", "hide" => "Hide"),
		"std" => "bottom"),

	array("name" => "Display Currently Reading Books",
		"desc" => "Choose if you want to display the \"Currently Reading\" section",
		"id" => "suf_nr_wid_curr_show",
		"parent" => "nr-template",
		"grouping" => "nr-w",
		"type" => "radio",
		"options" => array("show" => "Show", "hide" => "Hide"),
		"std" => "show"),

	array("name" => "Section title for Currently Reading Books",
		"id" => "suf_nr_wid_curr_title",
		"parent" => "nr-template",
		"grouping" => "nr-w",
		"type" => "text",
		"std" => "Currently Reading"),

	array("name" => "Display Unread Books",
		"desc" => "Choose if you want to display the \"Unread\" section",
		"id" => "suf_nr_wid_unread_show",
		"parent" => "nr-template",
		"grouping" => "nr-w",
		"type" => "radio",
		"options" => array("show" => "Show", "hide" => "Hide"),
		"std" => "show"),

	array("name" => "Section title for Unread Books",
		"id" => "suf_nr_wid_unread_title",
		"parent" => "nr-template",
		"grouping" => "nr-w",
		"type" => "text",
		"std" => "Up Next"),

	array("name" => "Display Completed Books",
		"desc" => "Choose if you want to display the \"Completed\" section",
		"id" => "suf_nr_wid_completed_show",
		"parent" => "nr-template",
		"grouping" => "nr-w",
		"type" => "radio",
		"options" => array("show" => "Show", "hide" => "Hide"),
		"std" => "show"),

	array("name" => "Section title for Completed Books",
		"id" => "suf_nr_wid_completed_title",
		"parent" => "nr-template",
		"grouping" => "nr-w",
		"type" => "text",
		"std" => "Finished Reading"),

	array("name" => "Order of sections",
		"desc" => "You can define the order in which the Currently Reading, Unread and Completed sections appear: ",
		"id" => "suf_nr_wid_order",
		"parent" => "nr-template",
		"grouping" => "nr-w",
		"type" => "sortable-list",
		"std" => suffusion_entity_prepositions('nr')),

	array("name" => "404 Page",
		"type" => "sub-section-3",
		"category" => "404-page",
		"parent" => "templates"
	),

	array("name" => "The \"404 Page\"",
		"desc" => "A 404 page is encountered when a user tries to access a page that isn't there. You can configure your 404 page title and content here.",
		"parent" => "404-page",
		"type" => "blurb"
	),

	array("name" => "Title of the 404 Page",
		"desc" => "You can set the title for the 404 page here. You can use short codes.
				While using short codes remember that not all short codes maybe available on a 404 page. E.g. [suffusion-the-year] will be available, but not [suffusion-the-author]",
		"id" => "suf_404_title",
		"parent" => "404-page",
		"type" => "text",
		"std" => $suffusion_404_title),

	array("name" => "Content of the 404 Page",
		"desc" => "You can set the content for the 404 page here. You can use short codes.
				While using short codes remember that not all short codes maybe available on a 404 page. E.g. [suffusion-the-year] will be available, but not [suffusion-the-author]",
		"id" => "suf_404_content",
		"parent" => "404-page",
		"type" => "textarea",
		"std" => $suffusion_404_content),
);
?>