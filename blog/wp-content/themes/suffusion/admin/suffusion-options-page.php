<?php
/**
 * Contains the layout functions for Suffusion's options.
 * This file is included in functions.php
 *
 * @package Suffusion
 * @subpackage Admin
 */

global $suffusion_options_file, $suffusion_options, $suffusion_unified_options, $suffusion_options_intro_page, $suffusion_options_theme_skinning_page, $suffusion_options_visual_effects_page, $suffusion_options_sidebars_and_widgets_page, $suffusion_options_blog_features_page, $suffusion_options_templates_page;
$suffusion_options_file = basename(__FILE__);

/**
 * Create the HTML markup for the options.
 *
 * @return void
 */
function suffusion_render_options() {
	global $suffusion_options_intro_page, $suffusion_options_theme_skinning_page, $suffusion_options_visual_effects_page, $suffusion_options_sidebars_and_widgets_page, $suffusion_options_blog_features_page, $suffusion_options_templates_page, $suffusion_options_custom_types_page;
	global $suffusion_intro_options, $suffusion_theme_skinning_options, $suffusion_visual_effects_options, $suffusion_sidebars_and_widgets_options, $suffusion_blog_features_options, $suffusion_templates_options, $suffusion_custom_types_options;
	$options_in_page = array(
		$suffusion_options_intro_page => $suffusion_intro_options,
		$suffusion_options_theme_skinning_page => $suffusion_theme_skinning_options,
		$suffusion_options_visual_effects_page => $suffusion_visual_effects_options,
		$suffusion_options_sidebars_and_widgets_page => $suffusion_sidebars_and_widgets_options,
		$suffusion_options_blog_features_page => $suffusion_blog_features_options,
		$suffusion_options_templates_page => $suffusion_templates_options,
		$suffusion_options_custom_types_page => $suffusion_custom_types_options,
	);

	$option_page_options = $suffusion_intro_options;
	$options_page = 'theme-options-intro.php';
	if (isset($_REQUEST['tab'])) {
		$options_page = $_REQUEST['tab'];
		if (isset($options_in_page[$options_page])) {
			$option_page_options = $options_in_page[$options_page];
		}
		else {
			$option_page_options = $suffusion_intro_options;
		}
	}
	$version = suffusion_get_current_version();
?>
	<div class="wrapper">
		<div class="suf-tabbed-options">
<?php
	echo suffusion_version_checker();
	echo suffusion_translation_checker();
	echo suffusion_bp_checker();
	if ((isset($_GET['updated']) && $_GET['updated'] == true) || (isset($_GET['settings-updated']) && $_GET['settings-updated'] == true)) {
		echo "<div class='updated fade fix'>Your settings have been updated.</div>";
	}

	if (isset($_GET['now-active']) && $_GET['now-active'] == true) {
		echo "<div class='updated fade fix'>Congratulations! Suffusion has been activated.</div>";
	}
		?>
			<div class="suf-header-nav">
				<div class="suf-header-nav-top fix">
					<h2 class='suf-header-1'>Settings for Suffusion &ndash; <?php echo $version; ?></h2>
					<div class='donate fix'>
						<form action="https://www.paypal.com/cgi-bin/webscr" method="post" id="paypal-submit" >
							<input type="hidden" name="cmd" value="_s-xclick"/>
							<input type="hidden" name="hosted_button_id" value="9018267"/>
							<ul>
								<li class='announcements'><a href='http://www.aquoid.com/news'>Announcements</a></li>
								<li class='support'><a href='http://www.aquoid.com/forum'>Support Forum</a></li>
								<li class='showcase'><a href='http://www.aquoid.com/news/showcase/'>Showcase</a></li>
								<li class='coffee'><input type='submit' name='submit' value='Like Suffusion? Buy me a coffee!' /></li>
							</ul>
							<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1"/>
						</form>
					</div><!-- donate -->
				</div>
				<div class="suf-options-header-bar fix">
					<ul class='suf-options-header-bar'>
						<li><a class='suf-load-page <?php if ($options_page == $suffusion_options_intro_page) echo 'current-tab'; ?>' id='theme-options-intro' href='?page=suffusion-options-manager&amp;tab=<?php echo $suffusion_options_intro_page; ?>'>Introduction</a></li>
						<li><a class='suf-load-page <?php if ($options_page == $suffusion_options_theme_skinning_page) echo 'current-tab'; ?>' id='theme-options-theme-skinning' href='?page=suffusion-options-manager&amp;tab=<?php echo $suffusion_options_theme_skinning_page; ?>'>Theme Skinning</a></li>
						<li><a class='suf-load-page <?php if ($options_page == $suffusion_options_visual_effects_page) echo 'current-tab'; ?>' id='theme-options-visual-effects' href='?page=suffusion-options-manager&amp;tab=<?php echo $suffusion_options_visual_effects_page; ?>'>Other Graphical Elements</a></li>
						<li><a class='suf-load-page <?php if ($options_page == $suffusion_options_sidebars_and_widgets_page) echo 'current-tab'; ?>' id='theme-options-sidebars-and-widgets' href='?page=suffusion-options-manager&amp;tab=<?php echo $suffusion_options_sidebars_and_widgets_page; ?>'>Sidebar Configuration</a></li>
						<li><a class='suf-load-page <?php if ($options_page == $suffusion_options_blog_features_page) echo 'current-tab'; ?>' id='theme-options-blog-features' href='?page=suffusion-options-manager&amp;tab=<?php echo $suffusion_options_blog_features_page; ?>'>Back-end Settings</a></li>
						<li><a class='suf-load-page <?php if ($options_page == $suffusion_options_templates_page) echo 'current-tab'; ?>' id='theme-options-templates' href='?page=suffusion-options-manager&amp;tab=<?php echo $suffusion_options_templates_page; ?>'>Templates</a></li>
						<li><a class='suf-load-page <?php if ($options_page == $suffusion_options_custom_types_page) echo 'current-tab'; ?>' id='theme-options-custom-types' href='?page=suffusion-options-manager&amp;tab=<?php echo $suffusion_options_custom_types_page; ?>'>Custom Types</a></li>
					</ul>
				</div>
			</div>
<?php
	$renderer = new Suffusion_Options_Renderer($option_page_options, __FILE__);
	$option_structure = $renderer->get_option_structure();
	$renderer->get_options_html($option_structure);
	?>
		</div><!-- suf-tabbed-options -->
	</div><!-- wrapper -->
<?php
}

/**
 * Set up the renderer and initialize the settings.
 *
 * @return void
 */
function suffusion_options_init_fn() {
	global $suffusion_options_intro_page, $suffusion_options_theme_skinning_page, $suffusion_options_visual_effects_page, $suffusion_options_sidebars_and_widgets_page, $suffusion_options_blog_features_page, $suffusion_options_templates_page, $suffusion_options_custom_types_page;
	global $suffusion_intro_options, $suffusion_theme_skinning_options, $suffusion_visual_effects_options, $suffusion_sidebars_and_widgets_options, $suffusion_blog_features_options, $suffusion_templates_options, $suffusion_custom_types_options;
	global $suffusion_options_renderer;
	$options_in_page = array(
		$suffusion_options_intro_page => $suffusion_intro_options,
		$suffusion_options_theme_skinning_page => $suffusion_theme_skinning_options,
		$suffusion_options_visual_effects_page => $suffusion_visual_effects_options,
		$suffusion_options_sidebars_and_widgets_page => $suffusion_sidebars_and_widgets_options,
		$suffusion_options_blog_features_page => $suffusion_blog_features_options,
		$suffusion_options_templates_page => $suffusion_templates_options,
		$suffusion_options_custom_types_page => $suffusion_custom_types_options,
	);
	$option_page_options = $suffusion_intro_options;
	if (isset($_REQUEST['tab'])) {
		$options_page = $_REQUEST['tab'];
		if (isset($options_in_page[$options_page])) {
			$option_page_options = $options_in_page[$options_page];
		}
		else {
			$option_page_options = $suffusion_intro_options;
		}
	}

	$suffusion_options_renderer = new Suffusion_Options_Renderer($option_page_options, __FILE__);
	$option_structure = $suffusion_options_renderer->get_option_structure();
	$suffusion_options_renderer->initialize_settings($option_structure);
}

