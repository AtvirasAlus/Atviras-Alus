<?php
/**
 * Renders the theme options. This file is to be loaded only for the admin screen
 */

global $suffusion_options_file, $suffusion_options_intro_page, $suffusion_options_theme_skinning_page, $suffusion_options_visual_effects_page, $suffusion_options_sidebars_and_widgets_page, $suffusion_options_blog_features_page, $suffusion_options_templates_page;
class Suffusion_Options_Renderer {
	var $options;
	var $option_structure;
	var $file;
	var $hidden_options;
	var $shown_options;
	var $nested_options;
	var $reverse_options;
	var $option_defaults;
	var $allowed_values;
	var $displayed_sections;
	var $previous_displayed_section;

	function Suffusion_Options_Renderer($options, $file) {
		$this->options = $options;
		$this->file = $file;
		$this->displayed_sections = 0;
		$this->shown_options = array();
		$this->reverse_options = array();
		$this->option_defaults = array();
		$this->allowed_values = array();
		$all_options = get_option('suffusion_options');
		if (!isset($all_options)) {
			$this->hidden_options = array();
		}
		else {
			$this->hidden_options = $all_options;
		}
		foreach ($options as $option) {
			if (isset($option['id'])) {
				$this->shown_options[] = $option['id'];
				$this->reverse_options[$option['id']] = $option['type'];
				if (isset($option['std'])) {
					$this->option_defaults[$option['id']] = $option['std'];
				}
				if (isset($option['options'])) {
					$this->allowed_values[$option['id']] = $option['options'];
				}
				if (isset($this->hidden_options[$option['id']])) unset($this->hidden_options[$option['id']]);
			}
		}
		add_action('wp_ajax_suffusion_admin_upload_file', array(&$this, 'admin_upload_file'));
	}

	/**
	 * Renders an option whose type is "title". Invoked by add_settings_field.
	 *
	 * @param  $value
	 * @return void
	 */
	function create_title($value) {
		echo '<h2 class="suf-header-1">'.$value['name']."</h2>\n";
	}

	/**
	 * Renders an option whose type is "suf-header-2". Invoked by add_settings_field.
	 *
	 * @param  $value
	 * @return void
	 */
	function create_suf_header_2($value) {
		echo '<h3 class="suf-header-2">'.$value['name']."</h3>\n";
	}

	/**
	 * Renders an option whose type is "suf-header-3". Invoked by add_settings_field.
	 *
	 * @param  $value
	 * @return void
	 */
	function create_suf_header_3($value) {
		echo '<h3 class="suf-header-3">'.$value['name']."</h3>\n";
	}

	/**
	 * Creates the opening markup for each option.
	 *
	 * @param  $value
	 * @return void
	 */
	function create_opening_tag($value) {
		$group_class = "";

		if (isset($value['grouping'])) {
			$group_class = "suf-grouping-rhs";
		}
		echo '<div class="suf-section fix">'."\n";
		if ($group_class != "") {
			echo "<div class='$group_class fix'>\n";
		}
		if (isset($value['name'])) {
			echo "<h3>" . $value['name'] . "</h3>\n";
		}
		if (isset($value['desc'])) {
			echo $value['desc']."<br />";
		}
		if (isset($value['note'])) {
			echo "<span class=\"note\">".$value['note']."</span><br />";
		}
	}

	/**
	 * Creates the closing markup for each option.
	 *
	 * @return void
	 */
	function create_closing_tag($value) {
		if (isset($value['grouping'])) {
			echo "</div>\n";
		}
		echo "</div><!-- suf-section -->\n";
	}

	/**
	 * Creates an option-grouping within a section. Invoked by add_settings_field.
	 *
	 * @param  $value
	 * @return void
	 */
	function create_suf_grouping($value) {
		echo "<div class='{$value['category']}-grouping suf-section grouping fix'>\n";
		echo "<h3 class='suf-group-handler'>".$value['name']."</h3>\n";
		if (isset($value['desc'])) echo $value['desc']."<br />";
/*		echo "<div class='{$value['category']}-body fix'>\n";
		echo "<div class='{$value['category']}-lhs suf-grouping-lhs'>\n";
		if (isset($value['desc'])) echo $value['desc']."<br />";
		echo "</div>\n";
		echo "<div id='{$value['category']}-rhs' class='{$value['category']}-rhs suf-grouping-rhs'>\n";
		echo "</div>\n";
		echo "</div>\n";*/
		echo "</div>\n";
	}

	/**
	 * Renders an option whose type is "text". Invoked by add_settings_field.
	 *
	 * @param  $value
	 * @return void
	 */
	function create_section_for_text($value) {
		global $suffusion_options;
		$this->create_opening_tag($value);
		$text = "";
		if (!isset($suffusion_options[$value['id']])) {
			$text = $value['std'];
		}
		else {
			$text = $suffusion_options[$value['id']];
			$text = stripslashes($text);
			$text = esc_attr($text);
		}

		echo '<input type="text" name="suffusion_options['.$value['id'].']" value="'.$text.'" />'."\n";
		if (isset($value['hint'])) {
			echo " &laquo; ".$value['hint']."<br />\n";
		}
		$this->create_closing_tag($value);
	}

	/**
	 * Renders an option whose type is "textarea". Invoked by add_settings_field.
	 *
	 * @param  $value
	 * @return void
	 */
	function create_section_for_textarea($value) {
		global $suffusion_options;
		$this->create_opening_tag($value);
		echo '<textarea name="suffusion_options['.$value['id'].']" cols="" rows="">'."\n";
		if (isset($suffusion_options[$value['id']]) && $suffusion_options[$value['id']] != "") {
			$text = stripslashes($suffusion_options[$value['id']]);
			$text = esc_attr($text);
			echo $text;
		}
		else {
			echo $value['std'];
		}
		echo '</textarea>';
		if (isset($value['hint'])) {
			echo " &laquo; ".$value['hint']."<br />\n";
		}
		$this->create_closing_tag($value);
	}

	/**
	 * Renders an option whose type is "select". Invoked by add_settings_field.
	 *
	 * @param  $value
	 * @return void
	 */
	function create_section_for_select($value) {
		global $suffusion_options;
		$this->create_opening_tag($value);
		echo '<select name="suffusion_options['.$value['id'].']">'."\n";
		foreach ($value['options'] as $option_value => $option_text) {
			echo "<option ";
			if (isset($suffusion_options[$value['id']]) && $suffusion_options[$value['id']] == $option_value) {
				echo ' selected="selected"';
			}
			elseif (!isset($suffusion_options[$value['id']])&& $option_value == $value['std']) {
				echo ' selected="selected"';
			}
			echo " value='$option_value' >".$option_text."</option>\n";
		}
		echo "</select>\n";
		$this->create_closing_tag($value);
	}

