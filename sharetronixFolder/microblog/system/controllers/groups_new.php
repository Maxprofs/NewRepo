<?php
	
	if( !$this->network->id ) {
		$this->redirect('home');
	}
	if( !$this->user->is_logged ) {
		$this->redirect('signin');
	}
	
	$this->load_langfile('inside/global.php');
	$this->load_langfile('inside/group.php');
	$this->load_langfile('inside/groups_new.php');
	
	$D->page_title	= $this->lang('newgroup_title', array('#SITE_TITLE#'=>$C->SITE_TITLE));
	
	$D->submit	= FALSE;
	$D->error	= FALSE;
	$D->errmsg	= '';
	$D->form_title		= '';
	$D->form_groupname	= '';
	$D->form_description	= '';
	$D->form_type		= 'public';
	
	if( isset($_POST['sbm']) ) {
		$D->submit	= TRUE;
		$D->form_title		= trim($_POST['form_title']);
		$D->form_groupname	= trim($_POST['form_groupname']);
		$D->form_description	= mb_substr(trim($_POST['form_description']), 0, $C->POST_MAX_SYMBOLS);
		$D->form_type		= trim($_POST['form_type'])=='private' ? 'private' : 'public';
		
		if( mb_strlen($D->form_title)<3 || mb_strlen($D->form_title)>30 ) {
			$D->error	= TRUE;
			$D->errmsg	= 'group_setterr_title_length';
		}
		elseif( preg_match('/[^ا-یא-תÀ-ÿ一-龥а-яa-z0-9\-\.\s]/iu', $D->form_title) ) {
			$D->error	= TRUE;
			$D->errmsg	= 'group_setterr_title_chars';
		}
		else {
			$db2->query('SELECT id FROM groups WHERE (groupname="'.$db2->e($D->form_title).'" OR title="'.$db2->e($D->form_title).'") LIMIT 1');
			if( $db2->num_rows() > 0 ) {
				$D->error	= TRUE;
				$D->errmsg	= 'group_setterr_title_exists';
			}
		}
		
		if( ! $D->error ) {
			if( mb_strlen($D->form_groupname)<3 || mb_strlen($D->form_groupname)>30 ) {
				$D->error	= TRUE;
				$D->errmsg	= 'group_setterr_name_length';
			}
			elseif( ! preg_match('/^[a-z0-9\-\_]{3,30}$/iu', $D->form_groupname) ) {
				$D->error	= TRUE;
				$D->errmsg	= 'group_setterr_name_chars';
			}
			else {
				$db2->query('SELECT id FROM groups WHERE (groupname="'.$db2->e($D->form_groupname).'" OR title="'.$db2->e($D->form_groupname).'") LIMIT 1');
				if( $db2->num_rows() > 0 ) {
					$D->error	= TRUE;
					$D->errmsg	= 'group_setterr_name_exists';
				}
				else {
					$db2->query('SELECT id FROM users WHERE username="'.$db2->e($D->form_groupname).'" LIMIT 1');
					if( $db2->num_rows() > 0 ) {
						$D->error	= TRUE;
						$D->errmsg	= 'group_setterr_name_existsu';
					}
					elseif( file_exists($C->INCPATH.'controllers/'.strtolower($D->form_groupname).'.php') ) {
						$D->error	= TRUE;
						$D->errmsg	= 'group_setterr_name_existss';
					}
					elseif( file_exists($C->INCPATH.'controllers/mobile/'.strtolower($D->form_groupname).'.php') ) {
						$D->error	= TRUE;
						$D->errmsg	= 'group_setterr_name_existss';
					}
				}
			}
		}
		
		if( ! $D->error ) {
			$db2->query('INSERT INTO groups SET groupname="'.$db2->e($D->form_groupname).'", title="'.$db2->e($D->form_title).'", about_me="'.$db2->e($D->form_description).'", is_public="'.($D->form_type=='public'?1:0).'" ');
			
			$g	= $this->network->get_group_by_id( intval($db2->insert_id()) );
			
			$db2->query('INSERT INTO groups_admins SET group_id="'.$g->id.'", user_id="'.$this->user->id.'" ');
			if( $g->is_private ) {
				$db2->query('INSERT INTO groups_private_members SET group_id="'.$g->id.'", user_id="'.$this->user->id.'", invited_by="'.$this->user->id.'", invited_date="'.time().'" ');
			}
			
			if( $g->is_public ) {
				$notif = new notifier();
				$notif->set_group_id($g->id);
				$notif->set_notification_obj('user', $this->user->id);
				$notif->onCreateGroup();
			}
			$this->user->follow_group($g->id);
			$this->network->get_group_by_id($g->groupname, TRUE);
			$this->network->get_group_by_id($g->title, TRUE);
			$this->network->get_private_groups_ids(TRUE);
			//$this->user->get_my_private_groups_ids(TRUE);
			$this->redirect( $C->SITE_URL.$g->groupname.'/msg:created' );
		}
	}
	
	$this->load_template('groups_new.php');
	
?>