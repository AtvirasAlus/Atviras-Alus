<?php
/*
Plugin Name: WP Smart Image II
Plugin URI: http://www.lab.darioferrer.com
Description: Powerful, reliable and lightweight plugin which helps you to show post images and handle them as you wish. Essential tool specially built for web developers and designers.
Author: Darío Ferrer (@metacortex)
Version: 0.2
Author URI: http://www.darioferrer.com
*/

/*  Copyright 2009 - 2010 Darío Ferrer (wp@darioferrer.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!defined ('ABSPATH')) die();

if ( !defined('WP_PLUGIN_DIR') ) $wpsi_plugin_dir = str_replace( ABSPATH, '', dirname(__FILE__) );
else $wpsi_plugin_dir = dirname( plugin_basename(__FILE__) );
if ( function_exists('load_plugin_textdomain') ) {
	if ( !defined('WP_PLUGIN_DIR') ) 
		load_plugin_textdomain('wp-smart-image', str_replace( ABSPATH , '', $wpsi_plugin_dir .'/languages' ) );
	else 
		load_plugin_textdomain('wp-smart-image', false, $wpsi_plugin_dir .'/languages' );
}

$wpsi_vars = array (
	'plugin_url'    => get_bloginfo('url') . '/' . PLUGINDIR . '/' . $wpsi_plugin_dir .'/',
	'admin_url'     => admin_url('options-general.php?page=wp-smart-image-ii'),
	'ruta'          => str_replace( '\\' , '/' , WP_PLUGIN_DIR) .'/'. $wpsi_plugin_dir,
	'ruta_global'   => $_SERVER['SCRIPT_FILENAME'],
	'plugin_dir'    => $wpsi_plugin_dir,
	'config_file'   => ABSPATH . '/' . PLUGINDIR . '/' . $wpsi_plugin_dir .'/wpsi-config.php',
	'basename'      => basename($_SERVER['PHP_SELF'])
);

if ( isset($_POST['wpsi_agregar_datos']) ) wpsi_llenar_bd();
if ( isset($_POST['wpsi_remover_datos']) ) wpsi_vaciar_options();
if ( isset($_POST['wpsi_borrar_postmeta']) ) wpsi_vaciar_postmeta();
$wpsi_ruta = $_SERVER['DOCUMENT_ROOT'].$_SERVER['REQUEST_URI'];
$wpsi_ruta = str_replace('//' , '/' , $wpsi_ruta);
function wpsi_extension($args) {
	global $wpsi_plugin_dir, $wpsi_ruta;
	switch ($args) {
		case 'path':
			return $_SERVER['DOCUMENT_ROOT'] . PLUGINDIR . '/' . $wpsi_plugin_dir . '/';
		break;
		case 'url':
			return get_settings('siteurl') . '/' . PLUGINDIR . '/' . $wpsi_plugin_dir . '/';
		break;
		case 'dir':
			return $wpsi_plugin_dir;
		break;
		case 'file':
			return plugin_basename(__FILE__);
		break;
	}
}

$wpsi_php = array();
if ( file_exists($wpsi_vars['config_file']) and is_writable($wpsi_vars['config_file']) )
	require_once $wpsi_vars['config_file'];

$wpsi_configuracion = array();
$wpsi_config = get_option('wpsi_configuracion');
$wpsi_vars_1 = array('wpsi_activar_metabox' , 'wpsi_ruta_img' , 'wpsi_reemp_mini' , 'wpsi_reemp_medio' , 'wpsi_reemp_grande' , 'wpsi_reemp_full' , 'wpsi_texto_alt' , 'wpsi_texto_title' , 'wpsi_img_rss' , 'wpsi_dtd' , 'wpsi_opcion_reemplazo' , 'wpsi_texto_alt_titulo' , 'wpsi_texto_title_titulo' , 'wpsi_rss' , 'wpsi_rdf' , 'wpsi_rss2' , 'wpsi_atom');

foreach ($wpsi_vars_1 as $wpsiv1)
	$wpsi_configuracion[$wpsiv1] = $wpsi_modo_php == 1 ? $wpsi_php[$wpsiv1] : (isset($_POST[$wpsiv1]) ? $_POST[$wpsiv1] : $wpsi_config[$wpsiv1]);

$dtdtag = $wpsi_configuracion['wpsi_dtd'] == 'html' ? '>' : ' />';

add_action('admin_menu', 'wpsi_options_page');
add_action('admin_head', 'wpsi_cargar_archivos');
add_action('wp_head', 'wpsi_cargar_header');
wpsi_rss();

if($wpsi_configuracion['wpsi_activar_metabox'] == 'wpsi') {
	activar_metabox();
	add_action('edit_post', 'wpsi_guardar_metabox_titulo');
} elseif($wpsi_configuracion['wpsi_activar_metabox'] == 'tpt') {
	if(function_exists('current_theme_supports'))
		if(!current_theme_supports('post-thumbnails'))
			add_theme_support('post-thumbnails');
}

function wpsi_ruta($a) {
	$ruta = ABSPATH .'wp-admin/'. $a;
	$ruta = str_replace ('\\' , '/' , $ruta);
	return $ruta;
}

function wpsi_options_page() {
  add_options_page(__('WP Smart Image II', 'wp-smart-image'), __('WP Smart Image II', 'wp-smart-image'), 8, 'wp-smart-image-ii', 'wpsi_options');
}

function wpsi_texto_php($a) {
	global $wpsi_configuracion, $wpsi_php, $wpsi_modo_php;
	$txt = '';
	$trad = array(
		'thumbnail'  => __('Thumbnail size', 'wp-smart-image') ,
		'medium'     =>  __('Medium size', 'wp-smart-image') ,
		'large'      =>  __('Large size', 'wp-smart-image') ,
		'full'       =>  __('Full size', 'wp-smart-image') ,
		$wpsi_configuracion['wpsi_dtd'] => strtoupper($wpsi_configuracion['wpsi_dtd'])
	);
	if ( $wpsi_modo_php ) {
		if (!empty($wpsi_configuracion[$a])) {
			if($wpsi_configuracion[$a] == 1)
				$txt .= '<span class="azul negrita">'.__('Yes', 'wp-smart-image').'</span>';
			else
				$txt .= strtr($wpsi_configuracion[$a] , $trad);
		} else {
			$txt .= '<span class="rojo negrita">'.__('No', 'wp-smart-image').'</span>';
		}
	}
	return $txt;
}

function wpsi_create_config_file() {
	global $wpsi_vars;
	$archivo = $wpsi_vars['ruta'] .'/wpsi-config.php';
	if( !is_file($archivo) ) {
		fclose( fopen($archivo,'x') );
		return true;
	} else return false;
}

function wpsi_config_file($args = 'crear') {
	global $wpsi_vars , $wpsi_vars_1;
	$archivo = $wpsi_vars['ruta'] .'/wpsi-config.php';
	$texto = null;
	switch ($args) {
		case 'crear':
			$texto .= '<?php' . "\n";
			$texto .= '$wpsi_modo_php = 1;' . "\n";
			foreach ($wpsi_vars_1 as $wpsiv1) {
				$var = isset($_POST[$wpsiv1]) ? $_POST[$wpsiv1] : null;
				$texto .= '$wpsi_php["'. $wpsiv1 .'"] = "'. $var .'";' . "\n";
			}
			$texto .= '?>';
		break;
		case 'restaurar':
$texto .='<?php
$wpsi_modo_php = 1;
$wpsi_php["wpsi_activar_metabox"] = "0";
$wpsi_php["wpsi_ruta_img"] = "'. $wpsi_vars['plugin_url'] .'img/";
$wpsi_php["wpsi_reemp_mini"] = "noimg-mini.jpg";
$wpsi_php["wpsi_reemp_medio"] = "noimg-med.jpg";
$wpsi_php["wpsi_reemp_grande"] = "noimg-big.jpg";
$wpsi_php["wpsi_reemp_full"] = "noimg-full.jpg";
$wpsi_php["wpsi_texto_alt"] = "'. __('Article image', 'wp-smart-image') .'";
$wpsi_php["wpsi_texto_title"] = "'. __('Go to article', 'wp-smart-image') .'";
$wpsi_php["wpsi_img_rss"] = "mini";
$wpsi_php["wpsi_dtd"] = "xhtml";
$wpsi_php["wpsi_opcion_reemplazo"] = "on";
$wpsi_php["wpsi_texto_alt_titulo"] = "";
$wpsi_php["wpsi_texto_title_titulo"] = "";
$wpsi_php["wpsi_rss"] = "on";
$wpsi_php["wpsi_rdf"] = "";
$wpsi_php["wpsi_rss2"] = "on";
$wpsi_php["wpsi_atom"] = "on";
?>';
		break;
	}
	if ( is_writable($archivo) ) {
		if (!$handle = fopen($archivo, 'w')) exit;
		if (fwrite($handle, $texto) === FALSE) exit;
		fclose($handle);
	}
}

function wpsi_activate_config_file($args = 1) {
	global $wpsi_vars , $wpsi_vars_1;
	$texto = '$wpsi_modo_php = '. $args .';';
	$archivo = $wpsi_vars['ruta'] .'/wpsi-config.php';
	$conteo = 1;
	$leer = file($archivo, FILE_IGNORE_NEW_LINES);
	if( isset($leer[$conteo]) and $texto != trim($leer[$conteo]) ) {
		$leer[$conteo] = $texto;
		if (is_writable($archivo)) {
			$nueva_linea = implode( "\n" , $leer );
			$abrir = fopen( $archivo , 'w' );
			fwrite( $abrir , $nueva_linea , strlen($nueva_linea) );
			fclose($abrir);
		}
	}
}

function wpsi_translators() {
	global $wpsi_vars;
	$translators = array(
	'Rene' => array('rene' , __('Web developer', 'wp-smart-image') , 'http://wpwebshop.com' , 'WP Webshop' , __('Dutch', 'wp-smart-image') , 'nl_NL', __('Amsterdam - Holland', 'wp-smart-image')),
	'Vladimir Garagulya' => array('vladimir' , __('Web developer', 'wp-smart-image') , 'http://www.shinephp.com' , 'ShinePHP' , __('Russian', 'wp-smart-image') , 'ru_RU', __('Novosibirsk - Russia', 'wp-smart-image')),
	'Darío Ferrer' => array('dario' , __('Web designer', 'wp-smart-image') , 'http://www.darioferrer.com' , 'Darío Ferrer - Blog' , __('Spanish', 'wp-smart-image') , 'es_ES' , __('Caracas - Venezuela', 'wp-smart-image'))
);
$lista = '
<ul class="autores">';
foreach ($translators as $k => $t) {
$lista .='
<li class="redondo" style="background: #fff url('. $wpsi_vars['plugin_url'] .'img/translator-'. $t[0] .'.jpg) no-repeat;">
		<h4>'. $k .'</h4>
	<dl>
		<dt>'.__('Lives in:', 'wp-smart-image').'</dt>
		<dd>'. $t[6] .'</dd>
		<dt>'.__('Profession:', 'wp-smart-image').'</dt>
		<dd>'. $t[1] .'</dd>
		<dt>'.__('Main Website:', 'wp-smart-image').'</dt> 
		<dd><a target="_blank" title="" href="'. $t[2] .'">'. $t[3] .'</a></dd>
		<dt>'.__('Language:', 'wp-smart-image').'</dt>
		<dd>'. $t[4] .' ('. $t[5] .')</dd>
	</dl>
</li>';
}
$lista .= '
</ul>';
echo $lista;
}

function wpsi_options() {
	global $wpsi_ruta, $wpsi_modo_php, $wpsi_configuracion, $wpsi_plugin_dir, $wpsi_php, $wpsi_vars;
	if($wpsi_modo_php) $wpsi_bd = $wpsi_php;
	else $wpsi_bd = get_option('wpsi_configuracion'); 			
	$opcion_reemplazo = $wpsi_bd['wpsi_opcion_reemplazo'];
	$wpsi_dtd = $wpsi_configuracion['wpsi_dtd'];
	$activar_metabox = $wpsi_bd['wpsi_activar_metabox'];
	$img_rss = $wpsi_configuracion['wpsi_img_rss'];
	$custom_rss = $wpsi_configuracion['wpsi_img_rss_cmtxt'];
	$activar_alt_titulo = $wpsi_bd['wpsi_texto_alt_titulo'];
	$activar_title_titulo = $wpsi_bd['wpsi_texto_title_titulo'];
	$checked = ' checked="checked"';
	$disabled = ' disabled="disabled"';

	if($img_rss == $custom_rss) {
		if(empty($img_rss)) {
			$tamano_rss = 'thumbnail';
			$custom_rss_checked = '';
		} else {
			$tamano_rss = '';
			$custom_rss_checked = $checked;
		}
		$echo_rss = $img_rss;
	} else {
		$tamano_rss = $img_rss;
		$echo_rss = '';
	}
	if($activar_metabox) $metabox_checked = $checked;
	if($custom_compat) $compat_checked = $checked;
	if($opcion_reemplazo) {
		$reemplazo_checked = $checked;
		$reemplazo_clase = '';
	} else {
		$wpsi_opcion_reemplazo_disabled = $disabled;
		$reemplazo_clase = ' class="wpsi-js-gris"';
	}

	if($wpsi_bd['wpsi_rss']) $rss_checked = $checked;
	if($wpsi_bd['wpsi_rdf']) $rdf_checked = $checked;
	if($wpsi_bd['wpsi_rss2']) $rss2_checked = $checked;
	if($wpsi_bd['wpsi_atom']) $atom_checked = $checked;
	if($activar_alt_titulo) $alt_titulo_estatico_checked = $checked;
	if($activar_title_titulo) $title_titulo_estatico_checked = $checked;
	$phpmode_disabled = $wpsi_modo_php == 1 ? $disabled : null;
?>
<div class="wrap wpsi-wrap">
	<div id="wpsi-contenedor">
		<h2><?php _e('WP Smart Image II - Settings', 'wp-smart-image') ?></h2>
		<?php
		if ($_GET['accion']) $estilo_contenido = ' style="margin-top: 45px"';
		switch ($_GET['accion']) {
			case 'modo-normal':
				wpsi_activate_config_file(0);
			break;
			case 'modo-php':
				if ( file_exists($wpsi_vars['config_file'])) {
					wpsi_activate_config_file(1);
				} else {
					wpsi_create_config_file();
					wpsi_config_file();
				}
			break;
			case 'restaurar-php':
				wpsi_create_config_file();
				wpsi_config_file('restaurar');
			break;
		}

		if ( isset($_POST['wp_smart_image_enviar']) ) {
			if($wpsi_modo_php) {
				wpsi_create_config_file();
				wpsi_config_file();
			} else {
				update_option( 'wpsi_configuracion' , $wpsi_configuracion );
			}
		}
		?>
		<ul id="wpsi-caja" class="wpsi-pestanas">
			<li class="wpsi-selected"><a class="redondo" href="#" rel="tcontent1"><?php _e('General Settings', 'wp-smart-image') ?></a></li>
			<li><a class="redondo" href="#" rel="tcontent2"><?php _e('RSS', 'wp-smart-image') ?></a></li>
			<li><a class="redondo" href="#" rel="tcontent3"><?php _e('Default images', 'wp-smart-image') ?></a></li>
			<li><a class="redondo" href="#" rel="tcontent4"><?php _e('Data management', 'wp-smart-image') ?></a></li>
			<li><a class="redondo" href="#" rel="tcontent5"><?php _e('Help', 'wp-smart-image') ?></a></li>
		</ul>
		<form action="<?php echo attribute_escape( $_SERVER['REQUEST_URI'] ); ?>" method="post" id="wpsi-form" class="clase-wpsi-form form1">
			<?php wp_original_referer_field(true, 'previous') ."\n"; ?>
			<?php wp_nonce_field('wpsi-mainform'); ?>
			<div id="tcontent1" class="wpsi-contenido"<?php echo $estilo_contenido ?>>
				<fieldset class="redondo">
					<div class="h3-general">
						<p class="info-switch">
							<?php
							if(file_exists($wpsi_vars['config_file'])) {
							$txt_modophp = is_writable($wpsi_vars['config_file']) ? '&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<a href="'. $wpsi_vars['admin_url'] .'&amp;accion=modo-php">'. __('Switch to PHP Mode', 'wp-smart-image') .'</a>' : 
							'&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<span class="cursiva gris-claro">'. __('PHP Mode unavailable', 'wp-smart-image') .' <a href="#">'. __('(Know why)', 'wp-smart-image') .'</a></span>';
							} else {
							$txt_modophp = '&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<a href="'. $wpsi_vars['admin_url'] .'&amp;accion=modo-php">'. __('Switch to PHP Mode for first time', 'wp-smart-image') .'</a>';
							}
							if($wpsi_modo_php)
								$switch_txt = __('PHP Mode activated', 'wp-smart-image') .'&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; <a href="'. $wpsi_vars['admin_url'] .'&amp;accion=modo-normal">'. __('Switch to Normal Mode', 'wp-smart-image') .'</a>';
							else
								$switch_txt = __('Normal Mode activated', 'wp-smart-image') .' '. $txt_modophp;
							echo $switch_txt;
							?>
						</p>
						<h3 class="wpsi-opciones-config"><?php _e('General settings', 'wp-smart-image') ?></h3>
					</div>
					<?php
					$wpsi_cf_buttons = null;
					if ($wpsi_modo_php) {
					$wpsi_cf_buttons = '
					<span class="button" title="The configuration file is already created" style="color: #999; cursor: default; padding: 3px 10px;">'. __('Create a new file', 'wp-smart-image') .'</span>
					<a class="button" href="'. $wpsi_vars['admin_url'] .'&amp;accion=restaurar-php" onclick="return confirm(\'Sure you want to reset the configuration file to default values?\');">'. __('Restore default data', 'wp-smart-image') .'</a>
					<a class="button" target="_blank" href="'. admin_url('plugin-editor.php?file=wp-smart-image-ii/wpsi-config.php&amp;plugin=wp-smart-image-ii/wp-smart-image.php') .'">'. __('View/Edit directly', 'wp-smart-image') .'</a>';

					echo '
					<p class="descripcion">'. __('Configuration file', 'wp-smart-image') .'</p>
					'. $wpsi_cf_buttons .'
					<p class="chiquita">'. __('Location:', 'wp-smart-image') .' <code>'.$wpsi_vars['ruta'].'/wpsi-config.php</code></p>
					<p class="explicacion">'. __('Create a new file', 'wp-smart-image') .'</p>';
					}
					?>
					<p class="descripcion"><?php _e('Method for managing images from the editor', 'wp-smart-image') ?></p>
					<?php 
					$form_metabox ='
					<p>
						<select name="wpsi_activar_metabox">
							<option value="0">'.__('None', 'wp-smart-image').'</option>';
							if(!function_exists('current_theme_supports')) $tpt_disabled = $disabled;
							$form_metabox .='
							<option value="tpt"'. $tpt_disabled .'>The Post Thumbnail</option>
							<option value="wpsi">WP Smart Image II</option>
						</select>
					</p>';
					echo str_replace('<option value="'. $wpsi_configuracion['wpsi_activar_metabox'] .'">' , '<option value="'. $wpsi_configuracion['wpsi_activar_metabox'] .'" selected="selected">' , $form_metabox);
					if(!function_exists('current_theme_supports'))
					echo '<p class="explicacion ultimo"><span class="rojo">'. __('Warning:', 'wp-smart-image') .'</span> '. __('Your current Wordpress version isn\'t prepared for <strong>The Post Thumbnail</strong> feature. Upgrade your system (carefully recommended for many reasons) or use the available options.', 'wp-smart-image') .'</p>';
					 ?>
					<p class="explicacion ultimo">
						<?php echo __('Take a few minutes to proove each of these three options and choose your favorite one. All of them are equally stable and recommended.', 'wp-smart-image').' <span class="cursiva azul">'.__('None:', 'wp-smart-image').'</span> '.__('Use your Media Library tab to choose the posted image. This option is the one which don\'t generates new fields in your _postmeta table.', 'wp-smart-image').' <span class="cursiva azul">'.__('The Post Thumbnail method:', 'wp-smart-image').'</span> '.__('Manage your images through this comfortable native Wordpress interface (available from WP 2.9 and above).', 'wp-smart-image').' <span class="cursiva azul">'.__('WP Smart Image II method:', 'wp-smart-image').'</span> '.__('A comprehensive tool set which gives multiple options for image management.', 'wp-smart-image'); ?>
					</p>
					<p class="descripcion"><?php _e('Advanced settings', 'wp-smart-image') ?></p>
					<?php 
					$form_dtd ='
					<p>'. __('Document Type Declaration of this website (DTD):', 'wp-smart-image') .'
						<select name="wpsi_dtd">
							<option value="xhtml">'. __('XHTML', 'wp-smart-image') .'</option>
							<option value="html">'. __('HTML', 'wp-smart-image') .'</option>
						</select>
					</p>';
					echo str_replace('<option value="'. $wpsi_configuracion['wpsi_dtd'] .'">' , '<option value="'. $wpsi_configuracion['wpsi_dtd'] .'" selected="selected">' , $form_dtd);
					?>
					<p class="explicacion ultimo"><?php _e('You must to set the correct DTD of this site to point tags to right W3C validation. If you need more info about this topic, visit this', 'wp-smart-image') ?> <a href="http://<?php _e('en.wikipedia.org/wiki/Document_Type_Declaration', 'wp-smart-image') ?>" target="_blank"><?php _e('comprehensive Wikipedia article', 'wp-smart-image') ?></a> <?php _e('or', 'wp-smart-image') ?> <?php _e('<a href="http://wordpress.org/support/" target="_blank">ask in the Wordpress Forum.</a>', 'wp-smart-image') ?></p>
					<p class="enviar"><input type="submit" name="wp_smart_image_enviar" value="<?php _e('Update options &raquo;', 'wp-smart-image') ?>" class="button-primary" /></p>
				</fieldset>
			</div>
			<div id="tcontent2" class="wpsi-contenido"<?php echo $estilo_contenido ?>>
				<fieldset class="redondo">
					<div class="h3-general">
						<p class="info-switch">
							<?php
							if($wpsi_modo_php)
								$switch_txt = __('PHP Mode activated', 'wp-smart-image') .'&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; <a href="'. $wpsi_vars['admin_url'] .'&amp;accion=modo-normal">'. __('Switch to Normal Mode', 'wp-smart-image') .'</a>';
							else
								$switch_txt = __('Normal Mode activated', 'wp-smart-image') .' '. $txt_modophp;
							echo $switch_txt;
							?>
						</p>
						<h3 class="wpsi-opciones-config"><?php _e('Feed Settings', 'wp-smart-image') ?></h3>
					</div>
					<p class="descripcion"><?php _e('Enable images for the following RSS systems', 'wp-smart-image') ?>:</p> 
					<p>
						<?php
						$form_rss_type = '
						<input type="checkbox" id="wpsi-rss" name="wpsi_rss"'. $rss_disabled . $rss_checked .' />
						<label for="wpsi-rss">RSS 0.92</label> - 
						<input type="checkbox" id="wpsi-rdf" name="wpsi_rdf"'. $rss_disabled . $rdf_checked .' />
						<label for="wpsi-rdf">RDF/RSS 1.0</label> - 
						<input type="checkbox" id="wpsi-rss2" name="wpsi_rss2"'. $rss_disabled . $rss2_checked .' />
						<label for="wpsi-rss2">RSS2</label> - 
						<input type="checkbox" id="wpsi-atom" name="wpsi_atom"'. $rss_disabled . $atom_checked .' />
						<label for="wpsi-atom">Atom</label>';
						echo $form_rss_type;
						?>
					</p>
					<p class="explicacion ultimo"><?php _e('Here you can apply the WP Smart Image function for one or several RSS environments.', 'wp-smart-image') ?></p>
					<p class="descripcion"><?php _e('Select image size', 'wp-smart-image') ?></p>
					<p class="chiquita">
						<?php
						if (class_exists('max_image_size_control')) {
							$wpsi_misc = get_option('max_image_size_control_data');
							$wpsi_m = $wpsi_misc['max_image_size'];
							$cuenta = 0;
							if(!empty($wpsi_m)) {
								foreach($wpsi_m as $key => $misc) {
									if($misc['everypost'] == 1) {
										$cuenta ++;
										if($cuenta > 1) continue;
										$cuenta_custom = count($misc['custom']);
										for ( $i = 0; $i <= $cuenta_custom; $i++ ) {
											if($misc['custom'][$i]['custom_size_w'] or $misc['custom'][$i]['custom_size_h']) {
												if($misc['custom'][$i]['custom_size_w'] and !$misc['custom'][$i]['custom_size_h'])
													$size_info = __('Width:', 'wp-smart-image') .' '. $misc['custom'][$i]['custom_size_w'].'px';
												elseif(!$misc['custom'][$i]['custom_size_w'] and $misc['custom'][$i]['custom_size_h'])
													$size_info = __('Height:', 'wp-smart-image') .' '. $misc['custom'][$i]['custom_size_h'].'px';
												else
													$size_info = $misc['custom'][$i]['custom_size_w'] .'x'. $misc['custom'][$i]['custom_size_h'];
												$customexists = true;
												$extrasizes .= '
												<option value="custom'.$i.'">'.__('Setting #', 'wp-smart-image') . $key .' / Custom'.$i.' ('.$size_info.')</option>';
											}
										}
									}
								}
							}
						}
						$form_rss = '
						<select name="wpsi_img_rss" id="wpsi-img-rss">';
							if ($customexists) $form_rss .= '
							<optgroup label="'.__('Native WP sizes:', 'wp-smart-image').'">';
							$form_rss .= '
								<option value="mini">'.__('Thumbnail', 'wp-smart-image').'</option>
								<option value="med">'.__('Medium', 'wp-smart-image').'</option>
								<option value="big">'.__('Large', 'wp-smart-image').'</option>
								<option value="full">'.__('Full', 'wp-smart-image').'</option>';
							if ($customexists) {
							$form_rss .= '
							</optgroup>
							<optgroup label="'.__('Custom sizes:', 'wp-smart-image').'">';
							$form_rss .= $extrasizes;
							$form_rss .= '</optgroup>';
							}
							$form_rss .= '
						</select>';
						echo str_replace('<option value="'. $wpsi_configuracion['wpsi_img_rss'] .'">' , '<option value="'. $wpsi_configuracion['wpsi_img_rss'] .'" selected="selected">' , $form_rss);
						?>
					</p>
					<p class="explicacion ultimo"><?php _e('Choose the image size for the feeds. Max Image Size Control custom settings will be detected', 'wp-smart-image') ?></p>
					<p class="enviar"><input type="submit" name="wp_smart_image_enviar" value="<?php _e('Update options &raquo;', 'wp-smart-image') ?>" class="button-primary" /></p>
				</fieldset>
			</div>
			<div id="tcontent3" class="wpsi-contenido"<?php echo $estilo_contenido ?>>
				<fieldset class="redondo">
					<div class="h3-general">
						<p class="info-switch">
							<?php
							if($wpsi_modo_php)
								$switch_txt = __('PHP Mode activated', 'wp-smart-image') .'&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; <a href="'. $wpsi_vars['admin_url'] .'&amp;accion=modo-normal">'. __('Switch to Normal Mode', 'wp-smart-image') .'</a>';
							else
								$switch_txt = __('Normal Mode activated', 'wp-smart-image') .' '. $txt_modophp;
							echo $switch_txt;
							?>
						</p>
						<h3 class="wpsi-opciones-config"><?php _e('Feed Settings', 'wp-smart-image') ?></h3>
					</div>
					<p class="descripcion">
						<label for="wpsi-opcion-reemplazo"><?php _e('Enable Default Images', 'wp-smart-image') ?>:</label>
						<input type="checkbox" name="wpsi_opcion_reemplazo" id="wpsi-opcion-reemplazo" onclick="reemplazochecked(this.form)" <?php echo $reemplazo_checked; ?> />
					</p>
					<p class="explicacion"><?php _e('If checked, imageless posts will show, by default, the images you have been set below.', 'wp-smart-image') ?></p>
					<p class="descripcion"><label for="wpsi_ruta_img"><?php _e('Default image path', 'wp-smart-image') ?></label></p>
					<p class="formulario"><input type="text" name="wpsi_ruta_img" id="wpsi_ruta_img"<?php echo $reemplazo_clase; ?> value="<?php echo $wpsi_configuracion['wpsi_ruta_img'] ?>"<?php echo $wpsi_opcion_reemplazo_disabled ?> /></p>
					<p class="explicacion"><?php _e('Change this path if you like to custom image folder location.', 'wp-smart-image') ?></p>
					<p class="descripcion"><?php _e('Assigned images', 'wp-smart-image') ?></p>
					<table class="tablesizes">
						<tr>
							<td>
								<p class="titulo chiquita"><label for="wpsi_reemp_mini"><?php _e('Thumbnail', 'wp-smart-image') ?></label></p>
								<p class="formulario"><input type="text" name="wpsi_reemp_mini" id="wpsi_reemp_mini"<?php echo $reemplazo_clase; ?> value="<?php echo $wpsi_configuracion['wpsi_reemp_mini'] ?>"<?php echo $wpsi_opcion_reemplazo_disabled ?> /></p>
							</td>
							<td>
								<p class="titulo chiquita"><label for="wpsi_reemp_medio"><?php _e('Medium', 'wp-smart-image') ?></label></p>
								<p class="formulario"><input type="text" name="wpsi_reemp_medio" id="wpsi_reemp_medio"<?php echo $reemplazo_clase; ?> value="<?php echo $wpsi_configuracion['wpsi_reemp_medio'] ?>"<?php echo $wpsi_opcion_reemplazo_disabled ?> /></p>
							</td>
							<td>
								<p class="titulo chiquita"><label for="wpsi_reemp_grande"><?php _e('Large', 'wp-smart-image') ?></label></p>
								<p class="formulario"><input type="text" name="wpsi_reemp_grande" id="wpsi_reemp_grande"<?php echo $reemplazo_clase; ?> value="<?php echo $wpsi_configuracion['wpsi_reemp_grande'] ?>"<?php echo $wpsi_opcion_reemplazo_disabled ?> /></p>
							</td>
							<td class="ultima-celda">
								<p class="titulo chiquita"><label for="wpsi_reemp_full"><?php _e('Full', 'wp-smart-image') ?></label></p>
								<p class="formulario"><input type="text" name="wpsi_reemp_full" id="wpsi_reemp_full"<?php echo $reemplazo_clase; ?> value="<?php echo $wpsi_configuracion['wpsi_reemp_full'] ?>"<?php echo $wpsi_opcion_reemplazo_disabled ?> /></p>
							</td>
						</tr>
					</table>
					<p class="explicacion"><?php _e('Place the image filenames for each size.', 'wp-smart-image') ?></p>
					<h3 class="wpsi-opciones-texto" style="margin-top: 10px;"><?php _e('Default Alt &amp; Title settings', 'wp-smart-image') ?></h3>
					<p class="descripcion"><label for="wpsi_texto_alt"><?php _e('Default ALT string:', 'wp-smart-image') ?></label></p>
					<p class="formulario">
						<input type="text" class="seiscientos" name="wpsi_texto_alt" id="wpsi_texto_alt"<?php echo $reemplazo_clase; ?> value="<?php echo $wpsi_configuracion['wpsi_texto_alt'] ?>"<?php echo $wpsi_opcion_reemplazo_disabled ?> />
						<label for="wpsi-texto-alt-titulo" class="chiquita" style="margin-left: 4px;"><?php _e('Use post title as', 'wp-smart-image') ?> ALT</label> <input type="checkbox" style="margin-left: 4px;" name="wpsi_texto_alt_titulo" id="wpsi-texto-alt-titulo" <?php echo $alt_titulo_estatico_checked; ?> />
					</p>
					<p class="descripcion"><label for="wpsi_texto_title"><?php _e('Default TITLE string:', 'wp-smart-image') ?></label></p>
					<p class="formulario">
						<input type="text" name="wpsi_texto_title" class="seiscientos" id="wpsi_texto_title"<?php echo $reemplazo_clase; ?> value="<?php echo $wpsi_configuracion['wpsi_texto_title'] ?>"<?php echo $wpsi_opcion_reemplazo_disabled ?> />
						<label for="wpsi-texto-title-titulo" class="chiquita" style="margin-left: 4px;"><?php _e('Use post title as', 'wp-smart-image') ?> TITLE</label> <input type="checkbox" style="margin-left: 4px;" name="wpsi_texto_title_titulo" id="wpsi-texto-title-titulo" <?php echo $title_titulo_estatico_checked; ?> />
					</p>
					<p class="explicacion ultimo"><?php _e('Enter some text to define both <code>ALT</code> and <code>TITLE</code> attribute for default images. Checkboxes activation will overrides the text field data.', 'wp-smart-image') ?></p>
					<p class="enviar"><input type="submit" name="wp_smart_image_enviar" value="<?php _e('Update options &raquo;', 'wp-smart-image') ?>" class="button-primary" /></p>
				</fieldset>
			</div>
		</form>
		<div id="tcontent4" class="wpsi-contenido"<?php echo $estilo_contenido ?>>
			<form action="<?php echo $wpsi_vars['admin_url'] ?>" method="post" id="wpsi-remover-datos" class="clase-wpsi-form form3">
				<fieldset class="redondo">
					<div class="h3-general">
						<p class="info-switch">
							<?php
							if($wpsi_modo_php)
								$switch_txt = __('PHP Mode activated', 'wp-smart-image') .'&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; <a href="'. $wpsi_vars['admin_url'] .'&amp;accion=modo-normal">'. __('Switch to Normal Mode', 'wp-smart-image') .'</a>';
							else
								$switch_txt = __('Normal Mode activated', 'wp-smart-image') .' '. $txt_modophp;
							echo $switch_txt;
							?>
						</p>
						<h3 class="wpsi-opciones-config"><?php _e('Feed Settings', 'wp-smart-image') ?></h3>
					</div>
					<p><span class="aviso"><?php _e('Warning!', 'wp-smart-image') ?></span> <?php _e('If you click the wrong button, you will loose all you have been set manually', 'wp-smart-image') ?></p>
					<p class="submit">
					<input type="submit" title="<?php _e('Remove plugin database info', 'wp-smart-image') ?>" name="wpsi_remover_datos" onclick="return confirm('<?php _e('Sure you want remove plugin database entry?', 'wp-smart-image') ?>');" value="<?php _e('Remove data', 'wp-smart-image') ?>"<?php echo $phpmode_disabled ?> />
					<input type="submit" title="<?php _e('Populate/Restore plugin database info', 'wp-smart-image') ?>" name="wpsi_agregar_datos" onclick="return confirm('<?php _e('Sure you want populate plugin database entry with default data?', 'wp-smart-image') ?>');" value="<?php _e('Populate/Restore data', 'wp-smart-image') ?>"<?php echo $phpmode_disabled ?> />
					<input type="submit" title="<?php _e('Delete post_meta info', 'wp-smart-image') ?>" name="wpsi_borrar_postmeta" onclick="return confirm('<?php _e('Sure you want delete post_meta info? This will delete all configurations you have been set through editor! Better think twice buddy!', 'wp-smart-image') ?>');" value="<?php _e('Delete post_meta info', 'wp-smart-image') ?>"<?php echo $phpmode_disabled ?> />
					</p>
					<p class="explicacion" style="margin-top: 10px;"><span class="negrita rojo"><?php _e('Remove data', 'wp-smart-image') ?>:</span> <?php _e('Use it to remove the <code>wpsi_configuracion</code> field from the <code>_options</code> table of your DB. All default settings will be deleted.', 'wp-smart-image') ?></p>
					<p class="explicacion"><span class="negrita"><?php _e('Populate/Restore plugin database info', 'wp-smart-image') ?>:</span> <?php _e('This button loads some default data to your DB. Use it as start guide to place your own data. Take in mind that this action will overwrite any previous configuration.', 'wp-smart-image') ?></p>
					<p class="explicacion"><span class="negrita rojo"><?php _e('Delete post_meta info', 'wp-smart-image') ?>:</span> <?php _e('Be careful with this button, because if you press it, all your WP Smart Image <code>postmeta</code> fields will be deleted. Postmeta fields are all the custom setting generated from your editor box, as post image shown.', 'wp-smart-image') ?></p>
				</fieldset>
			</form>
		</div>
		<div id="tcontent5" class="wpsi-contenido"<?php echo $estilo_contenido ?>>
			<div class="ayuda redondo">

					<div class="h3-general">
						<p class="info-switch">
							<?php
							if($wpsi_modo_php)
								$switch_txt = __('PHP Mode activated', 'wp-smart-image') .'&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; <a href="'. $wpsi_vars['admin_url'] .'&amp;accion=modo-normal">'. __('Switch to Normal Mode', 'wp-smart-image') .'</a>';
							else
								$switch_txt = __('Normal Mode activated', 'wp-smart-image') .' '. $txt_modophp;
							echo $switch_txt;
							?>
						</p>
						<h3><?php _e('Get support!', 'wp-smart-image') ?></h3>
					</div>
				<div id="wpsi-logo">
					<?php _e('WP Smart Image', 'wp-smart-image') ?> - <?php _e('Essential resource for web designers', 'wp-smart-image') ?>
				</div>
				<dl class="get-support">
					<dt><a class="negrita" href="http://www.lab.darioferrer.com/" target="_blank"><?php _e('WP Smart Image II - Main support site', 'wp-smart-image') ?></a></dt>
					<dd class="chiquita"><?php _e('A growing plugin codex.', 'wp-smart-image') ?></dd>
					<dt><a class="negrita" href="<?php _e('http://www.lab.darioferrer.com/doc/index.php?title=WPSI_II_-_Parameters_Table', 'wp-smart-image') ?>" target="_blank"><?php _e('Parameters table', 'wp-smart-image') ?></a></dt>
					<dd><?php _e('A complete index of parameters, well detailed and explained.', 'wp-smart-image') ?></dd>
					<dt><a class="negrita" href="<?php _e('http://www.lab.darioferrer.com/doc/index.php?title=WPSI_II_-_Examples,_tricks_and_hacks', 'wp-smart-image') ?>" target="_blank"><?php _e('Examples, tricks and hacks', 'wp-smart-image') ?></a></dt>
					<dd><?php _e('Discover more than one way to exploit all power from WP Smart Image II.', 'wp-smart-image') ?></dd>
					<dt><a class="negrita" href="<?php _e('http://www.lab.darioferrer.com/doc/index.php?title=WPSI_II_-_Working_on_PHP_Mode', 'wp-smart-image') ?>" target="_blank"><?php _e('Working on PHP Mode:', 'wp-smart-image') ?></a></dt>
					<dd><?php _e('All you need to know to activate the PHP Mode on WP Smart Image II.', 'wp-smart-image') ?></dd>
					<dt><a class="negrita" href="http://www.darioferrer.com/que/" target="_blank"><?php _e('Direct support', 'wp-smart-image') ?></a></dt>
					<dd><?php _e('Get help from the Forum Board.', 'wp-smart-image') ?></dd>
					<dt><a class="negrita" href="http://www.lab.darioferrer.com/trac" target="_blank">
					<?php _e('WP Smart Image II Trac', 'wp-smart-image') ?></a></dt>
					<dd><?php _e('Here you can report bugs and request new features.', 'wp-smart-image') ?></dd>
				</dl>
				<h3 style="margin-top: 30px;"><?php _e('WP Smart Image II was translated on your language by:', 'wp-smart-image') ?></h3>
				<?php wpsi_translators() ?>
				<div class="separador"></div>
			</div>
		</div>
	</div> 
</div> 
<script type="text/javascript">initializetabcontent("wpsi-caja")</script>
<?php }
function wpsi_cargar_archivos() {
	global $wpsi_ruta, $wpsi_plugin_dir, $wpsi_vars;
	$redirecciones = array( 'restaurar' => array('wpsi_agregar_datos' , 'fill-options') , 'borrar' => array('wpsi_remover_datos' , 'delete-option') , 'postmeta' => array('wpsi_borrar_postmeta' , 'delete-postmeta') , 'guardar' => array('wp_smart_image_enviar' , 'save') , 'php' => array('modophpp' , 'modo-php')  , 'normal' => array('modonor' , 'modo-normal') , 'restaurar-php' => array('resta' , 'restaurar-php') );
	$head_redir = null;
	foreach( $redirecciones as $k => $red ) {
		if ($_GET['accion'] == $red[1]) {
			wpsi_aviso($k);
		$head_redir .= header('refresh: 2; url='. $wpsi_vars['admin_url']);
		}
		if ( isset($_POST[$red[0]]) )
			$head_redir .= header('Location:'. $wpsi_vars['admin_url'] .'&accion='. $red[1]);
	}
	echo $head_redir;
	if($wpsi_vars['basename'] == 'post.php' or $wpsi_vars['basename'] == 'post-new.php' or $_GET['page'] == 'wp-smart-image-ii') {
		echo '
<link rel="stylesheet" type="text/css" href="' . get_settings('siteurl') . '/' . PLUGINDIR . '/' . $wpsi_plugin_dir . '/css/estilos.css" />
<!--[if lte IE 7]>
<link rel="stylesheet" type="text/css" href="' . get_settings('siteurl') . '/' . PLUGINDIR . '/' . $wpsi_plugin_dir . '/css/ie.css" />
<![endif]-->
<script type="text/javascript">
function wpsiCheck(obj,idcheckbox){
	if (obj.checked==false) {
        document.getElementById(idcheckbox).disabled=true;
		document.getElementById(idcheckbox).style.background=\'#f5f5f5\';
		document.getElementById(idcheckbox).style.cursor=\'default\';
		document.getElementById(idcheckbox).style.color=\'#999\';
		} else {
        document.getElementById(idcheckbox).disabled=false;
		document.getElementById(idcheckbox).style.background=\'#ffffff\';
		document.getElementById(idcheckbox).style.cursor=\'text\';
		document.getElementById(idcheckbox).style.color=\'#555555\';
	}
}
</script>';
}
if( $_GET['page'] == 'wp-smart-image-ii' ) {
		echo '
<script type="text/javascript" src="' . get_settings('siteurl') . '/' . PLUGINDIR . '/' . $wpsi_plugin_dir . '/js/tabcontent.js"></script>
<style type="text/css">
#wpsi-contenedor h3.wpsi-logo {';
if( WPLANG == es_ES)
echo 'background: url(' . get_settings('siteurl') . '/' . PLUGINDIR . '/' . $wpsi_plugin_dir . '/img/logo-es_ES.gif) no-repeat;
width: 354px;';
if( WPLANG == fr_FR)
echo 'background: url(' . get_settings('siteurl') . '/' . PLUGINDIR . '/' . $wpsi_plugin_dir . '/img/logo-fr_FR.gif) no-repeat;
width: 345px;';
else echo 'background: url(' . get_settings('siteurl') . '/' . PLUGINDIR . '/' . $wpsi_plugin_dir . '/img/logo-en_US.gif) no-repeat;
width: 321px;';
echo '}
</style>
<script type="text/javascript">
<!-- 
document.write(\'<style type="text/css">.wpsi-contenido{display:none;}<\/style>\');
 -->
</script>
<script type="text/javascript">';
$wpsi_estilos_js2 = array('wpsi_ruta_img' , 'wpsi_reemp_mini' , 'wpsi_reemp_medio' , 'wpsi_reemp_grande' , 'wpsi_reemp_full' , 'wpsi_texto_alt' , 'wpsi_texto_title');
echo'
function reemplazochecked(form) {
	if (form.wpsi_opcion_reemplazo.checked == true) {';
foreach ($wpsi_estilos_js2 as $wejs2) {
echo'
		form.'.$wejs2.'.disabled = false;
		form.'.$wejs2.'.style.background=\'#ffffff\';
		form.'.$wejs2.'.style.cursor=\'text\';
		form.'.$wejs2.'.style.color=\'#555555\';';
}
echo'
	} else {';
foreach ($wpsi_estilos_js2 as $wejs2) {
echo'
		form.'.$wejs2.'.disabled = true;
		form.'.$wejs2.'.style.background=\'#f5f5f5\';
		form.'.$wejs2.'.style.cursor=\'default\';
		form.'.$wejs2.'.style.color=\'#999999\';';
}
echo'
		}
	}
</script>
';
	}
}

function wpsi_aviso($a) {
	global $wpsi_vars;
	switch ($a) {
		case 'borrar':
			$txt = __('The field &#39;wpsi_configuracion&#39; has been removed from the database.', 'wp-smart-image');
		break;
		case 'restaurar':
			$txt = __('All default settings are loaded.', 'wp-smart-image');
		break;
		case 'restaurar-php':
			$txt = __('All default settings are loaded.', 'wp-smart-image');
		break;
		case 'postmeta':
			$txt = __('All WP Smart Image II &#39;_postmeta&#39; fields has been removed from the database.', 'wp-smart-image');
		break;
		case 'guardar':
			$txt = __('All settings were saved.', 'wp-smart-image');
		break;
		case 'php':
			$txt = __('Now WPSI is running under PHP Mode. Check your settings.', 'wp-smart-image');
		break;
		case 'normal':
			$txt = __('Normal Mode activated. Check your settings.', 'wp-smart-image');
		break;
	}
	echo '<div id="message" class="updated fade"><p>'.__($txt, 'wp-smart-image').'  <span class="chiquita cursiva" style="margin-left: 5px;">'. __('(Wait...)', 'wp-smart-image') .'</span></p></div>';
}

function wpsi_llenar_bd() {
	global $wpsi_vars;
	$wpsi_config_predet = array(
		'wpsi_ruta_img'		=> $wpsi_vars['plugin_url'] .'img/',
		'wpsi_reemp_mini'	=> 'noimg-mini.jpg',
		'wpsi_reemp_medio'	=> 'noimg-med.jpg',
		'wpsi_reemp_grande' => 'noimg-big.jpg',
		'wpsi_reemp_full'	=> 'noimg-full.jpg',
		'wpsi_texto_alt'	=> __('Article image', 'wp-smart-image'),
		'wpsi_texto_title'	=> __('Go to article', 'wp-smart-image'),
		'wpsi_opcion_reemplazo'	=> 1,
		'wpsi_activar_metabox'	=> 1,
		'wpsi_img_rss'	=> 'thumbnail',
		'wpsi_rss'	=> 0,
		'wpsi_rdf'	=> 0,
		'wpsi_rss2'	=> 1,
		'wpsi_atom'	=> 0,
		'wpsi_dtd'	=> 'xhtml'
	);
	update_option( 'wpsi_configuracion' , $wpsi_config_predet );
}	

function wpsi_vaciar_options() {
	delete_option( 'wpsi_configuracion' );
}

function wpsi_vaciar_postmeta() {
	global $wpdb;
	$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = '_wpsi_foto_lista'" );
}

function wpsi_img_rss() {
	global $post, $wpsi_configuracion, $wpsi_vars;
	$image = get_wpsi('array=1&element=mimetype&p='. $post->ID);
	foreach($image as $img)
	$rss .='<enclosure url="'.get_wpsi('size='. $wpsi_configuracion['wpsi_img_rss'] .'&type=url&p='. $post->ID).'" type="'. $img .'" />';
	echo $rss;
}

function wpsi_img_atom() {
	global $post, $wpsi_configuracion, $wpsi_vars;
	$image = get_wpsi('array=1&element=mimetype&p='. $post->ID);
	foreach($image as $img)
	$rss .='<link rel="enclosure" type="'. $img .'" href="'. get_wpsi('size='. $wpsi_configuracion['wpsi_img_rss'] .'&type=url&p='. $post->ID) .'" />';
	echo $rss;
}

function wpsi_img_rdf() {
	global $post, $wpsi_configuracion;
	$img = '
	<image rdf:about="'. get_wpsi('size='. $wpsi_configuracion['wpsi_img_rss'] .'&type=url&p='. $post->ID) .'">
	<title>News</title>
	<url>'. get_wpsi('size='. $wpsi_configuracion['wpsi_img_rss'] .'&type=url&p='. $post->ID) .'</url>

	<link>http://www.rssmaniac.com</link>
	</image>';
	echo $img;
	}

function wpsi_rss() {
	global $wpsi_modo_php, $wpsi_php;
	if($wpsi_modo_php) $wpsi_bd = $wpsi_php;
	else $wpsi_bd = get_option('wpsi_configuracion');
	$activar_rss = $wpsi_bd['wpsi_rss'];
	$activar_rdf = $wpsi_bd['wpsi_rdf'];
	$activar_rss2 = $wpsi_bd['wpsi_rss2'];
	$activar_atom = $wpsi_bd['wpsi_atom'];
	if($activar_rss) add_action('rss_item', 'wpsi_img_rss');
	if($activar_rdf) add_action('rdf_item', 'wpsi_img_rdf');
	if($activar_rss2) add_action('rss2_item', 'wpsi_img_rss');
	if($activar_atom) add_action('atom_entry', 'wpsi_img_atom');
}

function wpsi_cargar_header() {
echo 
'<script type="text/javascript"><!--//--><![CDATA[//><!--
function prepareTargetBlank() {
	var className = \'wpsi-blank\';
	var as = document.getElementsByTagName(\'a\');
	for(i=0;i<as.length;i++) {
		var a = as[i];
		r=new RegExp("(^| )"+className+"($| )");
		if(r.test(a.className)) {
			a.onclick = function() {
				window.open(this.href);
				return false;
			}
		}
	}
}
window.onload = prepareTargetBlank;
//--><!]]>
</script>';
}

function wpsi_metabox() {
	global $post;
	$imagenes = get_children( array( 
		'post_parent' => $post->ID, 
		'post_type' => 'attachment', 
		'post_mime_type' => 'image',
		'orderby' => 'menu_order', 
		'order' => 'ASC'
	));
	$wpsi_postmeta = get_post_meta( $post->ID, '_wpsi_foto_lista', true );
	if(is_array($wpsi_postmeta)) $wpsi_img_externa = isset($_POST['wpsi_img_externa']) ? $_POST['wpsi_img_externa'] : $wpsi_postmeta[0];
	if( $wpsi_img_externa )
		$externo_checked = ' checked="ckecked"';
	$wpsiext = get_post($wpsi_img_externa);
	if ( !wp_attachment_is_image( $wpsiext->ID ) )
		$wpsiext = false;
	if ( $item_ext == wp_get_attachment_image_src( $wpsi_img_externa, 'thumbnail' ) )
	$grande_ext = wp_get_attachment_image_src( $wpsi_img_externa, 'full' );
	$wpsi_parent_ext = get_post($wpsiext->post_parent);
	$resultado = wp_nonce_field( 'wpsi_metabox_args', '_wpsi_nonce', false, true );
	$resultado .= '
	<div id="wpsi-box-contenedor" class="wpsi-fl-contenedor">
		<table class="wpsi-fl-tabla">';
			if( !empty($imagenes) and !empty($post->ID) ) {
				$keys = array_keys($imagenes);
				$num = $keys[0];
				foreach ( $imagenes as $imagen ) {
					$ident = $imagen->ID;
					$wpsi_inputbox_ident_disabled = '';
					$wpsi_inputbox_ident = $_POST['wpsi_inputbox_'.$ident];
					if ( !$wpsi_inputbox_ident ) $wpsi_inputbox_ident_disabled = ' disabled ="disabled"';
					$wpsi_fotobox_titulo = $imagen->post_title;
					if ( $item == wp_get_attachment_image_src($imagen->ID, 'thumbnail') )
						$grande = wp_get_attachment_image_src($imagen->ID, 'full');
					$columna_id = ' id="wpsi-col-'.$ident.'"';
					$listabox = ' id="wpsi-col-'.$wpsi_postmeta.'"';
					if($listabox == $columna_id)
						$columna_id = str_replace( $columna_id , ' id="wpsi-gris"' , $columna_id );
					if ( $wpsi_postmeta ) {
						$foto_lista_checked = $ident == $wpsi_postmeta ? ' checked="ckecked"' : '';
						$aleatorio_checked = 'aleatorio' == $wpsi_postmeta ? ' checked="ckecked"' : '';
					} else { 
						$foto_lista_checked = $ident == $num ? 'checked="ckecked" ' : ''; 
					}	
					$resultado .= '
					<tr class="wpsibox-titulo">
						<th colspan="3">
							'.__('Title', 'wp-smart-image').': 
							<input type="text" id="wpsi-fotobox-title-'.$ident.'" name="wpsi_fotobox_title_'.$ident.'" value="'.$wpsi_fotobox_titulo.'"'. $wpsi_inputbox_ident_disabled .' class="wpsi-input-fotobox" autocomplete="off" />
							<input type="checkbox" name="wpsi_inputbox[]"  value="'.$ident.'" id="wpsi-inputbox-'.$ident.'" onclick="wpsiCheck(this,\'wpsi-fotobox-title-'.$ident.'\')" />
							<label for="wpsi-inputbox-'.$ident.'">'.__('Edit', 'wp-smart-image').'</label>
						</th>
					</tr>
					<tr'.$columna_id.' class="wpsi-col" onmouseover="this.style.cursor=\'pointer\';" onclick="getElementById(\'boton_'.$ident.'\').checked = true;">
						<td class="wpsi-fl-input">
							<input type="radio" name="wpsi_foto_lista" id="boton_'.$ident.'" value="'.$ident.'" '.$foto_lista_checked.'/>
						</td>
						<td class="wpsi-fl-img">
							<a href="'.$grande[0].'" target="_blank" title="'.__('View original in new window', 'wp-smart-image').'"><img src="'.$item[0].'" width="48" height="48" /></a>
						</td>
						<td class="wpsi-fl-datos">
							<p><span class="negrita">'.__('Image ID', 'wp-smart-image').':</span> '.$imagen->ID.'</p>
							<p><span class="negrita">'.__('Type', 'wp-smart-image').':</span> '.$imagen->post_mime_type.'</p>
							<p><span class="negrita">'.__('W:', 'wp-smart-image').'</span> '.$grande[1].'px | <span class="negrita">'.__('H:', 'wp-smart-image').'</span> '.$grande[2].'px</p>
						</td>
					</tr>
					';
				} // endforeach
				$resultado .= '
				<tr>
					<th colspan="3">'.__('Random images', 'wp-smart-image').'</th>
				</tr>
				<tr id="wpsi-col-aleatorio" class="wpsi-col" onmouseover="this.style.cursor=\'pointer\';" onclick="getElementById(\'boton_aleatorio\').checked = true;">
					<td class="wpsi-fl-input">
						<input type="radio" name="wpsi_foto_lista" id="boton_aleatorio" value="aleatorio" '.$aleatorio_checked.'/>
					</td>
					<td colspan="3">
						<p>'.__('If checked, the images will shown randomly. Very useful in some cases, as dynamic headers, backgrounds or widgets', 'wp-smart-image').'</p>
					</td>
				</tr>
				<tr>
					<th colspan="3">'.__('Load image from Media Library', 'wp-smart-image').'</th>
				</tr>';
					if($wpsi_img_externa) {
						if($wpsiext->ID != '') {
						$resultado .='
				<tr id="wpsi-col-aleatorio" class="wpsi-col" onmouseover="this.style.cursor=\'pointer\';" onclick="getElementById(\'boton_externo\').checked = true;">
					<td class="wpsi-fl-input">
						<input type="radio" name="wpsi_foto_lista" id="boton_externo" autocomplete="off" value="externo"'.$externo_checked.' />
					</td>
						<td class="wpsi-fl-img">
							<a href="'.$grande_ext[0].'" target="_blank" title="'.__('View original in new window', 'wp-smart-image').'"><img src="'.$item_ext[0].'" width="48" height="48" /></a>
						</td>
						<td class="wpsi-fl-datos">
							<p><span class="negrita">'.__('Image ID', 'wp-smart-image').':</span>
							<input type="text" name="wpsi_img_externa[]" id="wpsi_img_externa" autocomplete="off" value="'. $wpsi_img_externa .'" class="wpsi-input-externo" /></p><p><span class="negrita">'.__('Attached to post', 'wp-smart-image').':</span> ';	
							if($wpsi_parent_ext->ID == $wpsiext->post_parent)
								$resultado .= '<a href="post.php?action=edit&amp;post='.$wpsi_parent_ext->ID.'" title="'.$wpsi_parent_ext->post_title.'" target="_blank">'.$wpsi_parent_ext->ID.'</a></p>';
							else
								$resultado .= 'None</p>';
							$resultado .= '
							<p><span class="negrita">'.__('Type', 'wp-smart-image').':</span> '.$wpsiext->post_mime_type.'</p>
							<p><span class="negrita">'.__('W:', 'wp-smart-image').'</span> '.$grande_ext[1].'px | <span class="negrita">'.__('H:', 'wp-smart-image').'</span> '.$grande_ext[2].'px</p>
							<p><a href="media.php?action=edit&attachment_id='.$wpsiext->ID.'" target="_blank">Edit image</a></p>
						</td>
					</tr>';
						} else {
							$resultado .='
							<tr id="wpsi-fotolista-externo" class="wpsi-col" onmouseover="this.style.cursor=\'pointer\';" onclick="getElementById(\'boton_externo\').checked = true;">
								<td colspan="3">
									<p class="negrita"><span class="rojo">'.__('Error', 'wp-smart-image').':</span> '.__('Thid ID is not assigned to any image. Try again', 'wp-smart-image').'.</p>
									<p><span class="negrita">'.__('Image ID', 'wp-smart-image').':</span>
									<input type="radio" name="wpsi_foto_lista" id="boton_externo" autocomplete="off" value="externo"'.$externo_checked.' style="display: none" />
									<input type="text" name="wpsi_img_externa[]" id="wpsi_img_externa" autocomplete="off" value="'. $wpsi_img_externa .'" class="wpsi-input-externo" /></p>	
								</td>
							</tr>';
						}
					$resultado .= '
							<tr>
								<td colspan="3">
									<p>'.__('Always you can choose and publish any image stored on your ', 'wp-smart-image').' <a href="upload.php" target="_blank">'.__('Media Library', 'wp-smart-image').'</a>.</p>
								</td>
							</tr>';
				} else {
					$resultado .='
				<tr id="wpsi-fotolista-externo" class="wpsi-col">
					<td colspan="3">
						<p>'.__('Visit your', 'wp-smart-image').' <a href="upload.php" target="_blank">'.__('Media Library', 'wp-smart-image').'</a>, '.__('find the image ID and enter it in the field below:', 'wp-smart-image').'</p>
					</td>
				</tr>
				<tr onmouseover="this.style.cursor=\'pointer\';" onclick="getElementById(\'boton_externo\').checked = true;">
					<td class="wpsi-fl-input">
						<input type="radio" name="wpsi_foto_lista" id="boton_externo" value="externo"'.$externo_checked.' />
					</td>
					<td colspan="2">
						<input type="text" name="wpsi_img_externa[]" id="wpsi_img_externa" value="'. $wpsi_img_externa .'" autocomplete="off" />
					</td>
				</tr>';
				}
			} else {
				if( empty($post->ID) ) {
					$resultado .= '
					<tr id="wpsi-fotolista-no">
						<td>
							<p>'.__('Save this post to gain access to WP Smart Image functions', 'wp-smart-image').'</p>
						</td>
					</tr>';
				} else {
					$resultado .= '
					<tr>
						<th colspan="2">'.__('Upload an image for this entry', 'wp-smart-image').'</th>
					</tr>
					<tr class="wpsi-col" id="wpsi-fotolista-no">
						<td colspan="2">
							<p>'.__('You have not uploaded an image yet', 'wp-smart-image').' ¿<a href="media-upload.php?post_id='.$post->ID.'&amp;type=image&amp;TB_iframe=true" id="add_image" class="thickbox" title="Add an Image" onclick="return false;">'.__('Do you want to upload one now', 'wp-smart-image').'</a>? '.__('Thumbnail will show here next time you refresh this screen', 'wp-smart-image').'</p>
						</td>
					</tr>';
					if($wpsi_img_externa) {
						if($wpsiext->ID != '') {
							$resultado .='
							<tr>
								<th colspan="2">'.__('External image uploaded', 'wp-smart-image').'</th>
							</tr>
							<tr class="wpsi-col" id="wpsi-fotolista-externo">
								<td class="wpsi-fl-img-ext">
									<a href="'.$grande_ext[0].'" target="_blank" title="'.__('View original in new window', 'wp-smart-image').'"><img src="'.$item_ext[0].'" width="48" height="48" /></a>
								</td>
								<td class="wpsi-fl-datos">
									<p><span class="negrita">'.__('Image ID', 'wp-smart-image').':</span>
									<input type="radio" name="wpsi_foto_lista" id="boton_externo" autocomplete="off" value="externo"'.$externo_checked.' style="display: none" />
									<input type="text" name="wpsi_img_externa[]" id="wpsi_img_externa" autocomplete="off" value="'. $wpsi_img_externa .'" class="wpsi-input-externo" /></p>
									<p><span class="negrita">'.__('Attached to post', 'wp-smart-image').':</span> ';	
									if($wpsi_parent_ext->ID == $wpsiext->post_parent)
										$resultado .= '<a href="post.php?action=edit&amp;post='.$wpsi_parent_ext->ID.'" title="'.$wpsi_parent_ext->post_title.'" target="_blank">'.$wpsi_parent_ext->ID.'</a></p>';
									else
										$resultado .= 'None</p>';
									$resultado .= '
									<p><span class="negrita">'.__('Type', 'wp-smart-image').':</span> '.$wpsiext->post_mime_type.'</p>
									<p><span class="negrita">'.__('W:', 'wp-smart-image').'</span> '.$grande_ext[1].'px | <span class="negrita">'.__('H:', 'wp-smart-image').'</span> '.$grande_ext[2].'px</p>
									<p><a title="'.__('These modifications are globals', 'wp-smart-image').'" href="media.php?action=edit&attachment_id='.$wpsiext->ID.'" target="_blank">'.__('Edit image', 'wp-smart-image').'</a></p>
								</td>
							</tr>
							<tr>
								<td colspan="3">
									<p>'.__('Always you can choose and publish any image stored on your ', 'wp-smart-image').' <a href="upload.php" target="_blank">'.__('Media Library', 'wp-smart-image').'</a>.</p>
								</td>
							</tr>';
						} else {
							$resultado .='
							<tr>
								<th colspan="2">'.__('External image uploaded', 'wp-smart-image').'</th>
							</tr>
							<tr class="wpsi-col" id="wpsi-fotolista-externo">
								<td colspan="2">
									<p class="negrita"><span class="rojo">'.__('Error', 'wp-smart-image').':</span> '.__('This ID is not assigned to any image. Try again', 'wp-smart-image').'.</p>
									<p><span class="negrita">'.__('Image ID', 'wp-smart-image').':</span>
									<input type="radio" name="wpsi_foto_lista" id="boton_externo" autocomplete="off" value="externo"'.$externo_checked.' style="display: none" />
									<input type="text" name="wpsi_img_externa[]" id="wpsi_img_externa" autocomplete="off" value="'. $wpsi_img_externa .'" class="wpsi-input-externo" /></p>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<p>'.__('Always you can choose and publish any image stored on your ', 'wp-smart-image').' <a href="upload.php" target="_blank">'.__('Media Library', 'wp-smart-image').'</a>.</p>
								</td>
							</tr>';
						}
					} else {
						$resultado .='
						<tr>
							<th>'.__('... Or load it from Media Library', 'wp-smart-image').'</th>
						</tr>
						<tr class="wpsi-col" id="wpsi-fotolista-externo">
							<td>
								<p>'.__('First, visit your', 'wp-smart-image').' <a href="upload.php" target="_blank">'.__('Media Library', 'wp-smart-image').'</a>, '.__('grab the image ID and enter it in the field below:', 'wp-smart-image').'</p>
								<p><span class="negrita">'.__('Image ID', 'wp-smart-image').':</span>
								<input type="radio" name="wpsi_foto_lista" id="boton_externo" checked="checked" value="externo"'.$externo_checked.' style="display: none" />
								<input type="text" name="wpsi_img_externa[]" id="wpsi_img_externa" autocomplete="off" value="'. $wpsi_img_externa .'" class="wpsi-input-externo" /></p>
							</td>
						</tr>';
					}
				}
			}
			$resultado .='
			</table>
		</div>';
	return $resultado;
}

function wpsi_box() {
	echo wpsi_metabox();
}

function activar_metabox() {
	global $wpsi_modo_php, $wpsi_php;
	if($wpsi_modo_php) $wpsi_bd = $wpsi_php;
	else $wpsi_bd = get_option('wpsi_configuracion');
	$activar_metabox = $wpsi_bd['wpsi_activar_metabox'];
	if($activar_metabox) {
		add_action( 'do_meta_boxes' , 'wpsi_agregar_metabox' , 10, 2 );
		add_action( 'save_post', 'wpsi_guardar_metabox' );
	}
}

function wpsi_agregar_metabox() {
	add_meta_box('wpsi-metabox', __('Image to show', 'wp-smart-image'), 'wpsi_box', 'post', 'side', 'core');
	add_meta_box('wpsi-metabox', __('Image to show', 'wp-smart-image'), 'wpsi_box', 'page', 'side', 'core');
}

function wpsi_guardar_metabox( $post_ID ) {
	if ( wp_verify_nonce( $_REQUEST['_wpsi_nonce'], 'wpsi_metabox_args' ) ) {
		if(isset($_POST['wpsi_foto_lista'])) {
			if($_POST['wpsi_foto_lista'] == 'externo' ) 
				update_post_meta( $post_ID, '_wpsi_foto_lista', $_POST['wpsi_img_externa'] );
			else 
				update_post_meta( $post_ID, '_wpsi_foto_lista', $_POST['wpsi_foto_lista'] );
		}
		else delete_post_meta( $post_ID, '_wpsi_foto_lista' );
	}
	return $post_ID;
}

function wpsi_guardar_metabox_titulo() {
	global $post;
	if( isset($_POST['wpsi_inputbox']) ) { 
		foreach($_POST['wpsi_inputbox']  as  $valor)  {
			$imagen = array();
			$imagen['ID'] = $valor;
			$imagen['post_title'] = $_POST['wpsi_fotobox_title_'.$valor];
			wp_update_post( $imagen );
		}
	}
}

function wp_smart_image($args = '') {
	return wpsi($args);
}

function get_wpsi($args = '') {
	return wpsi('echo=0&'. $args);
}

function wpsi($args = '') {
	global $post, $wpsi_configuracion, $wpsi_modo_php, $wpsi_php, $dtdtag;
	$defaults = array(
		'echo'       => 1, // 1 | 0
		'element'    => '', // id | title | alt | mimetype | width | height
		'size'       => 'mini', // mini | med | big | full | custom[value]
		'type'       => 'link', // link | single | direct | url
		'wh'         => '', // css | html
		'class'      => '',
		'custom'     => '',
		'alt'        => '', // Any string | el_title | el_alt
		'title'      => '', // Any string | el_title | el_alt
		'p'          => '',
		'plink'      => '',
		'number'     => 1,
		'atitle'     => '', // Any string | el_title | el_alt
		'cid'        => '',
		'aclass'     => '',
		'acustom'    => '',
		'rel'        => '',
		'target'     => '',
		'targetname' => '',
		'aid'        => '',
		'array'      => 0,
		'quotes'     => '',
		'showtitle'  => 1 , // 1 | 0 | img | link,
		'ref'        => ''
	);
	
	if($wpsi_modo_php) $wpsi_bd = $wpsi_php;
	else $wpsi_bd = get_option('wpsi_configuracion'); 			
	$opcion_reemplazo = $wpsi_bd['wpsi_opcion_reemplazo'];
	$r = wp_parse_args($args, $defaults); extract($r);
	$plinked = ($plink == true and $p == true) ? $p : null;
	$q = $quotes == 'single' ? '\'' : '"';
	$imagen = '';
	$clase	= ' class='. $q . $class . $q;	
	$ident	= ' id='. $q . $cid . $q;	
	$aident	= ' id='. $q . $aid . $q;	
	$relatt	= ' rel='. $q . $rel . $q;
	$tname = ' target='. $q . $targetname . $q;
	$class = $class == true ? $clase : '';
	$cid = $cid == true ? $ident : '';
	$rel = $rel == true ? $relatt : '';
	$aid = $aid == true ? $aident  : '';
	$referer = $ref == true ? '?ref='. $ref : '';
	$ubicacion = $wpsi_configuracion['wpsi_ruta_img'];
	$targetname = $targetname == true ? $tname : '';
	$targetjs='';
	if($aclass == true) {
		if($target == 'js') {
			$aclase = ' class='. $q .'wpsi-blank '. $aclass . $q;
			$targetjs ='';
		} else {
			$aclase = ' class='. $q . $aclass . $q;
		} 
	} else {
		$aclase ='';
		$targetjs = ' class='. $q .'wpsi-blank'. $q;
	}
	$aclass	= $aclass == true ? $aclase  : '';
	$custom_entry = explode('|' , $custom);

	foreach($custom_entry as $entry) 
		$custom_array[] = explode( ',' , $entry);
	foreach($custom_array as $arr)
		$customatt .= !empty($custom) ? ' '. $arr[0] .'='. $q . $arr[1] . $q : null;

	$acustom_entry = explode('|' , $acustom);
	foreach($acustom_entry as $a_entry) 
		$acustom_array[] = explode( ',' , $a_entry);
	foreach($acustom_array as $a_arr)
		$acustomatt .= !empty($acustom) ? ' '. $a_arr[0] .'='. $q . $a_arr[1] . $q : null;

	$wpsi_postmeta_type = $wpsi_configuracion['wpsi_activar_metabox'] == 'tpt' ? '_thumbnail_id' : '_wpsi_foto_lista' ;
	$wpsi_postmeta_valor = get_post_meta( $post->ID, $wpsi_postmeta_type , true );
	if(!is_array($wpsi_postmeta_valor)) $wpsi_postmeta = $wpsi_postmeta_valor;
	else $wpsi_postmeta = $wpsi_postmeta_valor[0];
	$orden = 'aleatorio' == $wpsi_postmeta ? 'rand' : 'menu_order';
	$psingle = !empty($p) ? $p : $post->ID;
	$images = get_children(array(
		'post_parent'		=> $psingle,
		'post_type'			=> 'attachment',
		'showposts'		    => $number,
		'post_mime_type'	=> 'image',
		'orderby'			=> $orden,
		'order'				=> 'ASC'
	));
	switch ($size) {
		case 'mini': 
			$tam = 'thumbnail';
			$reemp = $wpsi_configuracion['wpsi_reemp_mini'];
		break;
		case 'med': 
			$tam = 'medium';
			$reemp = $wpsi_configuracion['wpsi_reemp_medio']; 
		break;
		case 'big': 
			$tam = 'large';
			$reemp = $wpsi_configuracion['wpsi_reemp_grande']; 
		break;
		case 'full': 
			$tam = 'full';
			$reemp = $wpsi_configuracion['wpsi_reemp_full']; 
		break;
		case $size: 
			$tam = $size;
			$reemp = 'noimg-'. $size .'.png';
		break;
		default: 
			$tam = 'thumbnail';
			$reemp = $wpsi_configuracion['wpsi_reemp_mini'];
		break;
	}
	switch ($target) {
		case 'blank': 
			$targetatt = ' target='. $q .'_blank'. $q;
		break;
		case 'self': 
			$targetatt = ' target='. $q .'_self'. $q; 
		break;
		case 'parent': 
			$targetatt = ' target='. $q .'_parent'. $q; 
		break;
		case 'top': 
			$targetatt = ' target='. $q .'_top'. $q; 
		break;
		case 'js': 
			$targetatt = $targetjs; 
		break;
		default: 
			$targetatt = '';
		break;
	}
	if($targetname == true) $target = ''; else $target = $targetatt;
	$wpsiext = get_post($wpsi_postmeta);
	$wpsiext_ruta = wp_get_attachment_image_src( $wpsiext->ID, $tam );
	$wpsiext_ruta_full = wp_get_attachment_image_src( $wpsiext->ID, 'full' );
	if($images) {
		foreach( $images as $image ) {
			$sep = $array == 1 ? '||y||' : null;
			$img = ($array == 1 or $number > 1) ? $image : ($wpsi_postmeta == true ? $wpsiext : $image);
			if($array == 1 or $number > 1) {
				$alt = array();
				$title = array();
				$custom = array();
				$acustom = array();
			}
			$wpsi_metabox = $wpsi_bd['wpsi_activar_metabox'];
			$alt_img_txt = function_exists('current_theme_supports') ? get_post_meta($img->ID, '_wp_attachment_image_alt', true) : $img->post_excerpt;
			$alt_img_txt = empty($alt_img_txt) ? $img->post_title : $alt_img_txt;
			$reemplazo_alt = array( 'el_title' => htmlspecialchars($post->post_title) , 'el_alt' => htmlspecialchars($alt_img_txt) );
			$alt = empty($alt) ? $alt_img_txt : strtr($alt , $reemplazo_alt);
			$ruta = wp_get_attachment_image_src($img->ID, $tam);
			$weburl_full = wp_get_attachment_image_src($img->ID, 'full');
			$title = !empty($title) ? strtr( htmlspecialchars($title) , $reemplazo_alt ) : $img->post_title;
			if($element) {
				switch ($element) {
					case 'id': 
						$imagen .= $img->ID . $sep; 
					break;
					case 'ID': 
						$imagen .= $img->ID . $sep; 
					break;
					case 'title': 
						$imagen .= $title . $sep;
					break;
					case 'alt': 
						$imagen .= $alt . $sep; 
					break;
					case 'mimetype': 
						$imagen .= $img->post_mime_type . $sep; 
					break;
					case 'width': 
						$imagen .= $ruta[1] . $sep; 
					break;
					case 'height': 
						$imagen .= $ruta[2] . $sep; 
					break;
					default: 
						$imagen .= '';
					break;
				}
			} else {
				switch ($wh) {
					case 'html': 
						$widtheight .= ' width='. $q . $ruta[1]. $q .' height='. $q . $ruta[2] . $q;
					break;
					case 'css': 
						$widtheight .= ' style='. $q .'width: '.$ruta[1].'px; height: '.$ruta[2].'px;' . $q; 
					break;
				}
				switch ($showtitle) {
				case '0':
						$titulo_img = '';
						$titulo_link = '';
					break;
					case '1':
						$titulo_img = ' title='. $q . $title . $q;
						$titulo_link = ' title='. $q . $title . $q;
					break;
	
					case 'img':
						$titulo_img = ' title='. $q . $title . $q;
						$titulo_link = '';
					break;
					case 'link':
						$titulo_img = '';
						$titulo_link = ' title='. $q . $title . $q;
					break;
				}
				$wh	= $wh == true ? $widtheight  : '';
				$alt_img = ' alt='. $q . $alt . $q;
				$linklist = $rel . $target . $targetname . $aclass . $aid . $titulo_link . $acustomatt;
				$img_list = $class . $cid . $widtheight . $alt_img . $titulo_img . $customatt;
				$weburl = $ruta[0];
				$img_single = '<img src='. $q . $weburl . $q . $img_list . $dtdtag;
				$weburl_img = '<a href='. $q . $weburl_full[0] . $q . $linklist .'>'. $img_single .'</a>' . "\n";
				$img_link = '<a'. $linklist .' href='. $q . get_permalink($plinked) . $referer . $q .'>'. $img_single .'</a>' . "\n";
				switch ($type) {
					case 'link': 
						$imagen .= $img_link . $sep;
					break;
					case 'single': 
						$imagen .= $img_single . $sep; 
					break;
					case 'direct': 
						$imagen .= $weburl_img . $sep; 
					break;
					case 'url': 
						$imagen .= $weburl . $sep; 
					break;
					default: 
						$imagen .= $img_link . $sep;
					break;
				}

			}
			if($array) {
				$imgarray = explode( $sep , $imagen );
				$imgarray = array_filter($imgarray);
			}
		} 
	} else {
		if ( $opcion_reemplazo ) {
			if($wpsi_postmeta) {
			$alt_img_txt = function_exists('current_theme_supports') ? get_post_meta($wpsiext->ID, '_wp_attachment_image_alt', true) : $wpsiext->post_excerpt;
			$alt_img_txt = empty($alt_img_txt) ? $wpsiext->post_title : $alt_img_txt;
				if($element) {
					switch ($element) {
						case 'id': 
							$imagen .= $wpsiext->ID; 
						break;
						case 'ID': 
							$imagen .= $wpsiext->ID; 
						break;
						case 'title': 
							$imagen .= $wpsiext->post_title;
						break;
						case 'alt': 
							$imagen .= $alt_img_txt; 
						break;
						case 'mimetype': 
							$imagen .= $wpsiext->post_mime_type; 
						break;
						case 'width': 
							$imagen .= $wpsiext_ruta[1]; 
						break;
						case 'height': 
							$imagen .= $wpsiext_ruta[2]; 
						break;
						default: 
							$imagen .= '';
						break;
					}
				} else {
					$weburl = $wpsiext_ruta;
					$reemplazo_alt = array( 'el_title' => htmlspecialchars($post->post_title) , 'el_alt' => htmlspecialchars($alt_img_txt) );
					$alt = empty($alt) ? $alt_img_txt : strtr($alt , $reemplazo_alt);
					$linklist = $rel . $target . $targetname . $aclass . $aid . $titulo_link . $acustomatt;
					$img_list = $class . $cid . $widtheight . $alt_img . $titulo_img . $customatt;
					$weburl_full = $wpsiext_ruta_full;
					$img_single = '<img src='. $q . $weburl[0] . $q . $img_list . $dtdtag . "\n";
					$weburl_img = '<a href='. $q . $weburl_full[0] . $q . $linklist .'>'. $img_single .'</a>' . "\n";
					$img_link = '<a'. $linklist .' href='. $q . get_permalink($plinked) . $referer . $q .'>'. $img_single .'</a>' . "\n";
					switch ($wh) {
						case 'html': 
							$wh = ' width='. $q . $wpsiext_ruta[1] . $q .' height='. $q . $wpsiext_ruta[2] . $q;
						break;
						case 'css': 
							$wh = ' style='. $q .'width: '. $wpsiext_ruta[1] .'px; height: '. $wpsiext_ruta[2] .'px;'. $q; 
						break;
					}
					switch ($type) {
						case 'link': 
							$imagen .= $img_link;
						break;
						case 'single': 
							$imagen .= $img_single; 
						break;
						case 'direct': 
							$imagen .= $weburl_img; 
						break;
						case 'url': 
							$imagen .= $weburl[0]; 
						break;
						default: 
							$imagen .= $img_link;
						break;
					}
				}
			} else {
				if($element) {
					$imagen .= __('N/A', 'wp-smart-image');
				} else {
					$alt_img_txt = $wpsi_configuracion['wpsi_texto_alt'];
					$titletxt = $wpsi_bd['wpsi_texto_title_titulo'] == true ? $post->post_title : $wpsi_configuracion['wpsi_texto_title'];
					$reemplazo_alt = array( 'el_title' => htmlspecialchars($post->post_title) , 'el_alt' => htmlspecialchars($alt_img_txt) );
					$alt = empty($alt) ? $alt_img_txt : strtr($alt , $reemplazo_alt);
					$title = !empty($title) ? strtr( htmlspecialchars($title) , $reemplazo_alt ) : $titletxt;
					$alt_img = ' alt='. $q . $alt . $q;
					switch ($showtitle) {
					case '0':
							$titulo_img = '';
							$titulo_link = '';
						break;
						case '1':
							$titulo_img = ' title='. $q. $title . $q;
							$titulo_link = ' title='. $q . $title . $q;
						break;
		
						case 'img':
							$titulo_img = ' title='. $q . $title . $q;
							$titulo_link = '';
						break;
						case 'link':
							$titulo_img = '';
							$titulo_link = ' title='. $q . $title . $q;
						break;
					}
					$linklist = $rel . $target . $targetname . $aclass . $aid . $titulo_link . $acustomatt;
					$img_def = '<img src='. $q . $ubicacion . $reemp . $q . $class . $cid . $alt_img . $titulo_img . $customatt . $dtdtag;
					$img_def_link = '<a'. $linklist .' href='. $q . get_permalink($plinked) . $referer . $q .'>'. $img_def .'</a>';
					switch ($type) {
						case 'link': 
							$imagen .= $img_def_link;
						break;
						case 'single': 
							$imagen .= $img_def; 
						break;
						case 'direct': 
							$imagen .= '<a'. $linklist .' href='. $q . $ubicacion . $wpsi_configuracion['wpsi_reemp_full'] . $q .'>'. $img_def .'</a>'; 
						break;
						case 'url': 
							$imagen .= $ubicacion . $reemp; 
						break;
						default: 
							$imagen .= $img_def_link;
						break;
					}
				}
			}
		} else {
			$imagen .= false;
		}
	}
if($array) return $imgarray;
if($echo) echo $imagen; 
else return $imagen;
}
?>