<?php
	
	if( !$this->network->id ) {
		echo 'ERROR';
		return;
	}
	if( !$this->user->is_logged ) {
		echo 'ERROR';
		return;
	}
	

	if( !isset($_POST['ntype']) || !isset($_POST['nname']) || !isset($_POST['in_group']) ) {
		echo 'ERROR';
		return;
	}
	$ntype 	= $this->db2->e($_POST['ntype']);
	$nname	= $this->db2->e($_POST['nname']);
	$in_group	= intval($_POST['in_group']);

	$nname 	= explode('_', $nname);
	$in_where = '';
	
	if( $nname[0] == 'none' ){
		unset($nname[0]);
	}else{
		$in_where .= ' AND notif_object_type="'.$nname[0].'" ';
	}

	if( $nname[1] == '0' ){
		unset($nname[1]);
	}else{
		$in_where .= ' AND notif_object_id="'.$nname[1].'" ';
	}
	

	$html = '';
	
	if( $in_group == 0 ){
		$this->db2->query('SELECT u.username, u.avatar FROM users u, notifications n WHERE u.id = n.from_user_id AND notif_type="'.trim($ntype).'" AND to_user_id="'.$this->user->id.'"'.$in_where.' GROUP BY from_user_id');
		while( $o = $this->db2->fetch_object() ){
			$html .= '<div style="float: left; margin: 5px;">';
			$html .= '<img src="'.$C->IMG_URL.'avatars/thumbs3/'.(!empty($o->avatar)? $o->avatar : $GLOBALS['C']->DEF_AVATAR_USER).'" alt="'.$o->username.'" />';
			$html .= '</div>';
			$html .= '<div style="float: left; margin: 10px 5px 5px 5px;">';
			$html .= '<a href="'.$C->SITE_URL.$o->username.'" style="color:#1975e1;font-family: Verdana, Arial, Helvetica, sans-serif;font-size: 12px;text-decoration: none; font-weight: bold; padding: 0;">'.$o->username.'</a>';
			$html .= '</div>';
			$html .= '<div class="klear"></div>';
		}
	}else{	
		$this->db2->query('SELECT in_group_id AS gid FROM notifications WHERE notif_type="'.trim($ntype).'" AND in_group_id>0 AND from_user_id="'.intval($nname[1]).'" AND to_user_id="'.$this->user->id.'" GROUP BY in_group_id');
		while( $o = $this->db2->fetch_object() ){
			$g = $this->network->get_group_by_id($o->gid);
			if( !$g ){
				continue;
			}
			$html .= '<div style="float: left; margin: 5px;">';
			$html .= '<img src="'.$C->IMG_URL.'avatars/thumbs3/'.$g->avatar.'" alt="'.$g->groupname.'" />';
			$html .= '</div>';
			$html .= '<div style="float: left; margin: 10px 5px 5px 5px;">';
			$html .= '<a href="'.$C->SITE_URL.$g->groupname.'" style="color:#1975e1;font-family: Verdana, Arial, Helvetica, sans-serif;font-size: 12px;text-decoration: none; font-weight: bold; padding: 0;">'.$g->groupname.'</a>';
			$html .= '</div>';
			$html .= '<div class="klear"></div>';
		}
	}
	
	echo 'OK:'.$html;
	return;
?>