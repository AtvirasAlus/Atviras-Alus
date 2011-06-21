jQuery(document).ready(function($){

	//hide boxes
	$('.ims-add-error').hide(); 
	$('#ims-pricelist').hide();
	//slected label
	slcttxt = $('.ims-image-count').html();
	
	//check all
	$('.ims-select-all a').click(function(){
		$(".ims-innerbox [type='checkbox']").attr('checked','checked');
		return false;
	});

	//uncheck all
	$('.ims-unselect-all a').click(function(){
		$(".ims-innerbox [type='checkbox']").removeAttr('checked');
		return false;
	});
	
	// image colorbox
	if(imstore.colorbox){
		$(".ims-gallery .ims-colorbox").colorbox({
			current:'',
			photo:true,
			maxWidth:"98%",
			maxHeight:'98%',
			speed: imstore.slideshowSpeed,
			next: imstore.nextLinkText,
			close: imstore.closeLinkText,
			previous: imstore.prevLinkText,
			title: function(){
				return ($(this).find('img').attr('title') == '') ? ' ' : $(this).find('img').attr('title');
			}
		})
	};
	
	//black and white preview
	$('#ims-color-bw').click(function(){
		color = ($(this).is(':checked'))? '&c=g': '';
		$('#ims-color-sepia').attr({checked:''});
		$('.image-wrapper img').animate({opacity:0},400,function(){
			$(this).attr({ src: $('.image-wrapper img').attr('src').replace('&c=g','').replace('&c=s','') + color})
			.delay(900/1.5).animate({opacity:1},700);
		});
	});
	
	//sepia preview
	$('#ims-color-sepia').click(function(){
		$('#ims-color-bw').attr({checked:''});
		color = ($(this).is(':checked'))? '&c=s': '';
		$('.image-wrapper img').animate({opacity:0},400,function(){
			$(this).attr({ src: $('.image-wrapper img').attr('src').replace('&c=g','').replace('&c=s','') + color})
			.delay(900/1.5).animate({opacity:1},700);
		});
	});
	
	// add to image to cart
	$(".add-images-to-cart a").colorbox({
		width:"75%",height: '280px',inline:true,href:"#ims-pricelist",
		onClosed: function(){ 
			$('.ims-add-error').hide();
			$('#ims-pricelist').hide(); 
		},
		onOpen:	function(){ 
			$('#ims-pricelist').show(); 
			count = $(".ims-innerbox input:checked").length;
			imgids = $(".ims-innerbox input:checked").map(function(){ return $(this).val(); }).get().join(',');
			$('#ims-to-cart-ids').val(imgids);
			$('.ims-image-count').html(count + ' ' + slcttxt); 
			if(count == 0) $('.ims-add-error').show();
		}
	});
	
	// add to cart box single slideshow
	$(".add-images-to-cart-single a").colorbox({
		width:"75%",height: '280px',inline:true,href:"#ims-pricelist",
		onClosed: function(){ 
			$('.ims-add-error').hide();
			$('#ims-pricelist').hide(); 
		},
		onOpen:	function(){ 
			$('#ims-pricelist').show(); 
			url = $('.image-wrapper img').attr('src').replace('&c=s','').replace('&c=g','').split('&img=');
			$('#ims-to-cart-ids').val(url[1]);
		}
	});
	
	//slideshow
	if($('#ims-thumbs').length > 0){
		var gallery = $('#ims-thumbs').galleriffic({
			preloadAhead:  		10,
			enableTopPager:  		true,
			enableBottomPager:		true,
			renderSSControls:		true,
			renderNavControls:		true,
			controlsContainerSel:	'#ims-player',
			captionContainerSel:	'#ims-caption',
			imageContainerSel:		'#ims-slideshow',
			numThumbs:  			parseInt(imstore.numThumbs),
			maxPagesToShow:  		parseInt(imstore.maxPagesToShow),
			playLinkText:			imstore.playLinkText,
  			pauseLinkText:			imstore.pauseLinkTex,
  			prevLinkText:			imstore.prevLinkText,
  			nextLinkText:			imstore.nextLinkText,
			delay:					parseInt(imstore.slideshowSpeed),
  			nextPageLinkText:		imstore.nextPageLinkText,
  			prevPageLinkText:		imstore.prevPageLinkText,
			autoStart:    imstore.autoStart,
			defaultTransitionDuration: parseInt(imstore.transitionTime),
			onSlideChange: function(prevIndex,nextIndex){
				$(".ims-slideshow-tools [type='checkbox']").removeAttr('checked');
			},
			onCreateImage: function(imageData){
				imageData.image.onload = '';
				imageData.image.src = imageData.image.src.replace('&c=g','').replace('&c=s','');
				return imageData;
			}
		});
	};
	
	// add to favorites
	$('.add-to-favorite a').click(function(){
		imgids = $(".ims-innerbox input:checked").map(function(){ return $(this).val(); }).get().join(',');
		$.get(imstore.imstoreurl+'ajax.php',{_wpnonce:imstore.ajaxnonce,galid:imstore.galid,action:"favorites",imgids:imgids},
		function(data){
			response = data.split('|');
			$('.ims-message').fadeOut().removeClass('ims-error').removeClass('ims-success').addClass(response[1]).html(response[0]).fadeIn();
		});
		return false;
	});
	
	// add a single image to favorites
	$('.add-to-favorite-single a').click(function(){
		url = $('.image-wrapper img').attr('src').replace('&c=s','').replace('&c=g','').split('&img=');
		$.get(imstore.imstoreurl+'ajax.php',{_wpnonce:imstore.ajaxnonce,galid:imstore.galid,action:"favorites",imgids:url[1]},
		function(data){
			response = data.split('|');
			$('.ims-message').fadeOut().removeClass('error').removeClass('success').addClass(response[1]).html(response[0]).fadeIn();
		});
		return false;
	});
	
	// remove from favorites
	$('.remove-from-favorite a').click(function(){
		imgids = $(".ims-innerbox input:checked").map(function(){ return $(this).val(); }).get().join(',');
		$(".ims-innerbox input:checked").each(function(){ $(this).parents('dt,li').remove(); }); 
		$.get(imstore.imstoreurl+'ajax.php',{_wpnonce:imstore.ajaxnonce,galid:imstore.galid,action:"remove-favorites",imgids:imgids},
		function(data){
			response = data.split('|');
			$('.ims-message').fadeOut().removeClass('error').removeClass('success').addClass(response[1]).html(response[0]).fadeIn();
		});
		return false;
	});

});