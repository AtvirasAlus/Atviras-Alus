<?php
class Suffusion_CSS_Generator {
	var $creation_date;
	function Suffusion_CSS_Generator($date = null) {
		if (is_null($date)) {
			$this->creation_date = date(get_option('date_format'));
		}
		else {
			$this->creation_date = $date;
		}
	}

	function get_creation_date() {
		return $this->creation_date;
	}

	function get_bg_information($option) {
		global $$option;
		$option_val = $$option;
		if (!is_array($option_val)) {
			$val_array = array();
			$vals = explode(';', $option_val);
			foreach ($vals as $val) {
				if (trim($val) == '') { continue; }
				$pair = explode('=', $val);
				$val_array[$pair[0]] = $pair[1];
			}
			$option_val = $val_array;
		}
		$bg_string = "";
		$bg_rgba_string = "";
		if ($option_val['colortype'] == 'transparent') {
			$bg_string .= " transparent ";
		}
		else {
			if (isset($option_val['color'])) {
				if (substr($option_val['color'], 0, 1) == '#') {
					//$bg_string .= " ".$option_val['color'];
					$color_string = substr($option_val['color'],1);
				}
				else {
					//$bg_string .= " #".$option_val['color'];
					$color_string = $option_val['color'];
				}
				$rgb_str_array = array();
				if (strlen($color_string)==3) {
					$rgb_str_array[] = substr($color_string, 0, 1).substr($color_string, 0, 1);
					$rgb_str_array[] = substr($color_string, 1, 1).substr($color_string, 1, 1);
					$rgb_str_array[] = substr($color_string, 2, 1).substr($color_string, 2, 1);
				}
				else {
					$rgb_str_array[] = substr($color_string, 0, 2);
					$rgb_str_array[] = substr($color_string, 2, 2);
					$rgb_str_array[] = substr($color_string, 4, 2);
				}
				$rgb_array = array();
				$rgb_array[] = hexdec($rgb_str_array[0]);
				$rgb_array[] = hexdec($rgb_str_array[1]);
				$rgb_array[] = hexdec($rgb_str_array[2]);
				$rgb_string = implode(',',$rgb_array);
				$rgb_string = ' rgb('.$rgb_string.') ';

				if (isset($option_val['trans'])) {
					$bg_rgba_string = $bg_string;
					$transparency = (int)$option_val['trans'];
					if ($transparency != 0) {
						$trans_dec = $transparency/100;
						$rgba_string = implode(',', $rgb_array);
						$rgba_string = ' rgba('.$rgba_string.','.$trans_dec.') ';
						$bg_rgba_string .= $rgba_string;
					}
				}

				$bg_string .= $rgb_string;
			}
		}
		if (trim($option_val['image']) != '') {
			$bg_string .= " url(".$option_val['image'].") ";
			$bg_string .= $option_val['position']." ".$option_val['repeat'];

			if (trim($bg_rgba_string) != '') {
				$bg_rgba_string .= " url(".$option_val['image'].") ";
				$bg_rgba_string .= $option_val['position']." ".$option_val['repeat'];
			}
		}

		if (trim($bg_string) != '') {
			$bg_string = "background: ".$bg_string.";\n";
			if (trim($bg_rgba_string) != '') {
				$bg_string .= "\tbackground: ".$bg_rgba_string.";\n";
			}
		}
		return $bg_string;
	}

	function get_font_information($option) {
		global $$option;
		$option_val = $$option;
		if (!is_array($option_val)) {
			$option_val = stripslashes($option_val);
			$val_array = array();
			$vals = explode(';', $option_val);
			foreach ($vals as $val) {
				$pair = explode('=', $val);
				if (is_array($pair) && count($pair) > 1) {
					$val_array[$pair[0]] = $pair[1];
				}
			}
			$option_val = $val_array;
		}
		//$font_string = "";
		$font_string = "";
		foreach ($option_val as $name => $value) {
			if ($name != "font-face" && $name != "font-size" && $name != "font-size-type" && $name != '' && $name != 'color') {
				$font_string .= $name.":".$value.";";
			}
			else if ($name == "font-size") {
				$font_string .= $name.":".$value.$option_val['font-size-type'].";";
			}
			else if ($name == "font-face") {
				$font_string .= "font-family:".$value.";";
			}
			else if ($name == 'color') {
				if (substr($value, 0, 1) != '#') {
					$value = '#'.$value;
				}
				$font_string .= "color:".$value.";";
			}
		}

		if (trim($font_string) != '') {
			$font_string = stripslashes($font_string);
			$font_string = wp_specialchars_decode($font_string, ENT_QUOTES);
			$font_string = $font_string."\n";
		}
		return $font_string;
	}

	function get_border_information($option) {
		global $$option;
		$option_val = $$option;
		if (!is_array($option_val)) {
			$option_val = stripslashes($option_val);
			$edge_array = array();
			$edges = explode('||', $option_val);
			foreach ($edges as $edge_val) {
				if (trim($edge_val) != '') {
					$edge_options = explode('::', trim($edge_val));
					if (is_array($edge_options) && count($edge_options) > 1) {
						$val_array = array();
						$vals = explode(';', $edge_options[1]);
						foreach ($vals as $val) {
							$pair = explode('=', $val);
							if (is_array($pair) && count($pair) > 1) {
								$val_array[$pair[0]] = $pair[1];
							}
						}
						$edge_array[$edge_options[0]] = $val_array;
					}
				}
			}
			$option_val = $edge_array;
		}
		$border_string = '';
		foreach ($option_val as $edge => $selections) {
			$border_string .= "\tborder-$edge: ";
			if (!isset($selections['style'])) {
				$selections['style'] = 'none';
			}
			if ($selections['style'] == 'none') {
				$border_string .= "none";
			}
			else {
				if (isset($selections['border-width'])) {
					$border_string .= $selections['border-width'];
				}
				if (isset($selections['border-width-type'])) {
					$border_string .= $selections['border-width-type'];
				}
				else {
					$border_string .= "px";
				}
				$border_string .= " ".$selections['style']." ";
				if ($selections['colortype'] == 'transparent') {
					$border_string .= "transparent";
				}
				else {
					if (substr($selections['color'], 0, 1) == '#') {
						$border_string .= $selections['color'];
					}
					else {
						$border_string .= '#'.$selections['color'];
					}
				}
			}
			$border_string .= ";\n";
		}
		return $border_string;
	}

	function strip_color_hash($color) {
		if (substr($color, 0, 1) == '#') {
			$temp_color = substr($color, 1, strlen($color) - 1);
		}
		else {
			$temp_color = $color;
		}
		return $temp_color;
	}

	function get_automatic_widths($cust_wrapper_width, $sidebar_count, $prefix) {
		if ($cust_wrapper_width < 600) {
			$wrapper_width = 600;
		}
		else {
			$wrapper_width = $cust_wrapper_width;
		}
		$computed_widths = array();
		if ($sidebar_count == 0) {
			$computed_widths['main-col'] = floor($wrapper_width);
			$computed_widths['sidebar-1'] = 0;
			$computed_widths['sidebar-2'] = 0;
		}
		else if ($sidebar_count == 1) {
			$computed_widths['main-col'] = floor(0.725 * $wrapper_width);
			$computed_widths['sidebar-1'] = $wrapper_width - $computed_widths['main-col'] - 15;
			$computed_widths['sidebar-2'] = 0;
		}
		else {
			$computed_widths['main-col'] = floor(0.63 * $wrapper_width);
			$computed_widths['sidebar-1'] = floor(0.5 * ($wrapper_width - $computed_widths['main-col'] - 30));
			$computed_widths['sidebar-2'] = floor(0.5 * ($wrapper_width - $computed_widths['main-col'] - 30));
		}

		$widths = $this->get_widths_from_components($computed_widths, $sidebar_count, $prefix);
		return $widths;
	}

