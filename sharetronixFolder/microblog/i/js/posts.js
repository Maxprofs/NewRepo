function post_fave(postid){
	$.post(siteurl+'ajax/favepost/r:'+Math.round(Math.random()*1000), 'type=on&postid='+encodeURIComponent(postid), function() {
		$("#postlink_fave_"+postid).css({cursor: 'pointer', display: 'none'});
		$("#postlink_unfave_"+postid).css('display', 'block');
	});
	$("#postlink_fave_"+postid).css('cursor', 'wait');
}
function post_unfave(postid, hide_post)
{
	$.post(siteurl+'ajax/favepost/r:'+Math.round(Math.random()*1000), 'type=off&postid='+encodeURIComponent(postid), function() {
		$("#postlink_unfave_"+postid).css({cursor: 'pointer', display: 'none'});
		$("#postlink_fave_"+postid).css('display', 'block');
		if( hide_post ){
			$("#post_"+postid).slideToggle('slow');
		}
	});
	$("#postlink_unfave_"+postid).css('cursor', 'wait');
}
function comment_like(postid, commentid, mode)
{
	$.post(siteurl+'ajax/commentlike/r:'+Math.round(Math.random()*1000), 'type='+encodeURIComponent(mode)+'&commentid='+encodeURIComponent(commentid)+'&postid='+encodeURIComponent(postid), function() {
		$("#commentlink_like_"+postid).css('cursor', 'pointer');
		if( $("#viewpost").length > 0 ){
			viewpost_synchronize();
		}else{
			posts_synchronize_single(postid);
		}
	});
	$("#commentlink_like_"+postid).css('cursor', 'wait');
}
function post_to_facebook(postid, confirmmsg, msg_after)
{
	if( ! confirm(confirmmsg) ) {
		return false;
	}		
	$.post(siteurl+'ajax/post-to-facebook/r:'+Math.round(Math.random()*1000), 'postid='+encodeURIComponent(postid), function() {
		if( msg_after ) {
			slim_msgbox(msg_after);
		}
	});
}
function post_to_twitter(postid, confirmmsg, msg_after)
{
	if( ! confirm(confirmmsg) ) {
		return false;
	}		
	$.post(siteurl+'ajax/post-to-twitter/r:'+Math.round(Math.random()*1000), 'postid='+encodeURIComponent(postid), function() {
		if( msg_after ) {
			slim_msgbox(msg_after);
		}
	});
}
function reshare_post(postid, confirmmsg, msg_after)
{
	if( ! confirm(confirmmsg) ) {
		return false;
	}		
	$.post(siteurl+'ajax/reshare/r:'+Math.round(Math.random()*1000), 'postid='+encodeURIComponent(postid)+'&type=on', function() {
		if( msg_after ) {
			slim_msgbox(msg_after);
		}
		posts_synchronize_single('public_'+postid);
	});
}
function unshare_post(postid, confirmmsg, msg_after)
{
	if( ! confirm(confirmmsg) ) {
		return false;
	}		
	$.post(siteurl+'ajax/reshare/r:'+Math.round(Math.random()*1000), 'postid='+encodeURIComponent(postid)+'&type=off', function() {
		if( msg_after ) {
			slim_msgbox(msg_after);
		}
		posts_synchronize_single('public_'+postid);
	});
}
function post_delete(postid, confirm_msg, callback_after)
{
	if( confirm_msg ) {
		if( ! confirm(confirm_msg) ) { return; }
	}
	$.post(siteurl+'ajax/delpost/r:'+Math.round(Math.random()*1000), "postid="+encodeURIComponent(postid), function() {
		$("#postlink_del_"+postid).css('cursor', 'pointer').slideToggle('slow');
		$("#post_"+postid).slideToggle('slow');
		//posts_synchronize(); 
	});
	$("#postlink_del_"+postid).css('cursor', 'wait');
}
function postcomments_open(post_id)
{
	if( $("#postcomments_"+post_id).css('display') == 'block' ){
		$("#postcomments_"+post_id).css('height', '').slideToggle('fast');
		$("#post_"+post_id).removeClass(" withcomments");
		return;	
	}
	if( $("#post_"+post_id).length == 0 ){
		return;
	}
	if( $("#postcomments_"+post_id).length == 0 ){
		return;
	}
	$("#post_"+post_id).addClass(" withcomments");
	$("#postcomments_"+post_id).css('height', 'auto').slideToggle('slow');
	$("#postcomments_"+post_id+"_textarea").focus();
	postcomments_mark(post_id);
}
function postcomments_mark(post_id)
{
	$.post(siteurl+'ajax/post-comments-mark/r:'+Math.round(Math.random()*1000), "postid="+encodeURIComponent(post_id), function() {
		if( $('#post_newcomments_'+post_id).length > 0 ){
			$('#post_newcomments_'+post_id).hide();	
		}
	});
}
function postcomments_close(post_id, callback_after)
{
	postcomments_collapse(post_id);
	if( $("#postcomments_"+post_id).css('display') == 'block' ){
		$("#postcomments_"+post_id).css('height', '').slideToggle('fast');
		$("#post_"+post_id).removeClass(" withcomments");
	}
}
function postcomments_expand(post_id)
{
	if( $("#postcomments_"+post_id+"_slimform").length > 0 ){
		$("#postcomments_"+post_id+"_slimform").hide();
		$("#postcomments_"+post_id+"_bigform").show();
		$("#postcomments_"+post_id+"_textarea").focus();
	}
}
function postcomments_collapse(post_id)
{
	if( $("#postcomments_"+post_id+"_slimform").length > 0 ){
		$("#postcomments_"+post_id+"_textarea").blur();
		$("#postcomments_"+post_id+"_bigform").hide();
		$("#postcomments_"+post_id+"_slimform").show();	
	}
}
function postcomments_submit(post_id)
{
	var message = jQuery.trim($('#postcomments_'+post_id+'_textarea').val());
	if( message === "" ){
		$('#postcomments_'+post_id+'_textarea').focus();
		return;
	}
	$('#postcomments_'+post_id+'_textarea').attr("disabled", "disabled").css('cursor', 'wait');
	$('#postcomments_'+post_id+'_submitbtn').attr("disabled", "disabled").css('cursor', 'wait').blur();
	$.post(siteurl+'ajax/post-comment/r:'+Math.round(Math.random()*1000), "postid="+encodeURIComponent(post_id)+"&message="+encodeURIComponent(message), function() {
		if( $("#viewpost").length > 0 ) {
			viewpost_synchronize();
		}
		else {
			posts_synchronize_single(post_id);
		}
	});
}
function postcomment_delete(post_id, comment_id, confirm_msg, callback_after)
{
	if( confirm_msg ) {
		if( ! confirm(confirm_msg) ) { return; }
	}
	$.post(siteurl+'ajax/delcomment/r:'+Math.round(Math.random()*1000), "postid="+encodeURIComponent(post_id)+"&commentid="+encodeURIComponent(comment_id), function() {
		if( $("#viewpost").length > 0 ) {
			viewpost_synchronize();
		}
		else {
			posts_synchronize_single(post_id);
		}
		$("#postcomment_"+comment_id).css('cursor', 'pointer');
	});
	$("#postcomment_"+comment_id).css('cursor', 'wait');
}
function posts_synchronize_single(post_id)
{
	var comments_open	= 0;
	if( $("#postcomments_"+post_id).css('display') == 'block' ) {
		comments_open	= 1;
	}
	
	$.post(siteurl+'ajax/single-post-synchronize/r:'+Math.round(Math.random()*1000), "post_id="+encodeURIComponent(post_id)+"&display_comments="+encodeURIComponent(comments_open), function(txt) {
		if( txt.substr(0,3) != "OK:" ) { return; }
		txt	= txt.replace(/^OK\:/, "");
		var ndv = jQuery('<div/>', {
			css: {  
				display: 'none',
				overflow: 'visible'
			}
		}).html(jQuery.trim(txt)).attr('id', 'temporary_'+post_id).insertAfter($("#post_"+post_id)).addClass('post');
		$("#post_"+post_id).remove();

		$("#temporary_"+post_id+" input").each(function(){
			postform_forbid_hotkeys_conflicts(this);
		});
		$("#temporary_"+post_id+" textarea").each(function(){
			postform_forbid_hotkeys_conflicts(this);
			input_set_autocomplete_toarea(this); 
		});

		ndv.attr('id', 'post_'+post_id);
		ndv.css('display', 'block');
		if( comments_open == 1 ){
			$("#post_"+post_id).addClass('withcomments');
		}
	});
}
function viewpost_synchronize()
{
	var url	= w.location.href.toString();
	if( ! url ) { return; }
	if( url.substr(0, siteurl.length) == siteurl ) {
		url	= url.substr(siteurl.length);
		url	= siteurl+url+"/from:ajax/r:"+Math.round(Math.random()*1000);
	}
	else {
		url	= url.replace(/^http(s)?\:\/\//, "");
		url	= url.substr(url.indexOf("/"));
		url	= siteurl+url+"/from:ajax/r:"+Math.round(Math.random()*1000);
	}
	url	= url.replace(/#comments/, "");
	
	$.post(url, function(txt) {
		txt = jQuery.trim(txt);
		if( txt.substr(0,3) != "OK:" ) { return; }
		txt	= txt.substr(3);
		var ndv = jQuery('<div/>', {
			css: {  
				display: 'none',
				overflow: 'visible',
			}
		}).html(jQuery.trim(txt)).attr('id', 'viewpost_temporary').insertAfter($("#viewpost"));
		
		$("#viewpost").remove();

		$("#viewpost_temporary input").each(function(){
			postform_forbid_hotkeys_conflicts(this);
		});
		$("#viewpost_temporary textarea").each(function(){
			postform_forbid_hotkeys_conflicts(this);
			input_set_autocomplete_toarea(this); 
		});

		ndv.attr('id', 'viewpost');
		ndv.css('display', 'block');
	});
}
function posts_synchronize()
{
	var open_comments = false;
	$('#posts_html').children().each(function(){ 
		if( $(this).hasClass('withcomments') ){
			open_comments = true;
		}
	});
	if( open_comments ){
		setTimeout(posts_synchronize, 120000);
		return;
	}
	
	var url	= w.location.href.toString();
	if( ! url ) { return; }
	if( url.substr(0, siteurl.length) == siteurl ) {
		url	= url.substr(siteurl.length);
		url	= siteurl+url+"/from:ajax/r:"+Math.round(Math.random()*1000);
	}
	else {
		url	= url.replace(/^http(s)?\:\/\//, "");
		url	= url.substr(url.indexOf("/"));
		url	= siteurl+url+"/from:ajax/r:"+Math.round(Math.random()*1000);
	}
	
	var i, ch, lastpostdate = 0, lastpostdates = [];
	$('#posts_html').children().each(function(){
		var tmpdate = parseInt($(this).attr('postdate'), 10);
		if( tmpdate > lastpostdate ){
			lastpostdate = tmpdate;
		}
	});
	
	$.post(url, "lastpostdate="+encodeURIComponent(lastpostdate), function(txt) {
		txt = jQuery.trim(txt);
		if( txt.substr(0,3) != "OK:" ) { return; }
		var get_last_post_id	= txt.match(/LAST_POST_ID\:([0-9]+)\:/g); //edit match here
		if( ! get_last_post_id ) { 
			return;
		}
		get_last_post_id	= get_last_post_id.toString().match(/([0-9]+)/g); //edit
		get_last_post_id = parseInt(get_last_post_id, 10);
		last_post_id = get_last_post_id;
		
		txt	= txt.replace(/^OK\:([0-9]+)\:NUM_POSTS\:([0-9]+)\:LAST_POST_ID\:([0-9]+)\:/, ""); 
		
		$("#posts_html").html(txt);
		
		$('#posts_html').children().each(function(){
			if( $(this).css('display') == 'none' ){
				$(this).slideToggle('slow');
			}
		});
		
		$("#posts_html input").each(function(){
			postform_forbid_hotkeys_conflicts(this);
		});
		$("#posts_html textarea").each(function(){
			postform_forbid_hotkeys_conflicts(this);
			input_set_autocomplete_toarea(this); 
		});
	});
}
function show_post_topbtns(pid)
{
	$('#post_btns_top_'+pid).show();
}
function hide_post_topbtns(pid, fast)
{
	$('#post_btns_top_'+pid).hide().delay(800);
}
function extshare_openbox(tmpid)
{	
	if( !$("#extshare_link_"+tmpid).length || !$("#extshare_tmpbox_"+tmpid).length ){
		return;
	}
	var offset = $("#extshare_link_"+tmpid).offset();	
	
	if( $("#extshare_tmpbox_"+tmpid).css('display') == 'block' ){
		$("#extshare_tmpbox_"+tmpid).offset({top : 0, left: 0}).css({'display':'none', 'z-index':'10'});
		return;
	}
	
	$("#extshare_tmpbox_"+tmpid).css({'position':'absolute', 'z-index':'10'}).offset({top : offset.top+15, left: offset.left}).slideToggle('fast');
	$("#extshare_link_"+tmpid).bind( "clickoutside", function(event){
		$("#extshare_tmpbox_"+tmpid).offset({top : 0, left: 0}).css({'display':'none', 'z-index':'10'});
	});
	return;
}
function extshare_closebox(tmpid)
{
	var offset = $("#extshare_link_"+tmpid).offset();	
	if( offset == undefined ){
		return;
	}
	$("#extshare_tmpbox_"+tmpid).offset({top : offset.top-15, left: offset.left}).hide();
	return; 
}
function textarea_autoheight(textarea)
{
	var mn	= 40;
	var mx	= 390;
	if( !textarea || !textarea.nodeName || textarea.nodeName!="TEXTAREA" ) {
		return;
	}
	if( !textarea.id ) {
		textarea.id	= "tmptxtarea_"+Math.round(Math.random()*10000);
	}
	dv	= d.getElementById("tmptxtdv_"+textarea.id);
	if( !dv ) {
		var dv	= d.createElement("DIV");
		dv.id		= "tmptxtdv_"+textarea.id;
		dv.className	= textarea.className;
		dv.style.width		= textarea.clientWidth + "px";
		dv.style.overflow		= "auto";
		dv.style.whiteSpace	= "pre-wrap";
		dv.style.visibility	= "hidden";
		dv.style.display		= "block";
		dv.style.position		= "absolute";
		textarea.parentNode.appendChild(dv);
	}
	dv.innerHTML	= "";
	dv.appendChild(d.createTextNode(textarea.value+"\n"));
	var	h = parseInt(dv.clientHeight, 10);
	if( isNaN(h) ) {
		return;
	}
	if( h <= mn ) {
		h	= mn;
		textarea.style.overflow	= "hidden";
	}
	else if( h >= mx ) {
		h	= mx;
		textarea.style.overflow	= "auto";
	}
	else {
		textarea.style.overflow	= "hidden";
	}
	textarea.style.height	= h + "px";
}
function dropcontrols_open(post_id) {
	//if(post_id.match(/^private_[0-9]*$/)) return;
	$("#dropcontrols_link_"+post_id).addClass("dropped");
	$("#dropcontrols_box_"+post_id).css('display', 'block');
	$("#dropcontrols_link_"+post_id).bind( "clickoutside", function(event){
		$("#dropcontrols_box_"+post_id).css('display', 'none');
		$("#dropcontrols_link_"+post_id).removeClass("dropped");
	});
}
function dropcontrols_share_open(post_id) {
	$("#dropcontrols_sharelink_"+post_id).addClass("dropped");
	$("#dropcontrols_sharebox_"+post_id).css('display', 'block');
	$("#dropcontrols_sharelink_"+post_id).bind( "clickoutside", function(event){
		$("#dropcontrols_sharebox_"+post_id).css('display', 'none');
		$("#dropcontrols_sharelink_"+post_id).removeClass("dropped");
	});
}

var postuserbox_last_open	= false;
var postuserbox_tmout		= false;
var postuserbox_open_timer	= false;
function postuserbox_open(post_id) {
	postuserbox_close();
	postuserbox_open_timer = setTimeout( function() {
		postuserbox_close();
		$("#postuserbox_av1_"+post_id).hide();
		$("#postuserbox_av2_"+post_id).show()
		postuserbox_last_open	= post_id;
	}, 2000);
}
function postuserbox_keepopen() {
	if( postuserbox_tmout ) { clearTimeout(postuserbox_tmout); }
}
function postuserbox_close_ev(post_id) {
	postuserbox_tmout	= setTimeout( function() { postuserbox_close(post_id) }, 1000 );
	if(postuserbox_open_timer) { clearTimeout(postuserbox_open_timer); }
}
function postuserbox_close(post_id) {
	postuserbox_keepopen();
	if( !post_id && postuserbox_last_open ) {
		post_id	= postuserbox_last_open;
	}
	if( !post_id ) {
		return;
	}
	$("#postuserbox_av2_"+post_id).hide();
	$("#postuserbox_av1_"+post_id).show();
	postuserbox_last_open	= false;
}
function show_notif_users(ntype, nname, in_group)
{	
	$.post(siteurl+'ajax/show-notif-users/r:'+Math.round(Math.random()*1000), "ntype="+encodeURIComponent(ntype)+"&nname="+encodeURIComponent(nname)+"&in_group="+encodeURIComponent(in_group), function(data) {
		if( data == 'ERROR' ){
			alert('Error Loading!');
		}
		var imgs	= data.replace(/^OK\:/, "");
		//$('#popupbox').html(imgs);
		$('<div>'+imgs+'</div>').dialog({
			modal: true,
			height: 'auto'
		});

	});
}
function show_notification_group(ntf_group)
{
	$('#dv_'+ntf_group).children().css('display', 'block');
	$('#a_'+ntf_group+'_show').css('display', 'none');
	$('#a_'+ntf_group+'_hide').css('display', 'block');
}