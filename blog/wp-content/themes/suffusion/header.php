<?php
/**
 * Core header file, invoked by the get_header() function
 *
 * @package Suffusion
 * @subpackage Templates
 */

global $suffusion_unified_options, $suffusion_interactive_text_fields, $suffusion_translatable_fields;
foreach ($suffusion_unified_options as $id => $value) {
	/**
	 * Some strings are set interactively in the admin screens of Suffusion. If you have WPML installed, then there may be translations of such strings.
	 * This code ensures that such translations are picked up, then the unified options array is rewritten so that subsequent calls can pick it up.
	 */
	if (function_exists('icl_t') && in_array($id, $suffusion_translatable_fields) && isset($suffusion_interactive_text_fields[$id])) {
		$value = wpml_t('suffusion-interactive', $suffusion_interactive_text_fields[$id]."|".$id, $value);
	}
	global $$id;
	$$id = $value;
	$suffusion_unified_options[$id] = $value;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

<?php
suffusion_document_header();
if (is_singular()) {
	wp_enqueue_script('comment-reply');
}
wp_head();
$suffusion_pseudo_template = suffusion_get_pseudo_template_class();
?>
</head>
<body <?php body_class($suffusion_pseudo_template); ?>>
    <?php suffusion_before_page(); ?>
		<?php
			suffusion_before_begin_wrapper();
		?>
		<div id="wrapper" class="fix">
		<?php
			suffusion_after_begin_wrapper();
		?>
			<div id="container" class="fix">
				<?php
					suffusion_after_begin_container();
				?>