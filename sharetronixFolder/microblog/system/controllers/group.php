<?php
	
	if( !$this->network->id ) {
		$this->redirect('home');
	}elseif($C->PROTECT_OUTSIDE_PAGES && !$this->user->is_logged){
		$this->redirect('home');
	}
	
	$this->load_langfile('inside/global.php');
	$this->load_langfile('inside/group.php');
	
	require_once( $C->INCPATH.'helpers/func_images.php' );
	require_once( $C->INCPATH.'helpers/func_api.php' );
	require_once( $C->INCPATH.'helpers/func_additional.php' );
	
	$g	= $this->network->get_group_by_id(intval($this->params->group));
	if( ! $g ) {
		$this->redirect('dashboard');
	}
	if( $g->is_private && !$this->user->is_logged ) {
		$this->redirect('home');
	}
	if( $g->is_private && !$this->user->info->is_network_admin ) {
		$u	= $this->network->get_group_invited_members($g->id);
		if( !$u || !in_array(intval($this->user->id),$u) ) {
			$this->redirect('dashboard');
		}
	}
	
	$D->page_title	= $g->title.' - '.$C->SITE_TITLE;
	$D->page_favicon	= $C->IMG_URL.'avatars/thumbs2/'.$g->avatar;
	
	$D->g	= & $g;
	$my_followers 		= $this->user->is_logged? array_keys( $this->network->get_user_follows($this->user->id, FALSE, 'hisfollowers')->followers ) : array();
	$group_members 		= array_keys( $this->network->get_group_members($g->id) );
	$D->i_am_member		= ($this->user->is_logged && in_array($this->user->id, $group_members))? TRUE : FALSE;
	$D->i_am_network_admin	= ( $this->user->is_logged && $this->user->info->is_network_admin > 0 );
	$D->i_am_admin		= $D->i_am_network_admin;

	if( !$D->i_am_network_admin ) {
		$D->i_am_admin	= $db->fetch('SELECT id FROM groups_admins WHERE group_id="'.$g->id.'" AND user_id="'.$this->user->id.'" LIMIT 1') ? TRUE : FALSE;
	}
	
	$D->i_can_invite	= ($D->i_am_admin && $D->i_am_member) || ($D->i_am_member && $g->is_public);
	if( $D->i_can_invite ) {
		$tmp	= $my_followers; 
		foreach($tmp as &$v) { $v = intval($v); }
		$tmp2	= $group_members;
		foreach($tmp2 as &$v) { $v = intval($v); }
		$tmp	= array_diff($tmp, $tmp2);
		$tmp2	= $this->network->get_group_invited_members($g->id);
		if( $tmp2 ) {
			foreach($tmp2 as &$v) { $v = intval($v); }
			$tmp	= array_diff($tmp, $tmp2);
		}
		$tmp	= array_diff($tmp, array(intval($this->user->id)));
		if( ! count($tmp) ) {
			$D->i_can_invite	= FALSE;
		}
		unset($tmp);
	}
	
	if( $this->param('act')=='join' || $this->param('act')=='leave' ) {
		if( $this->param('act')=='join' && !$D->i_am_member ) {
			$this->user->follow_group($g->id, TRUE);
		}
		elseif( $this->param('act')=='leave' && $D->i_am_member ) {
			$this->user->follow_group($g->id, FALSE);
		}
		$tmp_url	= $C->SITE_URL.$g->groupname;
		if( $this->param('tab') ) {
			$tmp_url	.= '/tab:'.$this->param('tab');
		}
		if( $this->param('subtab') ) {
			$tmp_url	.= '/subtab:'.$this->param('subtab');
		}
		if( $this->param('filter') ) {
			$tmp_url	.= '/filter:'.$this->param('filter');
		}
		if( $this->param('pg') ) {
			$tmp_url	.= '/pg:'.$this->param('pg');
		}
		$tmp_url	.= '/msg:'.$this->param('act');
		$this->redirect($tmp_url);
	}
	
	if( $D->i_am_member ) {
		$D->rss_feeds	= array(
			array( $C->SITE_URL.'rss/groupname:'.$g->groupname,	$this->lang('rss_grpposts',array('#GROUP#'=>$g->title)), ),
		);
	}
	
	$tabs	= array('updates', 'members');
	if( $D->i_am_admin ) { $tabs[] = 'settings'; }
	$D->tab	= 'updates';
	if( $this->param('tab') && in_array($this->param('tab'), $tabs) ) {
		$D->tab	= $this->param('tab');
	}
	$D->subtab	= '';
	$subtabs	= array();
	if( $D->tab == 'settings' ) {
		$subtabs = array('main', 'admins', 'rssfeeds', 'delgroup');
		if( $g->is_private ) {
			$subtabs[]	= 'privmembers';
		}
		$D->subtab	= 'main';
	}
	if( $this->param('subtab') && in_array($this->param('subtab'), $subtabs) ) {
		$D->subtab	= $this->param('subtab');
	}
	
	$D->num_members	= 0;
	$D->num_members	= count($group_members);

	$D->post_tags	= $this->network->get_recent_posttags(10, $g->id, 'group');
	$D->about_me	= nl2br(htmlspecialchars($D->g->about_me));
	if( FALSE!==strpos($D->about_me,'http://') || FALSE!==strpos($D->about_me,'http://') || FALSE!==strpos($D->about_me,'ftp://') ) {
		$D->about_me	= preg_replace('#(^|\s)((http|https|ftp)://\w+[^\s\[\]]+)#ie', 'post::_postparse_build_link("\\2", "\\1")', $D->about_me);
	}
	
	if( $D->tab == 'updates' )
	{
		$D->posts_html	= '';
		$D->filter	= 'all';
		
		$start_from = ( $this->param('start_from') )? ' AND id<'.intval( $this->param('start_from') ) : '';
		$q2	= 'SELECT *, "public" AS `type` FROM posts WHERE group_id="'.$g->id.'"'.$start_from.' ORDER BY id DESC ';
		
		$res	= $db2->query($q2.'LIMIT '.$C->PAGING_NUM_POSTS);
		$D->num_results = $db2->num_rows($res); 
		$D->start_from = 0;
		$D->lats_post_id = 0;
		
		if( 0 == $D->num_results && empty($start_from) ) {
			$arr	= array('#GROUP#'=>htmlspecialchars($g->title), '#SITE_TITLE#'=>htmlspecialchars($C->OUTSIDE_SITE_TITLE), '#A1#'=>'<a href="javascript:;" onclick="postform_open(({groupname:\''.htmlspecialchars($g->title).'\'}));">', '#A2#'=>'</a>', );
			$lngkey_ttl	= 'noposts_group_ttl';
			$lngkey_txt	= 'noposts_group_txt';		

			$D->noposts_box_title	= $this->lang($lngkey_ttl, $arr);
			$D->noposts_box_text	= $this->lang($lngkey_txt, $arr);
			$D->posts_html	= $this->load_template('noposts_box.php', FALSE);
		}
		else {
			$D->start_from = 0;
			$D->lats_post_id = 0;
			$tmpposts	= array();
			$tmpids	= array();
			$pids 	= array();
			$postusrs	= array();
			$buff 	= NULL; 	
			$i=1;
			while($obj = $db2->fetch_object($res)) {
				$D->start_from = $obj->id;
				if( $i == 1 ){
					$D->lats_post_id = $obj->id;
				}
				$i++;
				$buff = new post($obj->type, FALSE, $obj);
				if( $buff->error ) {
					continue;
				}
				$tmpposts[] = $buff;
				if( $this->param('from')=='ajax' && $this->param('onlypost')!="" && $this->param('onlypost')!=$buff->post_tmp_id ) {
					continue;
				}
				$tmpids[]	= $buff->post_tmp_id;
				$pids[] 	= $buff->post_id;
				$postusrs[]	= $buff->post_user->id;
			}
			unset($buff);
			
			post::preload_num_new_comments($tmpids);
			ob_start();
			
			$D->if_follow_me = array();
			if( count($postusrs)>0 && $this->user->is_logged  ){
				$r = $db2->query('SELECT who FROM users_followed WHERE who IN ('.implode(',', $postusrs).') AND whom="'.$this->user->id.'"');
				while($o = $db2->fetch_object($r)){
					if( isset($D->if_follow_me[$o->who]) ){
						continue;
					}
					$D->if_follow_me[$o->who] = 1;
				}
			}
			
			if( $this->user->is_logged ){
				$D->i_follow	= array_fill_keys(array_keys($this->network->get_user_follows($this->user->id, FALSE, 'hefollows')->follow_users), 1); 
				$D->i_follow[] 	= $this->user->id;
			}
			
			foreach($tmpposts as $tmp) {
				$D->p	= $tmp;
				$D->post_show_slow	= FALSE;
				if( $this->param('from')=='ajax' && isset($_POST['lastpostdate']) && $D->p->post_date>intval($_POST['lastpostdate']) ) {
					$D->post_show_slow	= TRUE;
				}
				if( $this->param('from')=='ajax' && $this->param('onlypost')!="" && $this->param('onlypost')!=$D->p->post_tmp_id ) {
					continue;
				}
				$D->parsedpost_attlink_maxlen	= 75;
				$D->parsedpost_attfile_maxlen	= 71;
				if( isset($D->p->post_attached['image']) ) {
					$D->parsedpost_attlink_maxlen	-= 10;
					$D->parsedpost_attfile_maxlen	-= 12;
				}
				if( isset($D->p->post_attached['videoembed']) ) {
					$D->parsedpost_attlink_maxlen	-= 10;
					$D->parsedpost_attfile_maxlen	-= 12;
				}
				$D->show_my_email = FALSE;
				if( $D->i_am_network_admin || in_array($D->p->post_user->id, $D->if_follow_me)){
					$D->show_my_email = TRUE;
				}
				$D->protected_profile = FALSE;
				$right_post_type = (!$D->p->is_system_post && !$D->p->is_feed_post);
				
				if( $right_post_type && !$D->show_my_email && $D->p->post_user->is_profile_protected && !$D->i_am_network_admin ){
					$D->protected_profile = TRUE;
				}
				
				$D->show_reshared_design = ( $D->p->post_resharesnum > 0 );
				
				$this->load_template('single_post.php');
			}
			unset($D->p, $tmp, $tmpposts, $tmpids, $pids, $right_post_type);

			$D->posts_html	= ob_get_contents();
			ob_end_clean();
		}
		if( $this->param('from') == 'ajax' ) {
			echo 'OK:'.$D->start_from.':NUM_POSTS:'.$D->num_results.':LAST_POST_ID:'.$D->lats_post_id.':'.$D->posts_html;
			exit;
		}
		$D->group_posts_title	= $this->lang('group_title_updates', array('#GROUP#'=>htmlspecialchars($g->title)));
	}
	elseif( $D->tab == 'members' )
	{
		$D->page_title	= $this->lang('group_pagetitle_members', array('#GROUP#'=>htmlspecialchars($g->title), '#SITE_TITLE#'=>$C->SITE_TITLE));
		$filters	= array('all', 'admins');
		$D->filter	= 'all';
		if( $this->param('filter') && in_array($this->param('filter'), $filters) ) {
			$D->filter	= $this->param('filter');
		}

		$tmp	= array();
		if( $D->filter == 'all' ) {
			$tmp	= $group_members;
		}
		elseif( $D->filter == 'admins' ) {
			$r = $db2->query('SELECT u.id FROM users u, groups_admins ga WHERE ga.group_id="'.$g->id.'" AND u.id=ga.user_id');
			while($o = $db2->fetch_object($r)) {
				$tmp[]	= $o->id;
			}
		}

		$D->num_results	= count($tmp);
		$D->num_pages	= ceil($D->num_results / $C->PAGING_NUM_USERS);
		$D->pg	= $this->param('pg') ? intval($this->param('pg')) : 1;
		$D->pg	= min($D->pg, $D->num_pages);
		$D->pg	= max($D->pg, 1);
		$from	= ($D->pg - 1) * $C->PAGING_NUM_USERS;
		$tmp	= array_slice($tmp, $from, $C->PAGING_NUM_USERS, TRUE);
		
		$D->if_follow_user = array();
		if($this->user->id && count($tmp)>0 ){ 
			$db2->query('SELECT whom FROM users_followed WHERE whom IN('.implode(',', $tmp).') AND who="'.$this->user->id.'"');
			while($o = $db2->fetch_object()){
				if( isset($D->if_follow_user[$o->whom]) ){
						continue;
				}
				$D->if_follow_user[$o->whom] = 1;
			}
		}
		
		if( count($tmp)>0 ) {
			$r = $db2->query('SELECT * FROM users WHERE id IN('.implode(',', $tmp).')');
			$tmp = array();
			while($o = $db2->fetch_object($r)) {
				$tmp[]	= generate_user_info_obj($o, TRUE);
			}
		}
		
		$D->users_html	= '';
		ob_start();
		foreach($tmp as $usr) {
			$D->u	= $usr;
			$this->load_template('single_user.php');
		}
		$D->paging_url	= $C->SITE_URL.$g->groupname.'/tab:members/filter:'.$D->filter.'/pg:';
		if( $D->num_pages > 1 ) {
			$this->load_template('paging_users.php');
		}
		$D->users_html	= ob_get_contents();
		ob_end_clean();
		unset($tmp, $sdf, $D->u);
	}
	elseif( $D->tab == 'settings' && $D->i_am_admin )
	{
		if( $D->subtab == 'main' ) {
			$D->page_title	= $this->lang('group_pagetitle_settings', array('#GROUP#'=>htmlspecialchars($g->title), '#SITE_TITLE#'=>$C->SITE_TITLE));
			$D->submit	= FALSE;
			$D->error	= FALSE;
			$D->errmsg	= '';
			$D->form_title		= $g->title;
			$D->form_groupname	= $g->groupname;
			$D->form_description	= $g->about_me;
			$D->form_type		= $g->is_private ? 'private' : 'public';
			if( isset($_POST['sbm']) ) {
				$D->submit	= TRUE;
				$D->form_title		= trim($_POST['form_title']);
				$D->form_groupname	= trim($_POST['form_groupname']);
				$D->form_description	= trim($_POST['form_description']);
				$D->form_type		= trim($_POST['form_type']);
				if( isset($_FILES['form_avatar']) && is_uploaded_file($_FILES['form_avatar']['tmp_name']) ) {
					$f	= (object) $_FILES['form_avatar'];
					list($w, $h, $tp) = getimagesize($f->tmp_name);
					if( $w==0 || $h==0 ) {
						$D->error	= TRUE;
						$D->errmsg	= 'group_setterr_avatar_invalidfile';
					}
					elseif( $tp!=IMAGETYPE_GIF && $tp!=IMAGETYPE_JPEG && $tp!=IMAGETYPE_PNG ) {
						$D->error	= TRUE;
						$D->errmsg	= 'group_setterr_avatar_invalidformat';
					}
					elseif( $w<$C->AVATAR_SIZE || $h<$C->AVATAR_SIZE ) {
						$D->error	= TRUE;
						$D->errmsg	= 'group_setterr_avatar_toosmall';
					}
					else {
						$fn	= time().rand(100000,999999).'.png';
						$res	= copy_avatar($f->tmp_name, $fn);
						if( ! $res) {
							$D->error	= TRUE;
							$D->errmsg	= 'group_setterr_avatar_cantcopy';
						}
						else {
							$old	= $g->avatar;
							if( $old != $C->DEF_AVATAR_GROUP ) {
								rm( $C->IMG_DIR.'avatars/'.$old );
								rm( $C->IMG_DIR.'avatars/thumbs1/'.$old );
								rm( $C->IMG_DIR.'avatars/thumbs2/'.$old );
								rm( $C->IMG_DIR.'avatars/thumbs3/'.$old );
							}
							$db2->query('UPDATE groups SET avatar="'.$db2->escape($fn).'" WHERE id="'.$g->id.'" LIMIT 1');
							$D->page_favicon	= $C->IMG_URL.'avatars/thumbs2/'.$fn;
						}
					}
				}
				if( $D->form_type=='public' && $g->is_private ) {
					$db2->query('UPDATE groups SET is_public=1 WHERE id="'.$g->id.'" LIMIT 1');
				}
				elseif( $D->form_type=='private' && $g->is_public ) {
					$db2->query('UPDATE groups SET is_public=0 WHERE id="'.$g->id.'" LIMIT 1');
					$tmp1	= array_keys($this->network->get_group_members($g->id));
					$tmp2	= $this->network->get_group_invited_members($g->id);
					$tmp	= array_diff($tmp1, $tmp2);
					foreach($tmp as $uid) {
						$db2->query('INSERT INTO groups_private_members SET group_id="'.$g->id.'", user_id="'.$uid.'", invited_by="'.$this->user->id.'", invited_date="'.time().'" ');
					}
					$tmp	= $this->network->get_group_invited_members($g->id, TRUE);
					$tmp	= $this->network->get_private_groups_ids(TRUE);
					unset($tmp, $tmp1, $tmp2);
				}
				$D->form_description	= mb_substr($D->form_description, 0, $C->POST_MAX_SYMBOLS);
				$db2->query('UPDATE groups SET about_me="'.$db2->e($D->form_description).'" WHERE id="'.$g->id.'" LIMIT 1');
				if( mb_strlen($D->form_title)<3 || mb_strlen($D->form_title)>30 ) {
					$D->error	= TRUE;
					$D->errmsg	= 'group_setterr_title_length';
				}
				elseif( preg_match('/[^ا-یא-תÀ-ÿ一-龥а-яa-z0-9\-\.\s]/iu', $D->form_title) ) {
					$D->error	= TRUE;
					$D->errmsg	= 'group_setterr_title_chars';
				}
				elseif( $D->form_title != $g->title ) {
					$db2->query('SELECT id FROM groups WHERE (groupname="'.$db2->e($D->form_title).'" OR title="'.$db2->e($D->form_title).'") AND id<>"'.$g->id.'" LIMIT 1');
					if( $db2->num_rows() > 0 ) {
						$D->error	= TRUE;
						$D->errmsg	= 'group_setterr_title_exists';
					}
					else {
						$db2->query('UPDATE groups SET title="'.$db2->e($D->form_title).'" WHERE id="'.$g->id.'" LIMIT 1');
						$D->page_title	= $D->form_title.' - '.$C->SITE_TITLE;
						$this->user->get_top_groups(1, TRUE);
					}
				}
				if( mb_strlen($D->form_groupname)<3 || mb_strlen($D->form_groupname)>30 ) {
					$D->error	= TRUE;
					$D->errmsg	= 'group_setterr_name_length';
				}
				elseif( ! preg_match('/^[a-z0-9\-\_]{3,30}$/iu', $D->form_groupname) ) {
					$D->error	= TRUE;
					$D->errmsg	= 'group_setterr_name_chars';
				}
				elseif( $D->form_groupname != $g->groupname ) {
					$db2->query('SELECT id FROM groups WHERE (groupname="'.$db2->e($D->form_groupname).'" OR title="'.$db2->e($D->form_groupname).'") AND id<>"'.$g->id.'" LIMIT 1');
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
						elseif( file_exists($C->INCPATH.'controllers/'.$D->form_groupname.'.php') ) {
							$D->error	= TRUE;
							$D->errmsg	= 'group_setterr_name_existss';
						}
						else {
							$db2->query('UPDATE groups SET groupname="'.$db2->e($D->form_groupname).'" WHERE id="'.$g->id.'" LIMIT 1');
							$this->network->get_group_by_name($g->groupname, TRUE);
							$this->network->get_group_by_name($D->form_groupname, TRUE);
							$this->user->get_top_groups(1, TRUE);
						}
					}
				}
				$g	= $this->network->get_group_by_id($g->id, TRUE);
				$this->network->get_private_groups_ids(TRUE);
				//$this->user->get_my_private_groups_ids(TRUE);
			}
		}
		elseif( $D->subtab == 'admins' ) { 
			$D->page_title	= $this->lang('group_pagetitle_settings_admins', array('#GROUP#'=>htmlspecialchars($g->title), '#SITE_TITLE#'=>$C->SITE_TITLE));
			$D->admins		= array();
			$D->jsmembers	= array();
			$D->admins 		= array();
			$r = $db2->query('SELECT u.username, u.id FROM users u, groups_admins ga WHERE ga.group_id="'.$g->id.'" AND u.id=ga.user_id');
			while($o = $db2->fetch_object($r)) {
				$D->admins[$o->id]	= $o->username;
			}

			foreach($group_members as $v) {
				$tmp	= $this->network->get_user_by_id($v);
				if( $tmp ) {
					$D->jsmembers[]	= $tmp->username;
				}
			}

			if( isset($_POST['admins']) ) { 
				$admins	= trim($_POST['admins']);
				$admins	= trim($admins, ',');
				$admins	= trim($admins);
				$admins	= explode(',', $admins);
				$members	= array_fill_keys($group_members, 1); 
				$ids	= array();
				if( isset($D->admins[$this->user->id]) || !$this->user->info->is_network_admin ) {
					$ids[]	= intval($this->user->id);
				}

				foreach($admins as $a) {
					$a	= trim($a);
					if( empty($a) ) { continue; }
					$a	= $this->network->get_user_by_username($a);
					if( ! $a ) { continue; }
					if( ! isset($members[$a->id]) ) { continue; }
					$ids[]	= intval($a->id);
				}
				$ids	= array_unique($ids);

				$this->db2->query('DELETE FROM groups_admins WHERE group_id="'.$g->id.'" ');
				foreach($ids as $a) {
					$this->db2->query('INSERT INTO groups_admins SET group_id="'.$g->id.'", user_id="'.$a.'" ');
				}
				$this->redirect( $C->SITE_URL.$D->g->groupname.'/tab:settings/subtab:admins/msg:admsaved' );
			}
		}
		elseif( $D->subtab == 'privmembers' && $g->is_private ) {
			$D->page_title	= $this->lang('group_pagetitle_settings_privmembers', array('#GROUP#'=>htmlspecialchars($g->title), '#SITE_TITLE#'=>$C->SITE_TITLE));
			$D->cannot_be_removed	= array();
			$D->can_be_removed	= array();
			
			$r	= $db2->query('SELECT ga.user_id, u.username FROM groups_admins ga, users u WHERE group_id="'.$g->id.'" AND u.id=ga.user_id');
			while($tmp = $db2->fetch_object($r)) {
				if( $tmp ) {
					$tmp->id	= intval($tmp->user_id);
					$D->cannot_be_removed[$tmp->user_id]	= $tmp;
				}
			}
			$r	= $db2->query('SELECT gpm.user_id, u.username FROM groups_private_members gpm, users u WHERE group_id="'.$g->id.'" AND gpm.user_id=u.id');
			while($tmp = $db2->fetch_object($r)) {
				if( $tmp ) {
					if( isset($D->cannot_be_removed[$tmp->user_id]) ) {
						continue;
					}
					$D->can_be_removed[$tmp->user_id]	= $tmp;
				}
			}
			arsort($D->cannot_be_removed);
			arsort($D->can_be_removed); 
			if( isset($_POST['admins']) ) {
				$admins	= trim($_POST['admins']);
				$admins	= trim($admins, ',');
				$admins	= trim($admins);
				$admins	= explode(',', $admins);
				$ids	= array();
				foreach($admins as $a) {
					$a	= trim($a);
					if( empty($a) ) { continue; }
					$a	= $this->network->get_user_by_username($a);
					if( ! $a ) { continue; }
					$a	= intval($a->id);
					if( isset($D->can_be_removed[$a]) ) {
						$ids[$a]	= 'keep';
					}
				}
				$remove	= array();
				foreach($D->can_be_removed as $u) {
					if( ! isset($ids[$u->user_id]) ) {
						$remove[]	= $u->user_id;
					}
				}
				foreach($remove as $u) {
					$db2->query('DELETE FROM groups_private_members WHERE group_id="'.$g->id.'" AND user_id="'.$u.'" ');
					$tmp	= $this->network->get_group_members($g->id);
					if( isset($tmp[$u]) ) {
						$this->db2->query('DELETE FROM groups_followed WHERE user_id="'.$u.'" AND group_id="'.$g->id.'" ');
						$this->db2->query('UPDATE groups SET num_followers=num_followers-1 WHERE id="'.$g->id.'" LIMIT 1');
						$this->db2->query('DELETE FROM post_userbox WHERE user_id="'.$u.'" AND post_id IN(SELECT id FROM posts WHERE group_id="'.$g->id.'" )');
						$this->db2->query('DELETE FROM post_userbox_feeds WHERE user_id="'.$u.'" AND post_id IN(SELECT id FROM posts WHERE group_id="'.$g->id.'" )');
					}
					$nothing	= $this->network->get_user_follows($u, TRUE);
					$nothing	= $this->network->get_user_by_id($u, TRUE);
				}
				$nothing	= $this->network->get_group_invited_members($g->id, TRUE);
				$nothing	= $this->network->get_group_members($g->id, TRUE);
				$nothing	= $this->network->get_group_by_id($g->id, TRUE);
				$this->redirect( $C->SITE_URL.$D->g->groupname.'/tab:settings/subtab:privmembers/msg:mmbsaved' );
			}
		}
		elseif( $D->subtab == 'rssfeeds' ) {
			$D->page_title	= $this->lang('group_pagetitle_settings_rssfeeds', array('#GROUP#'=>htmlspecialchars($g->title), '#SITE_TITLE#'=>$C->SITE_TITLE));
			$D->submit	= FALSE;
			$D->error	= FALSE;
			$D->errmsg	= '';
			$D->newfeed_url		= '';
			$D->newfeed_filter	= '';
			$D->newfeed_auth_req	= FALSE;
			$D->newfeed_auth_msg	= FALSE;
			$D->newfeed_username	= '';
			$D->newfeed_password	= '';
			if( isset($_POST['sbm']) ) {
				$D->submit	= TRUE;
				$D->newfeed_url		= trim($_POST['newfeed_url']);
				$D->newfeed_filter	= trim( mb_strtolower($_POST['newfeed_filter']) );
				$D->newfeed_filter	= preg_replace('/[^\,ا-یא-תÀ-ÿ一-龥а-яa-z0-9-\_\.\#\s]/iu', '', $D->newfeed_filter);
				$D->newfeed_filter	= preg_replace('/\s+/ius', ' ', $D->newfeed_filter);
				$D->newfeed_filter	= preg_replace('/(\s)*(\,)+(\s)*/iu', ',', $D->newfeed_filter);
				$D->newfeed_filter	= trim( trim($D->newfeed_filter, ',') );
				$D->newfeed_filter	= str_replace(',', ', ', $D->newfeed_filter);
				$D->newfeed_username	= isset($_POST['newfeed_username']) ? trim($_POST['newfeed_username']) : '';
				$D->newfeed_password	= isset($_POST['newfeed_password']) ? trim($_POST['newfeed_password']) : '';
				if( empty($D->newfeed_url) ) {
					$D->error	= TRUE;
					$D->errmsg	= 'group_feedsett_err_feed';
				}
				$f	= '';
				if( !$D->error ) {
					$f	= new rssfeed($D->newfeed_url);
					$auth	= $f->check_if_requires_auth();
					if( $f->error ) {
						$D->error	= TRUE;
						$D->errmsg	= 'group_feedsett_err_feed';
					}
					elseif( $auth ) {
						$D->newfeed_auth_req	= TRUE;
					}
					else {
						$f->read();
						if( $f->error ) {
							$D->error	= TRUE;
							$D->errmsg	= 'group_feedsett_err_feed';
						}
					}
				}
				if( !$D->error && $D->newfeed_auth_req && !empty($D->newfeed_username) && !empty($D->newfeed_password) ) {
					$f->set_userpwd($D->newfeed_username.':'.$D->newfeed_password);
					$auth	= $f->check_if_requires_auth();
					if( $f->error || $auth ) {
						$D->error	= TRUE;
						$D->errmsg	= 'group_feedsett_err_auth';
					}
					else {
						$f->read();
						if( $f->error ) {
							$D->error	= TRUE;
							$D->errmsg	= 'group_feedsett_err_feed';
						}
					}
				}
				if( !$D->error && $f->is_read ) {
					$f->fetch();
					$lastdate	= $f->get_lastitem_date();
					if( ! $lastdate ) {
						$lastdate	= time();
					}
					$title	= $f->title;
					if( empty($title) ) {
						$title	= preg_replace('/^(http|https|ftp)\:\/\//iu', '', $D->newfeed_url);
					}
					$title	= $this->db2->e($title);
					$usrpwd	= $D->newfeed_auth_req ? ($D->newfeed_username.':'.$D->newfeed_password) : '';
					$usrpwd	= $this->db2->e($usrpwd);
					$keywords	= str_replace(', ', ',', $D->newfeed_filter);
					$keywords	= $this->db2->e($keywords);
					$this->db2->query('SELECT id FROM groups_rssfeeds WHERE is_deleted=0 AND group_id="'.$g->id.'" AND feed_url="'.$this->db2->e($D->newfeed_url).'" AND feed_userpwd="'.$usrpwd.'" AND filter_keywords="'.$keywords.'" LIMIT 1');
					if( 0 == $this->db2->num_rows() ) {
						$this->db2->query('INSERT INTO groups_rssfeeds SET is_deleted=0, group_id="'.$g->id.'", feed_url="'.$this->db2->e($D->newfeed_url).'", feed_title="'.$title.'", feed_userpwd="'.$usrpwd.'", filter_keywords="'.$keywords.'", date_added="'.time().'", date_last_post=0, date_last_crawl="'.time().'", date_last_item="'.$lastdate.'", added_by_user="'.$this->user->id.'" ');
						if( ! empty($f->hub) ) {
							$this->db2->query('UPDATE groups_rssfeeds SET hub_url="'.$this->db2->e($f->hub).'" WHERE id="'.$this->db2->insert_id().'" LIMIT 1');
							$hui	= new pubsubhubbub($f->hub);
							$hui->subscribe($D->newfeed_url, TRUE);
						}
					}
					$this->redirect($C->SITE_URL.$g->groupname.'/tab:settings/subtab:rssfeeds/msg:added');
				}
				if( !$D->error && $D->newfeed_auth_req && (empty($D->newfeed_username) || empty($D->newfeed_password)) ) {
					$D->newfeed_auth_msg	= TRUE;
				}
			}
			$D->feeds	= array();
			$this->db2->query('SELECT id, feed_url, feed_title, filter_keywords FROM groups_rssfeeds WHERE is_deleted=0 AND group_id="'.$g->id.'" ORDER BY id ASC');
			while($obj = $this->db2->fetch_object()) {
				$obj->feed_url		= stripslashes($obj->feed_url);
				$obj->feed_title		= stripslashes($obj->feed_title);
				$obj->filter_keywords	= stripslashes($obj->filter_keywords);
				$obj->filter_keywords	= str_replace(',', ', ', $obj->filter_keywords);
				$D->feeds[$obj->id]	= $obj;
			}
			if( $this->param('delfeed') && isset($D->feeds[$this->param('delfeed')]) ) {
				$this->db2->query('SELECT feed_url, hub_url FROM groups_rssfeeds WHERE id="'.intval($this->param('delfeed')).'" AND is_deleted=0 AND group_id="'.$g->id.'" AND hub_url<>"" LIMIT 1');
				if( $tmp = $this->db2->fetch_object() ) {
					$hui	= new pubsubhubbub($tmp->hub_url);
					$hui->subscribe($tmp->feed_url, FALSE);
				}
				$this->db2->query('UPDATE groups_rssfeeds SET is_deleted=1 WHERE id="'.intval($this->param('delfeed')).'" AND is_deleted=0 AND group_id="'.$g->id.'" LIMIT 1');
				$this->redirect($C->SITE_URL.$g->groupname.'/tab:settings/subtab:rssfeeds/msg:deleted');
			}
		}
		elseif( $D->subtab == 'delgroup' ) {
			$D->page_title	= $this->lang('group_pagetitle_settings_delgroup', array('#GROUP#'=>htmlspecialchars($g->title), '#SITE_TITLE#'=>$C->SITE_TITLE));
			$D->submit	= FALSE;
			$D->error	= FALSE;
			$D->errmsg	= '';
			$D->f_postsact	= '';
			if( isset($_POST['sbm']) ) {
				$D->submit	= TRUE;
				$D->f_postsact	= isset($_POST['postsact']) ? trim($_POST['postsact']) : '';
				$D->password	= trim($_POST['password']);
				if( $g->is_private ) {
					$D->f_postsact	= 'del';
				}
				if( $D->f_postsact!='keep' && $D->f_postsact!='del' ) {
					$D->f_postsact	= '';
					$D->error		= TRUE;
					$D->errmsg		= 'group_del_f_err_posts';
				}
				if( !$D->error && md5($D->password)!=$this->user->info->password ) {
					$D->error		= TRUE;
					$D->errmsg		= 'group_del_f_err_passwd';
				}
				if( !$D->error ) {
					ini_set('max_execution_time', 10*60*60);
					if( $D->f_postsact == 'del' ) {
						$r	= $db2->query('SELECT * FROM posts WHERE group_id="'.$g->id.'" ORDER BY id ASC');
						while($obj = $db2->fetch_object($r)) {
							$p	= new post('public', FALSE, $obj);
							if( $p->error ) { continue; }
							$p->delete_this_post();
						}
						$r	= $db2->query('SELECT id FROM groups_rssfeeds WHERE group_id="'.$g->id.'" ');
						while($obj = $db2->fetch_object($r)) {
							$db2->query('DELETE FROM groups_rssfeeds_posts WHERE rssfeed_id="'.$obj->id.'" ');
						}
						$db2->query('DELETE FROM groups_rssfeeds WHERE group_id="'.$g->id.'" ');
					}
					$r	= $db2->query('SELECT * FROM posts WHERE user_id="0" AND group_id="'.$g->id.'" ORDER BY id ASC');
					while($obj = $db2->fetch_object($r)) {
						$p	= new post('public', FALSE, $obj);
						if( $p->error ) { continue; }
						$p->delete_this_post();
					}
					$f	= array_keys($this->network->get_group_members($g->id));
					$db2->query('DELETE FROM group_notifications WHERE to_group_id="'.$g->id.'" ');
					$db2->query('DELETE FROM notifications WHERE in_group_id="'.$g->id.'" ');
					$db2->query('DELETE FROM groups_followed WHERE group_id="'.$g->id.'" ');
					$db2->query('DELETE FROM groups_private_members WHERE group_id="'.$g->id.'" ');
					$db2->query('DELETE FROM groups_admins WHERE group_id="'.$g->id.'" ');
					$db2->query('UPDATE groups_rssfeeds SET is_deleted=1 WHERE group_id="'.$g->id.'" ');
					foreach($f as $uid) {
						$this->network->get_user_follows($uid, TRUE);
					}
					$db2->query('INSERT INTO groups_deleted (id, groupname, title, is_public) SELECT id, groupname, title, is_public FROM groups WHERE id="'.$g->id.'" LIMIT 1');
					$db2->query('DELETE FROM groups WHERE id="'.$g->id.'" LIMIT 1');
					$this->network->get_group_by_id($g->id, TRUE);
					$av	= $g->avatar;
					if( $av != $C->DEF_AVATAR_GROUP ) {
						rm( $C->IMG_DIR.'avatars/'.$av );
						rm( $C->IMG_DIR.'avatars/thumbs1/'.$av );
						rm( $C->IMG_DIR.'avatars/thumbs2/'.$av );
						rm( $C->IMG_DIR.'avatars/thumbs3/'.$av );
					}
					$this->redirect( $C->SITE_URL.'groups/msg:deleted' );
				}
			}
		}
	}
	
	$D->latest_join_notifications = array();
	$this->db2->query('SELECT u.username, u.avatar, n.date FROM users u, group_notifications n WHERE n.from_user_id = u.id AND n.notif_type="ntf_grp_if_u_joins" AND n.to_group_id="'.intval($g->id).'" ORDER BY n.id DESC LIMIT 10');
	while( $o = $this->db2->fetch_object() ){
		$D->latest_join_notifications[] = array( 'username' => $o->username, 'date' => $o->date, 'avatar'=>(!empty($o->avatar)? $o->avatar : $GLOBALS['C']->DEF_AVATAR_USER) );
	}
	
	$this->load_template('group.php');
	
?>