	function get_widths_from_components($component_widths, $sidebar_count, $prefix) {
		global $suf_sidebar_alignment, $suf_sidebar_2_alignment, $suf_sbtab_alignment, $suf_mag_headline_image_container_width, $content_width;
		$widths = array();
		$main_col_width = $component_widths['main-col'] < 380 ? 380 : $component_widths['main-col'];
		$sb_1_width = $component_widths['sidebar-1'] < 95 ? 95 : $component_widths['sidebar-1'];
		$sb_2_width = $component_widths['sidebar-2'] < 95 ? 95 : $component_widths['sidebar-2'];
		$widths['main-col'] = $main_col_width;

		if ($sidebar_count == 0) {
			$widths['wrapper'] = $main_col_width;
			$widths = $this->set_widths_for_no_sidebars($widths);
		}
		else if ($sidebar_count == 1) {
			$widths['sidebar-1'] = $sb_1_width;
			$widths['sidebar-2'] = 0;
			$widths['sidebar-container'] = $sb_1_width + 15;
			$widths['tabbed'] = $sb_1_width;
			$widths['wrapper'] = $main_col_width + $sb_1_width + 15;
			if (($suf_sidebar_alignment == 'left' && $prefix != '_1r') || $prefix == '_1l') {
				$widths['s1-lmargin'] = '-100%';
				$widths['s1-rmargin'] = 15;
				$widths['s2-lmargin'] = 0;
				$widths['s2-rmargin'] = 0;
				$widths['s1-l'] = 'auto';
				$widths['s1-r'] = $sb_1_width + 15;
				$widths['s2-l'] = 'auto';
				$widths['s2-r'] = 'auto';
//				$widths['s1-l-ie6'] = floor($sb_1_width/2) + 15;
				$widths['cleft'] = $sb_1_width + 15;
				$widths['cright'] = 0;
				$widths['cleft-ie6'] = $sb_1_width + 15;
				$widths['cright-ie6'] = 0;
			}
			else if (($suf_sidebar_alignment == 'right' && $prefix != '_1l') || $prefix == '_1r') {
				$widths['s1-lmargin'] = 15;
				$widths['s1-rmargin'] = - $sb_1_width - 15;
				$widths['s2-lmargin'] = 0;
				$widths['s2-rmargin'] = 0;
				$widths['s1-l'] = 'auto';
				$widths['s1-r'] =  'auto';
				$widths['s2-l'] = 'auto';
				$widths['s2-r'] = 'auto';
//				$widths['s1-l-ie6'] = 'auto';
//				$widths['s1-r-ie6'] =  $sb_1_width;
				$widths['cleft'] = 0;
				$widths['cright'] = $sb_1_width + 15;
				$widths['cleft-ie6'] = 0;
				$widths['cright-ie6'] = $sb_1_width + 15;
			}
		}
		else if ($sidebar_count == 2) {
			$widths['sidebar-1'] = $sb_1_width;
			$widths['sidebar-2'] = $sb_2_width;

			if (($suf_sidebar_alignment == $suf_sidebar_2_alignment) && $prefix != '_1l1r') {
				$widths['sidebar-container'] = $widths['sidebar-1'] + $widths['sidebar-2'] + 30;
				$widths['tabbed'] = $widths['sidebar-container'] - 17; // -17 because 2px are added by borders of widgets
				if (($suf_sidebar_alignment == 'left' && $prefix != '_2r') || $prefix == '_2l') {
					$widths = $this->set_widths_for_double_left_sidebar_layout($widths, $sb_1_width, $sb_2_width);
				}
				else if (($suf_sidebar_alignment == 'right' && $prefix != '_2l') || $prefix == '_2r') {
					$widths = $this->set_widths_for_double_right_sidebar_layout($widths, $sb_1_width, $sb_2_width);
				}
			}
			else {
				if (($suf_sidebar_alignment != $suf_sidebar_2_alignment && $prefix != '_2l' && $prefix != '_2r') || $prefix == '_1l1r') {
					if ($suf_sbtab_alignment == $suf_sidebar_alignment) {
						$widths['sidebar-container'] = $widths['sidebar-1'] + 15;
					}
					else {
						$widths['sidebar-container'] = $widths['sidebar-2'] + 15;
					}
					$widths['tabbed'] = $widths['sidebar-container'] - 15;

					if ($suf_sidebar_alignment == 'right') {
						$widths = $this->set_widths_for_single_left_single_right_sidebar_layout($widths, $sb_2_width, $sb_1_width,2, 1);
					}
					else if ($suf_sidebar_alignment == 'left') {
						$widths = $this->set_widths_for_single_left_single_right_sidebar_layout($widths, $sb_1_width, $sb_2_width, 1, 2);
					}
				}
				else {
					$widths['sidebar-container'] = $widths['sidebar-1'] + $widths['sidebar-2'] + 30;
					$widths['tabbed'] = $widths['sidebar-container'] - 17; // -17 because 2px are added by borders of widgets
					if ($prefix == '_2r' || ($prefix != '_2r' && $suf_sidebar_alignment == $suf_sidebar_2_alignment && $suf_sidebar_alignment == 'right')) {
						$widths = $this->set_widths_for_double_right_sidebar_layout($widths, $sb_1_width, $sb_2_width);
					}
					else if ($prefix == '_2l' || ($prefix != '_2l' && $suf_sidebar_alignment == $suf_sidebar_2_alignment && $suf_sidebar_alignment == 'left')) {
						$widths = $this->set_widths_for_double_left_sidebar_layout($widths, $sb_1_width, $sb_2_width);
					}
				}
			}
			$widths['wrapper'] = $main_col_width + $sb_1_width + $sb_2_width + 30;
		}

		$content_width = $widths['main-col'] - 30; // Account for padding on the sides
		$widths['wsidebar'] = $widths['sidebar-1'] + $widths['sidebar-2'] + 13;
		$widths['category'] = $widths['main-col'] - 275;
		$widths['tags'] = $widths['main-col'] - 275;
		$widths['calendar-side-padding'] = 4;

		$mag_hl_photos = suffusion_admin_get_size_from_field($suf_mag_headline_image_container_width, "250px");
		$widths['mag-headline-photos'] = (int)(substr($mag_hl_photos, 0, strlen($mag_hl_photos) - 2));
		$widths['mag-headline-block'] = '100%';

		return $widths;
	}

	function get_fluid_widths($component_widths, $sidebar_count, $prefix) {
		global $suf_sidebar_alignment, $suf_sidebar_2_alignment, $suf_sbtab_alignment, $suf_mag_headline_image_container_width, $content_width;
		$widths = array();
		$sb_1_width = $component_widths['sidebar-1'] < 95 ? 95 : $component_widths['sidebar-1'];
		$sb_2_width = $component_widths['sidebar-2'] < 95 ? 95 : $component_widths['sidebar-2'];

		$widths['wrapper'] = $component_widths['wrapper'].'%';
		$widths['wrapper-max'] = $component_widths['wrapper-max'];
		$widths['wrapper-min'] = $component_widths['wrapper-min'] < 600 ? 600 : $component_widths['wrapper-min'];
		$widths['main-col'] = '100%';

		if ($sidebar_count == 0) {
			$widths = $this->set_widths_for_no_sidebars($widths);
		}
		else if ($sidebar_count == 1) {
			$widths['sidebar-1'] = $sb_1_width;
			$widths['sidebar-2'] = 0;
			$widths['sidebar-container'] = $sb_1_width + 15;
			$widths['tabbed'] = $sb_1_width;
			if (($suf_sidebar_alignment == 'left' && $prefix != '_1r') || $prefix == '_1l') {
				$widths['s1-lmargin'] = '-100%';
				$widths['s1-rmargin'] = 15;
				$widths['s2-lmargin'] = 0;
				$widths['s2-rmargin'] = 0;
				$widths['s1-l'] = 'auto';
				$widths['s1-r'] = $sb_1_width + 15;
				$widths['s2-l'] = 'auto';
				$widths['s2-r'] = 'auto';
				$widths['cleft'] = $sb_1_width + 15;
				$widths['cright'] = 0;
				$widths['cleft-ie6'] = $sb_1_width + 15;
				$widths['cright-ie6'] = 0;
			}
			else if (($suf_sidebar_alignment == 'right' && $prefix != '_1l') || $prefix == '_1r') {
				$widths['s1-lmargin'] = 15;
				$widths['s1-rmargin'] = - $sb_1_width - 15;
				$widths['s2-lmargin'] = 0;
				$widths['s2-rmargin'] = 0;
				$widths['s1-l'] = 'auto';
				$widths['s1-r'] =  'auto';
				$widths['s2-l'] = 'auto';
				$widths['s2-r'] = 'auto';
//				$widths['s1-l-ie6'] = 'auto';
//				$widths['s1-r-ie6'] =  $sb_1_width;
				$widths['s1-l-ie6'] = 0;
				$widths['cleft'] = 0;
				$widths['cright'] = $sb_1_width + 15;
				$widths['cleft-ie6'] = 0;
				$widths['cright-ie6'] = $sb_1_width + 15;
			}
		}
		else if ($sidebar_count == 2) {
			$widths['sidebar-1'] = $sb_1_width;
			$widths['sidebar-2'] = $sb_2_width;

			if (($suf_sidebar_alignment == $suf_sidebar_2_alignment) && $prefix != '_1l1r') {
				$widths['sidebar-container'] = $widths['sidebar-1'] + $widths['sidebar-2'] + 30;
				$widths['tabbed'] = $widths['sidebar-container'] - 17; // -17 because 2px are added by borders of widgets
				if (($suf_sidebar_alignment == 'left' && $prefix != '_2r') || $prefix == '_2l') {
					$widths = $this->set_widths_for_double_left_sidebar_layout($widths, $sb_1_width, $sb_2_width);
				}
				else if (($suf_sidebar_alignment == 'right' && $prefix != '_2l') || $prefix == '_2r') {
					$widths = $this->set_widths_for_double_right_sidebar_layout($widths, $sb_1_width, $sb_2_width);
				}
			}
			else {
				if (($suf_sidebar_alignment != $suf_sidebar_2_alignment && $prefix != '_2l' && $prefix != '_2r') || $prefix == '_1l1r') {
					if ($suf_sbtab_alignment == $suf_sidebar_alignment) {
						$widths['sidebar-container'] = $widths['sidebar-1'] + 15;
					}
					else {
						$widths['sidebar-container'] = $widths['sidebar-2'] + 15;
					}
					$widths['tabbed'] = $widths['sidebar-container'] - 15;

					if ($suf_sidebar_alignment == 'right') {
						$widths = $this->set_widths_for_single_left_single_right_sidebar_layout($widths, $sb_2_width, $sb_1_width,2, 1);
					}
					else if ($suf_sidebar_alignment == 'left') {
						$widths = $this->set_widths_for_single_left_single_right_sidebar_layout($widths, $sb_1_width, $sb_2_width, 1, 2);
					}
				}
				else {
					$widths['sidebar-container'] = $widths['sidebar-1'] + $widths['sidebar-2'] + 30;
					$widths['tabbed'] = $widths['sidebar-container'] - 17; // -17 because 2px are added by borders of widgets
					if ($prefix == '_2r' || ($prefix != '_2r' && $suf_sidebar_alignment == $suf_sidebar_2_alignment && $suf_sidebar_alignment == 'right')) {
						$widths = $this->set_widths_for_double_right_sidebar_layout($widths, $sb_1_width, $sb_2_width);
					}
					else if ($prefix == '_2l' || ($prefix != '_2l' && $suf_sidebar_alignment == $suf_sidebar_2_alignment && $suf_sidebar_alignment == 'left')) {
						$widths = $this->set_widths_for_double_left_sidebar_layout($widths, $sb_1_width, $sb_2_width);
					}
				}
			}
		}

		$content_width = $widths['main-col'] - 30; // Account for padding on the sides
		$widths['wsidebar'] = $widths['sidebar-1'] + $widths['sidebar-2'] + 13;
		$widths['category'] = '60%';
		$widths['tags'] = '60%';
		$widths['calendar-side-padding'] = 4;

		$mag_hl_photos = suffusion_admin_get_size_from_field($suf_mag_headline_image_container_width, "250px");
		$widths['mag-headline-photos'] = (int)(substr($mag_hl_photos, 0, strlen($mag_hl_photos) - 2));
		$widths['mag-headline-block'] = '100%';

		return $widths;
	}