function suffusion_admin_header_style() {
	global $suffusion_options_intro_page, $suffusion_options_theme_skinning_page, $suffusion_options_visual_effects_page, $suffusion_options_sidebars_and_widgets_page, $suffusion_options_blog_features_page, $suffusion_options_templates_page;
	global $suffusion_options_renderer;
	$options_files = array($suffusion_options_intro_page, $suffusion_options_theme_skinning_page, $suffusion_options_visual_effects_page, $suffusion_options_sidebars_and_widgets_page, $suffusion_options_blog_features_page, $suffusion_options_templates_page);
	$landing_tab_sections = array($suffusion_options_intro_page => 'welcome', $suffusion_options_theme_skinning_page => 'theme-selection', $suffusion_options_visual_effects_page => 'favicon-setup', $suffusion_options_sidebars_and_widgets_page => 'sidebar-layout', $suffusion_options_blog_features_page => 'seo-settings', $suffusion_options_templates_page => 'magazine-template');

	$stored_options = get_option('suffusion_options');
	if (isset($stored_options) && is_array($stored_options) && isset($stored_options['last-set-section'])) {
		$category = $stored_options['last-set-section'];
	}
	else {
		$category = 'welcome';
	}
?>
	<script type="text/javascript">
		/* <![CDATA[ */
		$j = jQuery.noConflict();

		$j(document).ready(function() {
			var selected_category = '<?php echo $category; ?>';
			var $tabs = $j("#suf-options").tabs({
//				cookie: {
//					expires: 3
//				},
				fx: {
					opacity: "toggle",
					duration: "fast"
				}
			});
			$tabs.tabs('select', '#'+selected_category);

			$j('.suf-help').dialog({ autoOpen: false, width: 500 });
			$j('.suf-help-anchor').click(function() {
				var thisClass = this.className;
				thisClass = thisClass.substring(0, thisClass.indexOf(" "));
				thisClass = thisClass.substring(16);
				var helpClass = '.suf-help-' + thisClass;
				$j(helpClass).dialog('open');
				return false;
			});

			$j(".suf-tabbed-options .fade").fadeOut(20000);

			$j('div.suf-checklist input[type=checkbox]').change(function() {
				var thisClass = (this.className);
				thisClass = thisClass.substring(thisClass.indexOf(" ") + 1);
				thisClass = thisClass.substring(21);

				var all_checked = [];
				$j('#' + thisClass + '-chk :checked').each(function() {
					var thisChild = this.name;
					thisChild = thisChild.substring(thisClass.length + 1);
					all_checked.push(thisChild);
				});
				var joined = all_checked.join(',');
				$j('#' + thisClass).val(joined);
			});

			$j('input.suf-multi-select-button').live('click', function() {
				var thisAction = this.className.substring(0, this.className.indexOf(" "));
				var thisName = this.name.substring(0, this.name.indexOf("-"));
				if (thisAction == 'button-all') {
					$j('input[type=checkbox].suf-options-checkbox-' + thisName).attr('checked', true);
				}
				else if (thisAction == 'button-none') {
					$j('input[type=checkbox].suf-options-checkbox-' + thisName).attr('checked', false);
				}

				var all_checked = [];
				$j('#' + thisName + '-chk :checked').each(function() {
					var thisChild = this.name;
					thisChild = thisChild.substring(thisName.length + 1);
					all_checked.push(thisChild);
				});
				var joined = all_checked.join(',');
				$j('#' + thisName).val(joined);
			});

			$j('.suf-button-bar').draggable({handle: 'h2'});

			//AJAX Upload
			$j('.image_upload_button').live('click', function() {
				var clickedObject = $j(this);
				var thisID = $j(this).attr('id');

				new AjaxUpload(thisID, {
					action: ajaxurl,
					name: thisID,
					debug: true,
					data: {
						action: "suffusion_admin_upload_file",
						type: "upload",
						data: thisID
					},
					autoSubmit: true,
					responseType: false,
					onSubmit: function(file, extension) {
						clickedObject.text('Uploading ...');
						this.disable();
					},
					onComplete: function(file, response) {
						clickedObject.text('Upload Image');
						this.enable(); // enable upload button

						// If there was an error
						if (response.search('Upload Error') > -1) {
							var buildReturn = '<span class="upload-error">' + response + '</span>';
							$j(".upload-error").remove();
							clickedObject.parent().after(buildReturn);
						}
						else {
							thisID = thisID.substring(7);
							var buildReturn = '<div id="suffusion-preview-' + thisID + '"><img class="hidden suffusion-option-image" id="image_' + thisID + '" src="' + response + '" alt="" /></div>';
							$j(".upload-error").remove();
							$j("#image_" + thisID).remove();
							clickedObject.parent().after(buildReturn);
							$j('img#image_' + thisID).fadeIn();
							clickedObject.next('span').fadeIn();
							var text_field = $j("#"+thisID);
							text_field.val(response);
							clickedObject.fadeOut();
							text_field.change();
						}
					}
				});
			});

			//AJAX Remove (clear option value)
			$j('.image_reset_button').live('click', function() {
				var clickedObject = $j(this);
				var thisID = $j(this).attr('id');
				var image_Id = thisID.substring(6);

				var data = {
					action: 'suffusion_admin_upload_file',
					type: 'image_reset',
					data: thisID
				};

				$j.post(ajaxurl, data, function(response) {
					//var image_to_remove = $j('#image_' + image_Id);
					var image_to_remove = $j('.suf-tabbed-options').find("#suffusion-preview-" + image_Id);
					var button_to_hide = $j('.suf-tabbed-options').find('#reset_' + image_Id);
					var button_to_show = $j('.suf-tabbed-options').find('#upload_' + image_Id);
					image_to_remove.fadeOut(500, function() {
						clickedObject.remove();
					});
					button_to_hide.fadeOut();
					button_to_show.fadeIn();
					var text_field = $j('.suf-tabbed-options').find("#"+image_Id);
					text_field.val('');
					text_field.change();
				});

				return false;
			});

			$j('.suf-background-options input[type=text]').change(function(event) {
				var thisName = event.currentTarget.id;
				thisName = thisName.substring(0, thisName.indexOf('-'));
				$j("#" + thisName).val('color=' + $j("#" + thisName + "-bgcolor").val() + ';' +
						'colortype=' + $j("input[name=" + thisName + "-colortype]:checked").val() + ';' +
						'image=' + $j("#" + thisName + "-bgimg").val() + ';' +
						'position=' + $j("#" + thisName + "-position").val() + ';' +
						'repeat=' + $j("#" + thisName + "-repeat").val() + ';' +
						'trans=' + $j("#" + thisName + "-trans").val() + ';'
						);
			});

			$j('.suf-background-options input[type=radio]').change(function(event) {
				var thisName = event.currentTarget.name;
				thisName = thisName.substring(0, thisName.indexOf('-'));
				$j("#" + thisName).val('color=' + $j("#" + thisName + "-bgcolor").val() + ';' +
						'colortype=' + $j("input[name=" + thisName + "-colortype]:checked").val() + ';' +
						'image=' + $j("#" + thisName + "-bgimg").val() + ';' +
						'position=' + $j("#" + thisName + "-position").val() + ';' +
						'repeat=' + $j("#" + thisName + "-repeat").val() + ';' +
						'trans=' + $j("#" + thisName + "-trans").val() + ';'
						);
			});

			$j('.suf-background-options select').change(function(event) {
				var thisName = event.currentTarget.id;
				thisName = thisName.substring(0, thisName.indexOf('-'));
				$j("#" + thisName).val('color=' + $j("#" + thisName + "-bgcolor").val() + ';' +
								       'colortype=' + $j("input[name=" + thisName + "-colortype]:checked").val() + ';' +
								       'image=' + $j("#" + thisName + "-bgimg").val() + ';' +
								       'position=' + $j("#" + thisName + "-position").val() + ';' +
								       'repeat=' + $j("#" + thisName + "-repeat").val() + ';' +
								       'trans=' + $j("#" + thisName + "-trans").val() + ';'
					   );
		    });

		    $j('.suf-font-options input[type=text]').change(function(event) {
			    var thisName = event.currentTarget.id;
			    thisName = thisName.substring(0, thisName.indexOf('-'));
			    $j("#" + thisName).val('color=' + $j("#" + thisName + "-color").val() + ';' +
					    'font-face=' + $j("#" + thisName + "-font-face").val() + ';' +
					    'font-weight=' + $j("#" + thisName + "-font-weight").val() + ';' +
					    'font-style=' + $j("#" + thisName + "-font-style").val() + ';' +
					    'font-variant=' + $j("#" + thisName + "-font-variant").val() + ';' +
					    'font-size=' + $j("#" + thisName + "-font-size").val() + ';' +
					    'font-size-type=' + $j("#" + thisName + "-font-size-type").val() + ';'
					    );
		    });

			$j('.suf-font-options select').change(function(event) {
				var thisName = event.currentTarget.id;
				thisName = thisName.substring(0, thisName.indexOf('-'));
				$j("#" + thisName).val('color=' + $j("#" + thisName + "-color").val() + ';' +
						'font-face=' + $j("#" + thisName + "-font-face").val() + ';' +
						'font-weight=' + $j("#" + thisName + "-font-weight").val() + ';' +
						'font-style=' + $j("#" + thisName + "-font-style").val() + ';' +
						'font-variant=' + $j("#" + thisName + "-font-variant").val() + ';' +
						'font-size=' + $j("#" + thisName + "-font-size").val() + ';' +
						'font-size-type=' + $j("#" + thisName + "-font-size-type").val() + ';'
						);
			});

			$j('.suf-border-options input[type=text]').change(function(event) {
				var thisId = event.currentTarget.id;
				thisId = thisId.substring(0, thisId.indexOf('-'));
				var edges = new Array('top', 'right', 'bottom', 'left');
				var border = '';
				for (var x in edges) {
					var edge = edges[x];
					var thisName = thisId + '-' + edge;
					border += edge + '::';
					border += 'color=' + $j("#" + thisName + "-color").val() + ';' +
							'colortype=' + $j("input[name=" + thisName + "-colortype]:checked").val() + ';' +
							'style=' + $j("#" + thisName + "-style").val() + ';' +
							'border-width=' + $j("#" + thisName + "-border-width").val() + ';' +
							'border-width-type=' + $j("#" + thisName + "-border-width-type").val() + ';';
					border += '||';
				}
				$j('#' + thisId).val(border);
			});

			$j('.suf-border-options input[type=radio]').change(function(event) {
				var thisId = event.currentTarget.name;
				thisId = thisId.substring(0, thisId.indexOf('-'));
				var edges = new Array('top', 'right', 'bottom', 'left');
				var border = '';
				for (var x in edges) {
					var edge = edges[x];
					var thisName = thisId + '-' + edge;
					border += edge + '::';
					border += 'color=' + $j("#" + thisName + "-color").val() + ';' +
							'colortype=' + $j("input[name=" + thisName + "-colortype]:checked").val() + ';' +
							'style=' + $j("#" + thisName + "-style").val() + ';' +
							'border-width=' + $j("#" + thisName + "-border-width").val() + ';' +
							'border-width-type=' + $j("#" + thisName + "-border-width-type").val() + ';';
					border += '||';
				}
				$j('#' + thisId).val(border);
			});

			$j('.suf-border-options select').change(function(event) {
				var thisId = event.currentTarget.id;
				thisId = thisId.substring(0, thisId.indexOf('-'));
				var edges = new Array('top', 'right', 'bottom', 'left');
				var border = '';
				for (var x in edges) {
					var edge = edges[x];
					var thisName = thisId + '-' + edge;
					border += edge + '::';
					border += 'color=' + $j("#" + thisName + "-color").val() + ';' +
							'colortype=' + $j("input[name=" + thisName + "-colortype]:checked").val() + ';' +
							'style=' + $j("#" + thisName + "-style").val() + ';' +
							'border-width=' + $j("#" + thisName + "-border-width").val() + ';' +
							'border-width-type=' + $j("#" + thisName + "-border-width-type").val() + ';';
					border += '||';
				}
				$j('#' + thisId).val(border);
			});

			$j(".suffusion-options-form :input[type=submit]").click(function() {
				//This is needed, otherwise the event handler cannot figure out which button was clicked.
				suffusion_submit_button = $j(this);
			});

//			$j("#suffusion-options-form h3").each(function() {
			$j("#suf-options h3").each(function() {
				var text = $j(this).text();
				if (text == '') {
					$j(this).remove();
				}
			});

			$j('.suffusion-options-form').submit(function(event) {
				var field = suffusion_submit_button;
				var value = field.val();

				if (value == 'Migrate from 3.0.2 or lower') {
					if (!confirm("If you are NOT migrating from 3.0.2 or lower, this can wipe out all your settings! Are you sure you want to do this? This process is not reversible.")) {
						return false;
					}
				}
				else if (value == 'Migrate from 3.4.3 or lower') {
					if (!confirm("If you are NOT migrating from 3.4.3 or lower, this can wipe out all your settings! Are you sure you want to do this? This process is not reversible.")) {
						return false;
					}
				}
				else if (value.substring(0, 5) == 'Reset') {
					if (!confirm("This will reset your configurations to the original values!!! Are you sure you want to continue? This is not reversible!")) {
						return false;
					}
				}
				else if (value.substring(0, 6) == 'Delete') {
					if (!confirm("This will delete all your Suffusion configuration options!!! Are you sure you want to continue? This is not reversible!")) {
						return false;
					}
				}
			});

			//$j('#suffusion-options-form').ajaxForm();

			$j('.color').removeClass('text');
			$j('.slidertext').removeClass('text');

			$j('div.suf-loader').hide();
			$j('a.edit-post-type').live("click", function(){
				var thisId = this.id;
				var add_edit_form = $j('form#form-add-edit-post-type');
				$j('div.suf-loader').show();
				$j.post($j(this).attr("href"), {
					action: "suffusion_display_custom_post_type",
					post_type_index: parseInt(thisId.substr(15))
				}, function(data) {
					add_edit_form.html($j(data));
					$j(add_edit_form).find('.suf-button-bar').draggable({handle: 'h2'});
					$j('div.suf-loader').hide();
				  }
				);
				$tabs.tabs('select', 1);
				return false;
			});

			$j('a.delete-post-type').live("click", function(){
				var thisId = this.id;
				var list_types_form = $j('form#form-custom-post-types');
				var nonce = $j('#custom_post_types_wpnonce').val();
				var add_edit_type_form = $j('form#form-add-edit-post-type');
				$j('div.suf-loader').show();
				$j.post($j(this).attr("href"), {
					action: "suffusion_display_all_custom_post_types",
					post_type_index: parseInt(thisId.substr(17)),
					processing_function: "delete",
					custom_post_types_wpnonce: nonce
				}, function(data) {
					list_types_form.html($j(data).filter('.suf-custom-post-types-section'));
					add_edit_type_form.html($j(data).filter('.suf-post-type-edit-section'));
					$j(list_types_form).find('.suf-button-bar').draggable({handle: 'h2'});
					$j(add_edit_type_form).find('.suf-button-bar').draggable({handle: 'h2'});
					$j('div.suf-loader').hide();
				  }
				);
				$tabs.tabs('select', 0);
				return false;
			});

			$j('a.edit-taxonomy').live("click", function(){
				var thisId = this.id;
				var add_edit_form = $j('form#form-add-edit-taxonomy');
				$j('div.suf-loader').show();
				$j.post($j(this).attr("href"), {
					action: "suffusion_display_custom_taxonomy",
					taxonomy_index: parseInt(thisId.substr(14))
				}, function(data) {
					add_edit_form.html($j(data));
					$j(add_edit_form).find('.suf-button-bar').draggable({handle: 'h2'});
					$j('div.suf-loader').hide();
				  }
				);
				$tabs.tabs('select', 3);
				return false;
			});

			$j('a.delete-taxonomy').live("click", function(){
				var thisId = this.id;
				var list_types_form = $j('form#form-custom-taxonomies');
				var add_edit_type_form = $j('form#form-add-edit-taxonomy');
				$j('div.suf-loader').show();
				$j.post($j(this).attr("href"), {
					action: "suffusion_display_all_custom_taxonomies",
					taxonomy_index: parseInt(thisId.substr(16)),
					processing_function: "delete"
				}, function(data) {
					list_types_form.html($j(data).filter('.suf-custom-taxonomies-section'));
					add_edit_type_form.html($j(data).filter('.suf-taxonomy-edit-section'));
					$j(list_types_form).find('.suf-button-bar').draggable({handle: 'h2'});
					$j(add_edit_type_form).find('.suf-button-bar').draggable({handle: 'h2'});
					$j('div.suf-loader').hide();
				  }
				);
				$tabs.tabs('select', 2);
				return false;
			});

			$j('.suf-custom-type-settings input.button').live("click", function() {
				var thisName = this.name;
				var add_edit_post_type_form = $j('form#form-add-edit-post-type');
				var list_post_types_form = $j('form#form-custom-post-types');
				var add_edit_taxonomy_form = $j('form#form-add-edit-taxonomy');
				var list_taxonomies_form = $j('form#form-custom-taxonomies');
				var form_values;
				if (thisName == 'save-post-type-edit') {
					form_values = add_edit_post_type_form.serialize().replace(/%5B/g, '[').replace(/%5D/g, ']');

					$j('div.suf-loader').show();
					$j.post(ajaxurl, 'action=suffusion_save_custom_post_type&'+form_values, function(data) {
						add_edit_post_type_form.html($j(data).filter('.suf-post-type-edit-section'));
						list_post_types_form.html($j(data).filter('.suf-custom-post-types-section'));
						$j(list_post_types_form).find('.suf-button-bar').draggable({handle: 'h2'});
						$j(add_edit_post_type_form).find('.suf-button-bar').draggable({handle: 'h2'});
						$j('div.suf-loader').hide();
					});
					$tabs.tabs('select', 1);
				}
				else if (thisName == 'delete-all-custom-post-types') {
					var nonce = $j('#custom_post_types_wpnonce').val();
					$j('div.suf-loader').show();
					$j.post(ajaxurl, {
						action: "suffusion_display_all_custom_post_types",
						processing_function: "delete_all",
						custom_post_types_wpnonce: nonce
					}, function(data) {
						add_edit_post_type_form.html($j(data).filter('.suf-post-type-edit-section'));
						list_post_types_form.html($j(data).filter('.suf-custom-post-types-section'));
						$j('div.suf-loader').hide();
					});
				}
				else if (thisName == 'reset-post-type-edit') {
					$j(':input','form#form-add-edit-post-type')
							.not(':button, :submit, :reset, :hidden')
							.val('')
							.removeAttr('checked')
							.removeAttr('selected');

					//add_edit_form[0].reset();
					$j("#post_type_index").val("");
				}
				else if (thisName == 'save-taxonomy-edit') {
					form_values = add_edit_taxonomy_form.serialize().replace(/%5B/g, '[').replace(/%5D/g, ']');

					$j('div.suf-loader').show();
					$j.post(ajaxurl, 'action=suffusion_save_custom_taxonomy&'+form_values, function(data) {
						add_edit_taxonomy_form.html($j(data).filter('.suf-taxonomy-edit-section'));
						list_taxonomies_form.html($j(data).filter('.suf-custom-taxonomies-section'));
						$j(add_edit_taxonomy_form).find('.suf-button-bar').draggable({handle: 'h2'});
						$j(list_taxonomies_form).find('.suf-button-bar').draggable({handle: 'h2'});
						$j('div.suf-loader').hide();
					});

					$tabs.tabs('select', 3);
				}
				else if (thisName == 'delete-all-custom-taxonomies') {
					$j('div.suf-loader').show();
					$j.post(ajaxurl, {
						action: "suffusion_display_all_custom_taxonomies",
						processing_function: "delete_all"
					}, function(data) {
						add_edit_taxonomy_form.html($j(data).filter('.suf-taxonomy-edit-section'));
						list_taxonomies_form.html($j(data).filter('.suf-custom-taxonomies-section'));
						$j(add_edit_taxonomy_form).find('.suf-button-bar').draggable({handle: 'h2'});
						$j(list_taxonomies_form).find('.suf-button-bar').draggable({handle: 'h2'});
						$j('div.suf-loader').hide();
					});
				}
				else if (thisName == 'reset-taxonomy-edit') {
					$j(':input','form#form-add-edit-taxonomy')
							.not(':button, :submit, :reset, :hidden')
							.val('')
							.removeAttr('checked')
							.removeAttr('selected');

					//add_edit_form[0].reset();
					$j("#taxonomy_index").val("");
				}

				return false;
			});

			$j('#suf-options').fadeIn();
		});
		/* ]]> */
	</script>
<?php
}

