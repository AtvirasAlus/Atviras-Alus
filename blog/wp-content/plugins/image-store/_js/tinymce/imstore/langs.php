<?php 
global $language;
$language = ( empty($language) ) ?  'en' : $language;

// escape text only if it needs translating
function mce_escape( $text ) {
	global $language;
	if ( 'en' == $language ) return $text;
	else return esc_js($text);
}

global $ImStore;

$strings = 'tinyMCE.addI18n("' . $language . '.imstore",{
lightbox_label:"' . mce_escape( __('Lightbox', $'ims') ) . '",
list_label:"' . mce_escape( __('List', 'ims') ) . '",
slideshow_label:"' . mce_escape( __('Slideshow', 'ims') ) . '",
gallery_search:"' . mce_escape( __('Gallery search', 'ims') ) . '",
gallery_id:"' . mce_escape( __('Gallery id', 'ims') ) . '",
show_as:"' . mce_escape( __('Show as', 'ims') ) . '",
add_gallery:"' . mce_escape( __('Add Gallery', 'ims') ) . '",
box_title:"' . mce_escape( __('Image Store Galleries', 'ims') ) . '",
tab_tilte:"' . mce_escape( __('Galleries', 'ims') ) . '"
});';