	function get_widths_for_template($prefix, $sb_count, $template = null) {
		$wrapper_width_type_var = 'suf'.$prefix.'_wrapper_width_type';
		$wrapper_width_preset_var = 'suf'.$prefix.'_wrapper_width_preset';
		$wrapper_width_var = 'suf'.$prefix.'_wrapper_width';
		$wrapper_width_flex_var = 'suf'.$prefix.'_wrapper_width_flex';
		$wrapper_width_flex_max_var = 'suf'.$prefix.'_wrapper_width_flex_max';
		$wrapper_width_flex_min_var = 'suf'.$prefix.'_wrapper_width_flex_min';
		$main_col_width_var = 'suf'.$prefix.'_main_col_width';
		$sb_1_width_var = 'suf'.$prefix.'_sb_1_width';
		$sb_2_width_var = 'suf'.$prefix.'_sb_2_width';
		global $$wrapper_width_preset_var, $$wrapper_width_var, $$main_col_width_var, $$sb_1_width_var, $$sb_2_width_var, $suf_sidebar_count, $$wrapper_width_type_var, $$wrapper_width_flex_var, $$wrapper_width_flex_max_var, $$wrapper_width_flex_min_var;

		$wrapper_width_preset = $$wrapper_width_preset_var;
		$wrapper_width = $$wrapper_width_var;
		if (isset($$wrapper_width_flex_var)) $wrapper_width_flex = $$wrapper_width_flex_var;
		if (isset($$wrapper_width_flex_max_var)) $wrapper_width_flex_max = $$wrapper_width_flex_max_var;
		if (isset($$wrapper_width_flex_min_var)) $wrapper_width_flex_min = $$wrapper_width_flex_min_var;
		$main_col_width = $$main_col_width_var;
		if (isset($$sb_1_width_var)) $sb_1_width = $$sb_1_width_var;
		if (isset($$sb_2_width_var)) $sb_2_width = $$sb_2_width_var;
		if ($prefix) {
			$sidebar_count = $sb_count;
		}
		else {
			$sidebar_count = $suf_sidebar_count;
		}
		$wrapper_width_type = isset($$wrapper_width_type_var) ? $$wrapper_width_type_var : 'fixed';

		if ($wrapper_width_type == 'fluid') {
			$sb_1_width = isset($sb_1_width) ? suffusion_admin_get_size_from_field($sb_1_width, "260px", false) : "260px";
			$sb_2_width = isset($sb_2_width) ? suffusion_admin_get_size_from_field($sb_2_width, "260px", false) : "260px";
			$wrapper_width_flex = isset($wrapper_width_flex) ? $wrapper_width_flex : "75";
			$wrapper_width_flex_max = isset($wrapper_width_flex_max) ? suffusion_admin_get_size_from_field($wrapper_width_flex_max, "1200px", false) : "1200px";
			$wrapper_width_flex_min = isset($wrapper_width_flex_min) ? suffusion_admin_get_size_from_field($wrapper_width_flex_min, "600px", false) : "600px";
			$component_widths = array('wrapper' => $wrapper_width_flex,
				'wrapper-max' => (int)(substr($wrapper_width_flex_max, 0, strlen($wrapper_width_flex_max) - 2)),
				'wrapper-min' => (int)(substr($wrapper_width_flex_min, 0, strlen($wrapper_width_flex_min) - 2)),
				'sidebar-1' => (int)(substr($sb_1_width, 0, strlen($sb_1_width) - 2)),
				'sidebar-2' => (int)(substr($sb_2_width, 0, strlen($sb_2_width) - 2)));
			$widths = $this->get_fluid_widths($component_widths, $sidebar_count, $prefix);
		}
		else { // Fixed width
			if (($wrapper_width_preset != "custom") && ($wrapper_width_preset != "custom-components")) {
				$widths = $this->get_automatic_widths($wrapper_width_preset, $sidebar_count, $prefix);
			}
			else if ($wrapper_width_preset == "custom") {
				$wrapper_width = suffusion_admin_get_size_from_field($wrapper_width, "1000px");
				$widths = $this->get_automatic_widths((int)(substr($wrapper_width, 0, strlen($wrapper_width) - 2)), $sidebar_count, $prefix);
			}
			else {
				$main_col_width = suffusion_admin_get_size_from_field($main_col_width, "725px");
				$sb_1_width = isset($sb_1_width) ? suffusion_admin_get_size_from_field($sb_1_width, "260px") : "260px";
				$sb_2_width = isset($sb_2_width) ? suffusion_admin_get_size_from_field($sb_2_width, "260px") : "260px";
				$component_widths = array('main-col' => (int)(substr($main_col_width, 0, strlen($main_col_width) - 2)),
					'sidebar-1' => (int)(substr($sb_1_width, 0, strlen($sb_1_width) - 2)),
					'sidebar-2' => (int)(substr($sb_2_width, 0, strlen($sb_2_width) - 2)));
				$widths = $this->get_widths_from_components($component_widths, $sidebar_count, $prefix);
			}
		}
		return apply_filters('suffusion_set_template_widths', $widths, $template);
	}

	function set_widths_for_no_sidebars($widths) {
		if (!is_array($widths)) $widths = array();
		$widths['sidebar-1'] = 0;
		$widths['sidebar-2'] = 0;
		$widths['sidebar-container'] = 0;
		$widths['tabbed'] = 0;
		$widths['s1-lmargin'] = 0;
		$widths['s1-rmargin'] = 0;
		$widths['s2-lmargin'] = 0;
		$widths['s2-rmargin'] = 0;
		$widths['s1-l'] = 'auto';
		$widths['s1-r'] = 'auto';
		$widths['s2-l'] = 'auto';
		$widths['s2-r'] = 'auto';
		$widths['cleft'] = 0;
		$widths['cright'] = 0;
		return $widths;
	}

	function set_widths_for_double_left_sidebar_layout($widths, $sb_1_width, $sb_2_width) {
		if (!is_array($widths)) $widths = array();
		$widths['s1-lmargin'] = 0;
		$widths['s1-rmargin'] = 0;
		$widths['s2-lmargin'] = 15;
		$widths['s2-rmargin'] = - $sb_2_width - 15;
		$widths['s1-l'] = 'auto';
		$widths['s1-r'] = 'auto';
		$widths['s2-l'] = 'auto';
		$widths['s2-r'] = 'auto';
		$widths['sw-l'] = 'auto';
		$widths['sw-r'] = $sb_1_width + $sb_2_width + 30;
		$widths['sw-l-ie6'] = 'auto';
		$widths['sw-r-ie6'] = floor(($sb_1_width + $sb_2_width)/2) + 30;
		$widths['cleft'] = $sb_1_width + $sb_2_width + 30;
		$widths['cright'] = 0;
		return $widths;
	}

	function set_widths_for_double_right_sidebar_layout($widths, $sb_1_width, $sb_2_width) {
		if (!is_array($widths)) $widths = array();
		$widths['s1-lmargin'] = 0;
		$widths['s1-rmargin'] = 0;
		$widths['s2-lmargin'] = - $sb_2_width - 15;
		$widths['s2-rmargin'] = 15;
		$widths['s1-l'] = 'auto';
		$widths['s1-r'] = 'auto';
		$widths['s2-l'] = 'auto';
		$widths['s2-r'] = 'auto';
		$widths['cleft'] = 0;
		$widths['cright'] = $sb_1_width + $sb_2_width + 30;
		return $widths;
	}

	function set_widths_for_single_left_single_right_sidebar_layout($widths, $lwidth, $rwidth, $left, $right) {
		if (!is_array($widths)) $widths = array();
		$l = 's'.$left;
		$r = 's'.$right;

		$widths["$l-lmargin"] = '-100%';
		$widths["$l-rmargin"] = 0;
		$widths["$r-lmargin"] = 15;
		$widths["$r-rmargin"] = - $rwidth - 15;
		$widths["$l-l"] = 'auto';
		$widths["$l-r"] = $lwidth + 15;
		$widths["$r-l"] = 'auto';
		$widths["$r-r"] = 'auto';
		$widths["$l-l-ie6"] = floor(($lwidth + $rwidth)/2) + 30;
		$widths["$l-r-ie6"] = $lwidth + 15;
		$widths['cleft'] = $lwidth + 15;
		$widths['cright'] = $rwidth + 15;
		return $widths;
	}

	function get_wrapper_width_css($widths, $main_element, $control_element = "") {
		$wrapper_width = suffusion_admin_check_integer($widths['wrapper']) ? $widths['wrapper'].'px' : $widths['wrapper'];
		$ret = "
$main_element { width: $wrapper_width;";
		if (isset($widths['wrapper-max'])) {
			$ret .= " max-width: {$widths['wrapper-max']}px; min-width: {$widths['wrapper-min']}px;";
		}
		else {
			$ret .= " max-width: $wrapper_width; min-width: $wrapper_width;";
		}
		$ret .= " }\n";
		if (trim($control_element) != "") {
			$ret .=
"$control_element { width: ".(suffusion_admin_check_integer($widths['wrapper']) ? $widths['wrapper'].'px' : '100%')." }";
		}
		return $ret;
	}

	function get_template_specific_container_classes($template_class, $widths) {
		$ret = "";
		$ret .= $this->get_wrapper_width_css($widths, "$template_class #wrapper");
		if (isset($widths['wrapper-max'])) {
			$ret .= "
* html $template_class #wrapper {
	w\\idth: expression(this.width > {$widths['wrapper-max']} ? {$widths['wrapper-max']} + 'px' : (this.width < {$widths['wrapper-min']} ? {$widths['wrapper-min']} + 'px' : true));
}";
		}
		$ret .= "
$template_class #container {";
		if (isset($widths['cleft'])) {
			$ret .= " padding-left: {$widths['cleft']}px; padding-right: {$widths['cright']}px;";
		} else {
			$ret .= " padding-left: 0; padding-right: 0;";
		}
		$ret .= " }
$template_class #main-col {	width: ".(suffusion_admin_check_integer($widths['main-col']) ? $widths['main-col'].'px' : $widths['main-col'])."; }
* html $template_class #main-col { w\\idth: ".(suffusion_admin_check_integer($widths['main-col']) ? ($widths['main-col'] - 30).'px' : $widths['main-col'])." }";
		return $ret;
	}

