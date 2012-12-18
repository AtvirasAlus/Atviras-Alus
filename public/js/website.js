$.fn.displayError = function(errors) {
	var e_div = this.find('#error-content');
	if (e_div) {
		e_div.remove();
	}
	if (errors.length > 0) {
		var error = '<div class="ui-widget" id="error-content"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p >';
		for (var i = 0; i < errors.length; i++) {
			error += '<div>' + errors + '</div>';
		}
		error += '</p></div></div>';
		this.append(error);
	}
};
$.fn.serializeObject = function() {
	var o = {};
	var a = this.serializeArray();
	$.each(a, function () {
		if (o[this.name]) {
			if (!o[this.name].push) {
				o[this.name] = [o[this.name]];
			}
			o[this.name].push(this.value || '');
		} else {
			o[this.name] = this.value || '';
		}
	});
	return o;
};
function login() {
	var formvals = $('#login-form').serializeObject();
	$.ajax({
		type:'POST', 
		url:"/auth/login/", 
		data:formvals, 
		success:function (d) {
			var data = jQuery.parseJSON(d);
			if (data) {
				if (data.status == 1) {
					var _e = [];
					for (var i = 0; i < data.errors.length; i++) {
						_e.push(data.errors[i].message);
					}
					$('#login-form').displayError(_e);
				} else {
					//formvals.user_name= data.data.user_name
					if (window.location.href.indexOf("/calculus") > -1) {
						$("#login-dialog").dialog("destroy");
						$("#login-a").remove();
						$("#userScreen-u")[0].innerHTML = '<span>' + data.data.user_name + '</span>';
						$("#userScreen").css("display", "block");
					} else {
						location.reload();
					}
				}
			}
		}, 
		dataType:""
	});
}
function showLogin() {
	$("#login-dialog").dialog({
		disabled:false, 
		modal:true, 
		autoOpen:true
	});
	$("#login-dialog").css('visibility', 'visible');
	$('#login-form')[0].reset();
	$("#login-dialog").displayError([]);
}
$(document).ready(function () {
	$("#login-button").button();
	$("#login-button").bind('click', function (e) {
		login();
	});
	createUserMenu();

	$("#bugreport_button").click(function() {
		window.open('/tracker/bug_report_page.php', '_blank');
		return false;
	});
	var timer = $.timer(function(){
		$.ajax({
			url: "/index/ping",
			success: function(data){
				if (data != "0"){
					$(document).attr('title', 'Atviras alus ('+data+')');
				} else {
					$(document).attr('title', 'Atviras alus');
				}
			}
		});
	});
	timer.set({ time : 1000*30, autostart : true });
	$.ajax({
		url: "/index/pingstart",
		success: function(data){
			if (data != "0"){
				$(document).attr('title', 'Atviras alus ('+data+')');
			} else {
				$(document).attr('title', 'Atviras alus');
			}
		}
	});

	$("ul.topnav li a").click(function() {
		$(this).parent().find("ul.subnav").slideDown('fast').show();
	});
	$(document).mouseup(function (e) {
		if ($(e.target).parent("#user_info_name").length == 0) {
			$("ul.topnav li ul.subnav").hide();
		}
	});
});
function createUserMenu() {
	$("#user_info_name").click(function (e) {
		e.preventDefault();
		$("#user_info_submenu").css("position","absolute");
		$("#user_info_submenu").css("left",($("#user_info_name").offset().left+($("#user_info_name").width()-$("#user_info_submenu").width()-4)));
		$("#user_info_submenu").fadeToggle("fast", "linear");
		$("#user_info_name").toggleClass("menu-open");
	});
	$("#user_info_submenu").mouseup(function () {
		return false;
	});
	$(document).mouseup(function (e) {
		if ($(e.target).parent("#user_info_name").length == 0) {
			$("#user_info_submenu").css("position","");
			$("#user_info_name").removeClass("menu-open");
			$("#user_info_submenu").hide();
		}
	});
}
