<?php
if (!defined('DOKU_INC')) die();
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="lt" lang="lt">
	<head>
		<meta name="description" content="Atviras alus - naminio alaus aludarių bendruomenė. Alaus receptai. Stiliaus aprašymai. Alaus skaičiuoklė" />
		<meta name="alexaVerifyID" content="flj8CxyyfEenq5UbyH5vpBlz7DA" />
		<meta name="y_key" content="9421766f5e8b266a" />
		<meta name="google-site-verification" content="Q15UyxPCIq_du9gHO4mr1INQh_KzSx8YaB6XytiaO4M" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link type="text/css" href="/public/css/pepper-grinder/jquery-ui-1.8.7.custom.css" rel="stylesheet" />	
		<link type="text/css" href="/public/css/style.css?ver=<?=filemtime("../public/css/style.css")?>" rel="stylesheet" media="all" />
		<link type="text/css" href="/public/css/print.css" rel="stylesheet" media="print" />
		<link href="/public/css/userScreen.css" media="screen, projection" rel="stylesheet" type="text/css">
		<script type="text/javascript" src="https://apis.google.com/js/plusone.js">
			{lang: 'lt'}
		</script>
		<title>Vikis - <?php tpl_pagetitle()?> - Atviras Alus</title>
		<link type="text/css" href="lib/tpl/atvirasalus/design.css" rel="stylesheet" />	
		<link type="text/css" href="lib/tpl/atvirasalus/layout.css" rel="stylesheet" />	
		<link type="text/css" href="lib/tpl/atvirasalus/media.css" rel="stylesheet" />	
		<?php tpl_metaheaders()?>
		<script type="text/javascript" src="/public/js/jquery-1.4.4.min.js"></script>
		<script type="text/javascript" src="/public/ui/ui/jquery-ui-1.8.7.custom.js"></script>
		<script type="text/javascript" src="/public/ui/ui/jquery.ui.widget.js"></script>
		<script type="text/javascript" src="/public/ui/ui/jquery.ui.dialog.js"></script>
		<script type="text/javascript" src="/public/ui/ui/jquery.ui.position.js"></script>
	    <script type="text/javascript" src="/public/lightbox/js/jquery.lightbox-0.5.js"></script>
	    <script type="text/javascript" src="/public/js/jquery.simpletip-1.3.1.min.js"></script>
	    <script type="text/javascript" src="/public/js/jquery.timer.js"></script>
		<script type="text/javascript" src="/public/js/website.js?ver=<?=filemtime("../public/js/website.js")?>"></script>
		<style>
			#picker2 {
				background-color: #ffffff !important;
			}
			#picker2 button {
				display: inline-block;
				width: 14px;
				height: 14px;
				background-color: #ffffff;
			}
			#picker2 button:hover {
				background-color: #eee;
			}
		</style>
	</head>
	<body>
		<?php html_msgarea()?>
		<div id="header_container">
			<a href="/" id="logo" title="Atviras alus"></a>
			<div id="top_container">
				<div id="top">
					<div id="proverb">
						<?php
						include("conf/mysql.conf.php");
						$con = mysql_connect($conf['auth']['mysql']['server'], $conf['auth']['mysql']['user'], $conf['auth']['mysql']['password']);
						$db = mysql_select_db($conf['auth']['mysql']['database']);
						$result = mysql_query("SET NAMES UTF8");
						$sql = "SELECT * FROM beer_patarles ORDER BY RAND() LIMIT 1";
						$result = mysql_query($sql) or die(mysql_error());
						$row = mysql_fetch_assoc($result);
						echo $row['patarle_text'];
						?>
					</div>
					<div id="userinfo">
						<?php
						if (tpl_userinfo(true) != ""){
							$umail = tpl_usermail();
							$sql = "SELECT * FROM users WHERE user_email = '".$umail."'";
							$result = mysql_query($sql) or die(mysql_error());
							$uid = mysql_fetch_assoc($result);
							$uid = $uid['user_id'];
							$sql = "SELECT * FROM mail_users WHERE user_id='".$uid."' AND mail_read='0'";
							$result = mysql_query($sql) or die(mysql_error());
							$msgs = mysql_num_rows($result);
							?>
							<div id="user_info" style="display:block;">
								<div id="user_info_label">Prisijungęs:</div>
								<div id="user_info_name">
									<span><?php tpl_userinfo()?></span>
									<a rel="nofollow" href="/mail/inbox" id="mail_counter"><?=$msgs;?></a>
								</div>
								<div class="clear"></div>
								<ul style="list-style-type:none;" id="user_info_submenu"> 
									<li><a href="/brewer/recipes" rel="nofollow" accesskey="r">Receptai</a></li> 
									<li><a href="/storage" rel="nofollow">Atsargos</a></li> 
									<li><a href="/brewer/favorites" rel="nofollow">Mėgstamiausi receptai</a></li> 
									<li><a href="/mail/inbox" rel="nofollow" accesskey="p">Paštas</a></li> 
									<li><a href="/brew-session/brewer" rel="nofollow" accesskey="v">Virimų istorija</a></li>
									<li><a href="/maistas/mano" rel="nofollow">Patiekalai</a></li>
									<li><a href="/brewer/profile" rel="nofollow">Paskyra</a></li>
									<?php tpl_actionlink('admin', "<li>", "</li>")?>
									<li><a href="/auth/logout">Atsijungti</a></li> 
								</ul>
							</div>						
							<?php
						} else {
							?>
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
							<?php
						}
						?>
						
						
						<?php //tpl_actionlink('index')?>
					</div>
					<div class="clear"></div>
				</div>
				<div id="menu">
					<ul class="topnav">
						<li>
							<a href="javascript:void(0);" class="tria">
								Receptai								
							</a>
							<ul class="subnav" style="display: none; ">
								<li>
									<a href="/styles">
										Alaus stiliai													
									</a>
								</li>
								<li class="menuspacer"></li>
								<li>
									<a href="/recipes">
										Visi receptai													
									</a>
								</li>
								<li>
									<a href="/search">
										Receptų paieška													
									</a>
								</li>
								<li>
									<a href="/paieska">
										Paieška pagal parametrus
									</a>
								</li>
								<li>
									<a href="/comments">
										Komentarai													
									</a>
								</li>
								<li>
									<a href="/gallery">
										Atviro alaus galerija													
									</a>
								</li>
								<li>
									<a href="/recipes/special">
										Išskirtiniai receptai										
									</a>
								</li>
								<li class="menuspacer"></li>
								<li>
									<a href="/brew-session/history">
										Virimai													
									</a>
								</li>
								<?php
								if (tpl_userinfo(true) != ""){
									?>
									<li class="menuspacer"></li>
									<li>
										<a href="/brewer/recipes">
											Mano receptai													
										</a>
									</li>
									<li>
										<a href="/brewer/favorites">
											Mėgstamiausi receptai													
										</a>
									</li>
									<li>
										<a href="/brew-session/brewer">
											Mano virimai													
										</a>
									</li>
									<?php
								}
								?>
							</ul>								
						</li>
						<li>
							<a href="javascript:void(0);" class="tria">
								Skaičiuoklės								
							</a>
							<ul class="subnav" style="display: none; ">
								<li>
									<a href="/index/calculus">
										Receptų skaičiuoklė													
									</a>
								</li>
								<li>
									<a href="/calculus">
										Kitos skaičiuoklės													
									</a>
								</li>
							</ul>								
						</li>
						<li>
							<a href="javascript:void(0);" class="tria">
								Alus ir maistas								
							</a>
							<ul class="subnav" style="display: none; ">
								<li>
									<a href="/maistas/uzkandziai-prie-alaus">
										Užkandžiai prie alaus													
									</a>
								</li>
								<li>
									<a href="/maistas/pagrindiniai-patiekalai">
										Pagrindiniai patiekalai													
									</a>
								</li>
								<li>
									<a href="/maistas/receptai-su-alumi">
										Patiekalai iš alaus													
									</a>
								</li>
								<?php
								if (tpl_userinfo(true) != ""){
									?>
									<li class="menuspacer"></li>
									<li>
										<a href="/food/my">
											Mano receptai													
										</a>
									</li>
									<?php
								}
								?>
							</ul>								
						</li>
						<li>
							<a href="/forum">
								Forumas								
							</a>
						</li>
						<li>
							<a href="javascript:void(0);" class="tria">
								Skaitykla								
							</a>
							<ul class="subnav" style="display: none; ">
								<li>
									<a href="/wiki">
										Vikis
									</a>
								</li>
								<li>
									<a href="/tweet/all">
										Aludarių pranešimai													
									</a>
								</li>
								<li>
									<a href="/terminologija">
										Terminologija													
									</a>
								</li>
							</ul>								
						</li>
						<li>
							<a href="javascript:void(0);" class="tria">
								Bendruomenė								
							</a>
							<ul class="subnav" style="display: none; ">
								<li>
									<a href="/ivykiai">
										Įvykiai													
									</a>
								</li>
								<li class="menuspacer"></li>
								<li>
									<a href="/brewer/list">
										Aludariai													
									</a>
								</li>
								<li>
									<a href="/groups">
										Aludarių grupės													
									</a>
								</li>
								<li class="menuspacer"></li>
								<li>
									<a href="/content/about">
										Apie projektą													
									</a>
								</li>
								<li>
									<a href="/content/help">
										Pagalba													
									</a>
								</li>
								<li>
									<a href="/stats">
										Statistika													
									</a>
								</li>
								<li>
									<a href="/salygos">
										Naudojimosi sąlygos
									</a>
								</li>
								<li class="menuspacer"></li>
								<li>
									<a href="/turgus">
										Virtualus turgus													
									</a>
								</li>
								<li class="menuspacer"></li>
								<li>
									<a href="/idejos/naujausios">
										Idėjų bankas													
									</a>
								</li>
							</ul>								
						</li>
					</ul>
				</div>
			</div>
			<div class="clear"></div>
		</div>
		<div id="main_container">
			<div id="left">
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
				<div class="inner_container">
					<div class="inner_header"><?php tpl_pagetitle(); ?></div>
					<div class="wiki_bc">
							<?php tpl_youarehere()?>
						
					</div>						
					<div class="wiki_tools">
						<?php tpl_actionlink('edit')?>
				        <?php tpl_actionlink('history')?>
				        <?php tpl_actionlink('revert')?>
				        <?php tpl_actionlink('recent')?>
						<?php tpl_actionlink('subscribe')?>
						<?php tpl_actionlink('media')?>
						<?php tpl_actionlink('index')?>
				        <?php tpl_searchform()?>&#160;
						<div class="clear"></div>
					</div>
					<div class="dokuwiki">
						<?php tpl_content()?>
						<div class="clear"></div>
					</div>
				</div>
			</div>
			<div class="clear"></div>
		</div>
		<div id="bugreport_link"><input type="button" id="bugreport_button" class="ui-button" value="Praneškite apie svetainėje pastebėtas klaidas" /></div>
		<script type="text/javascript">
			var _gaq = _gaq || [];
			_gaq.push(['_setAccount', 'UA-21270974-1']);
			_gaq.push(['_trackPageview']);
			(function() {
				var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
				ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
				var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
			})();
		</script>
	</body>
</html>
