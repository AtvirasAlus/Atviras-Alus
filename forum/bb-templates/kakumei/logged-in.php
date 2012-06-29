<?php
$uid = (int)bb_get_current_user_info( 'id' );
$sql = "SELECT * FROM mail_users WHERE user_id='".$uid."' AND mail_read='0'";
$result = mysql_query($sql) or die(mysql_error());
$msgs = mysql_num_rows($result);
$sql = "SELECT beta_tester FROM users_attributes WHERE user_id='".$uid."'";
$result = mysql_query($sql) or die(mysql_error());
$beta_tester = mysql_fetch_assoc($result);
$beta_tester = $beta_tester['beta_tester'];
if ($beta_tester == "1"){
	?>
	<div class="new_user_info_block">
		<div class="new_user_info_block_username">
			<a href="/brewers/<?=$uid?>"><?=bb_get_current_user_info( 'name' )?></a>
		</div>
		<div class="new_user_info_block_mail">
			<a href="/mail/inbox" title="Paštas">
				<?php
				if ($mail_count > 0){
					?>
					<span><?=$msgs?></span>
					<?php
				}
				?>
			</a>
		</div>
		<div class="new_user_info_block_recipes" title="Mano receptai">
			<a href="/brewer/recipes"></a>
		</div>
		<div class="new_user_info_block_sessions" title="Mano virimai">
			<a href="/brew-session/brewer"></a>
		</div>
		<div class="new_user_info_block_favorites" title="Mėgstamiausi receptai">
			<a href="/brewer/favorites"></a>
		</div>
		<div class="new_user_info_block_settings" title="Paskyros nustatymai">
			<a href="/brewer/profile"></a>
		</div>
		<div class="new_user_info_block_logout" title="Atsijungti">
			<a href="/auth/logout"></a>
		</div>
		<div class="clear"></div>
	</div>
	<?php
} else{
	?>
	<div id="user_info" style="display:block;">
		<div id="user_info_label">Prisijungęs:</div>
		<div id="user_info_name">
			<span><?=bb_get_current_user_info( 'name' )?></span>
			<a rel="nofollow" href="/mail/inbox" id="mail_counter"><?=$msgs;?></a>
		</div>
		<div class="clear"></div>
		<ul style="list-style-type:none;" id="user_info_submenu"> 
			<li><a href="/brewer/recipes" rel="nofollow">Receptai</a></li> 
			<li><a href="/brewer/favorites" rel="nofollow">Mėgstamiausi receptai</a></li> 
			<li><a href="/mail/inbox" rel="nofollow">Paštas</a></li> 
			<li><a href="/brew-session/brewer" rel="nofollow">Virimų istorija</a></li>
			<li><a href="/brewer/profile" rel="nofollow">Paskyra</a></li>
			<li><a href="/auth/logout" rel="nofollow">Atsijungti</a></li> 
		</ul>
	</div>
	<?php
}
?>