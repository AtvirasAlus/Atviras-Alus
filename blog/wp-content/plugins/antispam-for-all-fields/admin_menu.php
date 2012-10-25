<form action="<?php echo admin_url($plugin->plugin_config_url); ?>" method="post">
<input type="hidden" name="action" value="afal_update"/>
<strong>Limits</strong>
<br/>
Lower, if amount of comments for same url, ip, OR author name in your comment spam folder exceeds this number, mark as for spam to approve later<br/>
<input type="text" name="limits[lower]" value="<?php echo $plugin->limits['lower']; ?>" />

<br/>
Upper, if amount of comments for same url, ip, OR author name in your comment spam folder exceeds this number, reject for spam<br/>
<input type="text" name="limits[upper]" value="<?php echo $plugin->limits['upper']; ?>" />


<br/>
Mark as spam if more then .. websites are in the comment<br/>
<input type="text" name="limits[numbersites]" value="<?php echo $plugin->limits['numbersites']; ?>" />

<br/><br/>
<strong>Notification</strong>
<br/>
If a comment is spammed or denied, mail me?<br/>
<input type="radio" name="mail[sent]" value="1" <?php if($plugin->mail['sent']) echo 'checked="checked"'; ?>/>Yes
<input type="radio" name="mail[sent]" value="0" <?php if(!$plugin->mail['sent']) echo 'checked="checked"'; ?>/>No

<br/>
Email to: (leave blank for default email <u><?php echo get_option('admin_email'); ?></u>)<br/>
<input type="text" name="mail[admin]" value="<?php echo $plugin->mail['admin']; ?>" />

<br/><br/>
<strong>Use external database service (stopforumspam.com)</strong>
<br/>
<input type="radio" name="api_stopforumspam" value="1" <?php if($plugin->api_stopforumspam == 1) echo 'checked="checked"'; ?>/>Yes
<input type="radio" name="api_stopforumspam" value="0" <?php if(!$plugin->api_stopforumspam == 1) echo 'checked="checked"'; ?>/>No

<br/><br/>
<strong>Spamwords</strong>

<br/>
Scan for these words (one word per line, start with * end with *)<br/>
This is an agressive list. Please delete or add any words your want.
<br/>
<textarea name="words" rows="15"><?php $i = 0; foreach($plugin->words as $word) { if($i > 0) {echo "\n";} echo $word; $i++;}; ?></textarea>

<input type="submit" name="button" value="Update" class="button-primary" />
</form>