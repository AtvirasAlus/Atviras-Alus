<?php

/**
 * Pricelist page
 *
 * @package Image Store
 * @author Hafid Trujillo
 * @copyright 20010-2012
 * @since 0.5.0 
 */
 
// Stop direct access of the file
if (!defined('ABSPATH'))
	die();

$css = '';
$meta = get_post_meta($this->pricelist_id, '_ims_list_opts', true);
$output = apply_filters('ims_before_pricelist_page', $output, $this->pricelist_id);

if( empty($meta['finishes']))
	$meta['finishes'] = array();

if( empty($meta['colors']) )
	$meta['colors'] = array();

$output .= '
	<table class="ims-table" role="grid">
		<thead>
			<tr>
				<th scope="col" colspan="2" class="ims-size">' . __('Image size', 'ims') . '</th>
				<th class="ims-blank">&nbsp;</th>
				<th class="ims-price">' . __('Price', 'ims') . '</th>
				<th class="ims-blank">&nbsp;</th>
				<th class="ims-download">' . __('Download', 'ims') . '</th>
			</tr>
		</thead>';

$output .= '<tbody>';
foreach ((array)$this->sizes as $size) :

	$package_sizes = '';
	$css = ( $css == ' alternate' ) ? '' : ' alternate';
	
	$output .='<tr class="row'.$css.'">' . "\n";
	
	if (isset($size['ID'])): 	//packages
	
		$output .= '<td role="gridcell" colspan="2" class="ims-size"><span class="ims-size-name">' . $size['name'] . ": </span> ";
		
		foreach ((array) get_post_meta($size['ID'], '_ims_sizes', true) as $package_size => $count):
			if (is_array($count)):
				$package_sizes .= $package_size . ' <span class="ims-unit">' . $count['unit'] . '</span> <span class="ims-pcount">( ' . $count['count'] . ' )</span>, ';
			else:
				$package_sizes .= $package_size . ' <span class="ims-scount">( ' . $count . ' )</span>, ';
			endif;
		endforeach;
		
		$output .= rtrim($package_sizes, ', ') . ' </td>';
		$output .= '<td role="gridcell" class="blank">&nbsp;</td>';
		$output .= '<td role="gridcell" class="ims-price">' . $this->format_price(get_post_meta($size['ID'], '_ims_price', true)) . '</td>';

	else: 	//image sizes
	
		$output .= 
		'<td role="gridcell"  colspan="2" class="ims-size">
			<span class="ims-size-name">' . $size['name'] . ' <span class="ims-unit">' . $size['unit'] . '</span></span>
		</td>';
		$output .='<td role="gridcell" class="blank">&nbsp;</td>' . "\n";
		$output .='<td role="gridcell" class="ims-price">' . $this->format_price($size['price']) . ' </td>' . "\n";
	endif;
	
	//downloadable
	$download = ( isset($size['download']) ) ? __('Included', 'ims') : '';
	
	$output .= '<td role="gridcell" class="blank">&nbsp;</td>';
	$output .= '<td role="gridcell" class="ims-download">' . $download . '</td>';
	
	$output .='</tr><!--.row-->' . "\n";
	
endforeach;
$output .= '</tbody>';

$output .= '<tfoot>';
$output .= '<tr role="row" class="divider-row"><td role="gridcell" colspan="6">&nbsp;</td></tr>';

$output .= '<tr role="row" class="subhead-row">';
$output .=	 '<td role="gridcell" colspan="2" class="subhead">'. __( 'Shipping', 'ims').'</td>';
$output .=	 '<td role="gridcell" colspan="2" class="subhead">'.__( 'Color Options', 'ims').'</td>';
$output .=	 '<td role="gridcell" colspan="2" class="subhead">'. __( 'Finishes', 'ims').'</td>';
$output .= '</tr>';

$max = max( count($meta['finishes']), count($meta['colors']), count($this->shipping_opts) );

for( $x=0; $max > $x; $x++):
	$output .= '<tr role="row" class="ims-list-meta">';
	
	if( isset( $this->shipping_opts[$x]['name'] ) ){ // shipping
		$output .=	 '<td role="gridcell" class="ims-shipping-name">'.$this->shipping_opts[$x]['name'].'</td>';
		$output .=	 '<td role="gridcell"  class="ims-shipping-price">'.$this->format_price($this->shipping_opts[$x]['price']).'</td>';
	}else $output .=	 '<td role="gridcell" colspan="2" class="ims-shipping-empty">&nbsp;</td>';
	
	if( isset( $meta['colors'][$x]['name'] ) ){ // colors
		$output .=	 '<td role="gridcell" class="ims-color-name">'.$meta['colors'][$x]['name'].'</td>';
		$output .=	 '<td role="gridcell"  class="ims-color-price">'.$this->format_price($meta['colors'][$x]['price']).'</td>';
	}else $output .=	 '<td role="gridcell" colspan="2" class="ims-color-empty">&nbsp;</td>';
		
	if( isset( $meta['finishes'][$x]['name'] ) ){ // finishes
		$output .=	 '<td role="gridcell" class="ims-finish-name">'.$meta['finishes'][$x]['name'].'</td>';
		$output .=	 '<td role="gridcell"  class="ims-finish-price">'.$this->format_price($meta['finishes'][$x]['price']).'</td>';
	}else $output .=	 '<td role="gridcell" colspan="2" class="ims-finish-empty">&nbsp;</td>';

	$output .= '</tr>';
endfor;


$output .= '</tfoot></table><!--.ims-table-->';

$output = apply_filters('ims_after_pricelist_page', $output, $this->pricelist_id);