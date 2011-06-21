<form class="login" method="post" id="login-form" action="<?php bb_uri( 'bb-login.php', null, BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_USER_FORMS ); ?>" onsubmit="login();return false">
	<p>
		<?php
	print (
		__( '<a href="../auth/register">Registruotis</a> ...  <a href="../auth/remember">ot velnias, pamiršau slaptažodį?</a>' )
		//bb_get_uri( 'register.php', null, BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_USER_FORMS ),
		//bb_get_uri( 'bb-login.php', null, BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_USER_FORMS )
	);
	?>

	</p>
	<div>
		<label>
			<?php _e('Username'); ?><br />
			<input name="user_email" type="text" id="user_email" size="13" maxlength="40" value="<?php if (!is_bool($user_login)) echo $user_login; ?>" tabindex="1" />
		</label>
		<label>
			<?php _e( 'Password' ); ?><br />
			<input name="user_password" type="password" id="user_password" size="13" maxlength="40" tabindex="2" />
		</label>
		<input name="re" type="hidden" value="<?php echo $re; ?>" />
		<?php wp_referer_field(); ?>

		<input type="submit" name="Submit" class="submit" value="<?php echo esc_attr__( 'Log in &raquo;' ); ?>" tabindex="4" />
	</div>
	<!--<div class="remember">
		<label>
			<input name="remember" type="checkbox" id="quick_remember" value="1" tabindex="3"<?php echo $remember_checked; ?> />
			<?php _e('Remember me'); ?>

		</label>
	</div>-->
</form>
<script>
function login() {

	var formvals= {user_email:$("#user_email").val(),user_password:$("#user_password").val()}
    $.ajax({
        type: 'POST',
        url: "../auth/login/",
        data: formvals,
        success: function (d) {
        	
            var data = jQuery.parseJSON(d);
            if (data) {
                if (data.status == 1) {
                    var _e = []
                    for (var i = 0; i < data.errors.length; i++) {
                        _e.push(data.errors[i].message);
                    }
               
                } else {
			
                    	    location.reload();
                    	

                }
            }
        },
        dataType: ""
    });
}
</script>