	/**
	 * Renders an option whose type is "multi-select". Invoked by add_settings_field.
	 *
	 * @param  $value
	 * @return void
	 */
	function create_section_for_multi_select($value) {
		global $suffusion_options;
		$this->create_opening_tag($value);
		echo '<div class="suf-checklist">'."\n";
		echo '<ul class="suf-checklist" id="'.$value['id'].'-chk" >'."\n";
		if (isset($value['std'])) {
			$consolidated_value = $value['std'];
		}
		if (isset($suffusion_options[$value['id']])) {
			$consolidated_value = $suffusion_options[$value['id']];
		}
		if (!isset($consolidated_value)) {
			$consolidated_value = "";
		}
		$consolidated_value = trim($consolidated_value);
		$exploded = array();
		if ($consolidated_value != '') {
			$exploded = explode(',', $consolidated_value);
		}
		foreach ($value['options'] as $option_value => $option_list) {
			$checked = " ";
			if ($consolidated_value) {
				foreach ($exploded as $idx => $checked_value) {
					if ($checked_value == $option_value) {
						$checked = " checked='checked' ";
						break;
					}
				}
			}
			echo "<li>\n";
			$depth = 0;
			if (isset($option_list['depth'])) {
				$depth = $option_list['depth'];
			}
			echo '<input type="checkbox" name="'.$value['id']."_".$option_value.'" value="true" '.$checked.' class="depth-'.($depth+1).' suf-options-checkbox-'.$value['id'].'" />'.$option_list['title']."\n";
			echo "</li>\n";
		}
		echo "</ul>\n";
		echo "<div class='suf-multi-select-button-panel'>\n";
		echo "<input type='button' name='".$value['id']."-button-all' value='Select All' class='button-all suf-multi-select-button' />\n";
		echo "<input type='button' name='".$value['id']."-button-none' value='Select None' class='button-none suf-multi-select-button' />\n";
		echo "</div>\n";
		if (isset($suffusion_options[$value['id']])) {
			$set_value = $suffusion_options[$value['id']];
		}
		else {
			$set_value = "";
		}
		echo '<input type="hidden" name="suffusion_options['.$value['id'].']" id="'.$value['id'].'" value="'.$set_value.'"/>'."\n";
		echo "</div>\n";
		$this->create_closing_tag($value);
	}

	/**
	 * Renders an option whose type is "radio". Invoked by add_settings_field.
	 *
	 * @param  $value
	 * @return void
	 */
	function create_section_for_radio($value) {
		global $suffusion_options;
		$this->create_opening_tag($value);
		foreach ($value['options'] as $option_value => $option_text) {
			$option_value = stripslashes($option_value);
			$checked = ' ';
			if (isset($suffusion_options[$value['id']]) && stripslashes($suffusion_options[$value['id']]) == $option_value) {
				$checked = ' checked="checked" ';
			}
			else if (!isset($suffusion_options[$value['id']]) && $value['std'] == $option_value){
				$checked = ' checked="checked" ';
			}
			else {
				$checked = ' ';
			}
			echo '<div class="suf-radio"><input type="radio" name="suffusion_options['.$value['id'].']" value="'.$option_value.'" '.$checked."/>".$option_text."</div>\n";
		}
		$this->create_closing_tag($value);
	}

	/**
	 * Renders an option whose type is "checkbox". Invoked by add_settings_field.
	 *
	 * @param  $value
	 * @return void
	 */
	function create_section_for_checkbox($value) {
		global $suffusion_options;
		$this->create_opening_tag($value);
		if($suffusion_options[$value['id']]) {
			$checked = "checked=\"checked\"";
		}
		else {
			$checked = "";
		}
		echo '<input type="checkbox" name="suffusion_options['.$value['id'].']" value="true" '.$checked."/>\n";
		$this->create_closing_tag($value);
	}

	/**
	 * Renders an option whose type is "color-picker". Invoked by add_settings_field.
	 *
	 * @param  $value
	 * @return void
	 */
	function create_section_for_color_picker($value) {
		global $suffusion_options;
		$this->create_opening_tag($value);
		if (!isset($suffusion_options[$value['id']])) {
			$color_value = $value['std'];
		}
		else {
			$color_value = $suffusion_options[$value['id']];
		}
		if (substr($color_value, 0, 1) != '#') {
			$color_value = "#$color_value";
		}

		echo '<div class="color-picker">'."\n";
		echo '<input type="text" id="'.$value['id'].'" name="suffusion_options['.$value['id'].']" value="'.$color_value.'" class="color color-'.$value['id'].'" /> <br/>'."\n";
		echo "<strong>Default: ".$value['std']."</strong> (You can copy and paste this into the box above)\n";
		echo "</div>\n";
		$this->create_closing_tag($value);
	}

	/**
	 * Renders an option whose type is "upload". Invoked by add_settings_field.
	 *
	 * @param  $value
	 * @return void
	 */
	function create_section_for_upload($value) {
		global $suffusion_options;
		$this->create_opening_tag($value);
		$upload = "";
		if (!isset($suffusion_options[$value['id']])) {
			$upload = $value['std'];
		}
		else {
			$upload = $suffusion_options[$value['id']];
			$upload = stripslashes($upload);
			$upload = esc_attr($upload);
		}
		$hint = isset($value['hint']) ? $value['hint'] : null;
		$this->display_upload_field($upload, $value['id'], "suffusion_options[{$value['id']}]", $hint);
		$this->create_closing_tag($value);
	}

	/**
	 * This method displays an upload field and button. This has been separated from the create_section_for_upload method,
	 * because this is used by the create_section_for_background as well.
	 *
	 * @param  $upload
	 * @param  $id
	 * @param  $hint
	 * @return void
	 */
	function display_upload_field($upload, $id, $name, $hint = null) {
		echo '<input type="text" name="'.$name.'" id="'.$id.'" value="'.$upload.'" />'."\n";
		if ($hint != null) {
			echo " &laquo; ".$hint."<br />\n";
		}

		echo '<div class="upload-buttons">';
		$hide = empty($upload) ? '' : 'hidden';
		echo '<span class="button image_upload_button '.$hide.'" id="upload_'.$id.'">Upload Image</span>';

		$hide = !empty($upload) ? '' : 'hidden';
		echo '<span class="button image_reset_button '. $hide.'" id="reset_'.$id.'">Reset</span>';
		echo '</div>' . "\n";

		if(!empty($upload)){
			echo "<div id='suffusion-preview-$id'>\n";
			echo "<p><strong>Preview:</strong></p>\n";
		    echo '<img class="suffusion-option-image" id="image_'.$id.'" src="'.$upload.'" alt="" />';
			echo "</div>";
		}
	}

	/**
	 * Renders an option whose type is "sortable-list". Invoked by add_settings_field.
	 *
	 * @param  $value
	 * @return void
	 */
	function create_section_for_sortable_list($value) {
		global $suffusion_options;
		$this->create_opening_tag($value);
		if (!isset($suffusion_options[$value['id']])) {
			$list_order = $value['std'];
		}
		else {
			$list_order = $suffusion_options[$value['id']];
		}
		if (is_array($list_order)) { // The order has not been set. These are the default values
			$list_order_array = $list_order;
			$list_order = implode(',', array_keys($list_order_array));
		}
		else { // The order may have been set. We need to reconcile any additions / deletions from the standard list.
			$defaults = $value['std'];
			$keys = explode(',',$list_order);
			$clean_keys = array();
			$list_order_array = array();
			foreach ($keys as $key) {
				if (isset($defaults[$key])) {
					$clean_keys[] = $key;
					$list_order_array[$key] = $defaults[$key];
				}
			}

			foreach ($defaults as $key => $key_value) {// Checking for additions
				if (!in_array($key, $clean_keys)) {
					$clean_keys[] = $key;
					$list_order_array[$key] = $key_value;
				}
			}
			$list_order = implode(',', $clean_keys);
		}
	?>
		<script type="text/javascript">
		$j = jQuery.noConflict();
		$j(document).ready(function() {
			$j("#<?php echo $value['id']; ?>-ui").sortable({
				update: function(){
					$j('input#<?php echo $value['id']; ?>').val($j("#<?php echo $value['id']; ?>-ui").sortable('toArray'));
				}
			});
			$j("#<?php echo $value['id']; ?>-ui").disableSelection();
		});
		</script>
	<?php
		echo "<ul id='".$value['id']."-ui' name='".$value['id']."-ui' class='suf-sort-list'>\n";
/*		foreach ($list_order_array as $list_item) {
			echo "<li id='".$list_item['key']."' class='suf-sort-list-item'>".$list_item['value']."</li>";
		}*/
		foreach ($list_order_array as $key => $key_value) {
			echo "<li id='".$key."' class='suf-sort-list-item'>".$key_value."</li>";
		}
		echo "</ul>\n";
		echo "<input id='".$value['id']."' name='suffusion_options[".$value['id']."]' type='hidden' value='$list_order'/>";
		$this->create_closing_tag($value);
	}

