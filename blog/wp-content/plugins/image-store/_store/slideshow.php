<?php 

/**
*Slideshow page
*
*@package Image Store
*@author Hafid Trujillo
*@copyright 20010-2012
*@since 0.5.0 
*/
 
// Stop direct access of the file
if( preg_match( '#'.basename(__FILE__) . '#',$_SERVER['PHP_SELF'])) 
	die( );

$output .= '<div class="ims-imgs-nav">'."\n";
$output .= '<div id="ims-thumbs">'."\n";
$output .= '<ul class="thumbs">'."\n";

foreach($this->attachments as $image ){

	$mini = $image->meta['sizes']['mini'];
	
	$url	= $this->get_image_url( $image,  'mini') ;
	$link 	= $this->get_image_url( $image ) . "&amp;id=" . $this->encrypt_id( $image->ID );
	
	$size = ' width="'.$mini['width'] . '" height="'.$mini['height'] . '"';
	$img = '<img src="'.$url . '" title="'.esc_attr( $image->post_excerpt ) . '" alt="'. esc_attr( $image->post_title ) . '"'.$size . ' />'; 
	
	$output .='<li class="ims-thumb"><a class="thumb" href="'.$link . '" title="'.esc_attr( $image->post_title) . '">'.$img . '</a>
				<span class="caption">'. apply_filters( 'ims_image_caption', $image->post_excerpt, $image ) . '</span></li>';
}

$output .= '</ul><!--.thumbs-->'."\n";
$output .= '</div><!--#ims-thumbs-->'."\n";
$output .= '</div><!--.ims-imgs-nav-->'."\n";



$output .= '<div class="ims-slideshow-box">';
$output .= '
	<div class="ims-preview">
		<div class="ims-slideshow-row">
			<div id="ims-slideshow" class="ims-slideshow" ></div>
		</div>
	</div><!--.ims-preview-->';
	
	
$output .= '<div class="ims-slideshow-tools-box">'."\n";
$output .= '<div class="zoom">&nbsp;</div>'."\n";
$output .= '<form method="post" class="ims-slideshow-tools">'."\n";

if( empty($this->opts['disablestore'])){
	$output .= '<div class="add-images-to-cart-single"><a href="#" role="button" rel="nofollow">' . __( 'Add to cart', $this->domain ) . '</a></div>'."\n";
	$output .= '<div class="add-to-favorite-single"><a href="#" role="button" rel="nofollow">' . __( 'Add to favorites', $this->domain ) . '</a></div>'."\n";
	$output .= apply_filters( 'ims_slideshow_actions', '' );

}

$output .= '<div class="image-color">'."\n";
if( empty( $this->opts['disablebw'] ))
	$output .= '<label><input type="checkbox" name="ims-color" id="ims-color-bw" value="bandw" /> '. __( 'Black &amp; White', $this->domain ) . '</label>	'."\n";
if( empty( $this->opts['disablesepia'] ))
	$output .= '<label><input type="checkbox" name="ims-color" id="ims-color-sepia" value="sepia" /> '. __( 'Sepia', $this->domain ) . '</label>	'."\n";

$output .= apply_filters( 'ims_color_options', '' );
$output .= '</div><!--.image-color-->'."\n";

$output .= '<div id="ims-player" class="ims-player">'."\n";
$output .= '<a href="#" class="bk" rel="nofollow">' . __( 'Back', $this->domain ) . '</a>';
$output .= '<a href="#" class="py" rel="nofollow">' . __( 'Play', $this->domain ) . '</a>';
$output .= '<a href="#" class="nx" rel="nofollow">' . __( 'Next', $this->domain ) . '</a>';
$output .= '</div><!--#ims-player-->'."\n";


$output .= '</form><!--.ims-slideshow-tools-->'."\n";
$output .= '<div id="ims-caption" class="ims-caption"></div>'."\n";
$output .= apply_filters( 'ims_after_slideshow', '' );

$output .= '</div><!--.ims-slideshow-tools-box-->'."\n";
$output .= '<div class="ims-cl"></div>'."\n";
$output .= '</div><!--.ims-slideshow-box-->'."\n";