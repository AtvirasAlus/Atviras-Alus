<p class="login">
	<?php printf(__('Welcome, %1$s'), bb_get_profile_link(bb_get_current_user_info( 'name' )));?>
	<?php bb_admin_link( 'before= | ' );?>
	| <a href="../auth/logout">Atsijungti</a>
</p>