	/**
	 * Renders an option whose type is "slider". Invoked by add_settings_field.
	 *
	 * @param  $value
	 * @return void
	 */
	function create_section_for_slider($value) {
		global $suffusion_options;
		$this->create_opening_tag($value);
		$options = $value['options'];
		if (!isset($suffusion_options[$value['id']])) {
			$default = $value['std'];
		}
		else {
			$default = $suffusion_options[$value['id']];
		}
	?>
		<script type="text/javascript">
		$j = jQuery.noConflict();
		$j(document).ready(function() {
			$j("#<?php echo $value['id']; ?>-slider").slider({
				range: "<?php echo $options['range']; ?>",
				value: <?php echo (int)$default; ?>,
				min: <?php echo $options['min']; ?>,
				max: <?php echo $options['max']; ?>,
				step: <?php echo $options['step']; ?>,
				slide: function(event, ui) {
					$j("input#<?php echo $value['id']; ?>").val(ui.value);
				}
			});

		});
		</script>

		<div class='slider'>
			<p>
				<input type="text" id="<?php echo $value['id']; ?>" name="suffusion_options[<?php echo $value['id']; ?>]" value="<?php echo $default; ?>" class='slidertext' /> <?php echo $options['unit'];?>
			</p>
			<div id="<?php echo $value['id']; ?>-slider"  style="width:<?php echo $options['size'];?>;"></div>
		</div>
	<?php
		$this->create_closing_tag($value);
	}

	/**
	 * Renders an option whose type is "background". Invoked by add_settings_field.
	 *
	 * @param  $value
	 * @return void
	 */
	function create_section_for_background($value) {
		global $suffusion_options;
		$this->create_opening_tag($value);
		if (!isset($suffusion_options[$value['id']])) {
			$default = $value['std'];
			$default_txt = "";
			foreach ($value['std'] as $opt => $opt_val) {
				$default_txt .= $opt."=".$opt_val.";";
			}
		}
		else {
			$default_txt = $suffusion_options[$value['id']];
			$default = $default_txt;
			$vals = explode(";", $default);
			$default = array();
			foreach ($vals as $val) {
				$pair = explode("=", $val);
				if (isset($pair[0]) && isset($pair[1])) {
					$default[$pair[0]] = $pair[1];
				}
				else if (isset($pair[0]) && !isset($pair[1])) {
					$default[$pair[0]] = "";
				}
			}
		}
		$repeats = array("repeat" => "Repeat horizontally and vertically",
			"repeat-x" => "Repeat horizontally only",
			"repeat-y" => "Repeat vertically only",
			"no-repeat" => "Do not repeat");

		$positions = array("top left" => "Top left",
			"top center" => "Top center",
			"top right" => "Top right",
			"center left" => "Center left",
			"center center" => "Middle of the page",
			"center right" => "Center right",
			"bottom left" => "Bottom left",
			"bottom center" => "Bottom center",
			"bottom right" => "Bottom right");

		foreach ($value['options'] as $option_value => $option_text) {
			if ($suffusion_options[$value['id']] == $option_value) {
				$checked = ' checked="checked" ';
			}
			else if (!isset($suffusion_options[$value['id']]) && $value['std'] == $option_value){
				$checked = ' checked="checked" ';
			}
			else {
				$checked = ' ';
			}
			echo '<div class="suf-radio"><input type="radio" name="'.$value['id'].'" value="'.$option_value.'" '.$checked."/>".$option_text."</div>\n";
		}
	?>
		<div class='suf-background-options'>
		<table class='opt-sub-table'>
	        <col class='opt-sub-table-cols'/>
	        <col class='opt-sub-table-cols'/>
			<tr>
				<td valign='top'>
					<div class="color-picker-group">
						<strong>Background Color:</strong><br />
						<input type="radio" name="<?php echo $value['id']; ?>-colortype" value="transparent" <?php if ($default['colortype'] == 'transparent') { echo ' checked="checked" ';} ?>/> Transparent / No color<br/>
						<input type="radio" name="<?php echo $value['id']; ?>-colortype" value="custom" <?php if ($default['colortype'] == 'custom') { echo ' checked="checked" ';} ?>/> Custom
						<input type="text" id="<?php echo $value['id']; ?>-bgcolor" name="<?php echo $value['id']; ?>-bgcolor" value="<?php echo $default['color']; ?>" class="color" /><br />
						Default: <span color='<?php echo $default['color']; ?>"'> <?php echo $default['color']; ?> </span>
					</div>
				</td>
				<td valign='top'>
					<strong>Image URL:</strong><br />
					<?php $this->display_upload_field($default['image'], $value['id']."-bgimg", $value['id']."-bgimg"); ?>
				</td>
			</tr>

			<tr>
				<td valign='top'>
					<strong>Image Position:</strong><br />
					<select name="<?php echo $value['id']; ?>-position" id="<?php echo $value['id']; ?>-position" >
				<?php
					foreach ($positions as $option_value => $option_text) {
						echo "<option ";
						if ($default['position'] == $option_value) {
							echo ' selected="selected"';
						}
						echo " value='$option_value' >".$option_text."</option>\n";
					}
				?>
					</select>
				</td>

				<td valign='top'>
					<strong>Image Repeat:</strong><br />
					<select name="<?php echo $value['id']; ?>-repeat" id="<?php echo $value['id']; ?>-repeat" >
				<?php
					foreach ($repeats as $option_value => $option_text) {
						echo "<option ";
						if ($default['repeat'] == $option_value) {
							echo ' selected="selected"';
						}
						echo " value='$option_value' >".$option_text."</option>\n";
					}
				?>
					</select>
				</td>
			</tr>
			<tr>
				<td valign='top' colspan='2'>
					<script type="text/javascript">
					$j = jQuery.noConflict();
					$j(document).ready(function() {
						$j("#<?php echo $value['id']; ?>-transslider").slider({
							range: "min",
							value: <?php echo (int)$default['trans']; ?>,
							min: 0,
							max: 100,
							step: 1,
							slide: function(event, ui) {
								$j("input#<?php echo $value['id']; ?>-trans").val(ui.value);
								$j("#<?php echo $value['id']; ?>").val('color=' + $j("#<?php echo $value['id']; ?>-bgcolor").val() + ';' +
																	   'colortype=' + $j("input[name=<?php echo $value['id']; ?>-colortype]:checked").val() + ';' +
																	   'image=' + $j("#<?php echo $value['id']; ?>-bgimg").val() + ';' +
																	   'position=' + $j("#<?php echo $value['id']; ?>-position").val() + ';' +
																	   'repeat=' + $j("#<?php echo $value['id']; ?>-repeat").val() + ';' +
																	   'trans=' + $j("#<?php echo $value['id']; ?>-trans").val() + ';'
										);
							}
						});
					});
					</script>

					<div class='slider'>
						<p>
							<strong>Layer Transparency (not for IE):</strong>
							<input type="text" id="<?php echo $value['id']; ?>-trans" name="<?php echo $value['id']; ?>-trans" value="<?php echo $default['trans']; ?>" class='slidertext' />
						</p>
						<div id="<?php echo $value['id']; ?>-transslider" class='transslider'></div>
					</div>
				</td>
			</tr>
		</table>
		<input type='hidden' id="<?php echo $value['id']; ?>" name="suffusion_options[<?php echo $value['id']; ?>]" value="<?php echo $default_txt; ?>" />
		</div>
	<?php
		$this->create_closing_tag($value);
	}

