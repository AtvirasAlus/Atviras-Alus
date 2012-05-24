<?php 

/**
*Settings page
*
*@package Image Store
*@author Hafid Trujillo
*@copyright 20010-2012
*@since 0.5.0
*/

if( !current_user_can( 'ims_change_settings')) 
	die( );

//tab navigation
$settings_tabs = apply_filters( 'ims_settings_tabs', array(
	'general' 	=> __( 'General', $this->domain ),
	'gallery' 	=> __( 'Gallery', $this->domain ),
	'image' 	=> __( 'Image', $this->domain ),
	'slideshow' => __( 'Slideshow', $this->domain ),
	'payment' 	=> __( 'Payment', $this->domain ),
	'checkout' 	=> __( 'Checkout', $this->domain ),
	'permissions' => __( 'User permissions', $this->domain ),
	'reset' 	=> __( 'Reset', $this->domain ),
));

//unset store features if they are disable
if( isset($this->opts['disablestore'])){
	foreach(array( 'payment', 'checkout', ) as $name )
		unset( $settings_tabs[$name] );
}

//unset permission tab if user doesn't have access
if( !current_user_can( 'ims_change_permissions'))
	unset($settings_tabs['permissions']);

include( IMSTORE_ABSPATH . "/admin/settings-fields.php");
?>

<ul class="ims-tabs add-menu-item-tabs">
	<?php foreach( $settings_tabs as $name => $tab ):?>
	<li class="tabs"><a href="#<?php echo $name ?>"><?php echo $tab ?></a></li>
	<?php endforeach?>
</ul>

<?php foreach($settings_tabs as $boxid => $box ): ?>
<div id="<?php echo $boxid ?>" class="ims-box">
	<form method="post" class="<?php echo "$boxid-table" ?>" action="<?php echo $this->pageurl , '#', $boxid?>" >
		<?php if( isset($settings[$boxid]) && is_array($settings[$boxid])): //start setting ?>
			<table class="ims-table">
				<tbody>
				<?php 
				$css = '';
				foreach( $settings[$boxid] as $name => $row ){
					echo '<tr class="row-'.$name.$css.'">'	;
					if( isset($row['col']) ){
						foreach( (array)$row['opts'] as $id => $opt ){
							echo '<td scope="row" class="col"><label for="', $id , '">', $opt['label'] , '</label></td>';
							echo '<td class="col-fields">';
							
							if( $this->is_checkbox($opt['type']) ) 
								echo '<input type="', $opt['type'] , '" name="', $id , '" id="', $name , '" 
								value="'. esc_attr((isset($opt['val']) ? $opt['val'] : 0 )) , '"', checked( $opt['val'], $this->vr( $id ), 0 ), ' /> ';
							else 
								echo '<input type="', $opt['type'] , '" name="', $id , '" id="', $name , '" value="', esc_attr( ($val = $this->vr( $id ) ) ? $val : $opt['val'] ) , '" />';
							
							echo ( isset( $opt['desc'] ) ) ? '<small>'. $opt['desc'] . '</small>' : '';
							echo '</td>';
						}
					}elseif( isset($row['multi']) ){
						echo '<td scope="row" class="multi">' , $row['label'] , '</td>';
						echo '<td class="multi-fields">'; 
						foreach( (array)$row['opts'] as $id => $opt ){
							$user = ( isset($opt['user']) ) ? $opt['user'] : 0 ;
							echo '<label>';
							
							if( $this->is_checkbox($opt['type']) )
								echo ' <input type="', $opt['type'] , '" name="', $name , '[', $id , ']" 
								value="'. esc_attr((isset($opt['val']) ? $opt['val'] : 0 )) , '"', checked( $opt['val'], $this->vr( $name.$id, $user ), 0 ) , ' /> ' , $opt['label'] ;
							
							else echo $opt['label'] , ' <input type="', $opt['type'] , '" name="', $name , '[' , $id , ']" 
								id="', $name,$id, '" value="', esc_attr( ($val = $this->vr( $name.$id ) ) ? $val : $opt['val'] ) , '" />';
							
							echo ( isset( $opt['desc'] ) ) ? '<small>'. $opt['desc'] .'</small>' : '';
							
							echo '</label>';
						}
						echo '</td>'; 
					}else{
						echo '<td scope="row" class="row">', (( $row['type'] == 'empty') ? '&nbsp;' : '<label for="'. $name .'">'.$row['label'].'</label>') , '</td>';
						$unstall = ( $row['type'] == 'uninstall' ) ? ' form-invalid error' : '' ;
						echo '<td class="row-fields', $unstall , '">'; 
							switch($row['type']){
								case 'select':
									echo '<select name="', $name , '" id="', $name , '">';
									foreach( (array)$row['opts'] as $val => $opt )
										echo '<option value="', $val , '"', selected( $val, $this->vr($name) ) , '>', esc_html( $opt ) , '</option>';
									echo '</select>';
									break;
								case 'textarea':
									echo '<textarea name="', $name , '" id="'. $name , '" >', esc_html( $this->vr( $name ) ) , '</textarea>';
									break;
								case 'radio':
									foreach( (array)$row['opts'] as $val => $opt )
										echo '<label><input type="', $row['type'] , '" name="', $name , '" value="', $val , '"', checked( $val, $this->vr( $name ), 0 ) , ' /> ', $opt , '</label><br /> ';
									break;
								case 'checkbox':
									echo '<input type="', $row['type'] , '" name="'. $name , '" id="'. $name , '" value="'. $row['val'] , '"'. checked( $row['val'], $this->vr( $name ), 0 ) , ' /> ';
									break;
								case 'empty':
									echo '&nbsp;';
									break;
								case 'uninstall':
									echo ( isset( $row['desc'] ) ) ? $row['desc'] : ''; unset( $row['desc'] );
									echo '<p><input type="submit" name="', $name , '" id="', $name , '" value="', esc_attr( $row['val'] ) , '" /></p>';
									break;
								default:
								echo '<input type="', $row['type'] , '" name="', $name , '" id="', $name , '" value="', esc_attr( ($val = $this->vr( $name ) ) ? $val : $row['val'] ) , '" /> ';
							}
						echo ( isset( $row['desc'] ) ) ? '<small>' . $row['desc'] . '</small>' : '';
						echo '</td>'; 
					}
					echo '</tr>';
					$css = ($css == ' alternate') ? '' : ' alternate'; 
				}
				?>
				<?php do_action( 'ims_settings', $boxid) ?>
				<tr>
					<td scope="row">&nbsp;</td>
					<td class="submit">
						<input type="hidden" name="ims-action" value="<?php echo esc_attr( $boxid ) ?>" />
						<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save', $this->domain )?>" />
					</td>
				</tr>
				</tbody>
			</table>
		<?php endif?>
		<?php wp_nonce_field( 'ims_settings')?>
	</form>
</div><!-- #<?php echo $boxid ?> -->
<?php endforeach?>