function suffusion_admin_script_loader() {
	global $wp_version;
	wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_script('jquery-ui-draggable');
	wp_enqueue_script('jquery-ui-tabs');
	wp_enqueue_script('jquery-ui-dialog');
	wp_enqueue_script('jquery-jscolor', get_template_directory_uri().'/admin/js/jscolor/jscolor.js', array('jquery'));

	if ($wp_version < 3.1) {
		wp_enqueue_script('suffusion-jquery-ui-custom', get_template_directory_uri().'/admin/js/jquery-ui/jquery-ui-1.7.3.custom.js', array('jquery-ui-core'));
	}
	else {
		wp_enqueue_script('suffusion-jquery-ui-custom', get_template_directory_uri().'/admin/js/jquery-ui/jquery-ui-1.8.7.custom.js', array('jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-mouse', 'jquery-ui-position'));
	}

	// Having jquery.cookie.js as a separate file was preventing some web-hosts from loading the file with a 404 error. Combining it with another JS file seems to work, though.
	// Weird. See: http://www.aquoid.com/forum/viewtopic.php?f=2&t=4189&p=17576#p17573
//	wp_enqueue_script('suffusion-jquery-cookie', get_template_directory_uri().'/admin/js/jquery.cookie.js', array('suffusion-jquery-ui-custom'));
//	wp_enqueue_script('suffusion-fileuploader', get_template_directory_uri().'/admin/js/ajaxupload.js');
	wp_enqueue_script('suffusion-misc', get_template_directory_uri().'/admin/js/suffusion-misc.js', array('suffusion-jquery-ui-custom'));
}

