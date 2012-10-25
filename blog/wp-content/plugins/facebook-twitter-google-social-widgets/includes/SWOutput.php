<?php
function sw_content_filter($content, $force = false) {
  $facebook = get_option('sw_facebook');
  $twitter = get_option('sw_twitter');
  $google = get_option('sw_google');
  $displayonposts = get_option('sw_displayonposts');
  $displayonpages = get_option('sw_displayonpages');
  $displayonhomepage = get_option('sw_displayonhomepage');
  $displayoncategories = get_option('sw_displayoncategories');
  $displayontags = get_option('sw_displayontags');
  $displayontaxonomies = get_option('sw_displayontaxonomies');
  $displayondates = get_option('sw_displayondates');
  $displayonauthors = get_option('sw_displayonauthors');
  $displayonsearches = get_option('sw_displayonsearches');
  $displayonattachments = get_option('sw_displayonattachments');
  $displayabovepost = get_option('sw_displayabovepost');
  $displaybelowpost = get_option('sw_displaybelowpost');
  $cssstyleall = get_option('sw_cssstyleall');
  $cssstylewidget = get_option('sw_cssstylewidget');
  if ($facebook == '') {
    $facebook = 1;
    $twitter = 2;
    $google = 3;
    update_option('sw_supportplugin', 1);
  }
  if ($displayonposts == '' &&
      $displayonpages == '' &&
      $displayonhomepage == '' &&
      $displayoncategories == '' &&
      $displayontags == '' &&
      $displayontaxonomies == '' &&
      $displayondates == '' &&
      $displayonauthors == '' &&
      $displayonsearches == '' &&
      $displayonattachments == '') {
    $displayonposts = 1;
    $displayonpages = 1;
  }
  if ($displayabovepost == '' && $displaybelowpost == '')
    $displaybelowpost = 1;

  if (!($force ||
        ($displayonposts && is_single()) ||
        ($displayonpages && is_page()) ||
        ($displayonhomepage && is_home()) ||
        ($displayoncategories && is_category()) ||
        ($displayontags && is_tag()) ||
        ($displayontaxonomies && is_tax()) ||
        ($displayondates && is_date()) ||
        ($displayonauthors && is_author()) ||
        ($displayonsearches && is_search()) ||
        ($displayonattachments && is_attachment())
       )
     )
    return $content;

  if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    $protocol = 'https';
  else
    $protocol = 'http';

  if ($facebook != 0) {
    $widgets[$facebook] =
      sprintf(
        '<div style="display:inline;%s"><iframe src="%s://www.facebook.com/plugins/like.php?href=%s&amp;send=false&amp;layout=button_count&amp;width=120&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:120px; height:21px;" allowTransparency="true"></iframe></div>',
        $cssstylewidget,
        $protocol,
        rawurlencode(get_permalink())
      );
  }

  if ($twitter != 0) {
    $widgets[$twitter] =
      sprintf(
        '<div style="display:inline;%s"><a href="%s://twitter.com/share?url=%s" class="twitter-share-button" data-count="horizontal">%s</a><script type="text/javascript" src="' . $protocol . '://platform.twitter.com/widgets.js"></script></div>',
        $cssstylewidget,
        $protocol,
        rawurlencode(get_permalink()),
        __("Tweet", SW_DEF_STRING)
      );
  }

  if ($google != 0) {
    $widgets[$google] =
      sprintf(
        '<div style="display:inline;%s"><g:plusone size="medium" href="%s"></g:plusone><script type="text/javascript">(function() { var po = document.createElement(\'script\'); po.type = \'text/javascript\'; po.async = true; po.src = \'https://apis.google.com/js/plusone.js\'; var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(po, s); })();</script></div>',
        $cssstylewidget,
        get_permalink()
      );
  }

  ksort($widgets);

  $sw_content = '<div style="' . $cssstyleall . '">';
  foreach ($widgets as $widget) {
    $sw_content .= $widget;
  }
  $sw_content .= '</div>';
  
  if ($displayabovepost == 1)
    $content = $sw_content . $content;
  if ($displaybelowpost == 1)
    $content .= $sw_content;
  
	return $content;
}
$contentfilterpriority = get_option('sw_contentfilterpriority');
if ($contentfilterpriority == '') $contentfilterpriority = 10;
add_filter('the_content', 'sw_content_filter', $contentfilterpriority);

function sw_footer() {
	$supportplugin = get_option('sw_supportplugin');

	if ($supportplugin) {
		$locale = get_locale();
		if (substr($locale, 0, 2) == 'de')
			$lang = 'de';
		else
			$lang = 'en';

		print('<p style="text-align:center;font-size:x-small;color:#808080;"><a style="font-weight:normal;color:#808080" href="http://www.ab-weblog.com/' . $lang . '/wordpress-plug-ins/social-widgets/" title="Facebook, Twitter &amp; Google+ Social Widgets" target="_blank">Social Widgets</a> powered by <a style="font-weight:normal;color:#808080" href="http://www.ab-weblog.com/' . $lang . '/" title="Software Developer Blog" target="_blank">AB-WebLog.com</a>.</p>');
	}
}
add_action('wp_footer', 'sw_footer');

/**
 * Returns the output of this plug-in for using the social widgets in your code.
 * 
 * @return string
 */
function get_the_social_widgets() {
	return sw_content_filter('', true);
}

/**
 * Directly sends the output of this plug-in to the browser.
 * 
 * @return string
 */
function the_social_widgets() {
	echo get_the_social_widgets();
}
?>