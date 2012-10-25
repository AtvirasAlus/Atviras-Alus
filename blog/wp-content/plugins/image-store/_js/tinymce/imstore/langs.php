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
lightbox_label:"' . mce_escape( __('Lightbox', $$ImStore->domain ) ) . '",
list_label:"' . mce_escape( __('List', $ImStore->domain ) ) . '",
slideshow_label:"' . mce_escape( __('Slideshow', $ImStore->domain ) ) . '",
gallery_search:"' . mce_escape( __('Gallery search', $ImStore->domain ) ) . '",
gallery_id:"' . mce_escape( __('Gallery id', $ImStore->domain ) ) . '",
show_as:"' . mce_escape( __('Show as', $ImStore->domain ) ) . '",
add_gallery:"' . mce_escape( __('Add Gallery', $ImStore->domain ) ) . '",
box_title:"' . mce_escape( __('Image Store Galleries', $ImStore->domain ) ) . '",
tab_tilte:"' . mce_escape( __('Galleries', $ImStore->domain ) ) . '"
});';