function suffusion_admin_style_loader() {
	wp_enqueue_style('suffusion-admin-jq', get_template_directory_uri().'/admin/js/jquery-ui/css/jquery-ui-1.7.3.custom.css', array(), '3.7.4');
	wp_enqueue_style('suffusion-admin', get_template_directory_uri().'/admin/admin.css', array('suffusion-admin-jq'), '3.7.4');
}

function suffusion_admin_init() {
	register_setting('suffusion_post_type_options', 'suffusion_post_types');
	register_setting('suffusion_taxonomy_options', 'suffusion_taxonomies');
}

add_action('wp_ajax_suffusion_save_custom_post_type', 'suffusion_save_custom_post_type');
function suffusion_save_custom_post_type() {
	global $suffusion_post_type_options, $suffusion_post_type_labels, $suffusion_post_type_args, $suffusion_post_type_supports;
	$post_type_index = $_POST['post_type_index'];
	$suffusion_post_type = $_POST['suffusion_post_type'];

	check_ajax_referer('add-edit-post-type-suffusion', 'add-edit-post-type-wpnonce');
	$suffusion_post_types = get_option('suffusion_post_types');
	$valid = suffusion_validate_custom_type_form($suffusion_post_type, array('options' => $suffusion_post_type_options, 'labels' => $suffusion_post_type_labels, 'args' => $suffusion_post_type_args, 'supports' => $suffusion_post_type_supports));
	if ($valid) {
		if ($suffusion_post_types == null || !is_array($suffusion_post_types)) {
				$suffusion_post_types = array();
		}
		if (isset($post_type_index) && $post_type_index != '' && $post_type_index != -5) {
			$suffusion_post_types[$post_type_index] = $suffusion_post_type;
			$index = $post_type_index;
		}
		else {
			$suffusion_post_types[] = $suffusion_post_type;
			$index = max(array_keys($suffusion_post_types));
		}

		update_option('suffusion_post_types', $suffusion_post_types);
		suffusion_display_custom_post_type($index, "Post Type saved successfully");
	}
	else {
		suffusion_display_custom_post_type(-1, "NOT SAVED: Please populate all required fields");
	}
	suffusion_display_all_custom_post_types();
}