	function get_template_specific_header_footer_nav_classes($template_class, $widths) {
		global $suf_navt_bar_style, $suf_wah_layout_style, $suf_header_layout_style, $suf_footer_layout_style, $suf_nav_bar_style;
		$ret = "";
		// Top Navigation Bar
		if ($suf_navt_bar_style == 'full-align') {
			$ret .= $this->get_wrapper_width_css($widths, "$template_class #nav-top .col-control, $template_class #top-bar-right-spanel .col-control");
		}
		else if ($suf_navt_bar_style == 'align') {
			$ret .= $this->get_wrapper_width_css($widths, "$template_class #nav-top, $template_class #top-bar-right-spanel", "$template_class #nav-top .col-control, $template_class #top-bar-right-spanel .col-control");
		}

		// Widgets above header
		if ($suf_wah_layout_style == 'full-align') {
			$ret .= $this->get_wrapper_width_css($widths, "$template_class #widgets-above-header .col-control");
		}
		else if ($suf_wah_layout_style == 'align') {
			$ret .= $this->get_wrapper_width_css($widths, "$template_class #widgets-above-header", "$template_class  #widgets-above-header .col-control");
		}

		// Header and Main Navigation Bar
		if ($suf_header_layout_style != 'in-align') {
			// Header
			if ($suf_header_layout_style == 'out-cfull-halign') {
				$ret .= $this->get_wrapper_width_css($widths, "$template_class #header-container .col-control");
			}
			else if ($suf_header_layout_style == 'out-hcalign') {
				$ret .= $this->get_wrapper_width_css($widths, "$template_class #header-container", "$template_class #header-container .col-control");
			}

			// Main Navigation Bar
			if ($suf_nav_bar_style == 'full-align') {
				$ret .= $this->get_wrapper_width_css($widths, "$template_class #nav .col-control");
			}
			else if ($suf_nav_bar_style == 'align') {
				$ret .= $this->get_wrapper_width_css($widths, "$template_class #nav", "$template_class #nav .col-control");
			}
		}
		else {
			$ret .= "$template_class #nav .col-control { width: ".(suffusion_admin_check_integer($widths['wrapper']) ? $widths['wrapper'].'px' : '100%')." }\n";
		}

		//Footer
		if ($suf_footer_layout_style != 'in-align') {
			if ($suf_footer_layout_style == 'out-cfull-halign') {
				$ret .= $this->get_wrapper_width_css($widths, "$template_class #page-footer .col-control");
			}
			else if ($suf_footer_layout_style == 'out-hcalign') {
				$ret .= $this->get_wrapper_width_css($widths, "$template_class #page-footer");
			}
		}

		return $ret;
	}

	function get_template_specific_other_elements($template_class, $widths) {
		$ret = "
$template_class .post-footer .category, $template_class .postdata .category {
	max-width: ".(suffusion_admin_check_integer($widths['category']) ? $widths['category'].'px' : $widths['category']).";
}
$template_class .tags {
	max-width: ".(suffusion_admin_check_integer($widths['tags']) ? $widths['tags'].'px' : $widths['tags']).";
}
* html $template_class .post-footer .category, * html $template_class .postdata .category, * html $template_class .tags {
	w\\idth: expression(this.width > Math.floor((document.getElementById('main-col').offsetWidth)/2) ? '60%' : true);
}
$template_class #sidebar, $template_class #sidebar-b, $template_class #sidebar-shell-1 {
	width: ".$widths['sidebar-1']."px;
}
$template_class #sidebar.flattened, $template_class #sidebar-b.flattened {
	width: ".($widths['sidebar-1'] - 2)."px;
}
$template_class #sidebar-shell-1 {
	margin-left: ".(suffusion_admin_check_integer($widths['s1-lmargin']) ? $widths['s1-lmargin'].'px' : $widths['s1-lmargin']).";
	margin-right: ".(suffusion_admin_check_integer($widths['s1-rmargin']) ? $widths['s1-rmargin'].'px' : $widths['s1-rmargin']).";
	left: ".(suffusion_admin_check_integer($widths['s1-l']) ? $widths['s1-l'].'px' : $widths['s1-l']).";
	right: ".(suffusion_admin_check_integer($widths['s1-r']) ? $widths['s1-r'].'px' : $widths['s1-r']).";
}";
		if ($widths['sidebar-2'] > 0) {
			$ret .= "
$template_class #sidebar-2, $template_class #sidebar-2-b, $template_class #sidebar-shell-2 {
	width: ".$widths['sidebar-2']."px;
}
$template_class #sidebar-2.flattened, $template_class #sidebar-2-b.flattened {
	width: ".($widths['sidebar-2'] - 2)."px;
}";
		}
		else {
			$ret .= "
$template_class #sidebar-2, $template_class #sidebar-2-b, $template_class #sidebar-shell-2, $template_class #sidebar-2.flattened, $template_class #sidebar-2-b.flattened {
	width: 0;
}";
		}
		$ret .= "
$template_class #sidebar-shell-2 {
	margin-left: ".(suffusion_admin_check_integer($widths['s2-lmargin']) ? $widths['s2-lmargin'].'px' : $widths['s2-lmargin']).";
	margin-right: ".(suffusion_admin_check_integer($widths['s2-rmargin']) ? $widths['s2-rmargin'].'px' : $widths['s2-rmargin']).";
	left: ".(suffusion_admin_check_integer($widths['s2-l']) ? $widths['s2-l'].'px' : $widths['s2-l']).";
	right: ".(suffusion_admin_check_integer($widths['s2-r']) ? $widths['s2-r'].'px' : $widths['s2-r']).";
}
$template_class #sidebar-container { width: ".$widths['sidebar-container']."px; }
$template_class .sidebar-container-left { right: ".$widths['sidebar-container']."px; }
$template_class .sidebar-container-right { margin-right: -".$widths['sidebar-container']."px; }
$template_class .sidebar-container-left #sidebar-wrap { right: auto; }
$template_class #wsidebar-top, $template_class #wsidebar-bottom { width: ".$widths['wsidebar']."px; }
$template_class #sidebar-wrap {
	width: ".($widths['wsidebar'] + 17)."px;";
		if (isset($widths['sw-l'])) { $ret .= "
	left: ".(suffusion_admin_check_integer($widths['sw-l']) ? $widths['sw-l'].'px' : $widths['sw-l']).";
	right: ".(suffusion_admin_check_integer($widths['sw-r']) ? $widths['sw-r'].'px' : $widths['sw-r']).";";
		} else { $ret .= "
	left: auto;
	right: auto;";
		}
		$ret .= "
}
* html $template_class #sidebar-wrap {";
		if (isset($widths['sw-l-ie6'])) { $ret .= "
	lef\\t: ".(suffusion_admin_check_integer($widths['sw-l-ie6']) ? $widths['sw-l-ie6'].'px' : $widths['sw-l-ie6']).";
	righ\\t: ".(suffusion_admin_check_integer($widths['sw-r-ie6']) ? $widths['sw-r-ie6'].'px' : $widths['sw-r-ie6']).";";
		} else { $ret .= "
	lef\\t: auto;
	righ\\t: auto;";
		}
		$ret .= "
}
$template_class .sidebar-wrap-left {
	margin-left: -100%;
}
* html $template_class .sidebar-wrap-left, * html $template_class #sidebar-container.sidebar-container-left {
	margin-left: expression((document.getElementById('main-col') == null ? (document.getElementById('single-col').offsetWidth - 15 + ".(floor(($widths['sidebar-1'] + $widths['sidebar-2'])/2)).") : (document.getElementById('main-col').offsetWidth - 15 + ".(floor(($widths['sidebar-1'] + $widths['sidebar-2'])/2))."))*(-1)+'px');
}
$template_class .sidebar-wrap-right {
	margin-right: -".($widths['wsidebar'] + 17)."px;
}
* html $template_class #sidebar, * html $template_class #sidebar-b, * html $template_class #sidebar-shell-1 {
	w\\idth: ".($widths['sidebar-1'] - 8)."px;
}
* html $template_class #sidebar-shell-1 {";
		if (isset($widths['s1-l-ie6'])) { $ret .= "
	lef\\t: ".$widths['s1-l-ie6']."px;";
		}
		if (isset($widths['s1-r-ie6'])) { $ret .= "
	r\\ight: ".$widths['s1-r-ie6']."px;";
		}
		$ret .= "
}";
		if ($widths['sidebar-2'] > 0) {
			$ret .= "
* html $template_class #sidebar-2, * html $template_class #sidebar-2-b, * html $template_class #sidebar-shell-2 {
	w\\idth: ".($widths['sidebar-2'] - 8)."px;
}";
		}
		$ret .= "