	/**
	 * Renders an option whose type is "border". Invoked by add_settings_field.
	 *
	 * @param  $value
	 * @return void
	 */
	function create_section_for_border($value) {
		global $suffusion_options;
		$this->create_opening_tag($value);
		if (!isset($suffusion_options[$value['id']])) {
			$default = $value['std'];
			$default_txt = "";
			foreach ($value['std'] as $edge => $edge_val) {
				$default_txt .= $edge.'::';
				foreach ($edge_val as $opt => $opt_val) {
					$default_txt .= $opt . "=" . $opt_val . ";";
				}
				$default_txt .= "||";
			}
		}
		else {
			$default_txt = $suffusion_options[$value['id']];
			$default = $default_txt;
			$edge_array = explode('||', $default);
			$default = array();
			if (is_array($edge_array)) {
				foreach ($edge_array as $edge_vals) {
					if (trim($edge_vals) != '') {
						$edge_val_array = explode('::', $edge_vals);
						if (is_array($edge_val_array) && count($edge_val_array) > 1) {
							$vals = explode(';', $edge_val_array[1]);
							$default[$edge_val_array[0]] = array();
							foreach ($vals as $val) {
								$pair = explode("=", $val);
								if (isset($pair[0]) && isset($pair[1])) {
									$default[$edge_val_array[0]][$pair[0]] = $pair[1];
								}
								else if (isset($pair[0]) && !isset($pair[1])) {
									$default[$edge_val_array[0]][$pair[0]] = "";
								}
							}
						}
					}
				}
			}
		}
		$edges = array('top' => 'Top', 'right' => 'Right', 'bottom' => 'Bottom', 'left' => 'Left');
		$styles = array("none" => "No border",
			"hidden" => "Hidden",
			"dotted" => "Dotted",
			"dashed" => "Dashed",
			"solid" => "Solid",
			"double" => "Double",
			"grove" => "Groove",
			"ridge" => "Ridge",
			"inset" => "Inset",
			"outset" => "Outset");

		$border_width_units = array("px" => "Pixels (px)", "em" => "Em");

		foreach ($value['options'] as $option_value => $option_text) {
			$checked = ' ';
			if ($suffusion_options[$value['id']] == $option_value) {
				$checked = ' checked="checked" ';
			}
			else if (!isset($suffusion_options[$value['id']]) && $value['std'] == $option_value){
				$checked = ' checked="checked" ';
			}
			else {
				$checked = ' ';
			}
			echo '<div class="suf-radio"><input type="radio" name="'.$value['id'].'" value="'.$option_value.'" '.$checked."/>".$option_text."</div>\n";
		}
	?>
		<div class='suf-border-options'>
			<p>For any edge set style to "No Border" if you don't want a border.</p>
			<table class='opt-sub-table-5'>
				<col class='opt-sub-table-col-51'/>
				<col class='opt-sub-table-col-5'/>
				<col class='opt-sub-table-col-5'/>
				<col class='opt-sub-table-col-5'/>
				<col class='opt-sub-table-col-5'/>

				<tr>
					<th scope="col">&nbsp;</th>
					<th scope="col">Border Style</th>
					<th scope="col">Color</th>
					<th scope="col">Border Width</th>
					<th scope="col">Border Width Units</th>
				</tr>

		<?php
			foreach ($edges as $edge => $edge_text) {
		?>
			<tr>
				<th scope="row"><?php echo $edge_text." border"; ?></th>
				<td valign='top'>
					<select name="<?php echo $value['id'].'-'.$edge; ?>-style" id="<?php echo $value['id'].'-'.$edge; ?>-style" >
				<?php
					foreach ($styles as $option_value => $option_text) {
						echo "<option ";
						if (isset($default[$edge]) && isset($default[$edge]['style']) && $default[$edge]['style'] == $option_value) {
							echo ' selected="selected"';
						}
						echo " value='$option_value' >".$option_text."</option>\n";
					}
				?>
					</select>
				</td>

				<td valign='top'>
					<div class="color-picker-group">
						<input type="radio" name="<?php echo $value['id'].'-'.$edge; ?>-colortype" value="transparent" <?php if ($default[$edge]['colortype'] == 'transparent') { echo ' checked="checked" ';} ?>/> Transparent / No color<br/>
						<input type="radio" name="<?php echo $value['id'].'-'.$edge; ?>-colortype" value="custom" <?php if ($default[$edge]['colortype'] == 'custom') { echo ' checked="checked" ';} ?>/> Custom
						<input type="text" id="<?php echo $value['id'].'-'.$edge; ?>-color" name="<?php echo $value['id']; ?>-color" value="<?php echo $default[$edge]['color']; ?>" class="color" /><br />
						Default: <span color='<?php echo $default[$edge]['color']; ?>"'> <?php echo $default[$edge]['color']; ?> </span>
					</div>
				</td>

				<td valign='top'>
					<input type="text" id="<?php echo $value['id'].'-'.$edge; ?>-border-width" name="<?php echo $value['id'].'-'.$edge; ?>-border-width" value="<?php echo $default[$edge]['border-width']; ?>" /><br />
				</td>

				<td valign='top'>
					<select name="<?php echo $value['id'].'-'.$edge; ?>-border-width-type" id="<?php echo $value['id'].'-'.$edge; ?>-border-width-type" >
				<?php
					foreach ($border_width_units as $option_value => $option_text) {
						echo "<option ";
						if ($default[$edge]['border-width-type'] == $option_value) {
							echo ' selected="selected"';
						}
						echo " value='$option_value' >".$option_text."</option>\n";
					}
				?>
					</select>
				</td>
			</tr>
		<?php
			}
		?>
			</table>
		<input type='hidden' id="<?php echo $value['id']; ?>" name="suffusion_options[<?php echo $value['id']; ?>]" value="<?php echo $default_txt; ?>" />
		</div>
	<?php
		$this->create_closing_tag($value);
	}

