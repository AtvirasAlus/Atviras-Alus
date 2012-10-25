<?php // Widgetized Sidebar.
function zbench_widgets_init() {
	register_sidebar(array(
		'name' => __('Primary Widget Area','zbench'),
		'id' => 'primary-widget-area',
		'description' => __('The primary widget area','zbench'),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widgettitle">',
		'after_title' => '</h3>'
	));
	register_sidebar(array(
		'name' => __('Singular Widget Area','zbench'),
		'id' => 'singular-widget-area',
		'description' => __('The singular widget area','zbench'),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widgettitle">',
		'after_title' => '</h3>'
	));
	register_sidebar(array(
		'name' => __('Not Singular Widget Area','zbench'),
		'id' => 'not-singular-widget-area',
		'description' => __('Not the singular widget area','zbench'),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widgettitle">',
		'after_title' => '</h3>'
	));
	register_sidebar(array(
		'name' => __('Footer Widget Area','zbench'),
		'id' => 'footer-widget-area',
		'description' => __('The footer widget area','zbench'),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widgettitle">',
		'after_title' => '</h3>'
	));
}
add_action( 'widgets_init', 'zbench_widgets_init' );

// Custom Comments List.
function zbench_mytheme_comment($comment, $args, $depth) {
   $GLOBALS['comment'] = $comment;
?>
<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
	<div id="comment-<?php comment_ID(); ?>">
		<div class="comment-author vcard">
			<?php echo get_avatar($comment,$size='40',$default='' ); ?>
			<cite class="fn"><?php comment_author_link(); ?></cite>
			<span class="comment-meta commentmetadata"><a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ); ?>"><?php printf(__('%1$s at %2$s'), get_comment_date(),  get_comment_time()); ?></a><?php edit_comment_link(__('[Edit]','zbench'),' ',''); ?></span>
		</div>
		<?php if ($comment->comment_approved == '0') : ?>
		<em class="approved"><?php _e('Your comment is awaiting moderation.','zbench'); ?></em>
		<br />
		<?php endif; ?>
		<div class="comment-text">
			<?php comment_text(); ?>
		</div>
		<div class="reply">
			<?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))); ?>
		</div>
	</div>
<?php }

/* wp_list_comments()->pings callback */
function zbench_custom_pings($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment;
    if('pingback' == get_comment_type()) $pingtype = 'Pingback';
    else $pingtype = 'Trackback';
?>
    <li id="comment-<?php echo $comment->comment_ID ?>">
        <?php comment_author_link(); ?> - <?php echo $pingtype; ?> on <?php echo mysql2date('Y/m/d/ H:i', $comment->comment_date); ?>
<?php }

if ( ! isset( $content_width ) )
	$content_width = 620;
	
// WP nav menu
if (function_exists('wp_nav_menu')) {
	register_nav_menus(array('primary' => 'Primary Navigation'));
}

// LOCALIZATION
load_theme_textdomain('zbench', get_template_directory() . '/lang');

// custom excerpt
function zbench_excerpt_length( $length ) {
	return 40;
}
add_filter( 'excerpt_length', 'zbench_excerpt_length' );

function zbench_continue_reading_link() {
	return '<p class="read-more"><a href="'. get_permalink() . '">' . __( 'Read more &raquo;', 'zbench' ) . '</a></p>';
}

function zbench_auto_excerpt_more( $more ) {
	return ' &hellip;' . zbench_continue_reading_link();
}
add_filter( 'excerpt_more', 'zbench_auto_excerpt_more' );

function zbench_custom_excerpt_more( $output ) {
	if ( has_excerpt() && ! is_attachment() ) {
		$output .= zbench_continue_reading_link();
	}
	return $output;
}
add_filter( 'get_the_excerpt', 'zbench_custom_excerpt_more' );

