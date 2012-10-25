<?php 

/**
*Pricelist page
*
*@package Image Store
*@author Hafid Trujillo
*@copyright 20010-2012
*@since 0.5.0 
*/

// Stop direct access of the file
if( preg_match( '#'.basename(__FILE__).'#',$_SERVER['PHP_SELF'])) 
	die( );

$css = '';
$package_sizes = '';
$meta = get_post_meta( $this->pricelist_id, '_ims_list_opts', true);

$output .= '
	<table class="ims-table">
		<thead>
			<tr>
				<th colspan="2" class="ims-size">' . __( 'Image size', $this->domain ) . '</th>
				<th class="blank">&nbsp;</th>
				<th class="ims-price">' . __( 'Price', $this->domain ) . '</th>
				<th class="ims-download">' . __( 'Download', $this->domain ) . '</th>
			</tr>
		</thead>
		<tbody>
';

foreach( $this->sizes as $size ){
		$css = ( $css == ' class="alternate"' ) ? '' : ' class="alternate"'; 
 		$output .="\t\t<tr{$css}>\n";	
		if( isset( $size['ID'] ) ){
			$output .= '<td colspan="2" class="ims-size"><span class="ims-size-name">'.$size['name'].": </span> ";
			foreach((array)get_post_meta($size['ID'], '_ims_sizes',true) as $package_size => $count){
				if( is_array($count) ) $package_sizes .= $package_size .' '. $count['unit'] . '( '.$count['count'].' ), '; 
				else $package_sizes .= $package_size .'( '.$count.' ), '; 
			}
			$output .= rtrim( $package_sizes, ', ' ) . ' </td>';
			$output .= '<td class="blank">&nbsp;</td>';
			$output .= '<td class="ims-price">'. $this->format_price(  get_post_meta( $size['ID'], '_ims_price', true )) . '</td>';
		}else{
			$output .= '<td colspan="2" class="ims-size"><span class="ims-size-name">'.$size['name'].' '.$size['unit'].'</span></td>
				 <td class="blank">&nbsp;</td>
				 <td class="ims-price">'. $this->format_price( $size['price'] ) .' </td>';
		}
		$download = ( isset($size['download']) ) ? __( 'Included', $this->domain ) : '';
		$output .= '<td class="ims-download">'. $download . '</td>';
		$output .= "\t\t</tr>\n";	
}

$output .= '</tbody><tfoot>';
$output .= '<tr class="divider-row"><td colspan="5">&nbsp;</td></tr>';
$output .=	' <tr class="subhead-row">
				<td colspan="2" class="subhead">' . __( 'Shipping', $this->domain ) . '</td>
				<td class="subhead">&nbsp;</td>
				<td colspan="2" class="subhead"> ';
if( empty( $this->opts['disablebw'] ) || empty($this->opts['disablesepia'])) 
$output .= __( 'Color Options', $this->domain ) . '&nbsp;';
$output .=	 '</td></tr>';

$output .=	
			'<tr>
				<td>' . __( 'Local', $this->domain ) . '</td>
				<td>' . $this->format_price( $meta['ims_ship_local'] ) . '</td>
				<td class="subhead">&nbsp;</td>
				<td>' . ( empty( $this->opts['disablesepia'] ) ? __( 'Sepia', $this->domain ) : '' ) . '&nbsp;</td>
				<td>' . ( empty($this->opts['disablesepia']) ? $this->format_price( $meta['ims_sepia'] ) : '' ) . '&nbsp;</td>
			</tr>';
			
$output .=	
			'<tr>
				<td>' . __( 'International', $this->domain ) . '</td>
				<td>' . $this->format_price( $meta['ims_ship_inter'] ) . '</td>
				<td class="subhead">&nbsp;</td>
				<td class="ims-size">' . ( empty($this->opts['disablebw']) ? __( 'Black & White', $this->domain ) : '' ) . '&nbsp;</td>
				<td>' . ( empty($this->opts['disablebw']) ? $this->format_price( $meta['ims_bw'] ) : '' ) . '&nbsp;</td>
			</tr>';

//$output .=	apply_filters( 'ims_pricelist_page', $this->pricelist_id );

$output .= '</tfoot></table>';