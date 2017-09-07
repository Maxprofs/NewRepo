var _d	= document;
var _w	= window;
var d		= document;
var w		= window;
var siteurl	= "/";
var disable_animations	= false;
var window_loaded	= false;

if( d.addEventListener ) {
	d.addEventListener("load", window_onload, false);
	w.addEventListener("load", window_onload, false);
}
else if( d.attachEvent ) {
	d.attachEvent("onload", window_onload);
	w.attachEvent("onload", window_onload);
}
function window_onload() {
	if( window_loaded ) {
		return;
	}
	window_loaded	= true;
	setInterval(keep_session, 300000);
	//if(posts_synchronize) {
	//	setTimeout(posts_synchronize, 15000);
	//}
	if(dbrd_check_tabs) {
		if( w.location.pathname && w.location.pathname.match("/dashboard") ) {
			setTimeout(dbrd_check_tabs, 10000);
		}
	}
}

function ajax_init(is_xml)
{
	var req = false;
	if (w.XMLHttpRequest) {
		req = new XMLHttpRequest();
		if (req.overrideMimeType) {
			if( is_xml ) { req.overrideMimeType("application/xml"); }
			else { req.overrideMimeType("text/plain"); }
		}
	} else if (w.ActiveXObject) {
		try { req = new w.ActiveXObject("MSXML3.XMLHTTP"); } catch(exptn) {
		try { req = new w.ActiveXObject("MSXML2.XMLHTTP.3.0"); } catch(exptn) {
		try { req = new w.ActiveXObject("Msxml2.XMLHTTP"); } catch(exptn) {
		try { req = new w.ActiveXObject("Microsoft.XMLHTTP"); } catch(exptn) {
		}}}}
	}
	return req;
}
function trim(str)
{
	if( typeof(str) != "string" ) {
		return str;
	}
	str	= str.replace(/^\s+/, "");
	str	= str.replace(/\s+$/, "");
	return str;
}
function ltrim(str)
{
	if( typeof(str) != "string" ) {
		return str;
	}
	str	= str.replace(/^\s+/, "");
	return str;
}
function preload_img()
{
	var tmp	= [];
	for(var i=0; i<arguments.length; i++) {
		tmp[i]	= new Image();
		tmp[i].src	= arguments[i];
	}
}
function obj_find_coords(obj)
{
	var X=0, Y=0;
	if( obj.offsetParent ) {
		X =	obj.offsetLeft;
		Y =	obj.offsetTop;
		if( obj.offsetParent ) {
			do {
				obj = obj.offsetParent;
				X +=	obj.offsetLeft;
				Y +=	obj.offsetTop;
			}
			while( obj.offsetParent );
		}
	}
	return [X,Y];
}
function get_screen_preview_size()
{
	var w=0, h=0;
	if( typeof( window.innerWidth ) == 'number' ) {
		w	= window.innerWidth;
		h	= window.innerHeight;
	}
	else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
		w	= document.documentElement.clientWidth;
		h	= document.documentElement.clientHeight;
	}
	else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
		w	= document.body.clientWidth;
		h	= document.body.clientHeight;
	}
	return [w, h];
}
function get_screen_scroll()
{
	var x=0, y=0;
	if( typeof( window.pageYOffset ) == 'number' ) {
		x	= window.pageXOffset;
		y	= window.pageYOffset;
	} else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
		x	= document.body.scrollLeft;
		y	= document.body.scrollTop;
	} else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
		y	= document.documentElement.scrollTop;
		x	= document.documentElement.scrollLeft;
	}
	return [x, y];
}