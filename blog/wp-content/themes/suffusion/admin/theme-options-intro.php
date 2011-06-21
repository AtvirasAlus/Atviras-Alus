<?php
/**
 * Lists out all the options from the Introduction Section of the theme options
 * This file is included in functions.php
 *
 * @package Suffusion
 * @subpackage Admin
 */

$suffusion_intro_options = array(
	array("name" => "Introduction",
		"type" => "sub-section-2",
		"category" => "intro-pages",
		"parent" => "root"
	),

	array("name" => "Welcome",
		"type" => "sub-section-3",
		"category" => "welcome",
		"buttons" => 'no-buttons',
		"parent" => "intro-pages"
	),

	array("name" => "Don't Panic!!",
		"desc" => "Welcome to Suffusion! But first...<br />
			<div class='suf-huge centered fix'>DON'T PANIC!!</div>
			<p>At first the number of options in Suffusion might alarm you, but don't panic, everything is organized well enough for you to find your way.
			You begin with the basic aspects of look-and-feel, then make your way into the more complex and innovative aspects of the theme.</p>",
		"parent" => "welcome",
		"type" => "blurb"),

	array("name" => "Are you upgrading?",
		"desc" => "If you are upgrading from an older version of the theme (3.4.3 or lower) you should first look into the \"Upgrades\" section and perform a migration.
			Otherwise your site might not work as you expect it to. But if this is your first installation of the theme, jump right into the customizations.",
		"parent" => "welcome",
		"type" => "blurb"),

	array("name" => "Upgrades",
		"type" => "sub-section-3",
		"category" => "upgrade-patches",
		"buttons" => 'special-buttons',
		"parent" => "intro-pages"
	),

	array("name" => "Migrate settings from version 3.0.2 or lower",
		"desc" => "Heavy code optimization was done on the theme in version 3.0.5.
			This caused a change in the way some of the settings operate and your theme might not work as you expect. E.g. the navigation bars might not be displayed etc.
			Click on the button below if you upgraded from Version 3.0.2 or lower. DON'T CLICK the button if:
			<ul class='margin-100'>
				<li>You are trying out the theme for the first time</li>
				<li>Or you are upgrading from version 3.0.5 or higher</li>
			</ul>",
		"id" => "suf_up_migrate_302",
		"parent" => "upgrade-patches",
		"type" => "button",
		"action" => "",
		"std" => "Migrate from 3.0.2 or lower"),

	array("name" => "Migrate settings from version 3.4.3 or lower",
		"desc" => "In version 3.4.3 and older Suffusion used to store each option separately in the database.
			While this is not much of an issue with PHP and MySQL, both of which are superfast,
			it is still a better practice to move it all to one single option array and then store that single array as an option.
			IF YOU ARE MIGRATING FROM A VERSION LOWER THAN 3.0.5, MAKE SURE YOU RUN THE PREVIOUS STEP FIRST!
			Click on the button below if you upgraded from Version 3.4.3 or lower. DON'T CLICK the button if:
			<ul class='margin-100'>
				<li>You are trying out the theme for the first time</li>
				<li>Or you are upgrading from version 3.4.5 or higher</li>
			</ul>",
		"id" => "suf_up_migrate_343",
		"parent" => "upgrade-patches",
		"type" => "button",
		"action" => "",
		"std" => "Migrate from 3.4.3 or lower"),

	array("name" => "Export / Import",
		"type" => "sub-section-3",
		"category" => "export-import",
		"buttons" => 'special-buttons',
		"parent" => "intro-pages"
	),

	array("name" => "Export options for use in other installations",
		"desc" => "You can export the options you have set in the theme. The options will be stored in a PHP file that you can save to your local disk.",
		"id" => "suf_export_options",
		"parent" => "export-import",
		"type" => "button",
		"action" => "",
		"std" => "Export to a file"),

	array("name" => "Import options from another installation",
		"desc" => "You can import options that you have exported from another installation. This is a two-step process:
		 	<ol class='margin-100'>
				<li>Copy the file 'suffusion-options.php' into the import folder in your Suffusion theme's admin directory on this installation</li>
				<li>Click on the Import button below. <b>This process is not reversible. So use it with care!!</b></li>
			</ol>",
		"id" => "suf_import_options",
		"parent" => "export-import",
		"type" => "button",
		"action" => "",
		"std" => "Import options"),

	array("name" => "FAQs",
		"type" => "sub-section-3",
		"category" => "help-faqs",
		"buttons" => 'no-buttons',
		"parent" => "intro-pages"
	),

	array("name" => "Answers to some frequently asked questions",
		"type" => "blurb",
		"parent" => "help-faqs",
		"desc" => "<ol class='faq-list'>
			<li><b>Where do I start with customizations?</b><br />
			Open the section for \"Theme Selection\". If you see a color combination that you like, select it and you are set.</li>
			<li><b>What if I don't like any of the default color combinations?</b><br />
			You can perform customizations within certain limits:
			<ul class='margin-40'>
			<li>To modify things like the overall background color and the background image, look at \"Theme Skinning\".</li>
			<li>For font colors used, colors of hyperlinks etc. look at \"Body Font Settings\".</li>
			<li>To customize the colors used for the blog title and blog description see \"Header Customization\".</li>
			<li>For the sidebar widgets you can select styled or unstyled headers in the \"Widget Styles\" section.</li>
			</ul>
			<li><b>Can I use my own background image for the page?</b><br />
			Absolutely! You can use any image that you like, tile/repeat it, align it, make it fixed etc. See the \"Theme Skinning\" section.</li>
			<li><b>Can I use a custom header image?</b><br />
			Yes. See the \"Header Customization\" section.</li>
			<li><b>Can I switch the sidebar to the left?</b><br />
			Of course. See the \"Sidebar Setup\" section.</li>
			<li><b>Can I define more sidebars?</b><br />
			Yes. See the \"Sidebar Setup\" section. You can define 2 sidebars and have them positioned either on the same side or on opposite sides of the content.</li>
			<li><b>The theme's screenshots show navigation menus at the top. Why don't I see any navigation menu?</b><br />
			There are two possible reasons. First, in the \"Navigation Bar Setup\" section, you may have chosen \"Hidden\" for displaying the navigation bar.
			Second, by default no pages are selected for display. So even if you have not chosen to hide the navigation bar, you will have to manually select the pages to display.</li>
			<li><b>Can I define what should show up on the drop-down menus at the top?</b><br />
			Yes. See the \"Navigation Bar Setup\" section. Only pages that you include there will be shown. New pages that you create will have to be manually added.</li>
			<li><b>What other features are enabled?</b><br />
			You can hook up your Google Analytics account to your blog. If you have your blog set up with OpenID, the requisite tags will be added to the header</li>
			<li><b>Are there any plans to enhance any features?</b><br />
			Yes. Features that are in consideration are - more default themes and better control over the colors in a post.
			I have de-scoped some features, like advertising support and support for custom feeds. This is because of the availability of plugins to do these tasks.</li>
			<li><b>Where can I report bugs or request for features?</b><br />
			You can visit the <a href='http://www.aquoid.com/news/themes/suffusion/'>theme's page</a> and leave a comment, or you could drop a comment on
			   <a href='http://mynethome.net/blog/contact'>the author's contact page</a>.</li>
			</ol>"
	),
);
?>