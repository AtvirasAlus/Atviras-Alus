if ( typeof lightbox_path == "undefined" ) var lightbox_path = 'http://'+location.hostname+'/wp-content/plugins/lightbox-gallery/';
if ( typeof hs != "undefined" ) {
	if ( typeof graphicsDir != "undefined" ) hs.graphicsDir = graphicsDir;
	else  hs.graphicsDir = 'http://'+location.hostname+'/wp-content/plugins/lightbox-gallery/graphics/';
}

if ( typeof hs == "undefined" ) {
	jQuery(document).ready(function () {

// If you make images display slowly, use following two lines;
//	var i = 0;
//	showImg(i);

		if ( jQuery.fn.lightBox ) {
			jQuery('a[rel*=lightbox]').lightBox();
			jQuery('.gallery1 a').lightBox({captionPosition:'gallery'});
		} else if ( jQuery.fn.colorbox ) {
			jQuery('a[rel*=lightbox]').colorbox();
		}
		jQuery('.gallery a').tooltip({track:true, delay:0, showURL: false});

// Add these lines if you want to handle multiple galleries in one page.
// You need to add into a [gallery] shorttag. ex) [gallery class="gallery2"] 
//  jQuery('.gallery2 a').lightBox({captionPosition:'gallery'});
//  jQuery('.gallery3 a').lightBox({captionPosition:'gallery'});
	});

	function showImg(i){
		if(i == jQuery('img').length){
			return;
		}else{
			jQuery(jQuery('img')[i]).animate({opacity:'show'},"normal",function(){i++;showImg(i)});
		}
	}
}