<?php
	
	if( !$this->network->id ) {
		$this->redirect('home');
	}
	if( !$this->user->is_logged ) {
		$this->redirect('home');
	}
	
	$this->load_langfile('inside/global.php');
	$this->load_langfile('inside/dashboard.php');
	
	$D->page_title	= $this->lang('dashboard_page_title', array('#SITE_TITLE#'=>$C->SITE_TITLE));
	
	if( $this->param('from')=='ajax' && isset($_POST['toggle_whattodo']) ) {
		$tmp	= intval($_POST['toggle_whattodo'])==0 ? 0 : 1;
		$this->user->sess['what_todo_active'] = $tmp;
		echo 'OK';
		exit;
	}

	if( $this->param('from')=='ajax' && isset($_POST['toggle_grpmenu']) ) {
		$tmp	= intval($_POST['toggle_grpmenu'])==0 ? 0 : 1;
		$this->user->sess['debrd_grpmenu_active'] = $tmp;
		echo 'OK';
		exit;
	}
	if( $this->param('from')=='ajax' && isset($_POST['hide_wrongaddr_warning']) ) {
		setcookie('wrongaddr_warning_'.md5($C->DOMAIN), 'off', time()+7*24*60*60, '/', cookie_domain());
		echo 'OK';
		exit;
	}
	$D->groupsmenu_active	= ( isset($this->user->sess['debrd_grpmenu_active']) && $this->user->sess['debrd_grpmenu_active'] ) ? TRUE : FALSE;
	
	$D->rss_feeds	= array(
		array( $C->SITE_URL.'rss/my:dashboard',	$this->lang('rss_mydashboard',array('#USERNAME#'=>$this->user->info->username)), ),
		array( $C->SITE_URL.'rss/my:posts',		$this->lang('rss_myposts',array('#USERNAME#'=>$this->user->info->username)), ),
		array( $C->SITE_URL.'rss/my:private',	$this->lang('rss_myprivate',array('#USERNAME#'=>$this->user->info->username)), ),
		array( $C->SITE_URL.'rss/my:mentions',	$this->lang('rss_mymentions',array('#USERNAME#'=>$this->user->info->username)), ),
		array( $C->SITE_URL.'rss/my:bookmarks',	$this->lang('rss_mybookmarks',array('#USERNAME#'=>$this->user->info->username)), ),
		array( $C->SITE_URL.'rss/all:posts',	$this->lang('rss_everybody',array('#SITE_TITLE#'=>$C->SITE_TITLE)), ),
	);
	
	$privtabs	= array('all', 'inbox', 'sent', 'usr');
	$privtab	= 'all';
	$privusr	= FALSE;
	if(in_array($this->param('privtab'),$privtabs)) {
		$privtab	= $this->param('privtab');
	}
	if($privtab=='usr' && $this->param('usr') && $this->param('usr')!=$this->user->info->username) {
		$privusr	= $this->network->get_user_by_username($this->param('usr'));
	}
	
	$q2	= '';
	$start_from = ( $this->param('start_from') )? ' AND p.id<'.intval( $this->param('start_from') ) : '';
	if($privtab == 'all') {	
		$q2	= 'SELECT p.*, "0" AS `likes`, "private" AS `type` FROM posts_pr p WHERE (p.user_id="'.$this->user->id.'" OR (p.to_user="'.$this->user->id.'" AND p.is_recp_del=0)) '.$start_from.' ORDER BY p.date_lastcomment DESC, p.id DESC ';
	}
	if($privtab == 'inbox') {
		$q2	= 'SELECT p.*, "0" AS likes, "private" AS `type` FROM posts_pr p WHERE p.to_user="'.$this->user->id.'" AND p.is_recp_del=0 '.$start_from.' ORDER BY p.date_lastcomment DESC, p.id DESC ';
	}
	elseif($privtab == 'sent') {	
		$q2	= 'SELECT p.*, "0" AS `likes`, "private" AS `type` FROM posts_pr p WHERE p.user_id="'.$this->user->id.'" '.$start_from.' ORDER BY p.date_lastcomment DESC, p.id DESC ';
	}
	elseif($privtab == 'usr' && $privusr) {
		$q2	= 'SELECT p.*, "0" AS `likes`, "private" AS `type` FROM posts_pr p WHERE ((p.user_id="'.$this->user->id.'" AND p.to_user="'.$privusr->id.'") OR (p.user_id="'.$privusr->id.'" AND p.to_user="'.$this->user->id.'" AND p.is_recp_del=0)) '.$start_from.' ORDER BY p.date_lastcomment DESC, p.id DESC ';
	}

	$D->tab		= 'private';
	$D->privtab		= $privtab;
	$D->privusr		= $privusr;
	$D->num_results	= 0;
	$D->num_pages	= 0;
	$D->pg		= 1;
	$D->posts_html	= '';
	if( $q2!='' ) {
		$res	= $db2->query($q2.'LIMIT '.$C->PAGING_NUM_POSTS);
		$D->num_results = $db2->num_rows($res);
		$D->start_from = 0;
		$D->lats_post_id = 0;
		
		$tmpposts	= array();
		$tmpids	= array();
		$postusrs	= array();
		$buff 	= NULL; 	
		while($obj = $db2->fetch_object($res)) {
			$D->start_from = $obj->id;
			if( $D->lats_post_id < $obj->id ){
				$D->lats_post_id = $obj->id;
			}
			
			$buff = new post($obj->type, FALSE, $obj);
			if( $buff->error ) {
				continue;
			}
			$tmpposts[] = $buff;
			if( $this->param('from')=='ajax' && $this->param('onlypost')!="" && $this->param('onlypost')!=$buff->post_tmp_id ) {
				continue;
			}
			$tmpids[]	= $buff->post_tmp_id;
			$postusrs[]	= $buff->post_user->id;
		}
		unset($buff);
		$postusrs = array_unique($postusrs);
	
		post::preload_num_new_comments($tmpids);
		ob_start();
		
		$D->if_follow_me = array();
		if( count($postusrs)>0 ){
			$r = $db2->query('SELECT who FROM users_followed WHERE who IN ('.implode(',', $postusrs).') AND whom="'.$this->user->id.'"');
			while($o = $db2->fetch_object($r)){
				if( isset($D->if_follow_me[$o->who]) ){
					continue;
				}
				$D->if_follow_me[$o->who] = 1;
			}
		}
		$D->i_follow	= array_fill_keys(array_keys($this->network->get_user_follows($this->user->id, FALSE, 'hefollows')->follow_users), 1); 
		$D->i_follow[] 	= $this->user->id; 
		
		foreach($tmpposts as $tmp) {
			$D->p	= $tmp;
			$D->post_show_slow	= FALSE;
			if( $this->param('from')=='ajax' && isset($_POST['lastpostdate']) && $D->p->post_date>intval($_POST['lastpostdate']) ) {
				$D->post_show_slow	= TRUE;
			}
			if( $this->param('from')=='ajax' && $this->param('onlypost')!="" && $this->param('onlypost')!=$D->p->post_tmp_id ) {
				continue;
			}
			$D->parsedpost_attlink_maxlen	= 51;
			$D->parsedpost_attfile_maxlen	= 48;
			if( isset($D->p->post_attached['image']) ) {
				$D->parsedpost_attlink_maxlen	-= 10;
				$D->parsedpost_attfile_maxlen	-= 12;
			}
			if( isset($D->p->post_attached['videoembed']) ) {
				$D->parsedpost_attlink_maxlen	-= 10;
				$D->parsedpost_attfile_maxlen	-= 12;
			}
			$D->show_my_email = FALSE;
			if( isset( $D->if_follow_me[$D->p->post_user->id] ) || $D->p->post_user->id == $this->user->id || $this->user->info->is_network_admin ){
				$D->show_my_email = TRUE;
			}

			$D->protected_profile = FALSE;
			$right_post_type = (!$D->p->is_system_post && !$D->p->is_feed_post);
			
			if($right_post_type && !$D->show_my_email && $D->p->post_user->is_profile_protected){
				$D->protected_profile = TRUE;
			}
			$D->show_reshared_design = FALSE;
			$this->load_template('single_post.php');
		}
		unset($D->p, $tmp, $tmpposts, $tmpids, $right_post_type);

		$D->posts_html	= ob_get_contents();
		ob_end_clean();
	}
	
	if( 0 == $D->num_results && empty($start_from)) {
		$D->tab		= 'private';
		$arr	= array('#USERNAME#'=>$this->user->info->username, '#SITE_TITLE#'=>htmlspecialchars($C->OUTSIDE_SITE_TITLE), '#A1#'=>'<a href="javascript:;" onclick="postform_open();">', '#A2#'=>'</a>', );
		$lngkey_ttl	= 'noposts_dtb_'.$D->tab.'_ttl';
		$lngkey_txt	= 'noposts_dtb_'.$D->tab.'_txt';

		$lngkey_ttl	.= '_'.$privtab;
		$lngkey_txt	.= '_'.$privtab;
		if( $privtab == 'usr' && $privusr ) {
			$arr['#USERNAME2#']	= '<a href="'.$C->SITE_URL.$privusr->username.'">'.$privusr->username.'</a>';
			$arr['#A3#']	= '<a href="javascript:;" onclick="postform_open(({username:\''.$privusr->username.'\'}));">';
			$arr['#A4#']	= '</a>';
		}
		
		if( $privtab != 'usr' || ($privtab == 'usr' && $privusr) ){
			$D->noposts_box_title	= $this->lang($lngkey_ttl, $arr);
			$D->noposts_box_text	= $this->lang($lngkey_txt, $arr);
			$D->posts_html	= $this->load_template('noposts_box.php', FALSE);
		}		
	}
	
	if( $this->param('from') == 'ajax' )
	{
		echo 'OK:'.$D->start_from.':NUM_POSTS:'.$D->num_results.':LAST_POST_ID:'.$D->lats_post_id.':';
		echo $D->posts_html;
		exit;
	}
	
	$D->menu_groups	= $this->user->get_top_groups(5);
	
	$D->tab = 'private';
	$D->tabs_state	= $this->network->get_dashboard_tabstate($this->user->id, array('all','@me','private','commented','feeds', 'tweets', 'notifications'), $D->tab);
	if( isset($D->tabs_state[$D->tab]) ) {
		$D->tabs_state[$D->tab]	= 0;
	}
	
	$D->whattodo_active	= FALSE;
	$D->whattodo_minimized	= FALSE;	
	$D->whattodo_lines	= $this->user->what_to_do_block();
	
	if( count($D->whattodo_lines) > 0 ) {
		$D->whattodo_active	= TRUE;
	}
	if( isset($this->user->sess['what_todo_active']) && !$this->user->sess['what_todo_active'] ) {
		$D->whattodo_minimized	= TRUE;
	}
	
	$D->last_online	= $this->network->get_online_users();
	
	$D->saved_searches	= array();
	$D->saved_searches	= $this->user->get_saved_searches();
	
	$D->post_tags	= array();
	$D->post_tags	= $this->network->get_recent_posttags();
	
	$D->check_new_posts = 'user_private_'.$D->privtab;
	$D->lats_post_id = (isset($D->lats_post_id))? $D->lats_post_id : 0;
	
	$D->mobi_site_url = str_cut(preg_replace('/^http\:\/\//iu', '', $C->SITE_URL).'m', 23);
	
	$this->load_template('privatemessages.php');
	
?>