// Tell WordPress to run zbench_setup() when the 'after_setup_theme' hook is run.
add_action( 'after_setup_theme', 'zbench_setup' );
if ( ! function_exists( 'zbench_setup' ) ):
function zbench_setup() {

	// Add default posts and comments RSS feed links to head
	add_theme_support( 'automatic-feed-links' );

	// This theme styles the visual editor with editor-style.css to match the theme style.
	add_editor_style();

	// This theme allows users to set a custom background
	add_custom_background();
	
	// This theme uses post thumbnails
	add_theme_support( 'post-thumbnails' );
	add_image_size( 'extra-featured-image', 620, 200, true );
	function zbench_featured_content($content) {
		if (is_home() || is_archive()) {
			the_post_thumbnail( 'extra-featured-image' );
		}
		return $content;
	}
	add_filter( 'the_content', 'zbench_featured_content',1 );
	function zbench_post_image_html( $html, $post_id, $post_image_id ) {
		$html = '<a href="' . get_permalink( $post_id ) . '" title="' . esc_attr( get_post_field( 'post_title', $post_id ) ) . '">' . $html . '</a>';
		return $html;
	}
	add_filter( 'post_thumbnail_html', 'zbench_post_image_html', 10, 3 );

	// Your changeable header business starts here
	define( 'HEADER_TEXTCOLOR', '' );
	// No CSS, just IMG call. The %s is a placeholder for the theme template directory URI.
	define( 'HEADER_IMAGE', '' ); // default: none IMG

	// The height and width of your custom header. You can hook into the theme's own filters to change these values.
	// Add a filter to zbench_header_image_width and zbench_header_image_height to change these values.
	define( 'HEADER_IMAGE_WIDTH', apply_filters( 'zbench_header_image_width', 950 ) );
	define( 'HEADER_IMAGE_HEIGHT', apply_filters( 'zbench_header_image_height', 180 ) );

	// We'll be using post thumbnails for custom header images on posts and pages.
	// We want them to be 950 pixels wide by 180 pixels tall.
	// Larger images will be auto-cropped to fit, smaller ones will be ignored. See header.php.
	set_post_thumbnail_size( HEADER_IMAGE_WIDTH, HEADER_IMAGE_HEIGHT, true );

	// Don't support text inside the header image.
	define( 'NO_HEADER_TEXT', true );

	// Add a way for the custom header to be styled in the admin panel that controls
	// custom headers. See zbench_admin_header_style(), below.
	add_custom_image_header( '', 'zbench_admin_header_style' );
	if ( ! function_exists( 'zbench_admin_header_style' ) ) {
	//Styles the header image displayed on the Appearance > Header admin panel.
		function zbench_admin_header_style() {
		?>
			<style type="text/css">
			/* Shows the same border as on front end */
			#headimg { }
			/* If NO_HEADER_TEXT is false, you would style the text with these selectors:
				#headimg #name { }
				#headimg #desc { }
			*/
			</style>
		<?php
		}
	}

} // end of zbench_setup()
endif;

// Theme Options
function zbench_options_items() {
	$items = array (
		array(
			'id' => 'logo_url',
			'name' => __('Logo URL', 'zbench'),
			'desc' => __('Put your full logo image address here.(with http://) Image Height: 36px', 'zbench')
		),
		array(
			'id' => 'rss_url',
			'name' => __('RSS URL', 'zbench'),
			'desc' => __('Put your full rss subscribe address here.(with http://)', 'zbench')
		),
		array(
			'id' => 'twitter_url',
			'name' => __('twitter URL', 'zbench'),
			'desc' => __('Put your full twitter address here.(with http:// , leave it blank for display none.)', 'zbench')
		),
		array(
			'id' => 'facebook_url',
			'name' => __('facebook URL', 'zbench'),
			'desc' => __('Put your full facebook address here.(with http:// , leave it blank for no display none.)', 'zbench')
		),
		array(
			'id' => 'googleplus_url',
			'name' => __('Google+ URL', 'zbench'),
			'desc' => __('Put your full Google+ address here.(with http:// , leave it blank for no display none.)', 'zbench')
		),
		array(
			'id' => 'excerpt_check',
			'name' => __('Excerpt?', 'zbench'),
			'desc' => __('If the home page and archive pages to display excerpt of post, check.', 'zbench')
		),
		array(
			'id' => 'comment_notes',
			'name' => __('Disable the comment notes','zbench'),
			'desc' => __('Disabling this will remove the note text that displays with more options for adding to comments (html).', 'zbench')
		),
		array(
			'id' => 'smilies',
			'name' => __('Disable the comments smilies','zbench'),
			'desc' => __('Disabling this will remove the comments smilies.', 'zbench')
		)
	);
	return $items;
}

