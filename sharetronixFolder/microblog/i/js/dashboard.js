function new_activity_check()
{	
	$.post(siteurl+'ajax/new-activity-check/r:'+Math.round(Math.random()*1000), "tab="+encodeURIComponent(about_tab)+"&user_id="+encodeURIComponent(about_user_id)+"&last_post_id="+encodeURIComponent(last_post_id)+"&group_id="+encodeURIComponent(group_id), function(data) {
		var new_posts	= data.match(/^OK\:([0-9]+)/g);
		if( ! new_posts ) { return; }
		new_posts	= new_posts.toString().match(/([0-9]+)/);
		new_posts 	= parseInt(new_posts, 10);
		if( new_posts > 0 ){
			$('#loadnewactivity').html(new_posts+' NEW ACTIVITIES').show('slow');
		}else{
			$('#loadnewactivity').html('').hide();
		}
	});
	setTimeout( new_activity_check, 30000 );
}
function new_activity_show()
{	
	$.post(siteurl+'ajax/new-activity-show/r:'+Math.round(Math.random()*1000), "tab="+encodeURIComponent(about_tab)+"&user_id="+encodeURIComponent(about_user_id)+"&last_post_id="+encodeURIComponent(last_post_id)+"&group_id="+encodeURIComponent(group_id), function(data) {
		var get_last_id	= data.match(/^OK\:([0-9]+)\:/); //edit match here
		if( ! get_last_id ) { return; }
		get_last_id	= get_last_id.toString().match(/([0-9]+)/g); //edit
		get_last_id = parseInt(get_last_id, 10);
		
		if( get_last_id != last_post_id ){
			var currentTime = new Date()
			currenttime = currentTime.getFullYear() + '_' + currentTime.getMonth() + '_' + currentTime.getDate() + '_' + currentTime.getHours() + '_' + currentTime.getMinutes() + '_' + currentTime.getSeconds();
			
			$('#loading_posts').hide();
			jQuery('<div/>', {
				id: '"'+currenttime+'"',
				css: {  
					display: 'none',  
					overflow: 'visible'  
				}, 
			}).html(data.replace(/^OK\:([0-9]+)\:/, "")).insertAfter($('#insertAfter')).show('slow');
			last_post_id = get_last_id;	
		}
	});
	$('#loadnewactivity').hide();
	$('#loading_posts').css('display', 'inline');
}
function check_new_comments()
{	
	if( !$('#posts_html').length ){
		setTimeout(check_new_comments, 120000); //check after 2 minutes
		return;
	}	
	var match, post_ids = new Array();
	
	$('#posts_html>div').each(function(){
		if( this.id == 'insertAfter' ){
			return true;
		}
		match = this.id.toString().match(/^post_((public|private)_([0-9]+))/);
		
		if( $('#post_'+match[1]).length > 0 && $('#post_'+match[1]).hasClass('withcomments') ){
			postcomments_mark(match[1]);
			posts_synchronize_single(match[1]);
		}
	});
	setTimeout(check_new_comments, 30000); 
}
function load_more_results(div_id, current_results) {
	current_results	= parseInt(current_results, 10); 
	var url	= w.location.href.toString();
	if( ! url ) { return; }
	if( url.substr(0, siteurl.length) == siteurl ) {
		url	= url.substr(siteurl.length);
		if( url.indexOf("#") != -1 ) {
			url	= url.substr(0, url.indexOf("#"));
		}
		url	= siteurl+url+"/from:ajax"+"/start_from:"+(current_results)+"/r:"+Math.round(Math.random()*1000);
	}
	else {
		url	= url.replace(/^http(s)?\:\/\//, "");
		url	= url.substr(url.indexOf("/"));
		if( url.indexOf("#") != -1 ) {
			url	= url.substr(0, url.indexOf("#"));
		}
		url	= siteurl+url+"/from:ajax"+"/r:"+Math.round(Math.random()*1000);
	}
	$.get(url, function(data) {
		var txt	= jQuery.trim(data);
		
		var num_new_posts	= txt.match(/NUM_POSTS\:([0-9]+)\:/g); 
		if( ! num_new_posts ) { return; }
		num_new_posts	= num_new_posts.toString().match(/([0-9]+)/);
		num_new_posts	= parseInt(num_new_posts, 10);
		
		var start_from	= txt.match(/^OK\:([0-9]+)\:/g); 
		if( ! start_from ) { return; }
		start_from	= start_from.toString().match(/([0-9]+)/);
		start_from	= parseInt(start_from, 10);
		if( start_from < 0 ) { return; }
		txt	= txt.replace(/^OK\:([0-9]+)\:NUM_POSTS\:([0-9]+)\:LAST_POST_ID\:([0-9]+)\:/, "");
		$('#'+div_id).append(jQuery('<div/>').css('overflow', 'visible').html(jQuery.trim(txt)));
		
		var all	= d.getElementsByTagName("TEXTAREA");
		for(var i=0; i<all.length; i++) {
			input_set_autocomplete_toarea(all[i]);
			postform_forbid_hotkeys_conflicts(all[i]);
		}
		
		if( num_new_posts < paging_num_posts ){ 
			$("#loadmore").slideToggle('fast').remove();
			return;
		}
		$("#loadmorelink").attr('onclick', 'load_more_results("'+div_id+'", '+start_from+')');
		$("#loadmore-img").hide();
	});
	
	$("#loadmore-img").show();
	$("#loadmorelink").blur();
}
function save_search_on(ajax_url)
{
	$.post(ajax_url+'/r:'+Math.round(Math.random()*1000), "savesearch=on", function(data) {
		var txt = jQuery.trim(data);
		if( txt.substr(0,3) != "OK:" ) { return; }
		$("#savesearch").css('cursor', 'pointer').slideToggle().hide();
		$("#remsearch").slideToggle().show();
	});
	$("#savesearch").css('cursor', 'wait');
}
function save_search_off(ajax_url, confirm_msg)
{
	$.post(ajax_url+'/r:'+Math.round(Math.random()*1000), "savesearch=off", function(data) {
		var txt = jQuery.trim(data);
		if( txt.substr(0,3) != "OK:" ) { return; }
		$("#remsearch").css('cursor', 'pointer').slideToggle().hide();
		$("#savesearch").slideToggle().show();
	});
	$("#savesearch").css('cursor', 'wait');
}
function user_follow(username, thislink, unfollow_linkid, msg_after)
{
	$.post(siteurl+'ajax/follow/r:'+Math.round(Math.random()*1000), "type=on&username="+encodeURIComponent(username), function(data) {
		if( $('#'+thislink.id).length > 0 ){
			$('#'+thislink.id).css('cursor', 'pointer').hide();
		}else{
			$(thislink).css('cursor', 'pointer').hide();
		}
		if(unfollow_linkid) {
			$('#'+unfollow_linkid).show();
		}
		if(msg_after) {
			slim_msgbox(msg_after);
		}
	});
	$('#'+thislink.id).css('cursor', 'wait');
}
function user_unfollow(username, thislink, follow_linkid, confirm_msg, msg_after)
{
	$.post(siteurl+'ajax/follow/r:'+Math.round(Math.random()*1000), "type=off&username="+encodeURIComponent(username), function(data) {
		$('#'+thislink.id).css('cursor', 'pointer').hide();
		if(follow_linkid) {
			$('#'+follow_linkid).show();
		}
		if(msg_after) {
			slim_msgbox(msg_after);
		}
	});
	$('#'+thislink.id).css('cursor', 'wait');
}
function group_follow(groupname, thislink, unfollow_linkid, msg_after)
{
	$.post(siteurl+'ajax/follow/r:'+Math.round(Math.random()*1000), "type=on&groupname="+encodeURIComponent(groupname), function(data) {
		$('#'+thislink.id).css('cursor', 'pointer').hide();
		if(unfollow_linkid) {
			$('#'+unfollow_linkid).show();
		}
		if(msg_after) {
			slim_msgbox(msg_after);
		}
	});
	$('#'+thislink.id).css('cursor', 'wait');
}
function group_unfollow(groupname, thislink, follow_linkid, confirm_msg, msg_after)
{
	$.post(siteurl+'ajax/follow/r:'+Math.round(Math.random()*1000), "type=off&groupname="+encodeURIComponent(groupname), function(data) {
		$('#'+thislink.id).css('cursor', 'pointer').hide();
		if(follow_linkid) {
			$('#'+follow_linkid).show();
		}
		if(msg_after) {
			slim_msgbox(msg_after);
		}
	});
	$('#'+thislink.id).css('cursor', 'wait');
}
var flybox_opened	= false;
function flybox_open(width, height, title, html)
{
	if( flybox_opened ) { return false; }
	flybox_opened	= true;

	if( !$("#flybox_container").length || !$("#flybox_box").length || !$("#flybox_main").length ) {
		return false; 
	}
	if( ! width ) { width = 600; }
	if( ! height ) { height = 500; }
	if( ! title ) { title = ""; }
	if( ! html ) { html = ""; }	
	
	var page_size	= get_screen_preview_size();
	var left	= Math.round((page_size[0] - width) / 2);

	var top = Math.round( $(window).scrollTop() );
	left	= Math.max(left, 10);
	top	= Math.max(top, 10); 
	if( height < page_size[1] ){
		height = page_size[1] - 50;
	}
	$("#flybox_box").css({width: "'"+width+"px'", height: "'"+height+"px'"}).offset({left: left, top: top});
	
	$("#flybox_title").html(title);
	$("#flybox_container").show();
	$("#flybox_main").html(html);
	$("body").css("overflow", "hidden");
}
function flybox_close()
{
	flybox_opened	= false;
	$("#flybox_container").hide();
	$("#flybox_box").attr('style', '');
	$("#flybox_main").html('');
	$("#flybox_title").html('');
	$("body").css("overflow", "auto");
}

function flybox_open_att_image(width, height, title, postid)
{
	width	= Math.max(width, 400);
	var html	= '<iframe id="image_flybox" src="'+siteurl+'getattachment/tp:image/pid:'+postid+'/imgid:0" style="width:'+(width+10)+'px; height:'+(height+41)+'px;" border="0" frameborder="0" style="border:0px solid;" scrolling="no"></iframe>';

	return flybox_open(width+34, height+129, title, html);
}

function flybox_change_attachment(width, height, title, postid, attid, ptype)
{
	width	= Math.max(width, 600);
	var html	= '<iframe src="'+parent.siteurl+'getattachment/tp:'+ptype+'/pid:'+postid+'/attid:'+attid+'" style="width:'+(width+10)+'px; height:'+(height+41)+'px;" border="0" frameborder="0" style="border:0px solid;" scrolling="no"></iframe>';

	parent.flybox_close();
	return parent.flybox_open(width+34, height+129, title, html);
}

function flybox_open_att_videoembed(width, height, title, postid)
{
	var html	= '<iframe src="'+siteurl+'getattachment/tp:videoembed/pid:'+postid+'" style="width:'+(width+10)+'px; height:'+(height+41)+'px;" border="0" frameborder="0" style="border:0px solid;" scrolling="no"></iframe>';
	return flybox_open(width+34, height+129, title, html);
}
function keep_session()
{
	$.post(siteurl+"ajax/keepsession/r:"+Math.round(Math.random()*1000));
}
var dropdivs	= {};
var dropdiv_dropstep_px	= 10;
var dropdiv_dropstep_tm	= 1;

function dropdiv_open(div_id, height_offset)
{
	if( dropdivs[div_id] == 1 ) {
		return dropdiv_close(div_id);
	}
	if( dropdivs[div_id] == 2 ) {
		return false;
	}
	var div	= _d.getElementById(div_id);
	if( !div ) {
		return false;
	}
	
	var height	= parseInt(div.style.height, 10);
	if( !height ) {
		div.style.visiblity	= "hidden";
		div.style.display		= "block";
		height	= parseInt(div.clientHeight, 10);
		div.style.display		= "none";
		div.style.visiblity	= "visible";
		if( height_offset ) {
			height	+= height_offset;
		}
	}
	if( !height ) {
		return false;
	}
	var h	= 0;
	var func	= function() {
		div.style.height	= h+"px";
		if( h >= height ) {
			dropdivs[div_id]	= 1;
			div.style.height	= height+"px";
			div.style.overflow	= div.getAttribute("orig_overflow");
			if( _d.addEventListener ) {
				_d.addEventListener("mouseup", function(){dropdiv_close(div_id);}, false);
			}
			else if( _d.attachEvent ) {
				_d.attachEvent("onmouseup", function(){dropdiv_close(div_id);} );
			}
			return true;
		}
		h	+= dropdiv_dropstep_px;
		setTimeout( func, dropdiv_dropstep_tm );
	};
	var tmp = div.getAttribute("orig_overflow");
	if( ! tmp ) {
		tmp	= div.style.overflow ? div.style.overflow : "visible";
	}
	div.setAttribute("orig_overflow", tmp);
	div.style.overflow	= "hidden";
	div.style.display		= "block";
	dropdivs[div_id]	= 2;
	func();
}

function dropdiv_close(div_id, do_it_fast)
{
	if( dropdivs[div_id] == 0 ) {
		return true;
	}
	if( dropdivs[div_id] == 2 ) {
		return false;
	}
	var div	= _d.getElementById(div_id);
	if( !div ) {
		return false;
	}

	var h	= parseInt(div.style.height, 10);
	var orig_h	= h;
	var func	= function() {
		div.style.height	= Math.max(0,h)+"px";
		if( h <= 0 ) {
			div.style.display	= "none";
			div.style.height	= orig_h+"px";
			dropdivs[div_id]	= 0;
			return true;
		}
		h	-= dropdiv_dropstep_px;
		setTimeout( func, dropdiv_dropstep_tm );
	};
	div.style.overflow	= "hidden";
	dropdivs[div_id]	= 2;
	func();
}

var dbrd_grpmenu_showst	= 0;
function dbrd_groupmenu_toggle()
{
	if( dbrd_grpmenu_showst == 0 ) {
		dbrd_grpmenu_showst	= 1;
		$('#dbrd_menu_groupsbtn').attr('class', 'dropio dropped');
		$.post(siteurl+"dashboard/from:ajax/r:"+Math.round(Math.random()*1000), "toggle_grpmenu=1", function(data) {
			$('#dbrd_menu_groups').slideToggle('slow');
		});
	}
	else {
		dbrd_grpmenu_showst	= 0;
		$('#dbrd_menu_groupsbtn').attr('class', 'dropio');
		$.post(siteurl+"dashboard/from:ajax/r:"+Math.round(Math.random()*1000), "toggle_grpmenu=0", function(data) {
			$('#dbrd_menu_groups').slideToggle('slow');
		});
	}
}
function dbrd_whattodo_show()
{
	$.post(siteurl+"dashboard/from:ajax/r:"+Math.round(Math.random()*1000), "toggle_whattodo=1", function(data) {
		$('#greentodo').slideToggle('slow');
		$('#closedgtd').hide('slow');
	});
}
function dbrd_whattodo_hide()
{
	$.post(siteurl+"dashboard/from:ajax/r:"+Math.round(Math.random()*1000), "toggle_whattodo=0", function(data) {
		$('#greentodo').slideToggle('slow');
		$('#closedgtd').show('slow');
	});
}
function show_hide_div_by_id(div_id)
{
	$('#'+div_id).slideToggle('slow');
}
function srchposts_togglefilt(which)
{
	if( $('#srchposts_dropbox_'+which).css('display') == 'block' ){
		$('#srchposts_droplnk_'+which).attr('class', 'sdropper');
	}else{
		$('#srchposts_droplnk_'+which).attr('class', 'sdropper dropppped');
	}
	$('#srchposts_dropbox_'+which).slideToggle('slow');
}
function dbrd_check_tabs()
{
	$.post(siteurl+"ajax/checktabs/r:"+Math.round(Math.random()*1000), "checktabs=all,@me,private,commented,feeds,tweets,notifications", function(txt) {
		var txt	= jQuery.trim(txt);
		if( txt.substr(0,3) != "OK:" ) { return; }
		txt	= jQuery.trim(txt.substr(3));
		txt	= txt.split("\n");
		if( txt.length > 0 ) {
			var i, j, tb, nm, div_id;
			for(i=0; i<txt.length; i++) {
				txt[i]	= txt[i].split(":");
				if( txt[i].length != 2 ) {
					continue;
				} 
				tb	= jQuery.trim(txt[i][0]);
				nm	= parseInt(txt[i][1],10);
				if( tb!="all" && tb!="@me" && tb!="private" && tb!="commented" && tb!="feeds" && tb!="tweets" && tb!="notifications") {
					continue;
				}
				div_id = tb=="@me" ? "dbrd_tab_mention" : ("dbrd_tab_"+tb);
				if( $('#'+div_id).length>0 && nm>0 && !$('#'+div_id).parent().parent().hasClass('onitem') ) {
					$('#'+div_id).html(nm).show('slow');
				}
				else if( $('#'+div_id).length>0 && nm==0 ) {
					$('#'+div_id).html('').hide('slow');
				}
			}
		}
	});
	setTimeout( dbrd_check_tabs, 20000 );
}
function userpage_top_tooltip(txt)
{
	if( txt == "" || !$('#usrpg_top_tooltip').length ){
		$('#usrpg_top_tooltip').css('display', 'none').children('div').html('');	
		return;
	}
	$('#usrpg_top_tooltip').slideToggle('fast').children('div').html(txt);	
}
function privmsg_usrfilter_setusr(username, check_first)
{	
	$("input[name=privusr_inp]").attr('disabled', true).css('cursor', 'wait').blur();
	var f_ok	= function() {
		var url	= w.location.href.toString();
		if( ! url ) { return; }
		url	= url.replace(/usr\:[a-z0-9а-я_-]+(\/)?/i, "");
		url	= url.replace(/pg\:[0-9]+(\/)?/i, "");
		url	= url.replace(/\/+$/, "");
		url	+= "/usr:"+username;
		w.location.href	= url;
	};
	var f_err	= function() {
		$("input[name=privusr_inp]").attr('disabled', false).css('cursor', 'text').focus();
	};
	if( check_first ) {
		$.post(siteurl+"ajax/checkname/ajaxtp:xml/r:"+Math.round(Math.random()*1000), "datatype=username&word="+encodeURIComponent(username), function(data) {
			data	= data.getElementsByTagName("result");
			if( !data || !data[0] ) {
				f_err();
				return;
			}
			data	= data[0].firstChild;
			if( !data ) {
				f_err();
				return;
			}
			username	= data.nodeValue;
			f_ok();
		});
	}
	f_ok();
}
function privmsg_usrfilter_reset()
{
	$("input[name=privusr_inp]").attr('disabled', false).css('cursor', 'text').val('').focus();
	$("#pmfilterok").css('display', 'none');
	$("#pmfilter").css('display', 'block');
}
function msgbox_close(which) {
	if( !$("#"+which).length ){
		return;
	}
	$("#"+which).slideToggle('slow');
}

function slim_msgbox(msg)
{
	if( !$("#slim_msgbox").length ){
		return;
	}
	$("#slim_msgbox_msg").html(msg);
	$("#slim_msgbox").slideToggle('fast');
}
function hdr_search_settype(val, txt)
{
	$("#search_drop_lnk").html(txt);
	_d.search_form.lookin.value	= val;
	_d.search_form.lookfor.focus();
}
function scroll_to_top()
{
	$('html, body').animate({scrollTop:0}, 'slow');		
}