	/**
	 * Renders an option whose type is "font". Invoked by add_settings_field.
	 *
	 * @param  $value
	 * @return void
	 */
	function create_section_for_font($value) {
		global $suffusion_options, $suffusion_safe_font_faces;
		$this->create_opening_tag($value);
		if (!isset($suffusion_options[$value['id']])) {
			$default = $value['std'];
			$default_txt = "";
			foreach ($value['std'] as $opt => $opt_val) {
				$default_txt .= $opt."=".stripslashes($opt_val).";";
			}
		}
		else {
			$default_txt = $suffusion_options[$value['id']];
			$default = $default_txt;
			$vals = explode(";", $default);
			$default = array();
			foreach ($vals as $val) {
				$pair = explode("=", $val);
				if (isset($pair[0]) && isset($pair[1])) {
					$default[$pair[0]] = stripslashes($pair[1]);
				}
				else if (isset($pair[0]) && !isset($pair[1])) {
					$default[$pair[0]] = "";
				}
			}
		}
		$font_size_types = array("pt" => "Points (pt)", "px" => "Pixels (px)", "%" => "Percentages (%)", "em" => "Em");
		$font_styles = array("normal" => "Normal", "italic" => "Italic", "oblique" => "Oblique", "inherit" => "Inherit");
		$font_variants = array("normal" => "Normal", "small-caps" => "Small Caps", "inherit" => "Inherit");
		$font_weights = array("normal" => "Normal", "bold" => "Bold", "bolder" => "Bolder", "lighter" => "Lighter", "inherit" => "Inherit");
	?>
		<div class='suf-font-options'>
		<table class='opt-sub-table'>
	        <col class='opt-sub-table-cols'/>
	        <col class='opt-sub-table-cols'/>
			<tr>
				<td valign='top'>
					<div class="color-picker-group">
						<strong>Font Color:</strong><br />
						<input type="text" id="<?php echo $value['id']; ?>-color" name="<?php echo $value['id']; ?>-color" value="<?php echo $default['color']; ?>" class="color" /><br />
						Default: <span color='<?php echo $default['color']; ?>"'> <?php echo $default['color']; ?> </span>
					</div>
				</td>
				<td valign='top'>
					<strong>Font Face:</strong><br />
					<select name="<?php echo $value['id']; ?>-font-face" id="<?php echo $value['id']; ?>-font-face" >
				<?php
					foreach ($suffusion_safe_font_faces as $option_value => $option_text) {
						echo "<option ";
						if (stripslashes($default['font-face']) == stripslashes($option_value)) {
							echo ' selected="selected"';
						}
						echo " value=\"".stripslashes($option_value)."\" >".$option_value."</option>\n";
					}
				?>
					</select>
				</td>
			</tr>

			<tr>
				<td valign='top'>
					<strong>Font Size:</strong><br />
					<input type="text" id="<?php echo $value['id']; ?>-font-size" name="<?php echo $value['id']; ?>-font-size" value="<?php echo $default['font-size']; ?>" /><br />
				</td>
				<td valign='top'>
					<strong>Font Size Type:</strong><br />
					<select name="<?php echo $value['id']; ?>-font-size-type" id="<?php echo $value['id']; ?>-font-size-type" >
				<?php
					foreach ($font_size_types as $option_value => $option_text) {
						echo "<option ";
						if ($default['font-size-type'] == $option_value) {
							echo ' selected="selected"';
						}
						echo " value='$option_value' >".$option_text."</option>\n";
					}
				?>
					</select>
				</td>
			</tr>

			<tr>
				<td valign='top'>
					<strong>Font Style:</strong><br />
					<select name="<?php echo $value['id']; ?>-font-style" id="<?php echo $value['id']; ?>-font-style" >
				<?php
					foreach ($font_styles as $option_value => $option_text) {
						echo "<option ";
						if ($default['font-style'] == $option_value) {
							echo ' selected="selected"';
						}
						echo " value='$option_value' >".$option_text."</option>\n";
					}
				?>
					</select>
				</td>
				<td valign='top'>
					<strong>Font Variant:</strong><br />
					<select name="<?php echo $value['id']; ?>-font-variant" id="<?php echo $value['id']; ?>-font-variant" >
				<?php
					foreach ($font_variants as $option_value => $option_text) {
						echo "<option ";
						if ($default['font-variant'] == $option_value) {
							echo ' selected="selected"';
						}
						echo " value='$option_value' >".$option_text."</option>\n";
					}
				?>
					</select>
				</td>
			</tr>

			<tr>
				<td valign='top' colspan='2'>
					<strong>Font Weight:</strong><br />
					<select name="<?php echo $value['id']; ?>-font-weight" id="<?php echo $value['id']; ?>-font-weight" >
				<?php
					foreach ($font_weights as $option_value => $option_text) {
						echo "<option ";
						if ($default['font-weight'] == $option_value) {
							echo ' selected="selected"';
						}
						echo " value='$option_value' >".$option_text."</option>\n";
					}
				?>
					</select>
				</td>
			</tr>
		</table>
		<input type='hidden' id="<?php echo $value['id']; ?>" name="suffusion_options[<?php echo $value['id']; ?>]" value="<?php echo stripslashes($default_txt); ?>" />
		</div>
	<?php
		$this->create_closing_tag($value);
	}

	/**
	 * Renders an option whose type is "blurb". Invoked by add_settings_field.
	 *
	 * @param  $value
	 * @return void
	 */
	function create_section_for_blurb($value) {
		$this->create_opening_tag($value);
		$this->create_closing_tag($value);
	}

	/**
	 * Renders an option whose type is "button". Invoked by add_settings_field.
	 *
	 * @param  $value
	 * @return void
	 */
	function create_section_for_button($value) {
		$this->create_opening_tag($value);
		$category = $value['parent'];
//		echo "<input name=\"".$value['id']."\" type=\"submit\" value=\"".$value['std']."\" class=\"button\" onclick=\"submit_form(this, document.forms['form-$category'])\" />\n";
		echo "<input name=\"suffusion_options[submit-$category]\" type='submit' value=\"".$value['std']."\" class=\"button\" />\n";
		$this->create_closing_tag($value);
	}

	/**
	 * Takes the flat options array and converts it into a hierarchical array, with the root level, and subsequent nested levels.
	 *
	 * @return array
	 */
	function get_option_structure() {
		if (isset($this->option_structure)) {
			return $this->option_structure;
		}
		$options = $this->options;
		$option_structure = array();
		$nested_options = array();
		foreach ($options as $value) {
			switch ($value['type']) {
				case "title":
					$option_structure[$value['category']] = array();
					$option_structure[$value['category']]['slug'] = $value['category'];
					$option_structure[$value['category']]['name'] = $value['name'];
					$option_structure[$value['category']]['children'] = array();
					$option_structure[$value['category']]['parent'] = null;
					break;
				case "sub-section-2":
				case "sub-section-3":
					$option_structure[$value['parent']]['children'][$value['category']] = $value['name'];

					$option_structure[$value['category']] = array();
					$option_structure[$value['category']]['slug'] = $value['category'];
					$option_structure[$value['category']]['name'] = $value['name'];
					$option_structure[$value['category']]['children'] = array();
					if (isset($value['help'])) $option_structure[$value['category']]['help'] = $value['help'];
					if (isset($value['parent'])) $option_structure[$value['category']]['parent'] = $value['parent'];
					if (isset($value['buttons'])) $option_structure[$value['category']]['buttons'] = $value['buttons'];

					if ($value['type'] == 'sub-section-3') {
						$nested_options[$value['category']] = array();
					}
					break;
				default:
					$option_structure[$value['parent']]['children'][$value['name']] = $value['name'];
					if (isset($value['id'])) {
						$nested_options[$value['parent']][] = $value['id'];
					}
			}
		}
		$this->option_structure = $option_structure;
		$this->nested_options = $nested_options;
		return $option_structure;
	}

	/**
	 * Creates the HTML markup for the page that shows up for a sub-menu page.
	 *
	 * @param  $option_structure
	 * @param  $group
	 * @return void
	 */
	function get_options_html_for_group($option_structure, $group) {
		echo "<div class='suf-options suf-options-$group' id='suf-options'>";
		echo "<div class='suf-options-page-header fix'>\n";
		foreach ($option_structure as $l1) {
			if (!isset($l1['parent']) || $l1['parent'] == null) {
				foreach ($l1['children'] as $l2slug => $l2name) {
					if ($group == $l2slug) {
						echo "<h1>$l2name</h1>\n";
						if (isset($option_structure[$l2slug]) && isset($option_structure[$l2slug]['help'])) {
							echo "<a href='#' class='suf-help-anchor-$l2slug suf-help-anchor' title='What is this section?'><img src='".get_template_directory_uri()."/admin/images/help.png' alt='Help'/></a>";
							echo "<div class='suf-help-$l2slug suf-help' title='$l2name'>".$option_structure[$l2slug]['help']."</div>";
						}
					}
				}
			}
		}
		echo "</div><!-- suf-options-page-header -->\n";

		echo "<ul class='suf-section-tabs'>";
		foreach ($option_structure as $l1) {
			if (!isset($l1['parent']) || $l1['parent'] == null) {
				foreach ($l1['children'] as $l2slug => $l2name) {
					if ($group == $l2slug) {
						foreach ($option_structure[$l2slug]['children'] as $l3slug => $l3name) {
							echo "<li><a href='#$l3slug'>".$l3name."</a></li>\n";
						}
					}
				}
			}
		}
		echo "</ul>";

		foreach ($option_structure as $option) {
			if (isset($option['parent']) && $option['parent'] == 'root' && $option['slug'] == $group) {
				do_settings_sections($this->file);
//				$last_key = array_keys($option['children']);
//				$last_key = $last_key[count($last_key) - 1];
//				echo "<div id='suffusion-color-picker-{$last_key}' class='suffusion-color-picker'></div>";
				echo "</form>\n";
				echo "</div><!-- main-content -->\n";
//				echo "</form>\n";
//				foreach ($option['children'] as $l2slug => $l2name) {
//					echo "<div id='suffusion-color-picker-$l2slug' class='suffusion-color-picker'></div>";
//					echo "</form>\n";
//				}
			}
		}

		echo "</div><!-- /#suf-options -->\n";
	}