add_action('wp_ajax_suffusion_display_all_custom_post_types', 'suffusion_display_all_custom_post_types');
function suffusion_display_all_custom_post_types() {
	$delete = "";
	if (isset($_POST['processing_function'])) {
		$processing_function = $_POST['processing_function'];
	}
	else {
		$processing_function = "";
	}
	if ($processing_function == 'delete') {
		$delete = suffusion_delete_post_type();
		$delete = $delete == "" ? null : "<div id='message' class='updated fade'><p><strong>$delete</strong></p></div>";
	}
	else if ($processing_function == 'delete_all') {
		$delete = suffusion_delete_all_custom_post_types();
		$delete = $delete == "" ? null : "<div id='message' class='updated fade'><p><strong>$delete</strong></p></div>";
	}
	$suffusion_post_types = get_option('suffusion_post_types');
?>
	<div class='suf-custom-post-types-section suf-section'>
		<table class="form-table">
			<tr>
				<td>
		<?php
		echo $delete;
		echo wp_nonce_field('custom_post_types_suffusion', 'custom_post_types_wpnonce', true, false);
		?>
		<p>The following post types exist. You can edit / delete any of these. Note that if you edit / delete the name of any of these, it will not delete associated posts. You can recreate these post types again and everything will be back to normal:</p>
		<input type="hidden" name="post_type_index" value="" />
		<input type="hidden" name="formaction" value="default" />

		<table class='custom-type-list'>
			<tr>
				<th>Post Type</th>
				<th>Name</th>
				<th>Singular Name</th>
				<th>Action</th>
			</tr>
<?php
	if (is_array($suffusion_post_types)) {
		foreach ($suffusion_post_types as $id => $custom_post_type) {
?>
			<tr>
				<td><?php echo $custom_post_type['post_type']; ?></td>
				<td><?php echo $custom_post_type['labels']['name']; ?></td>
				<td><?php echo $custom_post_type['labels']['singular_name']; ?></td>
				<td><a class='edit-post-type' id='edit-post-type-<?php echo $id; ?>' href='<?php echo site_url(); ?>/wp-admin/admin-ajax.php'>Edit</a> | <a class='delete-post-type' id='delete-post-type-<?php echo $id; ?>' href='<?php echo site_url(); ?>/wp-admin/admin-ajax.php'>Delete</a></td>
			</tr>
<?php
		}
	}
?>
		</table>

		<div class="suf-button-bar">
			<h2>Custom Type Actions</h2>
			<label><input name="delete-all-custom-post-types" type="button" value="Delete All Custom Post Types" class="button delete-all-custom-post-types" /></label>
		</div><!-- suf-button-bar -->
				</td>
			</tr>
		</table>
	</div><!-- .suf-custom-post-types-section -->
<?php
	if ($processing_function == 'delete' || $processing_function == 'delete_all') {
		suffusion_display_custom_post_type(-1);
	}
}

add_action('wp_ajax_suffusion_modify_post_type_layout', 'suffusion_modify_post_type_layout');
function suffusion_modify_post_type_layout() {
	$layout_positions = array('hide' => 'Do not show', 'tleft' => 'Below title, left', 'tright' => 'Below title, right',
		'bleft' => 'Below content, left', 'bright' => 'Below content, right');
	$delete = "";
	$processing_function = $_POST['processing_function'];
	if (isset($processing_function) && $processing_function == 'delete') {
		$delete = suffusion_delete_post_type();
		$delete = $delete == "" ? null : "<div id='message' class='updated fade'><p><strong>$delete</strong></p></div>";
	}
	else if (isset($processing_function) && $processing_function == 'delete_all') {
		$delete = suffusion_delete_all_custom_post_types();
		$delete = $delete == "" ? null : "<div id='message' class='updated fade'><p><strong>$delete</strong></p></div>";
	}
	$suffusion_post_types = get_option('suffusion_post_types');
?>
	<div class='suf-modify-post-type-layout-section suf-section'>
		<table class="form-table">
			<tr>
				<td>
		<?php echo $delete; ?>
		<p>The following post types exist. You can edit / delete any of these. Note that if you edit / delete the name of any of these, it will not delete associated posts. You can recreate these post types again and everything will be back to normal:</p>
		<input type="hidden" name="post_type_index" value="" />
		<input type="hidden" name="formaction" value="default" />

		<table class='custom-type-list'>
			<tr>
				<th>Post Type</th>
				<th>Position of Elements</th>
			</tr>
<?php
	if (is_array($suffusion_post_types)) {
		foreach ($suffusion_post_types as $id => $custom_post_type) {
?>
			<tr>
				<td><?php echo $custom_post_type['post_type']; ?></td>
				<td>
					<?php
						$custom_post_type_supports = $custom_post_type['options'];
						if (in_array('author', $custom_post_type_supports)) {
							echo "Author Position: ";
						}
					?>
				</td>
				<td><?php echo $custom_post_type['labels']['singular_name']; ?></td> -->
			</tr>
<?php
		}
	}
?>
		</table>

		<div class="suf-button-bar">
			<h2>Custom Type Actions</h2>
			<label><input name="save-post-type-layouts" type="button" value="Save Post Type Layouts" class="button delete-all-custom-post-types" /></label>
		</div><!-- suf-button-bar -->
				</td>
			</tr>
		</table>
	</div><!-- .suf-modify-post-type-layout-section -->
<?php
	if ($processing_function == 'delete' || $processing_function == 'delete_all') {
		suffusion_display_custom_post_type(-1);
	}
}

function suffusion_delete_post_type() {
	// For some reason a blank nonce is being fetched here even if I do $_POST['custom_post_types_wpnonce']. Weird
	check_ajax_referer('custom_post_types_suffusion', 'custom_post_types_wpnonce');
	$post_type_index = $_POST['post_type_index'];
	$ret = "";
	if (isset($post_type_index)) {
		$suffusion_post_types = get_option('suffusion_post_types');
		if (is_array($suffusion_post_types)) {
			unset($suffusion_post_types[$post_type_index]);
			update_option('suffusion_post_types', $suffusion_post_types);
			$ret = "Post type deleted.";
		}
		else {
			$ret = "Failed to delete post type. Post types are not stored as an array in the database.";
		}
	}
	return $ret;
}