* html $template_class #sidebar-shell-2 {";
		if (isset($widths['s2-l-ie6'])) { $ret .= "
	lef\\t: ".($widths['s2-l-ie6'])."px;";
		}
		if (isset($widths['s2-r-ie6'])) { $ret .= "
	r\\ight: ".($widths['s2-r-ie6'])."px;";
		}
		$ret .= "
}
* html $template_class #sidebar-container {
	w\\idth: ".($widths['sidebar-container'] - 10)."px;
}
$template_class .tab-box {
	width: ".$widths['tabbed']."px;
}";
		return $ret;
	}

	function get_template_specific_classes($template_class, $widths) {
		$ret = $this->get_template_specific_container_classes($template_class, $widths);
		$ret .= $this->get_template_specific_header_footer_nav_classes($template_class, $widths);
		$ret .= $this->get_template_specific_other_elements($template_class, $widths);
		return $ret;
	}

	function get_column_width($num_columns) {
		$col_width = "100%";
		switch ($num_columns) {
		case 1:
			$col_width = "100%";
			break;
		case 2:
			$col_width = "49%";
			break;
		case 3:
			$col_width = "32%";
			break;
		case 4:
			$col_width = "24%";
			break;
		case 5:
			$col_width = "19%";
			break;
		default:
			$col_width = "100%";
			break;
		}
		return $col_width;
	}

	function get_margin($num_columns) {
		$margin = "5px";
		switch ($num_columns) {
		case 1:
			$margin = "5px 0 5px 0";
			break;
		case 2:
			$margin = "5px 0.39% 5px 0.39%";
			break;
		case 3:
			$margin = "5px 0.51% 5px 0.51%";
			break;
		case 4:
			$margin = "5px 0.38% 5px 0.38%";
			break;
		case 5:
			$margin = "5px 0.35% 5px 0.35%";
			break;
		default:
			$margin = "5px 5px 5px 5px";
			break;
		}
		return $margin;
	}

	function get_ie6_margin($num_columns) {
		$margin = "5px";
		switch ($num_columns) {
		case 1:
			$margin = "5px 0px 5px 0px";
			break;
		case 2:
			$margin = "5px 3px 5px 3px";
			break;
		case 3:
			$margin = "5px 5px 5px 4px";
			break;
		case 4:
			$margin = "5px 3px 5px 3px";
			break;
		case 5:
			$margin = "5px 2px 5px 2px";
			break;
		default:
			$margin = "5px 0px 5px 0px";
			break;
		}
		return $margin;
	}

	function get_navigation_bar_custom_css($bar) {
		if ($bar == 'nav') { $opt = 'nav'; } else { $opt = 'navt'; }
		$def_cust = "suf_{$opt}_skin_def_cust";
		global $$def_cust;
		$ret = "";
		if ($$def_cust == 'custom') {
			$nav = '#'.$bar;
			$ret .= "
$nav, $nav.continuous {
	".$this->get_bg_information("suf_{$opt}_skin_settings_bg").";
	".$this->get_font_information("suf_{$opt}_skin_settings_bg_font").";
	".$this->get_border_information("suf_{$opt}_skin_settings_bg_border")."
}
$nav ul li, $nav ul li a, $nav.continuous ul li, $nav.continuous ul li a,
$nav ul ul li, $nav ul ul li a, $nav ul ul a.drop, $nav.continuous ul ul li, $nav.continuous ul ul li a, $nav.continuous ul ul a.drop {
	".$this->get_bg_information("suf_{$opt}_skin_settings").";
	".$this->get_font_information("suf_{$opt}_skin_settings_font").";
	".$this->get_border_information("suf_{$opt}_skin_settings_border").";
}
$nav ul li a:visited, $nav ul ul li a:visited, $nav ul ul a.drop:visited, $nav.continuous ul li a:visited, $nav.continuous ul ul li a:visited, $nav.continuous ul ul a.drop:visited {
	".$this->get_bg_information("suf_{$opt}_skin_settings_visited").";
	".$this->get_font_information("suf_{$opt}_skin_settings_visited_font").";
	".$this->get_border_information("suf_{$opt}_skin_settings_visited_border").";
}
$nav li a:hover, $nav ul li a:hover, $nav ul ul li a:hover, $nav ul ul ul li a:hover, $nav ul ul ul ul li a:hover, $nav ul ul ul ul ul li a:hover, $nav ul ul ul ul ul ul li a:hover,
$nav ul ul ul ul ul ul ul li a:hover, $nav :hover > a, $nav ul ul :hover > a, $nav ul ul :hover > a.drop, $nav ul ul a.drop:hover, $nav .current_page_item a:hover,
$nav.continuous ul li a:hover, $nav.continuous ul ul li a:hover, $nav.continuous ul ul ul li a:hover, $nav.continuous ul ul ul ul li a:hover,
$nav.continuous :hover > a, $nav.continuous ul ul :hover > a, $nav.continuous ul ul :hover > a.drop, $nav.continuous ul ul a.drop:hover, $nav.continuous .current_page_item a:hover,
$nav.continuous ul ul ul ul ul li a:hover, $nav.continuous ul ul ul ul ul ul li a:hover, $nav.continuous ul ul ul ul ul ul ul li a:hover {
	".$this->get_bg_information("suf_{$opt}_skin_settings_hover").";
	".$this->get_font_information("suf_{$opt}_skin_settings_hover_font").";
	".$this->get_border_information("suf_{$opt}_skin_settings_hover_border").";
}
$nav ul li a:active, $nav a:active, $nav ul li.current_page_item a, $nav ul li.current-cat a, $nav ul li.current-menu-item a,
$nav.continuous ul li a:active, $nav.continuous a:active, $nav.continuous ul li.current_page_item a, $nav.continuous ul li.current-cat a, $nav.continuous ul li.current-menu-item a {
	".$this->get_bg_information("suf_{$opt}_skin_settings_hl").";
	".$this->get_font_information("suf_{$opt}_skin_settings_hl_font").";
	".$this->get_border_information("suf_{$opt}_skin_settings_hl_border").";
}";
		}
		return $ret;
	}

	function get_custom_body_settings() {
		global $suf_body_style_setting, $suf_body_background_color, $suf_body_background_image, $suf_body_background_repeat, $suf_body_background_attachment, $suf_body_background_position;
		if ($suf_body_style_setting == 'custom') {
			$ret = "
body {
	background-color: #".$this->strip_color_hash($suf_body_background_color).";";
			if ($suf_body_background_image != "") {
				$body_bg_url = " url($suf_body_background_image) ";
				$ret .= "
	background-image: $body_bg_url;
	background-repeat: $suf_body_background_repeat;
	background-attachment: $suf_body_background_attachment;
	background-position: $suf_body_background_position;";
			}
			$ret .= "
}";
			return $ret;
		}
		return "";
	}

	function get_zero_sidebars_template_widths($template_class = ".page-template-no-sidebars-php") {
		$ret = "
$template_class #container { padding-left: 0; padding-right: 0; }
$template_class .entry img { max-width: 99%; }
$template_class .wp-caption { max-width: 99%; }
$template_class .entry .wp-caption img { max-width: 100%; }
* html $template_class .entry img { w\\idth: expression(this.width > (parseInt(document.getElementById('wrapper').offsetWidth) - 60) ? '96%' : true); }
* html $template_class .wp-caption { w\\idth: expression(this.offsetWidth > (parseInt(document.getElementById('wrapper').offsetWidth) - 60) ? '96%' : true); }
* html $template_class .entry .wp-caption img { w\\idth: expression(this.width > (parseInt(document.getElementById('wrapper').offsetWidth) - 60) ? '100%' : true); }
$template_class #main-col {	width: 100%; }";
		return $ret;
	}

	function get_nr_css($widths) {
		global $suf_nr_main_cover_w, $suf_nr_main_cover_h, $suf_nr_books_per_row, $suf_nr_books_per_row;
		$slot_width = floor(100/$suf_nr_books_per_row);
		$ret = "
div.booklisting img, div.bookentry img { width: ".suffusion_admin_get_size_from_field($suf_nr_main_cover_w, "108px")."; height: ".suffusion_admin_get_size_from_field($suf_nr_main_cover_h, "160px")."; }
div.bookentry .stats { width: ".(suffusion_get_numeric_size_from_field($suf_nr_main_cover_w, 108) + 34)."px; }
div.bookentry .review { width: ".($widths['main-col'] - suffusion_get_numeric_size_from_field($suf_nr_main_cover_w, 108) - 80)."px; }
* html div.bookentry .review { w\\idth: ".($widths['main-col'] - suffusion_get_numeric_size_from_field($suf_nr_main_cover_w, 108) - 100)."px; }
col.nr-shelf-slot { width: $slot_width%; }";
		return $ret;
	}

	function get_pullout_css($type) {
		$position = "suf_{$type}_meta_position";
		global $$position;
		$ret = "";
		if ($$position != 'corners') {
			if ($$position == 'left-pullout') {
				$ret .= "
div.$type .entry-container { padding-left: 150px; }";
			}
			else {
				$ret .= "
div.$type .entry-container { padding-right: 150px; }";
			}
			$ret .= "
div.$type .entry { width: 100%; float: left; }";
		}
		return $ret;
	}

	function get_finalized_header_footer_nav_css() {
		global $suf_navt_bar_style, $suf_wah_layout_style, $suf_wah_columns, $suf_footer_layout_style, $suf_header_layout_style, $suf_nav_bar_style;
		global $suf_nav_text_transform, $suf_navt_text_transform, $suf_navt_dd_pos;
		$ret = "";
		if ($suf_navt_bar_style == 'full-full') {
			$ret .= "
#nav-top .col-control, #top-bar-right-spanel .col-control { width: auto; }
#nav-top {
	border-radius: 0;
	-moz-border-radius: 0;
	-webkit-border-radius: 0;
	-khtml-border-radius: 0;
}";
		}
		else if ($suf_navt_bar_style == 'full-align') {
			$ret .= "
#nav-top, #top-bar-right-spanel { width: auto; }
#nav-top {
	border-radius: 0;
	-moz-border-radius: 0;
	-webkit-border-radius: 0;
	-khtml-border-radius: 0;
}";
		}
		else if ($suf_navt_bar_style == 'align') {
			$ret .= "
#nav-top, #top-bar-right-spanel { margin: 0 auto; }";
		}

		$ret .= "
#nav ul { text-transform: $suf_nav_text_transform; }
#nav-top ul { text-transform: $suf_navt_text_transform; float: $suf_navt_dd_pos; }";

		if ($suf_wah_layout_style == 'full-full') {
			$ret .= "
#widgets-above-header .col-control { width: auto; }";
		}
		else if ($suf_wah_layout_style == 'full-align') {
			$ret .= "
#widgets-above-header { width: auto; }";
		}
		else if ($suf_wah_layout_style == 'align') {
			$ret .= "
#widgets-above-header { margin: 0 auto; }";
		}

		$wah_columns = intval($suf_wah_columns);
		$wah_width = $this->get_column_width($wah_columns);
		$wah_margin = $this->get_margin($wah_columns);
		$wah_ie_margin = $this->get_ie6_margin($wah_columns);
		$ret .= "
