<?php
	
	if( !$this->network->id ) {
		$this->redirect('home');
	}
	if( !$this->user->is_logged ) {
		$this->redirect('signin');
	}
	
	$this->load_langfile('inside/global.php');
	$this->load_langfile('inside/group.php');
	$this->load_langfile('inside/group_invite.php');
	
	
	$g	= $this->network->get_group_by_id(intval($this->params->group));
	if( ! $g ) {
		$this->redirect('groups');
	}
	if( $g->is_private ) {
		$u	= $this->network->get_group_members($g->id);
		if( !$u || !isset($u[$this->user->id]) ) {
			$this->redirect('dashboard');
		}
	}
	
	$D->g	= & $g;
	$D->i_am_member	= $this->user->if_follow_group($g->id);
	$D->i_am_admin	= FALSE;
	if( $D->i_am_member ) {
		$D->i_am_admin	= $db->fetch('SELECT id FROM groups_admins WHERE group_id="'.$g->id.'" AND user_id="'.$this->user->id.'" LIMIT 1') ? TRUE : FALSE;
	}
	if( !$D->i_am_admin && $this->user->info->is_network_admin==1 ) {
		$D->i_am_admin	= TRUE;
	}
	$D->i_can_invite	= $D->i_am_admin || ($D->i_am_member && $g->is_public);
	
	if( ! $D->i_can_invite ) {
		$this->redirect($C->SITE_URL.$g->groupname);
	}
	
	$D->page_title	= $this->lang('os_grpinv_pagetitle', array('#GROUP#'=>$g->title, '#SITE_TITLE#'=>$C->SITE_TITLE));
	$D->page_favicon	= $C->IMG_URL.'avatars/thumbs2/'.$g->avatar;
	
	$tmp	= array_keys( $this->network->get_user_follows($this->user->id, FALSE, 'hisfollowers')->followers );
	foreach($tmp as &$v) { $v = intval($v); }
	$tmp2	= array_keys($this->network->get_group_members($g->id));
	foreach($tmp2 as &$v) { $v = intval($v); }
	$tmp	= array_diff($tmp, $tmp2);
	$tmp2	= $this->network->get_group_invited_members($g->id);
	if( $tmp2 ) {
		foreach($tmp2 as &$v) { $v = intval($v); }
		$tmp	= array_diff($tmp, $tmp2);
	}
	$tmp	= array_diff($tmp, array(intval($this->user->id)));
	if( 0 == count($tmp) ) {
		$this->redirect($C->SITE_URL.$g->groupname);
	}
	$data	= array();
	foreach($tmp as $tmp2) {
		$tmp2	= $this->network->get_user_by_id($tmp2);
		if( ! $tmp2 ) { continue; }
		$data[$tmp2->id]	= $tmp2;
	}
	unset($tmp, $tmp2);
	
	if( isset($_POST['invite_users']) )
	{
		$users	= trim($_POST['invite_users'], ',');
		$users	= str_replace(',,', ',', $users);
		$users	= explode(',', $users);
		
		$notif = new notifier();
		$notif->set_notification_obj('user', $this->user->id);
		$notif->set_group_id($g->id);
		$notif->onGroupInvite( $g->id, $data, $users );
		
		$this->network->get_group_invited_members($g->id, TRUE);
		$this->redirect( $C->SITE_URL.$g->groupname.'/msg:invited' );
	}
	
	$D->nobody	= FALSE;
	if( 0 == count($data) ) {
		$D->nobody	= TRUE;
	}
	
	$D->members	= array();
	foreach($data as $o) {
		$D->members[]	= array (
			intval($o->id),
			$o->username,
			$o->fullname,
			$o->position,
			$o->avatar,
			0, // selected
		);
	}
	
	$this->load_template('group_invite.php');
	
?>