<div id="user-login-links">
		<a href="#"  onclick="showLogin()">prisijungti </a> / <a href="/auth/register" style="font-weight:bold">registruotis</a>
	</div>
<div id="login-dialog"  title="Prisijungti" style="display:none">
	<form id="login-form" onsubmit="return false">
		<dl>
			<dt>El. paštas:</dt>
			<dd><input type="text" name="user_email" /></dd>
			<div class="clear"></div>
		</dl>
		<dl>
			<dt>Slaptažodis:</dt>
			<dd><input type="password" name="user_password" /></dd>
			<div class="clear"></div>
		</dl>
		<dl>
			<dt>&nbsp;</dt>
			<dd><input type="checkbox" name="remember" id="remember"/><label for="remember">prisiminti mane:</label></dd>
			<div class="clear"></div>
		</dl>
		<dl style="padding: 0px; margin: 0px;">
			<dt style="padding: 0px; margin: 0px; padding-left: 30px;">&nbsp;</dt>
			<dd style="padding: 0px; margin: 0px;"><a href="/auth/remember">Pamiršau slaptažodį...</a></dd>
			<div class="clear"></div>
		</dl>
		<dl>
			<dt>&nbsp;</dt>
			<dd><button id="login-button">Jungtis</button></dd>
			<div class="clear"></div>
		</dl>
	</form>
</div>