#widgets-above-header .suf-widget { width: $wah_width; display: inline-block; margin: $wah_margin; }
* html #widgets-above-header .suf-widget { ma\\rgin: $wah_ie_margin; }";

		if ($suf_footer_layout_style != 'in-align') {
			if ($suf_footer_layout_style == 'out-hcfull') {
				$ret .= "
#page-footer .col-control { width: auto; }";
			}
			else if ($suf_footer_layout_style == 'out-cfull-halign') {
				$ret .= "
#page-footer { width: auto; }";
			}
			else if ($suf_footer_layout_style == 'out-hcalign') {
				$ret .= "
#page-footer { margin: 0 auto; padding: 0 10px; }";
			}
		}

		if ($suf_header_layout_style != 'in-align') {
			if ($suf_header_layout_style == 'out-hcfull') {
				$ret .= "
#header-container .col-control { width: auto; }";
			}
			else if ($suf_header_layout_style == 'out-cfull-halign') {
				$ret .= "
#header-container { width: auto; }";
			}
			else if ($suf_header_layout_style == 'out-hcalign') {
				$ret .= "
#header-container { margin: 0 auto; padding: 0 10px; }";
			}
			if ($suf_nav_bar_style == 'full-full') {
				$ret .= "
#nav .col-control { width: auto; }";
			}
			else if ($suf_nav_bar_style == 'full-align') {
				$ret .= "
#nav { width: auto; }";
			}
			else if ($suf_nav_bar_style == 'align') {
				$ret .= "
#nav { margin: 0 auto; }";
			}
		}
		else {
			$ret .= "
#nav { margin: 0 auto; width: 100%; ";
			if (is_rtl()) {
				$ret .= " float: right; ";
			}
			$ret .= "}";
		}
		return $ret;
	}

	function get_mag_template_widths($widths) {
		global $suf_mag_headlines_height, $suf_mag_excerpts_per_row, $suf_mag_excerpts_image_box_height, $suf_mag_catblocks_per_row, $suf_mag_catblocks_image_box_height;
		global $suf_mag_catblocks_title_alignment, $suf_mag_catblocks_main_title_alignment, $suf_mag_excerpts_main_title_alignment, $suf_mag_excerpt_title_alignment, $suf_mag_headline_main_title_alignment;
		$mag_excerpt_td_width = floor(100/(int)$suf_mag_excerpts_per_row);
		if (suffusion_admin_check_integer($widths['main-col'])) {
			$mag_excerpt_td_img_width = floor($widths['main-col']/(int)$suf_mag_excerpts_per_row) - 20;
			$mag_excerpt_td_img_width_ie = floor($widths['main-col']/(int)$suf_mag_excerpts_per_row) - 20 - (int)$suf_mag_excerpts_per_row;
			$mag_category_td_img_width = floor($widths['main-col']/(int)$suf_mag_catblocks_per_row) - 20;
			$mag_category_td_img_width_ie = floor($widths['main-col']/(int)$suf_mag_catblocks_per_row) - 20 - (int)$suf_mag_catblocks_per_row;
		}
		else {
			$mag_excerpt_td_img_width = '98%';
			$mag_excerpt_td_img_width_ie = '95%';
			$mag_category_td_img_width = '98%';
			$mag_category_td_img_width_ie = '95%';
		}
		$mag_category_td_width = floor(100/(int)$suf_mag_catblocks_per_row);
		$ret = "
.suf-mag-headlines {";
		if (isset($widths['mag-headlines'])) {
			$ret .= "
	width: ".(suffusion_admin_check_integer($widths['mag-headlines']) ? $widths['mag-headlines'].'px' : $widths['mag-headlines']).";";
		}
		$ret .= "
	padding-left: ".(suffusion_admin_check_integer($widths['mag-headline-photos']) ? ($widths['mag-headline-photos']).'px' : $widths['mag-headline-photos']).";
}";
		if (isset($widths['mag-headlines'])) {
			$ret .= "
* html .suf-mag-headlines { w\\idth: ".(suffusion_admin_check_integer($widths['mag-headlines']) ? ($widths['mag-headlines'] - 15).'px' : '96%')."; }
* html table.suf-mag-excerpts, * html table.suf-mag-categories { w\\idth: ".(suffusion_admin_check_integer($widths['mag-headlines']) ? ($widths['mag-headlines'] - 25).'px' : '96%')."; }";
		}
		$ret .= "
.suf-mag-headline-photo-box { width: {$widths['mag-headline-photos']}px; right: {$widths['mag-headline-photos']}px; }
.suf-mag-headline-block { width: ".(suffusion_admin_check_integer($widths['mag-headline-block']) ? $widths['mag-headline-block'].'px' : $widths['mag-headline-block'])."; }
* html .suf-mag-headline-block { w\\idth: ".(suffusion_admin_check_integer($widths['mag-headline-block']) ? ($widths['mag-headline-block'] - 20).'px' : $widths['mag-headline-block'])."; }
.suf-mag-headlines { height: ".suffusion_admin_get_size_from_field($suf_mag_headlines_height, "250px")." }
col.suf-mag-excerpt { width: ".$mag_excerpt_td_width."%; }
.suf-mag-excerpt-image {
	width: ".(suffusion_admin_check_integer($mag_excerpt_td_img_width) ? $mag_excerpt_td_img_width.'px' : $mag_excerpt_td_img_width).";
	height: ".suffusion_admin_get_size_from_field($suf_mag_excerpts_image_box_height, "100px").";
}
* html .suf-mag-excerpt-image { w\\idth: ".(suffusion_admin_check_integer($mag_excerpt_td_img_width_ie) ? $mag_excerpt_td_img_width_ie.'px' : $mag_excerpt_td_img_width_ie)."; }
col.suf-mag-category { width: $mag_category_td_width%; }
.suf-mag-category-image {
	width: ".(suffusion_admin_check_integer($mag_category_td_img_width) ? $mag_category_td_img_width.'px' : $mag_category_td_img_width).";
	height: ".suffusion_admin_get_size_from_field($suf_mag_catblocks_image_box_height, "100px").";
}
* html .suf-mag-category-image { w\\idth: ".(suffusion_admin_check_integer($mag_category_td_img_width_ie) ? $mag_category_td_img_width_ie.'px' : $mag_category_td_img_width_ie)."; }
h2.suf-mag-category-title {	text-align: $suf_mag_catblocks_title_alignment; }
.suf-mag-categories th { text-align: $suf_mag_catblocks_main_title_alignment; }
.suf-mag-excerpts th { text-align: $suf_mag_excerpts_main_title_alignment; }
h2.suf-mag-excerpt-title { text-align: $suf_mag_excerpt_title_alignment; }
h2.suf-mag-headlines-title { text-align: $suf_mag_headline_main_title_alignment; }";

		return $ret;
	}

	function get_custom_header_settings() {
		global $suf_header_style_setting, $suf_header_image_type, $suf_header_background_image, $suf_header_background_rot_folder, $suf_header_background_repeat;
		global $suf_header_background_position, $suf_header_section_height, $suf_header_height, $suf_header_gradient_start_color, $suf_header_gradient_end_color, $suf_header_gradient_style;
		global $suf_blog_title_color, $suf_blog_title_style, $suf_blog_title_hover_color, $suf_blog_title_hover_style, $suf_blog_description_color, $suf_sub_header_vertical_alignment;
		global $suf_header_alignment, $suf_sub_header_alignment, $suf_wih_width;
		$ret = "";
		if ($suf_header_style_setting == "custom") {
			if (($suf_header_image_type == "image" && isset($suf_header_background_image) && trim($suf_header_background_image) != '') ||
					($suf_header_image_type == "rot-image" && isset($suf_header_background_rot_folder) && trim($suf_header_background_rot_folder) != '')) {
				if ($suf_header_image_type == "image") {
					$header_bg_url = " url($suf_header_background_image) ";
				}
				else {
					$header_bg_url = " url(".suffusion_get_rotating_image($suf_header_background_rot_folder).") ";
				}
				$ret .= "
#header-container {	background-image: $header_bg_url; background-repeat: $suf_header_background_repeat; background-position: $suf_header_background_position; height: $suf_header_section_height; }";
			}
			else if ($suf_header_image_type == "gradient") {
				if (isset($suf_header_height)) {
					$header_bg_url = " url(".get_template_directory_uri()."/gradient.php?start=$suf_header_gradient_start_color&finish=$suf_header_gradient_end_color&direction=$suf_header_gradient_style&height=$suf_header_height)";
				}
				else {
					$header_bg_url = " url(".get_template_directory_uri()."/gradient.php?start=$suf_header_gradient_start_color&finish=$suf_header_gradient_end_color&direction=$suf_header_gradient_style&height=121)";
				}
				if ($suf_header_gradient_style == "top-down" || $suf_header_gradient_style == "down-top") {
					$header_bg_repeat = "repeat-x";
				}
				else if ($suf_header_gradient_style == "left-right" || $suf_header_gradient_style == "right-left") {
					$header_bg_repeat = "repeat-y";
				}
				if ($suf_header_gradient_style == "top-down" || $suf_header_gradient_style == "left-right") {
					$header_bg_color = $suf_header_gradient_end_color;
				}
				else if ($suf_header_gradient_style == "down-top" || $suf_header_gradient_style == "right-left") {
					$header_bg_color = $suf_header_gradient_start_color;
				}
				$ret .= "
#header-container { background-image: $header_bg_url; background-repeat: $header_bg_repeat; background-color: #".$this->strip_color_hash($header_bg_color)."; }";
			}
			$ret .= "
/*div.blogtitle a { color: #".$this->strip_color_hash($suf_blog_title_color)."; text-decoration: $suf_blog_title_style; }*/
.blogtitle a { color: #".$this->strip_color_hash($suf_blog_title_color)."; text-decoration: $suf_blog_title_style; }
.blogtitle a:hover { color: #".$this->strip_color_hash($suf_blog_title_hover_color)."; text-decoration: $suf_blog_title_hover_style; }
.description { color: #".$this->strip_color_hash($suf_blog_description_color)."; }";

			if ($suf_sub_header_vertical_alignment == "above" || $suf_sub_header_vertical_alignment == "below") {
				$ret .= "
.description { display: block; width: 100%; margin-top: 0; margin-left: 0; margin-right: 0; }
.blogtitle { width: 100%; }";
			}
			$ret .= "
.blogtitle { ";
			if ($suf_header_alignment == "right") {
				$ret .= "float: right; text-align: right; ";
			}
			else if ($suf_header_alignment == "left") {
				$ret .= "float: left; text-align: left; ";
			}
			else if ($suf_header_alignment == "center") {
				$ret .= "float: none; margin-left: auto; margin-right: auto; ";
			}
			else if ($suf_header_alignment == "hidden") {
				$ret .= "display: none; visibility: hidden; ";
			}
			$ret .= " }";

			if ($suf_header_alignment == "center") {
				$ret .= "
#header { text-align: center; }";
			}

			$ret .= "
.description { ";
			if ($suf_sub_header_alignment == "right") {
				$ret .= " float: right; text-align: right;";
			}
			else if ($suf_sub_header_alignment == "left") {
				$ret .= " float: left; text-align: left;";
			}
			else if ($suf_sub_header_alignment == "center") {
				$ret .= " float: none; margin-left: auto; margin-right: auto; margin-top: 0px;";
			}
			else if ($suf_sub_header_alignment == "hidden") {
				$ret .= " display: none; visibility: hidden;";
			}
			$ret .= " }";//.description
			if (isset($suf_header_height)) {
				$header_height = suffusion_admin_get_size_from_field($suf_header_height, "55px");
				$ret .= "
#header { height: $header_height; }";
			}
		}

		// If there are header widgets then the width of the header needs to be balanced
		if (!suffusion_is_sidebar_empty(12)) {
			$wih_width = suffusion_admin_get_size_from_field($suf_wih_width, "300px");
			if ($suf_header_alignment != 'right') {
				$ret .= "
#header { float: left; width: auto; }
.blogtitle, .description { float: none; }
#header-widgets { float: right; width: $wih_width; }";
			}
			else {
				$ret .= "
#header { float: right; width: auto; }
.blogtitle, .description { float: none; }
#nav { float: left; }
#header-widgets { float: left; width: $wih_width; }";
			}
		}
		return $ret;
	}

	function get_custom_wrapper_settings() {
		global $suf_wrapper_settings_def_cust, $suf_show_shadows, $suf_wrapper_margin, $suf_header_style_setting;
		$ret = "";
		if ($suf_wrapper_settings_def_cust == 'custom') {
			$ret .= "
#wrapper {
	".$this->get_bg_information('suf_wrapper_bg_settings');
			if ($suf_show_shadows == 'show') {
				$ret .= "
	/* Shadows - CSS3 for browsers that support it */
	box-shadow: 10px 10px 5px #888;
	-moz-box-shadow: 10px 10px 5px #888;
	-khtml-box-shadow: 10px 10px 5px #888;
	-webkit-box-shadow: 10px 10px 5px #888;";
			}
			$ret .="
}";
		}
		if ($suf_header_style_setting == "custom" && isset($suf_wrapper_margin)) {
			$wrapper_margin = "50px";
			$wrapper_margin = suffusion_admin_get_size_from_field($suf_wrapper_margin, "50px");
			$ret .= "
#wrapper { margin: $wrapper_margin auto; }";
		}
		return $ret;
	}

	function get_custom_post_bg_settings() {
		global $suf_post_bg_settings_def_cust;
		$ret = "";
		if ($suf_post_bg_settings_def_cust == 'custom') {
			$ret .= "
.post, div.page {
	".$this->get_bg_information('suf_post_bg_settings')."
}
";
		}
		return $ret;
	}

	function get_custom_body_font_settings() {
		global $suf_body_font_style_setting, $suf_font_color, $suf_body_font_family, $suf_link_color, $suf_link_style;
		global $suf_visited_link_color, $suf_visited_link_style, $suf_link_hover_color, $suf_link_hover_style;
		$ret = "";
		if ($suf_body_font_style_setting == 'custom') {
			$font_string = stripslashes($suf_body_font_family);
			$font_string = wp_specialchars_decode($font_string, ENT_QUOTES);
			$ret .= "
body { color: #".$this->strip_color_hash($suf_font_color)."; font-family: ".$font_string."; }
a { color: #".$this->strip_color_hash($suf_link_color)."; text-decoration: $suf_link_style; }
a:visited { color: #".$this->strip_color_hash($suf_visited_link_color)."; text-decoration: $suf_visited_link_style; }
a:hover { color: #".$this->strip_color_hash($suf_link_hover_color)."; text-decoration: $suf_link_hover_style; }
";
		}
		return $ret;
	}

	function get_custom_date_box_css() {
		global $suf_date_box_show, $suf_date_box_show, $suf_post_meta_position, $suf_date_box_settings_def_cust;
		$ret = "";
		if ($suf_date_box_show == 'hide' || $suf_date_box_show == 'hide-search' || $suf_post_meta_position != 'corners') {
			if ($suf_date_box_show == 'hide-search' && $suf_post_meta_position == 'corners') {
				$template_class = '.search-results';
			}
			else {
				$template_class = '';
			}
			$ret .= "
$template_class .post .date { display: none; }
$template_class .title-container { padding-left: 0; padding-right: 0; }
$template_class .post .title { padding-left: 0; }
";
		}
		else if ($suf_date_box_settings_def_cust == 'custom') {
			$ret .= "
.post .date {
	".$this->get_bg_information('suf_date_box_settings')."
}
.post .date span.day {
	".$this->get_font_information('suf_date_box_dfont')."
}
.post .date span.month {
	".$this->get_font_information('suf_date_box_mfont')."
}
.post .date span.year {
	".$this->get_font_information('suf_date_box_yfont')."
}
";
		}
		return $ret;
	}

	function get_custom_emphasis_css() {
		global $suf_emphasis_customization, $suf_download_font_color, $suf_download_background_color, $suf_download_border_color, $suf_announcement_font_color, $suf_announcement_background_color;
		global $suf_announcement_border_color, $suf_note_font_color, $suf_note_background_color, $suf_note_border_color, $suf_warning_font_color, $suf_warning_background_color, $suf_warning_border_color;
		$ret = "";
		if ($suf_emphasis_customization == 'custom') {
			$ret .= "
.download { color: #".$this->strip_color_hash($suf_download_font_color)."; background-color: #".$this->strip_color_hash($suf_download_background_color)."; border-color: #".$this->strip_color_hash($suf_download_border_color)."; }
.announcement { color: #".$this->strip_color_hash($suf_announcement_font_color)."; background-color: #".$this->strip_color_hash($suf_announcement_background_color)."; border-color: #".$this->strip_color_hash($suf_announcement_border_color)."; }
.note { color: #".$this->strip_color_hash($suf_note_font_color)."; background-color: #".$this->strip_color_hash($suf_note_background_color)."; border-color: #".$this->strip_color_hash($suf_note_border_color)."; }
.warning { color: #".$this->strip_color_hash($suf_warning_font_color)."; background-color: #".$this->strip_color_hash($suf_warning_background_color)."; border-color: #".$this->strip_color_hash($suf_warning_border_color)." }";
		}
		return $ret;
	}

	function get_custom_byline_css() {
		global $suf_post_show_cats, $suf_post_show_comment, $suf_page_show_comment, $suf_post_show_tags, $suf_post_show_posted_by, $suf_page_show_posted_by;
		$ret = "";
		$cat_align = ($suf_post_show_cats == 'show-tright' || $suf_post_show_cats == 'show-bright') ? "right" : "left";
		$post_comment_align = ($suf_post_show_comment == 'show-tleft' || $suf_post_show_comment == 'show-bleft') ? "left" : "right";
		$page_comment_align = ($suf_page_show_comment == 'show-tleft' || $suf_page_show_comment == 'show-bleft') ? "left" : "right";
		$tag_align = ($suf_post_show_tags == 'show-tleft' || $suf_post_show_tags == 'show-bleft') ? "left" : "right";
		$post_author_align = ($suf_post_show_posted_by == 'show-tright' || $suf_post_show_posted_by == 'show-bright') ? "right" : "left";
		$page_author_align = ($suf_page_show_posted_by == 'show-tright' || $suf_page_show_posted_by == 'show-bright') ? "right" : "left";
		$ret .= "
.post-footer .category, .postdata .category{ float: $cat_align; }
.post .post-footer .comments, .post .postdata .comments { float: $post_comment_align; }
div.page .post-footer .comments, div.page .postdata .comments { float: $page_comment_align; }
.tags { float: $tag_align; text-align: $tag_align; }
.post span.author { float: $post_author_align; ".($post_author_align == "left" ? "padding-right: 10px; " : "padding-left: 10px; ")." }
div.page span.author { float: $page_author_align; ".($page_author_align == "left" ? "padding-right: 10px; " : "padding-left: 10px; ")." }
";
		return $ret;
	}

	function get_custom_wabh_css() {
		global $suf_widget_area_below_header_enabled, $suf_widget_area_below_header_columns, $suf_wa_wabh_style, $suf_header_for_widgets_below_header, $suf_wabh_font_style_setting;
		global $suf_wabh_font_color, $suf_wabh_link_color, $suf_wabh_link_style, $suf_wabh_visited_link_color, $suf_wabh_visited_link_style, $suf_wabh_link_hover_color, $suf_wabh_link_hover_style;
		$ret = "";
		if ($suf_widget_area_below_header_enabled == "enabled") {
			$bw1_columns = intval($suf_widget_area_below_header_columns);
			$bw1_width = $this->get_column_width($bw1_columns);
			$bw1_margin = $this->get_margin($bw1_columns);
			$bw1_ie_margin = $this->get_ie6_margin($bw1_columns);
			if (!($suf_wa_wabh_style == 'flattened' && $bw1_columns == 1)) {
				$ret .= "
#horizontal-outer-widgets-1 .suf-horizontal-widget { width: $bw1_width; display: inline-block; margin: $bw1_margin; }
* html #horizontal-outer-widgets-1 .suf-horizontal-widget { ma\\rgin: $bw1_ie_margin; }";
		    }
			else {
				$ret .= "
#horizontal-outer-widgets-1 .suf-horizontal-widget { display: block; float: none; }";
			}
			if ($suf_header_for_widgets_below_header == "plain-borderless") {
				$ret .= "
#horizontal-outer-widgets-1 .dbx-handle { border-bottom: none; }";
			}
			if ($suf_wabh_font_style_setting == "custom") {
				$ret .= "
#horizontal-outer-widgets-1 { color: #".$this->strip_color_hash($suf_wabh_font_color)."; }
#horizontal-outer-widgets-1 a { color: #".$this->strip_color_hash($suf_wabh_link_color)."; text-decoration: $suf_wabh_link_style; }
#horizontal-outer-widgets-1 a:visited { color: #".$this->strip_color_hash($suf_wabh_visited_link_color)."; text-decoration: $suf_wabh_visited_link_style; }
#horizontal-outer-widgets-1 a:hover { color: #".$this->strip_color_hash($suf_wabh_link_hover_color)."; text-decoration: $suf_wabh_link_hover_style; }";
			}
		}
		return $ret;
	}

	function get_custom_waaf_css() {
		global $suf_widget_area_above_footer_enabled, $suf_widget_area_above_footer_columns, $suf_wa_waaf_style, $suf_header_for_widgets_above_footer, $suf_waaf_font_style_setting;
		global $suf_waaf_font_color, $suf_waaf_link_color, $suf_waaf_link_style, $suf_waaf_visited_link_color, $suf_waaf_visited_link_style, $suf_waaf_link_hover_color, $suf_waaf_link_hover_style;
		$ret = "";
		if ($suf_widget_area_above_footer_enabled == "enabled") {
			$bw2_columns = intval($suf_widget_area_above_footer_columns);
			$bw2_width = $this->get_column_width($bw2_columns);
			$bw2_margin = $this->get_margin($bw2_columns);
			$bw2_ie_margin = $this->get_ie6_margin($bw2_columns);

			if (!($suf_wa_waaf_style == 'flattened' && $bw2_columns == 1)) {
				$ret .= "
#horizontal-outer-widgets-2 .suf-horizontal-widget { width: $bw2_width; display: inline-block; margin: $bw2_margin; }
* html #horizontal-outer-widgets-2 .suf-horizontal-widget { ma\\rgin: $bw2_ie_margin; }";
			}
			else {
				$ret .= "
#horizontal-outer-widgets-2 .suf-horizontal-widget { display: block; float: none; }";
		    }
			if ($suf_header_for_widgets_above_footer == "plain-borderless") {
				$ret .= "
#horizontal-outer-widgets-2 .dbx-handle { border-bottom: none; }";
			}
			if ($suf_waaf_font_style_setting == "custom") {
				$ret .= "
#horizontal-outer-widgets-2 { color: #".$this->strip_color_hash($suf_waaf_font_color)."; }
#horizontal-outer-widgets-2 a { color: #".$this->strip_color_hash($suf_waaf_link_color)."; text-decoration: $suf_waaf_link_style; }
#horizontal-outer-widgets-2 a:visited {	color: #".$this->strip_color_hash($suf_waaf_visited_link_color)."; text-decoration: $suf_waaf_visited_link_style; }
#horizontal-outer-widgets-2 a:hover { color: #".$this->strip_color_hash($suf_waaf_link_hover_color)."; text-decoration: $suf_waaf_link_hover_style; }";
			}
		}
		return $ret;
	}

	function get_custom_featured_css() {
		global $suf_featured_height, $suf_featured_excerpt_width, $suf_featured_excerpt_bg_color, $suf_featured_excerpt_font_color, $suf_featured_excerpt_link_color, $suf_featured_show_border;
		$featured_height = suffusion_admin_get_size_from_field($suf_featured_height, "250px");
		$featured_excerpt_width = suffusion_admin_get_size_from_field($suf_featured_excerpt_width, "250px");
		$ret = "
#slider, #sliderContent { height: $featured_height; }
#featured-posts .left, #featured-posts .right { height: $featured_height; }
.sliderImage { height: $featured_height; }
#featured-posts .left { width: $featured_excerpt_width !important; }
#featured-posts .right { width: $featured_excerpt_width !important; }
.sliderImage div { background-color: #".$this->strip_color_hash($suf_featured_excerpt_bg_color)."; color: #".$this->strip_color_hash($suf_featured_excerpt_font_color)."; }
.sliderImage div a { color: #".$this->strip_color_hash($suf_featured_excerpt_link_color)."; }";
		if ($suf_featured_show_border == "show") {
			$ret .= "
#featured-posts { border-width: 1px; border-style: solid; }";
		}
		return $ret;
	}

	function get_custom_adhoc_css() {
		$ret = "";
		for ($i=1; $i<=5; $i++) {
			$adhoc_column_option = 'suf_adhoc'.$i.'_columns';
			global $$adhoc_column_option;
			$adhoc_columns = intval($$adhoc_column_option);
			$adhoc_width = $this->get_column_width($adhoc_columns);
			$adhoc_margin = $this->get_margin($adhoc_columns);
			$adhoc_ie_margin = $this->get_ie6_margin($adhoc_columns);
			$ret .= "
#ad-hoc-$i .suf-widget { width: $adhoc_width; display: inline-block; margin: $adhoc_margin; }
* html #ad-hoc-$i .suf-widget { ma\\rgin: $adhoc_ie_margin; }";
		}
		return $ret;
	}

	function get_custom_tiled_layout_css($widths) {
		global $wp_query;
		global $suf_tile_excerpts_per_row, $suf_tile_image_box_height, $suf_tile_title_alignment;
		$number_of_cols = count($wp_query->posts) - suffusion_get_full_content_count();
		if ($number_of_cols > (int)$suf_tile_excerpts_per_row || $number_of_cols <= 0) {
			$number_of_cols = (int)$suf_tile_excerpts_per_row;
		}
		$tile_td_width = floor(100/$number_of_cols);

		if (suffusion_admin_check_integer($widths['main-col'])) {
			$tile_td_img_width = floor($widths['main-col']/(int)$number_of_cols) - 20;
			$tile_td_img_width_ie = floor($widths['main-col']/(int)$number_of_cols) - 20 - (int)$number_of_cols;
		}
		else {
			$tile_td_img_width = '95%';
			$tile_td_img_width_ie = '95%';
		}
		$ret = "
* html table.suf-tiles { w\\idth: ".(suffusion_admin_check_integer($widths['main-col']) ? ($widths['main-col'] - 25).'px' : '96%')." }
col.suf-tile { width: $tile_td_width%; }
.suf-tile-image {
	width: ".(suffusion_admin_check_integer($tile_td_img_width) ? $tile_td_img_width.'px' : $tile_td_img_width).";
	height: ".(suffusion_admin_get_size_from_field($suf_tile_image_box_height, "100px")).";
}
* html .suf-tile-image { w\\idth: ".(suffusion_admin_check_integer($tile_td_img_width_ie) ? $tile_td_img_width_ie.'px' : $tile_td_img_width_ie)."; }
h2.suf-tile-title { text-align: $suf_tile_title_alignment; }";
		return $ret;
	}

	function get_custom_tbrh_css() {
		global $suf_wa_tbrh_style, $suf_wa_tbrh_columns, $suf_wa_tbrh_panel_color, $suf_wa_tbrh_panel_border_color, $suf_wa_tbrh_panel_font_color;
		$ret = "";
		if ($suf_wa_tbrh_style != 'tiny') {
			$tbrh_columns = intval($suf_wa_tbrh_columns);
			$tbrh_width = $this->get_column_width($tbrh_columns);
			$tbrh_margin = $this->get_margin($tbrh_columns);
			$tbrh_ie_margin = $this->get_ie6_margin($tbrh_columns);
			$ret .= "
#top-bar-right-spanel .suf-panel-widget, #top-bar-right-spanel .suf-flat-widget, #top-bar-right-spanel .suf-widget { width: $tbrh_width; display: inline-block; margin: $tbrh_margin; }
* html #top-bar-right-spanel .suf-panel-widget, #top-bar-right-spanel .suf-flat-widget, #top-bar-right-spanel .suf-widget { ma\\rgin: $tbrh_ie_margin; }
#top-bar-right-spanel { background-color: #".$this->strip_color_hash($suf_wa_tbrh_panel_color)."; border-color: #".$this->strip_color_hash($suf_wa_tbrh_panel_border_color)."; color: #".$this->strip_color_hash($suf_wa_tbrh_panel_font_color)."; }";
		}
		return $ret;
	}

	function get_custom_sidebar_settings_css() {
		global $suf_sb_font_style_setting, $suf_sb_font_color, $suf_sb_link_color, $suf_sb_link_style, $suf_sb_visited_link_color, $suf_sb_visited_link_style;
		global $suf_sb_link_hover_color, $suf_sb_link_hover_style, $suf_sidebar_header;
		$ret = "";
		if ($suf_sidebar_header == "plain-borderless") {
			$ret .= "
#sidebar .dbx-handle, #sidebar-2 .dbx-handle { border-bottom: none; }";
		}

		if ($suf_sb_font_style_setting == "custom") {
			$ret .= "
#sidebar, #sidebar-2, #sidebar-container { color: #".$this->strip_color_hash($suf_sb_font_color)."; }
#sidebar a, #sidebar-2 a, #sidebar-container a { color: #".$this->strip_color_hash($suf_sb_link_color)."; text-decoration: $suf_sb_link_style; }
#sidebar a:visited, #sidebar-2 a:visited, #sidebar-container a:visited { color: #".$this->strip_color_hash($suf_sb_visited_link_color)."; text-decoration: $suf_sb_visited_link_style; }
#sidebar a:hover, #sidebar-2 a:hover, #sidebar-container a:hover { color:  #".$this->strip_color_hash($suf_sb_link_hover_color)."; text-decoration: $suf_sb_link_hover_style; }";
		}
		$ret .= "
.sidebar-container-left #sidebar-shell-1 { float: left; margin-left: 0; margin-right: 15px; right: auto; }
.sidebar-container-left #sidebar-shell-2 { float: left; margin-left: 0; margin-right: 15px; right: auto;}
.sidebar-container-right #sidebar-shell-1 { float: right; margin-right: 0; margin-left: 15px; right: auto; left: auto; }
.sidebar-container-right #sidebar-shell-2 { float: right; margin-right: 0; margin-left: 15px; right: auto; left: auto;}
.sidebar-wrap-right #sidebar-shell-1 { float: right; margin-left: 0; margin-right: 0;}
.sidebar-wrap-right #sidebar-shell-2 { float: right; margin-right: 15px; margin-left: 0;}
.sidebar-wrap-left #sidebar-shell-1 { float: left; margin-left: 0; margin-right: 0;}
.sidebar-wrap-left #sidebar-shell-2 { float: left; margin-left: 15px; margin-right: 0;}
.sidebar-container-left #sidebar-wrap { margin-left: 0; margin-right: 0; left: auto; right: auto; }
.sidebar-container-right #sidebar-wrap { margin-left: 0; margin-right: 0; left: auto; right: auto; }
#sidebar-container .tab-box { margin-left: 0; margin-right: 0; }
#sidebar-container.sidebar-container-left { margin-left: -100%; }
.sidebar-container-left .tab-box { float: left; }
.sidebar-container-right .tab-box { float: right; }
* html #sidebar-container #sidebar-shell-1, * html #sidebar-container #sidebar-shell-2 { lef\\t: auto; r\\ight: auto; }
* html .sidebar-container-left #sidebar-wrap, * html .sidebar-container-right #sidebar-wrap { lef\\t: auto; r\\ight: auto; }";
		return $ret;
	}

	function get_custom_miscellaneous_css() {
		global $suf_audio_att_player_width, $suf_audio_att_player_height, $suf_application_att_player_width, $suf_text_att_player_width, $suf_video_att_player_width, $suf_video_att_player_height;
		global $suf_uprof_post_info_gravatar_alignment;
		$ret = "";
		//Attachments
		$ret .= "
.attachment object.audio { width: {$suf_audio_att_player_width}px; height: {$suf_audio_att_player_height}px; }
.attachment object.application { width: {$suf_application_att_player_width}px; }
.attachment object.text { width: {$suf_text_att_player_width}px; }
.attachment object.video { width: {$suf_video_att_player_width}px; height: {$suf_video_att_player_height}px; }";

		//Avatar
		$ret .= "
.author-info img.avatar { float: $suf_uprof_post_info_gravatar_alignment; padding: 5px; }";

		return $ret;
	}
}
?>