	/**
	 * Retrieves the sections for a given submenu page.
	 *
	 * @param  $sub_menu
	 * @return array
	 */
	function get_sections_for_submenu($sub_menu) {
		$options = $this->options;
		$ret = array();
		if ($sub_menu == "all") {
			return $options;
		}
		foreach ($options as $value) {
			if (isset($value['parent']) && $value['parent'] == $sub_menu) {
				$ret[] = $value;
			}
		}
		return $ret;
	}

	/**
	 * Makes calls to add_settings_field for different types of options.
	 *
	 * @param string $section
	 * @return void
	 */
	function add_settings_fields($section, $parent) {
		$filtered_options = $this->get_sections_for_submenu($section);
		$ctr = 0;
		foreach ($filtered_options as $value) {
			$ctr++;
			switch ($value['type']) {
				case "title":
					add_settings_field('', '', array(&$this, "create_title"), $parent, $section, $value);
					break;

				case "sub-section-2":
					add_settings_field('', '', array(&$this, "create_suf_header_2"), $parent, $section, $value);
					break;

				case "sub-section-3":
					add_settings_field('', '', array(&$this, "create_suf_header_3"), $parent, $section, $value);
					break;

				case "sub-section-4":
					add_settings_field($section.'-'.$ctr, '', array(&$this, "create_suf_grouping"), $parent, $section, $value);
					break;

				case "text";
					add_settings_field($value['id'], '', array(&$this, "create_section_for_text"), $parent, $section, $value);
					break;

				case "textarea":
					add_settings_field($value['id'], '', array(&$this, "create_section_for_textarea"), $parent, $section, $value);
					break;

				case "select":
					add_settings_field($value['id'], '', array(&$this, "create_section_for_select"), $parent, $section, $value);
					break;

				case "multi-select":
					add_settings_field($value['id'], '', array(&$this, "create_section_for_multi_select"), $parent, $section, $value);
					break;

				case "radio":
					add_settings_field($value['id'], '', array(&$this, "create_section_for_radio"), $parent, $section, $value);
					break;

				case "checkbox":
					add_settings_field($value['id'], '', array(&$this, "create_section_for_checkbox"), $parent, $section, $value);
					break;

				case "color-picker":
					add_settings_field($value['id'], '', array(&$this, "create_section_for_color_picker"), $parent, $section, $value);
					break;

				case "upload";
					add_settings_field($value['id'], '', array(&$this, "create_section_for_upload"), $parent, $section, $value);
					break;

				case "sortable-list":
					add_settings_field($value['id'], '', array(&$this, "create_section_for_sortable_list"), $parent, $section, $value);
					break;

				case "slider":
					add_settings_field($value['id'], '', array(&$this, "create_section_for_slider"), $parent, $section, $value);
					break;

				case "background":
					add_settings_field($value['id'], '', array(&$this, "create_section_for_background"), $parent, $section, $value);
					break;

				case "border":
					add_settings_field($value['id'], '', array(&$this, "create_section_for_border"), $parent, $section, $value);
					break;

				case "font":
					add_settings_field($value['id'], '', array(&$this, "create_section_for_font"), $parent, $section, $value);
					break;

				case "blurb":
					add_settings_field($section.'-'.$ctr, '', array(&$this, "create_section_for_blurb"), $parent, $section, $value);
					break;

				case "button":
					add_settings_field($value['id'], '', array(&$this, "create_section_for_button"), $parent, $section, $value);
					break;
			}
		}
	}

	/**
	 * Top level rendering call for a sub-menu page. This in turn invokes the rendering calls for individual sections (tabs) within
	 * a sub-menu page.
	 *
	 * @param  $option_structure
	 * @return void
	 */
	function get_options_html($option_structure = null) {
		if (is_null($option_structure)) {
			$option_structure = $this->get_option_structure();
		}

		foreach ($option_structure as $l1) {
			if (!isset($l1['parent']) || $l1['parent'] == null) {
				foreach ($l1['children'] as $l2slug => $l2name) {
					if ($l2slug != 'custom-types') {
						$this->get_options_html_for_group($option_structure, $l2slug);
					}
					else {
						$this->render_custom_types($l2slug);
					}
				}
			}
		}
	}

	/**
	 * Registers settings, then adds individual settings sections and their fields to the queue for rendering. The result of this
	 * is used by do_settings_sections.
	 *
	 * @param  $structure
	 * @return void
	 *
	 * @uses register_setting
	 * @uses add_settings_section
	 * @uses $this->add_settings_fields
	 */
	function initialize_settings($structure = null) {
		$options = $this->options;
		if (is_null($structure)) {
			$structure = $this->get_option_structure($options);
		}

//		$white_list = array('intro-pages', 'skinning', 'visual-effects', 'sidebar-setup', 'blog-features', 'templates');
//		foreach ($white_list as $allow) {
//			register_setting('suffusion-options-'.$allow, 'suffusion_options', array(&$this, "validate_options"));
//		}

		foreach ($structure as $option_entity) {
			if (!isset($option_entity['parent'])) {
				// Do nothing. This is the root node.
			}
			else if (isset($option_entity['parent']) && $option_entity['parent'] == 'root') {
				// This is the sub-menu that we are seeing. Options have already been registered for each of these.
				// If we weren't using tabs for building the options page we would have registered suffusion-options-$option_entity['slug'] here.
				// register_setting('suffusion-options-'.$option_entity['slug'], 'suffusion_options', array(&$this, "validate_options"));
			}
			else if (isset($option_entity['parent']) && $option_entity['parent'] != 'root') {
				// This is a section under the current sub-menu. Let's add sections and options
				register_setting('suffusion-options-'.$option_entity['slug'], 'suffusion_options', array(&$this, "validate_options"));
				add_settings_section($option_entity['slug'], "", array(&$this, "create_settings_section"), $this->file);
				$this->add_settings_fields($option_entity['slug'], $this->file);
			}
		}
	}

