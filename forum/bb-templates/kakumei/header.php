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
		<link type="text/css" href="/public/css/style.css?ver=<?=filemtime("../public/css/style.css")?>" rel="stylesheet" media="all" />
		<link type="text/css" href="/public/css/forum.css" rel="stylesheet" media="all" />
		<link href="/public/css/userScreen.css" media="screen, projection" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="/public/js/jquery.timer.js"></script>
		<script type="text/javascript" src="/public/ui/ui/jquery.ui.widget.js"></script>
		<script type="text/javascript" src="/public/ui/ui/jquery.ui.dialog.js"></script>
		<script type="text/javascript" src="/public/ui/ui/jquery.ui.position.js"></script>
		<script type="text/javascript" src="/public/js/website.js?ver=<?=filemtime("../public/js/website.js")?>"></script>
		<script type="text/javascript" src="https://apis.google.com/js/plusone.js">
			{lang: 'lt'}
		</script>
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<title><?php bb_title() ?></title>
		<?php bb_feed_head(); ?>
		<?php bb_head(); ?>
	</head>
	<body id="<?php bb_location(); ?>">
		<div id="header_container">
			<a href="/" id="logo" title="Atviras alus"></a>
			<div id="top_container">
				<div id="top">
					<div id="proverb">
						<?php
						$sql = "SELECT * FROM beer_patarles ORDER BY RAND() LIMIT 1";
						$result = mysql_query($sql) or die(mysql_error());
						$row = mysql_fetch_assoc($result);
						echo $row['patarle_text'];
						?>
					</div>
					<div id="userinfo">
						<?php if (!in_array(bb_get_location(), array('login-page', 'register-page'))) login_form(); ?>
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
								if (bb_is_user_logged_in()){
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
								if (bb_is_user_logged_in()){
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
									<a href="/mieliu-bankas/siulo">
										Mielių bankas
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