function suffusion_delete_all_custom_post_types() {
	check_ajax_referer('custom_post_types_suffusion', 'custom_post_types_wpnonce');
	$option = get_option('suffusion_post_types');
	if (isset($option) && is_array($option)) {
		$ret = delete_option('suffusion_post_types');
		if ($ret) {
			$ret = "All post types deleted.";
		}
		else {
			$ret = "Failed to delete post types.";
		}
	}
	else {
		$ret = "No post types exist!";
	}
	return $ret;
}

add_action('wp_ajax_suffusion_display_custom_post_type', 'suffusion_display_custom_post_type');
function suffusion_display_custom_post_type($index = null, $msg = null) {
	global $suffusion_post_type_labels, $suffusion_post_type_args, $suffusion_post_type_supports, $suffusion_post_type_options;
	if (isset($_POST['post_type_index'])) {
		$post_type_index = $_POST['post_type_index'];
	}
	else {
		$post_type_index = -5;
	}
	$suffusion_post_types = get_option('suffusion_post_types');
	if (is_array($suffusion_post_types) && $post_type_index != '' && $post_type_index != -5) {
		$suffusion_post_type = $suffusion_post_types[$post_type_index];
	}
	else if (is_array($suffusion_post_types) && ($post_type_index =='' || $post_type_index == -5) && ($index > -1)) {
		$suffusion_post_type = $suffusion_post_types[$index];
	}
	else if (isset($_POST['suffusion_post_type']) && ($post_type_index =='' || $post_type_index == -5) && $index == -1) {
		$suffusion_post_type = $_POST['suffusion_post_type'];
	}
	else {
		$suffusion_post_type = array('labels' => $suffusion_post_type_labels, 'args' => $suffusion_post_type_args, 'supports' => $suffusion_post_type_supports);
		foreach ($suffusion_post_type as $parameter_type => $parameters) {
			foreach ($parameters as $parameter => $parameter_value) {
				$suffusion_post_type[$parameter_type][$parameter] = FALSE;
			}
		}
	}

	$msg = $msg == null ? null : "<div id='message' class='updated fade'><p><strong>$msg</strong></p></div>";
?>
<div class='suf-post-type-edit-section suf-section'>
	<table class="form-table">
		<tr>
			<td>
	<?php
	echo $msg;
	echo wp_nonce_field('add-edit-post-type-suffusion', 'add-edit-post-type-wpnonce', true, false);
	?>
	<input type='hidden' name='post_type_index' id='post_type_index' value="<?php echo $post_type_index; ?>"/>
	<table>
		<?php
			foreach ($suffusion_post_type_options as $option) {
		?>
		<tr>
			<?php suffusion_options_process_custom_type_option($option, null, $suffusion_post_type, 'suffusion_post_type'); ?>
		</tr>
		<?php
			}
		?>
	</table>

	<table class="custom-type-table">
		<tr>
			<col class='half-width' />
			<col/>
		</tr>
		<tr valign='top'>
			<th scope='row'>Display information</th>
			<th scope='row'>Arguments</th>
		</tr>
		<tr>
			<td rowspan='2'>
				<table>
					<?php foreach ($suffusion_post_type_labels as $label) { ?>
					<tr>
						<?php suffusion_options_process_custom_type_option($label, 'labels', $suffusion_post_type, 'suffusion_post_type'); ?>
					</tr>
					<?php } ?>
				</table>
			</td>

			<td>
				<table>
					<?php foreach ($suffusion_post_type_args as $arg) { ?>
					<tr>
						<?php suffusion_options_process_custom_type_option($arg, 'args', $suffusion_post_type, 'suffusion_post_type'); ?>
					</tr>
					<?php } ?>
				</table>
			</td>
		</tr>

		<tr>
			<td>
				<table width='100%'>
					<tr>
						<th>Supports</th>
					</tr>

					<tr>
						<td>
							<table>
								<?php foreach ($suffusion_post_type_supports as $support) { ?>
								<tr>
									<?php suffusion_options_process_custom_type_option($support, 'supports', $suffusion_post_type, 'suffusion_post_type'); ?>
								</tr>
								<?php } ?>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>

	<div class="suf-button-bar">
		<h2>Custom Type Actions</h2>
		<label><input name="save-post-type-edit" type="button" value="Save changes" class="button save-post-type-edit" /></label>
		<label><input name="reset-post-type-edit" type="button" value="Clear all fields" class="button reset-post-type-edit" /></label>
		<input type="hidden" name="formaction" value="default" />
		<input type="hidden" name="formcategory" value="add-edit-post-type" />
	</div><!-- suf-button-bar -->
			</td>
		</tr>
		</table>
</div><!-- suf-post-type-edit-section -->
<?php
}

add_action('wp_ajax_suffusion_save_custom_taxonomy', 'suffusion_save_custom_taxonomy');
function suffusion_save_custom_taxonomy() {
	global $suffusion_taxonomy_options, $suffusion_taxonomy_labels, $suffusion_taxonomy_args;
	$taxonomy_index = $_POST['taxonomy_index'];
	$suffusion_taxonomy = $_POST['suffusion_taxonomy'];
	$valid = suffusion_validate_custom_type_form($suffusion_taxonomy, array('options' => $suffusion_taxonomy_options, 'labels' => $suffusion_taxonomy_labels, 'args' => $suffusion_taxonomy_args));
	if ($valid) {
		$suffusion_taxonomies = get_option('suffusion_taxonomies');
		if ($suffusion_taxonomies == null || !is_array($suffusion_taxonomies)) {
			$suffusion_taxonomies = array();
		}
		if (isset($taxonomy_index) && $taxonomy_index != '' && $taxonomy_index != -5) {
			$suffusion_taxonomies[$taxonomy_index] = $suffusion_taxonomy;
			$index = $taxonomy_index;
		}
		else {
			$suffusion_taxonomies[] = $suffusion_taxonomy;
			$index = max(array_keys($suffusion_taxonomies));
		}

		update_option('suffusion_taxonomies', $suffusion_taxonomies);
		suffusion_display_custom_taxonomy($index, "Taxonomy saved successfully");
	}
	else {
		suffusion_display_custom_taxonomy(-1, "NOT SAVED: Please populate all required fields");
	}
	suffusion_display_all_custom_taxonomies();
}