	/**
	 * Validates the inputs provided by users. For now:
	 *  1. All text type of options including slider, color-picture etc. are simply checked for special characters
	 *  2. Radio buttons/select items are checked for presence in a master list defined by the 'options' key in the inbuilt options array
	 *  3. Each item in Multi-select and sortable-list fields is checked against a master list defined by the 'options' key in the options array
	 *
	 * @param  $options
	 * @return void
	 */
	function validate_options($options) {
		foreach ($options as $option => $option_value) {
			if (isset($this->reverse_options[$option])) {
				//Sanitize options
				switch ($this->reverse_options[$option]) {
					// For all text type of options make sure that the eventual text is properly escaped.
					case "text":
					case "textarea":
					case "slider":
					case "color-picker":
					case "background":
					case "border":
					case "font":
					case "upload":
						$options[$option] = esc_attr($option_value);
						break;

					case "select":
					case "radio":
						if (isset($this->allowed_values[$option])) {
							if (!array_key_exists($option_value, $this->allowed_values[$option])) {
								$options[$option] = $this->option_defaults[$option];
							}
						}
				        break;

					case "multi-select":
						$selections = explode(',', $option_value);
						$final_selections = array();
						foreach ($selections as $selection) {
							if (array_key_exists($selection, $this->allowed_values[$option])) {
								$final_selections[] = $selection;
							}
						}
						$options[$option] = implode(',', $final_selections);
						break;

					case "sortable-list":
						$selections = explode(',', $option_value);
						$final_selections = array();
						$master_list = $this->option_defaults[$option]; // Sortable lists don't have their values in ['options']
						foreach ($selections as $selection) {
							if (array_key_exists($selection, $master_list)) {
								$final_selections[] = $selection;
							}
						}
						$options[$option] = implode(',', $final_selections);
						break;

					case "checkbox":
						if (!in_array($option_value, array('on', 'off', 'true', 'false')) && isset($this->option_defaults[$option])) {
							$options[$option] = $this->option_defaults[$option];
						}
						break;
				}
			}
		}

		/* The Settings API does an update_option($option, $value), overwriting the $suffusion_options array with the values on THIS page
		 * This is problematic because all options are stored in a single array, but are displayed on different options pages.
		 * Hence the overwrite kills the options from the other pages.
		 * So this is a workaround to include the options from other pages as hidden fields on this page, so that the array gets properly updated.
		 * The alternative would be to separate options for each page, but that would cause a migration headache for current users.
		 */
		if (isset($this->hidden_options) && is_array($this->hidden_options)) {
			foreach ($this->hidden_options as $hidden_option => $hidden_value) {
				if (strlen($hidden_option) >= 7 && (substr($hidden_option, 0, 7) == 'submit-' || substr($hidden_option, 0, 6) == 'reset-')) {
					continue;
				}
				$options[$hidden_option] = esc_attr($hidden_value);
			}
		}

		foreach ($this->nested_options as $section => $children) {
			if (isset($options['submit-'.$section])) {
				$options['last-set-section'] = $section;
				if (substr($options['submit-'.$section], 0, 9) == 'Save page' || substr($options['submit-'.$section], 0, 10) == 'Reset page') {
					global $suffusion_options;
					foreach ($this->nested_options as $inner_section => $inner_children) {
						if ($inner_section != $section) {
							foreach ($inner_children as $inner_child) {
								if (isset($suffusion_options[$inner_child])) {
									$options[$inner_child] = $suffusion_options[$inner_child];
								}
							}
						}
					}

					if (substr($options['submit-'.$section], 0, 10) == 'Reset page') {
						unset($options['submit-'.$section]);
						// This is a reset for an individual section. So we will unset the child fields.
						foreach ($children as $child) {
							unset($options[$child]);
						}
					}
					unset($options['submit-'.$section]);
				}
				else if (substr($options['submit-'.$section], 0, 12) == 'Save changes') {
					unset($options['submit-'.$section]);
				}
				else if (substr($options['submit-'.$section], 0, 13) == 'Reset changes') {
					unset($options['submit-'.$section]);
					// This is a reset for all options in the sub-menu. So we will unset all child fields.
					foreach ($this->nested_options as $section => $children) {
						foreach ($children as $child) {
							unset($options[$child]);
						}
					}
				}
				else if (substr($options['submit-'.$section], 0, 6) == 'Delete') {
					return;
				}
				else if ($options['submit-'.$section] == 'Migrate from 3.0.2 or lower') {
					unset($options['submit-'.$section]);
					$options = $this->migrate_from_v302($options);
				}
				else if ($options['submit-'.$section] == 'Migrate from 3.4.3 or lower') {
					unset($options['submit-'.$section]);
					$options = $this->migrate_from_v343($options);
				}
				else if ($options['submit-'.$section] == 'Export to a file') {
					$this->export_settings();
				}
				else if ($options['submit-'.$section] == 'Import options') {
					$options = $this->import_settings($options);
				}
				break;
			}
		}
		return $options;
	}

	/**
	 * Workaround for the default behavior in do_settings_sections. Native WP behaviour has this markup:
	 *  <h3>Section Title</h3>
	 *  <table class="form-table">
	 *      <tr>
	 *          <td>Field 1 title</td>
	 *          <td>Result of callback defined for field 1</td>
	 *      </tr>
	 *      <tr>
	 *          <td>Field 2 title</td>
	 *          <td>Result of callback defined for field 2</td>
	 *      </tr>
	 *      ...
	 *  </table>
	 * This doesn't work well if you need to do tabbed layouts or accordions etc. Hence we kill the section's h3 tag and
	 * don't print the field titles. We also include section-specific floating button-bars.
	 *
	 * @param  $section
	 * @return void
	 */
	function create_settings_section($section) {
		$option_structure = $this->option_structure;
		if ($this->displayed_sections != 0) {
			echo "</form>\n";
			echo "</div><!-- main-content -->\n";
		}

		echo "<div id='{$option_structure[$section['id']]['slug']}' class='suffusion-options-panel'> <!-- main-content -->\n";
		echo "<form method=\"post\" action=\"options.php\" id=\"suffusion-options-form-{$section['id']}\" class='suffusion-options-form'>\n";
		echo '<h3>' . $option_structure[$section['id']]['name'] . "</h3>\n";

		/*
		 * We store all options in one array, but display them across multiple pages. Hence we need the following hack.
		 * We are registering the same setting across multiple pages, hence we need to pass the "page" parameter to options.php.
		 * Otherwise options.php returns an error saying "Options page not found"
		 */
		echo "<input type='hidden' name='page' value='" . esc_attr($_REQUEST['page']) . "' />\n";
		if (!isset($_REQUEST['tab'])) {
			$tab = 'theme-options-intro.php';
		}
		else {
			$tab = esc_attr($_REQUEST['tab']);
		}
		echo "<input type='hidden' name='tab' value='" . $tab . "' />\n";

		settings_fields("suffusion-options-{$section['id']}");
		if (!isset($option_structure[$section['id']]['buttons']) ||
				($option_structure[$section['id']]['buttons'] != 'no-buttons' && $option_structure[$section['id']]['buttons'] != 'special-buttons')) {
			$root_children = $option_structure['root']['children'];
			if (is_array($root_children)) {
				foreach ($root_children as $slug => $desc) {
					$group_name = $desc;
				}
			}
			echo "<div class=\"suf-button-bar\">\n";
			echo "<h2>Save / Reset</h2>\n";
			echo "<input name=\"suffusion_options[submit-{$section['id']}]\" type='submit' value=\"Save page '{$option_structure[$section['id']]['name']}'\" class=\"button suf-button-section\" />\n";
			echo "<input name=\"suffusion_options[submit-{$section['id']}]\" type='submit' value=\"Reset page '{$option_structure[$section['id']]['name']}'\" class=\"button suf-button-section\" />\n";
			//echo "<input name=\"suffusion_options[submit-{$section['id']}]\" type='submit' value=\"Save changes for '$group_name'\" class=\"button suf-button-sub-menu\" />\n";
			echo "<input name=\"suffusion_options[submit-{$section['id']}]\" type='submit' value=\"Reset changes for '$group_name'\" class=\"button suf-button-sub-menu\" />\n";
			echo "<input name=\"suffusion_options[submit-{$section['id']}]\" type='submit' value=\"Delete all theme options\" class=\"button suf-button-all\" />\n";
			echo "</div><!-- suf-button-bar -->\n";
		}
		$this->displayed_sections++;
		$this->previous_displayed_section = $section['id'];
	}

