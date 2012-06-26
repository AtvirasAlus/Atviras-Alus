<?php
$_head_profile_attr = '';
if (bb_is_profile()) {
	global $self;
	if (!$self) {
		$_head_profile_attr = ' profile="http://www.w3.org/2006/03/hcard"';
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"<?php bb_language_attributes('1.1'); ?>>
	<head<?php echo $_head_profile_attr; ?>>
		<meta name="description" content="Atviras alus - naminio alaus aludarių bendruomenė. Alaus receptai. Stiliaus aprašymai. Alaus skaičiuoklė" />
		<meta name="alexaVerifyID" content="flj8CxyyfEenq5UbyH5vpBlz7DA" />
		<meta name="y_key" content="9421766f5e8b266a" />
		<meta name="google-site-verification" content="Q15UyxPCIq_du9gHO4mr1INQh_KzSx8YaB6XytiaO4M" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<script type="text/javascript" src="/public/js/jquery-1.4.4.min.js"></script>
		<script type="text/javascript" src="/public/ui/ui/jquery-ui-1.8.7.custom.js"></script>
		<link type="text/css" href="/public/css/pepper-grinder/jquery-ui-1.8.7.custom.css" rel="stylesheet" />	
		<link type="text/css" href="/public/css/style.css" rel="stylesheet" media="all" />
		<link type="text/css" href="/public/css/stylenew.css" rel="stylesheet" media="all" />
		<link type="text/css" href="/public/css/forum.css" rel="stylesheet" media="all" />
		<link href="/public/css/userScreen.css" media="screen, projection" rel="stylesheet" type="text/css">
		<script type="text/javascript" src="/public/ui/ui/jquery.ui.widget.js"></script>
		<script type="text/javascript" src="/public/ui/ui/jquery.ui.dialog.js"></script>
		<script type="text/javascript" src="/public/ui/ui/jquery.ui.position.js"></script>
		<script type="text/javascript" src="/public/js/website.js"></script>
		<script type="text/javascript" src="https://apis.google.com/js/plusone.js">
			{lang: 'lt'}
		</script>
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<title><?php bb_title() ?></title>
		<?php bb_feed_head(); ?>
		<?php bb_head(); ?>
	</head>
	<body id="<?php bb_location(); ?>">
		<div class="new_header">
			<div class="new_header_contrainer">
				<div class="new_proverb">
					<?php
					$sql = "SELECT * FROM beer_patarles ORDER BY RAND() LIMIT 1";
					$result = mysql_query($sql) or die(mysql_error());
					$row = mysql_fetch_assoc($result);
					echo $row['patarle_text'];
					?>
				</div>
				<div class="new_userinfo"><?php if (!in_array(bb_get_location(), array('login-page', 'register-page'))) login_form(); ?></div>
				<div class="clear"></div>
			</div>
		</div>
		<div class="body_center">
			<div id="main_container">
				<div id="left">
					<a href="/" class="new_logo" title="Atviras alus"></a>
					<?php
					$user_id = (int)bb_get_current_user_info( 'id' );
					if (isset($user_id) && !empty($user_id)){
						$sql = "SELECT COUNT(mail_id) as kiekis FROM mail_users WHERE user_id='".$user_id."' AND mail_read='0'";
						$result = mysql_query($sql) or die(mysql_error());
						while($row = mysql_fetch_assoc($result)){
							$mail_count = $row['kiekis'];
						}
					} else {
						$mail_count = 0;
					}
					?>
					<div id="new_menu">
						<ul id="new_topmenu">
							<li>
								<a href="/">Titulinis</a>
							</li>
							<li>
								<a href="/mail/inbox" class="">Paštas</a>
							</li>
							<li>
								<a href="/styles" class="">Alaus stiliai</a>
							</li>
							<li>
								<a href="/recipes" class="">Receptai</a>
							</li>
							<li>
								<a href="/brew-session/history" class="">Virimai</a>
							</li>
							<li>
								<a href="/index/calculus" class="">Skaičiuoklės</a>
							</li>
							<li>
								<a href="/forum" class="active">Forumas</a>
							</li>
							<li>
								<a href="/brewer/list" class="">Aludariai</a>
							</li>
							<li>
								<a href="/skaitykla" class="">Skaitykla</a>
							</li>
							<li>
								<a href="/ivykiai" class="">Įvykiai</a>
							</li>
							<li>
								<a href="/idejos/naujausios" class="">Idėjos</a>
							</li>
							<li>
								<a href="/content/about" class="">Atviras Alus</a>
							</li>
						</ul>
					</div>
					<div class="banner">
						<a href="http://tikrasalus.lt" target="_blank">
							<img src="/public/images/TAD.png" width="120" border="0"/>
						</a>
					</div>
					<div class="banner">
						<a href="http://savasalus.lt" target="_blank">
							<img src="/public/images/savasalus.jpg" width="120" border="0"/>
						</a>
					</div>
					<div class="banner">
						<a href="http://www.akl.lt" target="_blank">
							<img src="/public/images/akl.jpg" width="120" border="0"/>
						</a>
					</div>
				</div>
				<div id="main_content">