add_action('wp_ajax_suffusion_display_all_custom_taxonomies', 'suffusion_display_all_custom_taxonomies');
function suffusion_display_all_custom_taxonomies() {
	$delete = "";
	if (isset($_POST['processing_function'])) {
		$processing_function = $_POST['processing_function'];
	}
	else {
		$processing_function = "";
	}
	if ($processing_function == 'delete') {
		$delete = suffusion_delete_taxonomy();
		$delete = $delete == "" ? null : "<div id='message' class='updated fade'><p><strong>$delete</strong></p></div>";
	}
	else if ($processing_function == 'delete_all') {
		$delete = suffusion_delete_all_custom_taxonomies();
		$delete = $delete == "" ? null : "<div id='message' class='updated fade'><p><strong>$delete</strong></p></div>";
	}
	$suffusion_taxonomies = get_option('suffusion_taxonomies');
?>
	<div class='suf-custom-taxonomies-section suf-section'>
		<table class="form-table">
			<tr>
				<td>
		<?php
		echo $delete;
		echo wp_nonce_field('custom-taxonomies-suffusion', 'custom-taxonomies-wpnonce', true, false);
		?>
		<p>The following taxonomies exist. You can edit / delete any of these. Note that if you edit / delete the name of any of these, it will not delete associated posts. You can recreate these taxonomies again and everything will be back to normal:</p>
		<input type="hidden" name="taxonomy_index" value="" />
		<input type="hidden" name="formaction" value="default" />

		<table class='custom-type-list'>
			<tr>
				<th>Taxonomy</th>
				<th>Object Type</th>
				<th>Name</th>
				<th>Singular Name</th>
				<th>Action</th>
			</tr>
<?php
	if (is_array($suffusion_taxonomies)) {
		foreach ($suffusion_taxonomies as $id => $custom_taxonomy) {
?>
			<tr>
				<td><?php echo $custom_taxonomy['taxonomy']; ?></td>
				<td><?php echo $custom_taxonomy['object_type']; ?></td>
				<td><?php echo $custom_taxonomy['labels']['name']; ?></td>
				<td><?php echo $custom_taxonomy['labels']['singular_name']; ?></td>
				<td><a class='edit-taxonomy' id='edit-taxonomy-<?php echo $id; ?>' href='<?php echo site_url(); ?>/wp-admin/admin-ajax.php'>Edit</a> | <a class='delete-taxonomy' id='delete-taxonomy-<?php echo $id; ?>' href='<?php echo site_url(); ?>/wp-admin/admin-ajax.php'>Delete</a></td>
			</tr>
<?php
		}
	}
?>
		</table>

		<div class="suf-button-bar">
			<h2>Custom Type Actions</h2>
			<label><input name="delete-all-custom-taxonomies" type="button" value="Delete All Custom Taxonomies" class="button delete-all-custom-taxonomies" /></label>
		</div><!-- suf-button-bar -->
				</td>
			</tr>
			</table>
	</div><!-- .suf-custom-taxonomies-section -->
<?php
	if ($processing_function == 'delete' || $processing_function == 'delete_all') {
		suffusion_display_custom_taxonomy(-1);
	}
}

add_action('wp_ajax_suffusion_display_custom_taxonomy', 'suffusion_display_custom_taxonomy');
function suffusion_display_custom_taxonomy($index = null, $msg = null) {
	global $suffusion_taxonomy_labels, $suffusion_taxonomy_args, $suffusion_taxonomy_options;
	if (isset($_POST['taxonomy_index'])) {
		$taxonomy_index = $_POST['taxonomy_index'];
	}
	else {
		$taxonomy_index = -5;
	}
	$suffusion_taxonomies = get_option('suffusion_taxonomies');
	if (is_array($suffusion_taxonomies) && $taxonomy_index != '' && $taxonomy_index != -5) {
		$suffusion_taxonomy = $suffusion_taxonomies[$taxonomy_index];
	}
	else if (is_array($suffusion_taxonomies) && ($taxonomy_index =='' || $taxonomy_index == -5) && ($index > -1)) {
		$suffusion_taxonomy = $suffusion_taxonomies[$index];
	}
	else if (isset($_POST['suffusion_taxonomy']) && ($taxonomy_index =='' || $taxonomy_index == -5) && $index == -1) {
		$suffusion_taxonomy = $_POST['suffusion_taxonomy'];
	}
	else {
		$suffusion_taxonomy = array('labels' => $suffusion_taxonomy_labels, 'args' => $suffusion_taxonomy_args);
		foreach ($suffusion_taxonomy as $parameter_type => $parameters) {
			foreach ($parameters as $parameter => $parameter_value) {
				$suffusion_taxonomy[$parameter_type][$parameter] = FALSE;
			}
		}
	}

	$msg = $msg == null ? null : "<div id='message' class='updated fade'><p><strong>$msg</strong></p></div>";
?>
<div class='suf-taxonomy-edit-section suf-section'>
	<table class="form-table">
		<tr>
			<td>
	<?php
	echo $msg;
	echo wp_nonce_field('add-edit-taxonomy-suffusion', 'add-edit-taxonomy-wpnonce', true, false);
	?>
	<input type='hidden' name='taxonomy_index' id='taxonomy_index' value="<?php echo $taxonomy_index; ?>"/>
	<table>
	<?php
		foreach ($suffusion_taxonomy_options as $option) {
	?>
	<tr>
		<?php suffusion_options_process_custom_type_option($option, null, $suffusion_taxonomy, 'suffusion_taxonomy'); ?>
	</tr>
	<?php
		}
	?>
	</table>

	<table class="custom-type-table">
		<tr>
			<col class='half-width' />
			<col/>
		</tr>
		<tr valign='top'>
			<th scope='row'>Display information</th>
			<th scope='row'>Arguments</th>
		</tr>
		<tr>
			<td>
				<table>
					<?php foreach ($suffusion_taxonomy_labels as $label) { ?>
					<tr>
						<?php suffusion_options_process_custom_type_option($label, 'labels', $suffusion_taxonomy, 'suffusion_taxonomy'); ?>
					</tr>
					<?php } ?>
				</table>
			</td>

			<td>
				<table>
					<?php foreach ($suffusion_taxonomy_args as $arg) { ?>
					<tr>
						<?php suffusion_options_process_custom_type_option($arg, 'args', $suffusion_taxonomy, 'suffusion_taxonomy'); ?>
					</tr>
					<?php } ?>
				</table>
			</td>
		</tr>
	</table>

	<div class="suf-button-bar">
		<h2>Custom Type Actions</h2>
		<label><input name="save-taxonomy-edit" type="button" value="Save changes" class="button save-taxonomy-edit" /></label>
		<label><input name="reset-taxonomy-edit" type="button" value="Clear all fields" class="button reset-taxonomy-edit" /></label>
		<input type="hidden" name="formaction" value="default" />
		<input type="hidden" name="formcategory" value="add-edit-taxonomy" />
	</div><!-- suf-button-bar -->
			</td>
		</tr>
		</table>
</div><!-- suf-taxonomy-edit-section -->
<?php
}

function suffusion_delete_taxonomy() {
	$taxonomy_index = $_POST['taxonomy_index'];
	$ret = "";
	if (isset($taxonomy_index)) {
		$suffusion_taxonomies = get_option('suffusion_taxonomies');
		if (is_array($suffusion_taxonomies)) {
			unset($suffusion_taxonomies[$taxonomy_index]);
			update_option('suffusion_taxonomies', $suffusion_taxonomies);
			$ret = "Taxonomy deleted.";
		}
		else {
			$ret = "Failed to delete taxonomy. Taxonomies are not stored as an array in the database.";
		}
	}
	return $ret;
}

function suffusion_delete_all_custom_taxonomies() {
	$option = get_option('suffusion_taxonomies');
	if (isset($option) && is_array($option)) {
		$ret = delete_option('suffusion_taxonomies');
		if ($ret) {
			$ret = "All taxonomies deleted.";
		}
		else {
			$ret = "Failed to delete taxonomies.";
		}
	}
	else {
		$ret = "No taxonomies exist!";
	}
	return $ret;
}

function suffusion_validate_custom_type_form($custom_type, $validation_options) {
	foreach ($validation_options as $option_type => $option_set) {
		if ($option_type == 'options') {
			$to_validate = $custom_type;
		}
		else {
			if (isset($custom_type[$option_type])) {
				$to_validate = $custom_type[$option_type];
			}
		}
		foreach ($option_set as $option) {
			if (isset($option['reqd'])) {
				if (isset($to_validate[$option['name']]) && trim($to_validate[$option['name']]) == '') {
					return false;
				}
			}
		}
	}
	return true;
}

/**
 * Checks if a new version of the theme is available. If so, a notification is displayed on the top of the Suffusion admin screens,
 * with a link to the release notes of the latest version.
 *
 * @return string
 */