	/**
	 * Upto version 3.0.2 the multi-select options were handled in a quirky manner, with an individual option created for each
	 * selection in a particular option item. This method migrates all such selections to a single option. To be used only if
	 * the user is migrating from 3.0.2 or lower.
	 *
	 * @param  $options
	 * @return void
	 */
	function migrate_from_v302($options) {
		global $suffusion_inbuilt_options;

		foreach ($suffusion_inbuilt_options as $option => $value) {
			if (isset($value['type']) && $value['type'] == 'multi-select') {
				$allowed = $value['options'];
				$new_value = array();
				foreach ($allowed as $idx => $idx_value) {
					$spawn = $value['id'].'_'.$idx;
					if (get_option($spawn)) {
						$new_value[] = $idx;
					}
				}
				$new_value = implode(',', $new_value);
				$options[$value['id']] = $new_value;
			}
		}

		/**
		 * In 3.0.2 and before, things like alternative page titles too were stored as options. This was later changed to
		 * meta fields for individual posts. The following handles the migration.
		 */
		$meta_fields = array('suf_alt_page_title' => 'text');
		foreach ($meta_fields as $meta_field => $type) {
			$pages = get_pages();
			if ($pages && is_array($pages)) {
				foreach ($pages as $page) {
					$page_id = $page->ID;
					if ($page != null) {
						if ($type == 'checkbox') {
							$data = 'on';
						}
						else if ($type == 'text') {
							$data = get_option($meta_field.'_'.$page_id);
						}
						if (get_post_meta($page_id, $meta_field) == '') {
							add_post_meta($page_id, $meta_field, $data, true);
						}
						else if ($data != get_post_meta($page_id, $meta_field, true)) {
							update_post_meta($page_id, $meta_field, $data);
						}
						else if ($data == '') {
							delete_post_meta($page_id, $meta_field, get_post_meta($page_id, $meta_field, true));
						}
					}
				}
			}
		}

		return $options;
	}

	/**
	 * In version 3.4.3 and earlier all options were stored as different elements in the database. This was modified in version
	 * 3.4.5 to have a single array with all the options. This function handles the migration of options prior to 3.4.3, to
	 * any later version.
	 *
	 * @param  $options
	 * @return
	 */
	function migrate_from_v343($options) {
		global $suffusion_inbuilt_options;
		foreach ($suffusion_inbuilt_options as $value) {
			if (isset($value['id'])) {
				if (get_option($value['id']) === FALSE) {
					unset($options[$value['id']]);
				}
				else {
					$options[$value['id']] = get_option($value['id']);
				}
			}
		}

		return $options;
	}

	/**
	 * Exports your current settings as a PHP file. You can re-import these settings to other implementations.
	 * Only options with an id and without "export" set to "ne" (no export) are exported. Fields with id settings are not
	 * exported. So settings in the featured content section or the navigation bar setup are not exported.
	 *
	 * @return void
	 */
	function export_settings() {
		global $suffusion_inbuilt_options, $suffusion_options;
		$export = array();
		foreach ($suffusion_inbuilt_options as $value) {
			if ((isset($value['export']) && $value['export'] == 'ne') || !isset($value['id']) || $value['type'] == 'button') {
				continue;
			}
			if (!isset($suffusion_options[$value['id']]) && isset($value['std'])) {
				$export[$value['id']] = $value['std'];
			}
			else {
				$export[$value['id']] = $suffusion_options[$value['id']];
			}
		}
		header('Content-Type: text/plain');
		header('Content-Disposition: attachment; filename="suffusion-options.php"');
		echo "<?php \n";
		echo "/* Suffusion settings exported on ".date('Y-m-d H:i')." */ \n";
		echo '$suffusion_exported_options = ';
		var_export($export);
		echo ";\n ?>";
		die;
	}

	/**
	 * Imports a file exported by $this->export_settings(). Your file for import has to be in the "import" folder under "admin"
	 * in your "suffusion" directory.
	 *
	 * @param  $options
	 * @return
	 */
	function import_settings($options) {
		global $suffusion_reevaluate_styles, $suffusion_unified_options, $suffusion_exported_options;

		if (file_exists(TEMPLATEPATH."/admin/import/suffusion-options.php")) {
			include (TEMPLATEPATH."/admin/import/suffusion-options.php");
			foreach ($suffusion_exported_options as $option => $option_value) {
				$options[$option] = $option_value;
			}
			$suffusion_reevaluate_styles = true;
		}
		return $options;
	}

	/**
	 * Called when you upload a file for option type "upload". This is an AJAX call
	 * @return void
	 */
	function admin_upload_file() {
		$save_type = $_POST['type'];
		if ($save_type == 'upload') {
			$data = $_POST['data']; // Acts as the name
			$filename = $_FILES[$data];
			$filename['name'] = preg_replace('/[^a-zA-Z0-9._\-]/', '', $filename['name']);

			$override['test_form'] = false;
			$override['action'] = 'wp_handle_upload';
			$uploaded_file = wp_handle_upload($filename, $override);

			$image_id = substr($data, 7);

			if (!empty($uploaded_file['error'])) {
				echo 'Upload Error: ' . $uploaded_file['error'];
			}
			else {
				$this->options[$image_id] = $uploaded_file['url'];
				echo $uploaded_file['url'];
			}
		}
		elseif ($save_type == 'image_reset') {
			$data = $_POST['data'];
			$image_id = substr($data, 6);
			if (isset($this->options[$image_id])) unset($this->options[$image_id]);
		}
		die();
	}

	/**
	 * Creates a page for custom post types. This is treated differently from the rest of the options, as these are special cases.
	 * @param  $group
	 * @return void
	 */
	function render_custom_types($group) {
		?>
		<div class='suf-loader'><img src='<?php echo get_template_directory_uri(); ?>/admin/images/ajax-loader-large.gif' alt='Processing'></div>
		<div class='suf-options suf-options-$group suf-custom-type-settings' id='suf-options'>
			<div class='suf-options-page-header fix'>
				<h1>Custom Types for Suffusion</h1>
			</div><!-- suf-options-page-header -->

			<div class="suf-loader"><img src='<?php echo get_template_directory_uri(); ?>/admin/images/ajax-loader-large.gif' alt='Processing'></div>
				<ul class='suf-section-tabs'>
					<li><a href="#custom-post-types">Existing Post Types</a></li>
					<li><a href="#add-edit-post-type">Add / Edit Post Type</a></li>
					<li><a href="#custom-taxonomies">Existing Taxonomies</a></li>
					<li><a href="#add-edit-taxonomy">Add / Edit Taxonomy</a></li>
				</ul>

				<div class='custom-post-types main-content' id='custom-post-types'>
					<h3 class='suf-header-2'>Existing Post Types</h3>
					<form method="post" name="form-custom-post-types" id="form-custom-post-types" action="options.php">
					<?php
						suffusion_display_all_custom_post_types();
					?>
					</form>
				</div><!-- .custom-post-types -->

				<div class='add-edit-post-type main-content' id='add-edit-post-type'>
					<h3 class='suf-header-2'>Add / Edit Post Type</h3>
					<form method="post" name="form-add-edit-post-type" id="form-add-edit-post-type" action="options.php">
					<?php
						suffusion_display_custom_post_type(-1);
					?>
					</form>

				</div><!-- .add-edit-post-type -->

				<div class='custom-taxonomies main-content' id='custom-taxonomies'>
					<h3 class='suf-header-2'>Existing Taxonomies</h3>
					<form method="post" name="form-custom-taxonomies" id="form-custom-taxonomies" action="options.php">
					<?php
						suffusion_display_all_custom_taxonomies();
					?>
					</form>
				</div><!-- .custom-taxonomies -->

				<div class='add-edit-taxonomy main-content' id='add-edit-taxonomy'>
					<h3 class='suf-header-2'>Add / Edit Taxonomy</h3>
					<form method="post" name="form-add-edit-taxonomy" id="form-add-edit-taxonomy" action="options.php">
					<?php
						suffusion_display_custom_taxonomy(-1);
					?>
					</form>
				</div><!-- .add-edit-taxonomies -->
			</div><!-- .suf-options-post-types -->
	<?php
	}
}
?>