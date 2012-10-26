<?php 
header( "Content-type: text/css" );
$url = isset( $_REQUEST['url'] ) ? rtrim( $_REQUEST['url'] ) : '';
?>

/*
 The following fixes png-transparency for IE6. 
 It is also necessary for png-transparency in IE7 & IE8 to avoid 'black halos' with the fade transition
 
 Since this method does not support CSS background-positioning, it is incompatible with CSS sprites.
 Colorbox preloads navigation hover classes to account for this.
 
 !! Important Note: AlphaImageLoader src paths are relative to the HTML document,
 while regular CSS background images are relative to the CSS document.
*/
.cboxIE div#cboxTopLeft { background:transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true,sizingMethod='scale',src=<?php echo $url?>/_img/borderTopLeft.png);}
.cboxIE div#cboxTopCenter { background:transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true,sizingMethod='scale',src=<?php echo $url?>/_img/borderTopCenter.png);}
.cboxIE div#cboxTopRight { background:transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true,sizingMethod='scale',src=<?php echo $url?>/_img/borderTopRight.png);}
.cboxIE div#cboxBottomLeft { background:transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true,sizingMethod='scale',src=<?php echo $url?>/_img/borderBottomLeft.png);}
.cboxIE div#cboxBottomCenter { background:transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true,sizingMethod='scale',src=<?php echo $url?>/_img/borderBottomCenter.png);}
.cboxIE div#cboxBottomRight { background:transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true,sizingMethod='scale',src=<?php echo $url?>/_img/borderBottomRight.png);}
.cboxIE div#cboxMiddleLeft { background:transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true,sizingMethod='scale',src=<?php echo $url?>/_img/borderMiddleLeft.png);}
.cboxIE div#cboxMiddleRight { background:transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true,sizingMethod='scale',src=<?php echo $url?>/_img/borderMiddleRight.png);}
div#ims-mainbox div#ims-slideshow img{ max-width:340px; border:none; padding:0; margin:0}
div.ims-slideshow-box .ims-slideshow{ padding:5px 0 0 0}