function suffusion_version_checker() {
	$local_version = suffusion_get_current_version();
	
	$feed_url = 'http://www.aquoid.com/news/category/theme-releases/feed/';
	$rss = fetch_feed($feed_url);
	if (is_wp_error($rss)) {
		$error = $rss->get_error_code();
		$update_message = '<div class="updated">Update notifier failed (<code>'.$error.'</code>)</div>';
		return $update_message;
	}

	// Fetch the last published item from this feed.
	$rss_items = $rss->get_items(0, 1);
	if (count($rss_items) == 0) {
		$latest_version_via_rss = 0;
		$update_message = "";
	}
	else {
		$item = $rss_items[0];
		$latest_version_via_rss = $item->get_title();

		//Determine the latest released version from the feed. A version has a structure #.#.#.a (accounting for beta releases)
		$version_regex = "/[0-9\.]+[a-z]*/";
		preg_match($version_regex, $latest_version_via_rss, $latest_version_via_rss);
		$latest_version_via_rss = $latest_version_via_rss[0];

		if (strcmp($latest_version_via_rss, $local_version) > 0) {
			$update_message = "<div class='updated'>A new version of Suffusion is available! Click to read the release notes and determine if you want to update: <a href='".$item->get_link()."'>".$item->get_title()."</a>.</div>";
		}
		else {
			$update_message = "";
		}
	}

	return $update_message;
}

/**
 * Checks if you are using a non-American-English version of the theme. If so, it checks where your translations are.
 * If you are using the core Suffusion theme:
 *  1. This prompts you to move your translations if found to a child theme
 *  2. Otherwise it provides a link to the page where translations can be got.
 * If you are using a child theme of Suffusion:
 *  1. If your translation file is in the core theme folder it suggests to move it to the child theme
 *  2. If there are no translation files in the core theme folder or the child theme folder, it points you to the translation page.
 *
 * @return string
 */
function suffusion_translation_checker() {
	if (!defined('WPLANG') || WPLANG == 'en' || WPLANG == '') {
		$lang = 'en_US';
	}
	else {
		$lang = WPLANG;
	}

	$message = "";
	if ($lang != 'en_US') {
		if (!is_child_theme()) {
			if (file_exists(TEMPLATEPATH."/translation/$lang.mo")) {
				$message = "<div class='updated'>You are using a version of WordPress that is not in American English, and your translation files are in the theme's main folder.
					You will lose these files if you upgrade the theme. <a href='http://www.aquoid.com/news/themes/suffusion/translating-suffusion/#use-basic'>Move your translations to a child theme</a> instead.</div>";
			}
			else {
				$message = "<div class='updated'>You are using a version of WordPress that is not in American English.
					Translations for your language <a href='http://www.aquoid.com/news/themes/suffusion/translating-suffusion/'>might be available</a>.</div>";
			}
		}
		else {
			if (file_exists(TEMPLATEPATH."/translation/$lang.mo") && !file_exists(STYLESHEETPATH."/translation/$lang.mo")) {
				$message = "<div class='updated'>Your translation files are in Suffusion's folder. You will lose these files if you upgrade the theme.
					<a href='http://www.aquoid.com/news/themes/suffusion/translating-suffusion/#use-basic'>Move your translations to a child theme</a> instead.</div>";
			}
			else if (!file_exists(STYLESHEETPATH."/translation/$lang.mo")) {
				$message = "<div class='updated'>You are using a version of WordPress that is not in American English.
					Translations for your language <a href='http://www.aquoid.com/news/themes/suffusion/translating-suffusion/'>might be available</a>.</div>";
			}
		}
	}
	return $message;
}

/**
 * Determines if you are running BP. If so, and if you don't have the Suffusion BuddyPress Pack installed, it directs you to install the same.
 * If the plugin is installed and you are not running it on a child theme, it recommends you to switch to a child theme.
 *
 * @return string
 */
function suffusion_bp_checker() {
	global $bp;
	$message = "";
	if (isset($bp)) {// Using BP
		if (!class_exists('Suffusion_BP_Pack')) {
			$message = "<div class='updated'>You are using BuddyPress. Please install the <a href='http://wordpress.org/extend/plugins/suffusion-buddypress-pack'>Suffusion BuddyPress Pack</a> for best results.
				See the <a href='http://www.aquoid.com/news/plugins/suffusion-buddypress-pack/'>plugin's home page</a> for further instructions.</div>";
		}
		else if (!is_child_theme()) {
			$message = "<div class='updated'>The <a href='http://wordpress.org/extend/plugins/suffusion-buddypress-pack'>Suffusion BuddyPress Pack</a> works best in a child theme.
				See the <a href='http://www.aquoid.com/news/plugins/suffusion-buddypress-pack/'>plugin's home page</a> for further instructions.</div>";
		}
	}
	return $message;
}

function suffusion_options_process_custom_type_option($option, $section, $suffusion_custom_type, $custom_type_name) {
	if (is_array($option)) {
		$required = "";
		if (isset($option['reqd'])) {
			$required = " <span class='note'>[Required *]</span> ";
		}
		switch ($option['type']) {
			case 'text':
				echo "<td>".$option['desc'].$required."</td>";
				if ($section != null) {
					if (isset($option['name']) && isset($suffusion_custom_type[$section][$option['name']])) {
						echo "<td><input name='{$custom_type_name}[$section][".$option['name']."]' type='text' value=\"".$suffusion_custom_type[$section][$option['name']]."\"/></td>";
					}
					else {
						echo "<td><input name='{$custom_type_name}[$section][".$option['name']."]' type='text' value=\"\"/></td>";
					}
				}
				else {
					if (isset($option['name']) && isset($suffusion_custom_type[$option['name']])) {
						echo "<td><input name='{$custom_type_name}[".$option['name']."]' type='text' value=\"".$suffusion_custom_type[$option['name']]."\"/></td>";
					}
					else {
						echo "<td><input name='{$custom_type_name}[".$option['name']."]' type='text' value=\"\"/></td>";
					}
				}
				break;

			case 'checkbox':
?>
		<td colspan='2'>
		<?php
				if ($section != null) {
		?>
			<input name='<?php echo $custom_type_name; ?>[<?php echo $section; ?>][<?php echo $option['name'];?>]' type='checkbox' value='1' <?php if (isset($suffusion_custom_type[$section][$option['name']])) checked('1', $suffusion_custom_type[$section][$option['name']]); ?> />
		<?php
				}
				else {
		?>
			<input name='<?php echo $custom_type_name; ?>[<?php echo $option['name'];?>]' type='checkbox' value='1' <?php if (isset($suffusion_custom_type[$option['name']])) checked('1', $suffusion_custom_type[$option['name']]); ?> />
		<?php
				}
		?>
			&nbsp;&nbsp;<?php echo $option['desc'].$required;?>
		</td>
<?php
		        break;

			case 'select':
?>
		<td><?php echo $option['desc'].$required;?></td>
		<td>
		<?php
				if ($section != null) {
					if (!isset($suffusion_custom_type[$section][$option['name']]) || $suffusion_custom_type[$section][$option['name']] == null) {
						$value = $option['std'];
					}
					else {
						$value = $suffusion_custom_type[$section][$option['name']];
					}
		?>
			<select name='<?php echo $custom_type_name; ?>[<?php echo $section; ?>][<?php echo $option['name'];?>]' >
		<?php
					foreach ($option['options'] as $dd_value => $dd_display) {
		?>
				<option value='<?php echo $dd_value;?>' <?php if ($value == $dd_value) { echo " selected='selected' "; } ?>><?php echo $dd_display; ?></option>
		<?php
					}
		?>

			</select>
		<?php
				}
				else {
					if (!isset($suffusion_custom_type[$option['name']]) || $suffusion_custom_type[$option['name']] == null) {
						$value = $option['std'];
					}
					else {
						$value = $suffusion_custom_type[$option['name']];
					}
		?>
			<select name='<?php echo $custom_type_name; ?>[<?php echo $option['name'];?>]' >
		<?php
					foreach ($option['options'] as $dd_value => $dd_display) {
		?>
				<option value='<?php echo $dd_value;?>' <?php if ($value == $dd_value) { echo " selected='selected' "; } ?>><?php echo $dd_display; ?></option>
		<?php
					}
		?>

			</select>
		<?php
				}
		?>
		</td>
<?php
		        break;
		}
	}
}
?>