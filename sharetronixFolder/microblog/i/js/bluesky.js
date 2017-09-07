var last_hdrmenu_dropped	= false;
function hdrmenu_dropsub(tmpid)
{
	var t_close;
	
	if( $("#hdr_drop_"+last_hdrmenu_dropped).css('display') == 'block' ){
		$("#hdr_drop_"+last_hdrmenu_dropped).hide();
	}	
	$("#hdr_drop_"+tmpid).show();
	last_hdrmenu_dropped = tmpid;
	
	$("#hdr_drop_"+tmpid).bind( "mouseleave", function(event){
		t_close = setTimeout(function(){$("#hdr_drop_"+tmpid).hide();}, 500);
	});
	$("#hdr_drop_"+tmpid).bind( "mouseover", function(event){
		clearTimeout(t_close);
	});
	$("#hdr_drop_"+tmpid).bind( "clickoutside", function(event){
		$("#hdr_drop_"+tmpid).hide();
		if( $("#hdr_drop_"+last_hdrmenu_dropped).css('display') == 'block' ){
			$("#hdr_drop_"+last_hdrmenu_dropped).hide();
		}
	});
	return;
}