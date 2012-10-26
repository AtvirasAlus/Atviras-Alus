<?
/*
Plugin Name: Atviro antraštė
Plugin URI: atvirasalus.lt
Description: Atvaizduoja AtvirasAlus.lt antraštę.
Author: ponasniekas
Version: 1.0
Author URI: http://atvirasalus.lt
*/
function atv_meta() {
 print '<meta name="alexaVerifyID" content="flj8CxyyfEenq5UbyH5vpBlz7DA" />';
	print '<META name="y_key" content="9421766f5e8b266a" />' ;
	print '<meta name="google-site-verification" content="Q15UyxPCIq_du9gHO4mr1INQh_KzSx8YaB6XytiaO4M" /> ';
	print '<!-- Google Analytics Tracking by Google Analyticator 6.2: http://ronaldheft.com/code/analyticator/ -->
<script type="text/javascript">
	var analyticsFileTypes = [""];
	var analyticsEventTracking = "enabled";
</script>
<script type="text/javascript">
	var _gaq = _gaq || [];
	_gaq.push(["_setAccount", "UA-21270974-1"]);
	_gaq.push(["_trackPageview"]);
	_gaq.push(["_trackPageLoadTime"]);

	(function() {
		var ga = document.createElement("script"); ga.type = "text/javascript"; ga.async = true;
		ga.src = ("https:" == document.location.protocol ? "https://ssl" : "http://www") + ".google-analytics.com/ga.js";
		var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ga, s);
	})();
</script>';
}
function atv_header() {
 
  print '<link type="text/css" href="'.WP_PLUGIN_URL.'/atv-header/atv-header.css" rel="stylesheet" />';
  print '<div id="atv-header"><span id="atv-header-title"><a href="http://atvirasalus.lt/blog">AtvirasAlus.lt aludarių dienoraščių tinklas</a></span><span id="atv-header-logo"><a href="http://atvirasalus.lt"><img src="http://www.atvirasalus.lt/public/images/logo.png" border="0" width="50"/></a></span></div>';
}
add_action('wp_footer', 'atv_header');
add_action('wp_head', 'atv_meta');
?>