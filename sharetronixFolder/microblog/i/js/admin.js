function group_admins_putintolist(username) {
	if( !d.admform || !d.admform.admins ) {
		return false;
	}
	if( d.admform.admins.value.toLowerCase().indexOf(","+username.toLowerCase()+",") != -1 ) {
		return true;
	}
	var dv	= d.createElement("DIV");
	dv.className	= "addadmins";
	dv.appendChild(d.createTextNode(username+" "));
	var a	= d.createElement("A");
	a.href	= "javascript:;";
	a.onfocus	= function() { this.blur(); };
	a.onclick	= function() {
		var msg	= jsconfirm_admin_remove.replace("#USERNAME#", username);
		if( msg != "" ) {
			if( ! confirm(msg) ) { return false; }
		}
		d.admform.admins.value	= d.admform.admins.value.replace(","+username+",", ",");
		d.admform.admins.value	= d.admform.admins.value.replace(",,", ",");
		if( d.admform.admins.value=="" || d.admform.admins.value=="," ) {
			if( d.getElementById("group_admins_link_empty_msg") ) {
				d.getElementById("group_admins_link_empty_msg").style.display = "block";
			}
		}
		this.parentNode.parentNode.removeChild(this.parentNode);
	};
	dv.appendChild(a);
	d.getElementById("group_admins_list").appendChild(dv);
	if( d.getElementById("group_admins_link_empty_msg") ) {
		d.getElementById("group_admins_link_empty_msg").style.display	= "none";
	}
	d.admform.admins.value	+= ","+username+",";
	d.admform.admins.value	= d.admform.admins.value.replace(",,", ",");
}