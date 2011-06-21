<?php if(is_home()) echo '</div></div>';?>

<div id="body">
<div class="wrap">
  <div id="errorpost" class="nonet">	
	<div id="head"><div id="errorimage"></div>
	</div>
		
	<div class="posttitle">
		<h2><?php _e('Are you lost?','ml');?></h2>
	</div>
	<div class="entry">
		<p><?php _e('You may have mistyped a URL, or perhaps the link you clicked was out of date.','ml');?></p>
		<p><?php _e('Don\'t worry! You can get back on track.','ml');?></p>
<p><?php _e('Try doing a <strong>search</strong> or browsing through the <strong>Archives</strong>.','ml');?></p>
<p><?php _e('Or, if you think this is not your fault, use the <strong>contact form</strong> to let us know about a bug in the system.','ml');?></p>
	</div>

<?php
					//validate email adress
					function is_valid_email($email)
					{
  						return (eregi ("^([a-z0-9_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,4}$", $email));
					}
					function is_valid_user($answer)
					{
						global $cp_answer;
						if ($answer == $cp_answer) { return true; } else { return false;}
					}
					//clean up text
					function clean($text)
					{
						return stripslashes($text);
					}
					//encode special chars (in name and subject)
					function encodeMailHeader ($string, $charset = 'UTF-8')
					{
    					return sprintf ('=?%s?B?%s?=', strtoupper ($charset),base64_encode ($string));
					}

					$cp_name    = (!empty($_POST['cp_name']))    ? $_POST['cp_name']    : "";
					$cp_email   = (!empty($_POST['cp_email']))   ? $_POST['cp_email']   : "";
					$cp_url     = (!empty($_POST['cp_url']))     ? $_POST['cp_url']     : "";
					$cp_message = (!empty($_POST['cp_message'])) ? $_POST['cp_message'] : "";
					$cp_message = clean($cp_message);
					$error_msg = "";
					$send = 0;
					if (!empty($_POST['submit'])) {
						$send = 1;
						if (empty($cp_name) || empty($cp_email) || empty($cp_message)) {
							$error_msg.= "<p style='color:#ff5400'><strong>".__("Please fill in all required fields.",'ml')."</strong></p>\n";
							$send = 0;
						}
						if (!is_valid_email($cp_email)) {
							$error_msg.= "<p style='color:#ff5400'><strong>".__("Your email adress failed to validate.",'ml')."</strong></p>\n";
							$send = 0;
						}

					}
					if (!$send) { ?>

						<div class="contact-form">
							
							
							<?php echo $error_msg;?>
							<form method="post" action="<?php echo "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" id="contactform">
<h3><?php _e('Contact form:','ml');?></h3>


								<fieldset>
                  					<strong><?php _e('Name','ml');?></strong>*<br/>
									<input type="text" class="textbox" id="cp_name" name="cp_name" value="<?php echo $cp_name ;?>" /><br/><br/>
									<strong><?php _e('Email','ml');?></strong>*<br/>
									<input type="text" class="textbox" id="cp_email" name="cp_email" value="<?php echo $cp_email ;?>" /><br/><br/>

* <strong> - <?php _e('Required Fields','ml');?></strong>

</fieldset>									<fieldset class="message"><strong><?php _e('Message','ml');?></strong>*<br/>
									<textarea id="cp_message" name="cp_message" cols="100%" rows="4"><?php echo $cp_message ;?></textarea><br/>
									<input type="submit" id="submit" name="submit" value="<?php _e('Send Message','ml');?>" />
</fieldset>
								
							</form>
						</div>
					<?php
					} else {
						$displayName_array	= explode(" ",$cp_name);
						$displayName = htmlentities(utf8_decode($displayName_array[0]));

						$header  = "MIME-Version: 1.0\n";
						$header .= "Content-Type: text/plain; charset=\"utf-8\"\n";
						$header .= "From:" . encodeMailHeader($cp_name) . "<" . $cp_email . ">\n";
						$email_subject	= "[" . get_settings('blogname') . "] " . encodeMailHeader($cp_name);
						$email_text		= "From......: " . $cp_name . "\n" .
							  "Email.....: " . $cp_email . "\n" .
							  "Url.......: " . $cp_url . "\n\n" .
							  $cp_message;

						if (@mail(get_settings('admin_email'), $email_subject, $email_text, $header)) {
							echo "<div class='answer'><h3>" . __('Hey','ml') . " " . $displayName . ",</h3><p>".__('thanks for your message! I\'ll get back to you as soon as possible.','ml')."</p></div>";
						}
					}
					?>

  </div><!-- errorpost/nonet -->