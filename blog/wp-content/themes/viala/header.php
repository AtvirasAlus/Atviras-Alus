<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<title><?php bloginfo('name'); ?> <?php wp_title(); ?></title>
<meta name="generator" content="WordPress <?php bloginfo('version'); ?>" /> <!-- leave this for stats -->
<meta name="keywords" content="<?php bloginfo('description'); ?>" />
<meta name="description" content="<?php bloginfo('description'); ?>" />
<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
<!--[if IE]>
<style type="text/css">
@import url("<?php bloginfo('template_directory'); ?>/ie.css");
</style>
<![endif]-->
<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> <?php _e('RSS Feed','ml');?>" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<?php
global $page_sort;
	if(get_settings('viala_sortpages')!='')
	{
		$page_sort = 'sort_column='. get_settings('viala_sortpages');
	}
	global $pages_to_exclude;

	if(get_settings('viala_excludepages')!='')
	{
		$pages_to_exclude = 'exclude='. get_settings('viala_excludepages');
	}
?>
<?php wp_head(); ?>
</head>
<body id="viala">


<div id="nav">
	<div class="wrap">

	<div id="header"><h1><a href="<?php bloginfo('siteurl');?>/" title="<?php bloginfo('name');?>"><?php bloginfo('name');?></a></h1><p id="desc"><?php bloginfo('description');?></p>
	</div>

	<div id="menu">
	<ul>
	<li <?php if(is_home()){echo 'class="current_page_item"';}?>><a href="<?php bloginfo('siteurl'); ?>/" title="<?php _e('Home','ml');?>"><?php _e('Home','ml');?></a></li>
	<?php wp_list_pages('title_li=&depth=1&'.$page_sort.'&'.$pages_to_exclude)?>
	<li><a class="feed" href="<?php bloginfo('rss2_url'); ?>"><?php _e('RSS','ml');?></a></li>
	</ul>
	<form method="get" id="searchform" action="<?php bloginfo('home'); ?>"><input type="text" class="textbox" value="<?php _e('Search','ml');?>..." name="s" id="s" onClick="this.value=''"/><input type="submit" id="searchsubmit" value="<?php _e('Search','ml');?>" /></form>
	</div><!-- menu -->

	</div><!-- wrap -->
</div><!-- end id:navigation -->