add_action( 'admin_init', 'zbench_theme_options_init' );
add_action( 'admin_menu', 'zbench_theme_options_add_page' );
function zbench_theme_options_init(){
	register_setting( 'zbench_options', 'zBench_options', 'zbench_options_validate' );
}
function zbench_theme_options_add_page() {
	add_theme_page( __( 'Theme Options' ), __( 'Theme Options' ), 'edit_theme_options', 'theme_options', 'zbench_theme_options_do_page' );
}

function zbench_default_options() {
	$options = get_option( 'zBench_options' );
	foreach ( zbench_options_items() as $item ) {
		if ( ! isset( $options[$item['id']] ) ) {
			$options[$item['id']] = '';
		}
	}
	update_option( 'zBench_options', $options );
}
add_action( 'init', 'zbench_default_options' );

function zbench_theme_options_do_page() {
	if ( ! isset( $_REQUEST['updated'] ) )
		$_REQUEST['updated'] = false;
?>
	<div class="wrap">
		<?php screen_icon(); echo "<h2>" . sprintf( __( '%1$s Theme Options', 'zbench' ), get_current_theme() )	 . "</h2>"; ?>
		<?php if ( false !== $_REQUEST['updated'] ) : ?>
		<div class="updated fade"><p><strong><?php _e( 'Options saved', 'zbench' ); ?></strong></p></div>
		<?php endif; ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'zbench_options' ); ?>
			<?php $options = get_option( 'zBench_options' ); ?>
			<table class="form-table">
			<?php foreach (zbench_options_items() as $item) { ?>
				<?php if ($item['id'] == 'excerpt_check' || $item['id'] == 'comment_notes' || $item['id'] == 'smilies') { ?>
				<tr valign="top" style="margin:0 10px;border-bottom:1px solid #ddd;">
					<th scope="row"><?php echo $item['name']; ?></th>
					<td>
						<input name="<?php echo 'zBench_options['.$item['id'].']'; ?>" type="checkbox" value="true" <?php if ( $options[$item['id']] ) { $checked = "checked=\"checked\""; } else { $checked = ""; } echo $checked; ?> />
						<label class="description" for="<?php echo 'zBench_options['.$item['id'].']'; ?>"><?php echo $item['desc']; ?></label>
					</td>
				</tr>
				<?php } else { ?>
				<tr valign="top" style="margin:0 10px;border-bottom:1px solid #ddd;">
					<th scope="row"><?php echo $item['name']; ?></th>
					<td>
						<input name="<?php echo 'zBench_options['.$item['id'].']'; ?>" type="text" value="<?php if ( $options[$item['id']] != "") { echo $options[$item['id']]; } else { echo ''; } ?>" size="80" />
						<br/>
						<label class="description" for="<?php echo 'zBench_options['.$item['id'].']'; ?>"><?php echo $item['desc']; ?></label>
					</td>
				</tr>
				<?php } ?>
			<?php } ?>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e( 'Save Options', 'zbench' ); ?>" />
			</p>
		</form>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<div class="wrap" style="background:#DCEEFC; margin-bottom:1em;">
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row"><strong><?php _e('Donation','zbench'); ?></strong></th>
							<td>
								<?php _e('If you feel my work is useful and want to support the development of more free resources, you can donate me. Thank you very much!', 'zbench'); ?>
								<br />
								<input type="hidden" name="cmd" value="_s-xclick">
								<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
								">
								<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
								<img alt="" border="0" src="https://www.paypal.com/zh_XC/i/scr/pixel.gif" width="1" height="1">
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</form>
	</div>
<?php
}
function zbench_options_validate($input) {
	foreach ( zbench_options_items() as $item ) {
		$input[$item['id']] = wp_filter_nohtml_kses($input[$item['id']]);
	}
	return $input;
}