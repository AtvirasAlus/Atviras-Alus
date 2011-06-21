jQuery(document).ready(function($){
	
	//tabs default state
	$('.ims-box').hide();
	$('.ims-box').eq(0).show();
	$('.ims-tabs li').eq(0).addClass('current');
	
	//tabs actions
	$('.ims-tabs li').click(function(){
		$('#message').remove();
		$('.ims-box').hide();
		$('.ims-tabs li').removeClass('current');
		$('.ims-box').eq($('.ims-tabs li').index($(this))).show();
		$(this).addClass('current');
		return false;
	});
	
	//tabs select
	if(hash = window.location.hash){
		$('.ims-box').hide();
		$('.ims-tabs li').removeClass('current');
		index = $('.ims-tabs li a').index($('a[href|=' + hash + ']'));
		$('.ims-tabs li').eq(index).addClass('current');
		$(hash).show();
		$('html,body').animate({scrollTop:'0px'});
	};
	
	//permissions:user dropdown
	$('#ims_user').change(function(){
		if($(this).val() > 0){
			window.location.hash = 'caps_settings';
			window.location.search = 'post_type=ims_gallery&page=ims-settings&userid='+ $(this).val();
			window.location.href = window.location;
		}
	});
	
	//add watermark url
	window.add_watermark_url = function(image){
		jQuery('#watermarkurl').val(image);
		tb_remove();
	};
	
	//open thickbox
	$('#addwatermarkurl').live('click',function(){
		win = window.dialogArguments || opener || parent || top;
		tb_show('Attach File','media-upload.php?imstore=1&TB_iframe=true'); 				 
	});
	
	//add watermark url
	window.add_watermark_url = function(image){
		jQuery('#watermarkurl').val(image);
		tb_remove();
	};
	
	//add downloadable image size
	$('#addimagesize').click(function(){
		var count = $('.image-size').length;
		var clas = (count % 2) ?'':' alternate';
		var row = '<tr class="t image-size'+clas+'"><td scope="row">';
		row += '<input type="checkbox" name="imgid_'+count+'" class="inputmd" /></td>';
 		row += '<td><input type="text" name="imagesize_'+count+'[name]" class="inputmd" /></td>';
		row += '<td><label><input type="text" name="imagesize_'+count+'[w]" class="inputsm" /></label></td>';
		row += '<td><label><input type="text" name="imagesize_'+count+'[h]" class="inputsm" /></label></td>';
		row += '<td><label><input type="text" name="imagesize_'+count+'[q]" class="inputsm" />(%)</label></td>';
		row += '<td>'+ imslocal.pixels +'</td>';
		row += '<td>&nbsp;</td></tr>';
		$('.ims-image-sizes').before(row);	
		return false;
	});
	
	//add image size
	$(".add-image-size").click(function(){
		counter = $(this).parents('.postbox').find(".copyrow .name").val();
		row = $(this).parents('.postbox').find(".copyrow").clone().removeClass('copyrow');
		row.find('.name').attr('name','sizes['+counter+'][name]').removeAttr('value');
		row.find('.price').attr('name','sizes['+counter+'][price]');
		row.find('.unit').attr('name','sizes['+counter+'][unit]');
		$(this).parents('.postbox').find(".addrow").before(row);
		$(this).parents('.postbox').find(".copyrow .name").val(parseInt(counter)+1);
		return false;
	});
	
	/********DRAG/DROP/SORT**********/
	
	$(".price-list tbody").sortable({
		//revert:true,
		cursor:'move',
		handle:'.move',
		placeholder:'widget-placeholder',
		stop:function(event,ui){
			$("tr.filler").hide();
			ui.item.attr('class','alternate size').removeAttr('style'); 
		}
	});
	
	$("#price-list .sizes-list .size,#price-list .package-list .size").draggable({
		helper:'clone',
		revert:'invalid',
		handle:'.move',
		connectToSortable:'.price-list tbody'
	});
	
	$("#packages .package-list tbody").sortable({
		cursor:'move',
		handle:'.move',
		placeholder:'widget-placeholder',
		stop:function(event,ui){
			$("tr.filler").hide();
			ui.item.find('.price').remove();
			ui.item.attr('class','alternate size').removeAttr('style'); 
		}
	});
	
	var i = parseInt($('input.sort_count').val());
	$("table.sort-images tbody").sortable({
		axis:'y',
		cursor:'move',
		helper:'clone',
		placeholder:'widget-placeholder',
		update:function(){ 
			$(this).find('tr').each(function(){
				$(this).find('.column-imorder input').val(i++);
				if((i%2) != 0) $(this).removeClass('alternate').addClass('alternate');
				else $(this).removeClass('alternate');
			});
			i = parseInt($('input.sort_count').val())
		} 
	});
	
	$("table.sort-images tbody tr").disableSelection();	
	$("#packages .sizes-list .size").draggable({
		helper:'clone',
		revert:'invalid',
		handle:'.move',
		connectToSortable:'.package-list tbody'
	});

	$("tr.size").disableSelection();
	$('td.x').live('click',function(){
			if($(this).parent().parent().find('tr.size').length <= 1) 
				$('tr.filler').show();
			$(this).parent().remove();
		}
	);
	
	/********WIDGETS**********/
	
	//default state
	$('.show-free').hide();
	$('tbody.content').hide();
	$('tbody.content').hide();
	$('tfoot.content').hide();
	$('.show-download').hide();
	
	//show/hide widget list content
	$('.itemtop a').toggle(
		function(){ 
			$(this).html('[-]'); 
			index = $('.itemtop a').index($(this));
			$('tbody.content').eq(index).show();
			$('tfoot.content').eq(index).show();
			if($('tbody.content').eq(index).find('tr.size').length <= 0)
				$('tr.filler').eq(index).show();
			else $('tr.filler').eq(index).hide();
			$(".price-list tbody").sortable("refresh");
			$(".package-list tbody").sortable("refresh");
		},	
		function(){ 
			$(this).html('[+]');
			index = $('.itemtop a').index($(this));
			$('tbody.content').eq(index).hide();
			$('tfoot.content').eq(index).hide();
			$('tr.filler').eq(index).hide();
			$(".price-list tbody").sortable("refresh");
			$(".package-list tbody").sortable("refresh");
		}
	);
	
	//trash pricelist
	$("#price-list .trash").click(function(){
		del = confirm(imslocal.deletelist);
		if(del){
			id = $(this).parent().find('.listid').val();
			$.get(imslocal.imsajax,{ 	
				action		:'deletelist',
				postid		:id,
				_wpnonce	:imslocal.nonceajax
			},function(){$('#ims-list-'+id).remove() });
			
		}
		return false;
	});
	
	//trash package
	$("#packages .trash").click(function(){
		del = confirm(imslocal.deletepackage);
		if(del){
			id = $(this).parent().find('.packageid').val();
			$.get(imslocal.imsajax,{ 	
				action:'deletepackage',
				packageid:id,
				_wpnonce:imslocal.nonceajax
			},function(){$('#package-list-'+id).remove()});
			
		}
		return false;
	});
	
	//promotions 
	$("#promo_type").change(function(){
		if($(this).val() == 3) $('input[name="discount"]').attr({disabled:"disabled"});
		else $('input[name="discount"]').removeAttr('disabled');
	});
	
	/********IMAGE UPLOAD **********/
	
	//set up
	var colspan = parseInt($('.metabox-prefs').eq(1).find('input:checked').length)+1;
	$('#custom-queue').attr({colspan:colspan});
	if(imslocal.hiddengal != '.column-') $(imslocal.hiddengal).hide();
	
	//remove message
	function ims_file_selected(){
		$('#message').remove();
		folder = 'wp-content/'+$('#_ims_folder_path').val();
		$('#imagefiles').uploadifySettings('folder',folder);
	};
	
	// run every time a file is uploaded
	function ims_file_uploaded(event,ID,fileObj,response,data){
		if(response == 'x'){
			$('<tr><td colspan="'+colspan+'" class"exists">'+imslocal.exists+' <a href="#" class="imdelete">'+imslocal.remove+'</a></td></tr>')
			.prependTo('#ims_images_box .ims-table tbody:eq(1)');
		}else{
			$.get(imslocal.imsajax,{ 
				filepath	:response,
				imagename 	:fileObj.name,
				action		:'flashimagedata',
				galleryid	:$('#post_ID').val(),
				_wpnonce	:imslocal.nonceajax
			},function(data){$(data).prependTo('#ims_images_box .ims-table tbody:eq(1)')});
		}
	};

	//delete fail image load
	$('a.imdelete').live('click',function(){
		$(this).parents('tr').remove();
		return false;
	});
	
	//trash image
	$('a.imstrash').live('click',function(){
		$(this).parents('tr').addClass('totrash').fadeIn().fadeOut();
		$.get(imslocal.imsajax,{ 
				action		:'editimstatus',
				_wpnonce	:imslocal.nonceajax,
				status		:$(this).attr('rel'),
				imgid		:$(this).attr('href').replace('#','')
		});
		
		if($('li.statustrash').length < 1)
		$('<li class="statustrash"> | <a href="'+document.location.href+'&status=trash">'+imslocal.trash
		 +' <span class="count">(<em>1</em>)</span></a></li>').appendTo('.subsubsub');
		else $('li.statustrash a em').html(parseInt($('li.statustrash a em').text())+1);
		$('li.statuspublish a em').html(parseInt($('li.statuspublish a em').text())-1);
		return false;
	});
	
	//restore image
	$('a.imsrestore').live('click',function(){
		$(this).parents('tr').addClass('restore').fadeIn().fadeOut();
		$.get(imslocal.imsajax,{ 
				action		:'editimstatus',
				_wpnonce	:imslocal.nonceajax,
				status		:$(this).attr('rel'),
				imgid		:$(this).attr('href').replace('#','')
		});
		
		if($('li.statuspublish').length < 1)
		$('<li class="statuspublish"><a href="'+document.location.href+'">'+imslocal.publish
		 +' <span class="count">(<em>1</em>)</span></a> | </li>').prependTo('.subsubsub');
		else $('li.statuspublish a em').html(parseInt($('li.statuspublish a em').text())+1);
		
		$('li.statustrash a em').html(parseInt($('li.statustrash a em').text())-1);
		return false;
	});
	
	//delete image
	$('a.imsdelete').live('click',function(){
		$(this).parents('tr').addClass('totrash').fadeIn().fadeOut();
		$.get(imslocal.imsajax,{ 	
				action		:'deleteimage',
				deletefile	:imslocal.deletefile,
				postid		:$(this).attr('href').replace('#',''),
				_wpnonce	:imslocal.nonceajax
		});
		$('li.statustrash a em').html(parseInt($('li.statustrash a em').text())-1);
		return false;	
	});
	
	//update image
	$('a.imsupdate').live('click',function(){
		$(this).parents('tr').addClass('doupdate')
		.fadeOut(function(){$(this).removeClass('doupdate')}).fadeIn();
		$.get(imslocal.imsajax,{ 	
				action		:'upadateimage',
				_wpnonce	:imslocal.nonceajax,
				imgtid		:$(this).attr('href').replace('#',''),
				caption		:$(this).parents('tr').find('textarea').val(),
				order		:$(this).parents('tr').find('.column-imorder input').val(),
				imgtitle	:$(this).parents('tr').find('.column-imtitle input').val()
		});
		return false;	
	});
	
	//change colspan
	$('.metabox-prefs input').click(function(){
		colspan = $('.metabox-prefs').eq(1).find('input:checked').length;								 
		$('#custom-queue').attr({colspan:parseInt(colspan)+1});
	});
	
	if($('#disableflash').length > 0){
		$("#imagefiles").uploadify({
			'multi' 		 :true,
			'auto'     		 :true,
			'queueID'		 :'custom-queue',			  
			'buttonText'	 :imslocal.flastxt,
			'uploader' 		 :imslocal.imsurl + '_swf/uploadify.swf',
			'script' 		 :imslocal.imsurl + 'admin/swfupload.php',
			'scriptData' 	 :{'_wpnonce':imslocal.nonceajax,'userid':imslocal.userid,'domain':document.domain},
			'cancelImg' 	 :imslocal.imsurl + '_img/xit.gif',
			'height'		 :'26',
			'width'			 :'118',
			'buttonTextColor':'#333333',
			'fileExt'		 :'*.jpg;*.jpeg;*.gif;*.png',
			'fileDesc'		 :'Image files',
			'onSelect'	 	 :ims_file_selected,
			'onComplete'	 :ims_file_uploaded
		});
	};
	
	/********DATE PICKERS **********/
	$("#date").datepicker({ altField:'#post_date',altFormat:'yy-m-d',dateFormat:imslocal.dateformat });
	$("#imsexpire").datepicker({ altField:'#_ims_expire',altFormat:'yy-m-d',dateFormat:imslocal.dateformat });
	
	$("#starts").datepicker({ altField:'#start_date',altFormat:'yy-m-d',dateFormat:imslocal.dateformat });
	$("#expires").datepicker({ altField:'#expiration_date',altFormat:'yy-m-d',dateFormat:imslocal.dateformat });
	
		
	$('.post-php .tablenav-pages a').unbind('click');
	$('.post-php .tablenav-pages a').click(function(){ return true });
	
});