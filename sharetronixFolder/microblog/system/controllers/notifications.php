<?php
	
	if( !$this->user->is_logged ) {
		$this->redirect('home');
	}

	$this->load_langfile('inside/global.php');
	$this->load_langfile('inside/dashboard.php');
	$this->load_langfile('inside/notifications.php');
	
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
	
	$D->tab	= 'notifications';
	$notifications = array();
	
	$db2->query( 'SELECT notif_type, in_group_id, from_user_id, notif_object_type, notif_object_id, date FROM notifications WHERE to_user_id="'.intval($this->user->id).'" ORDER BY id DESC LIMIT 50' );
	while( $n = $db2->fetch_object() ){
		
		//this could be removed
		if( $n->in_group_id > 0 ){
			$g = $this->network->get_group_by_id( $n->in_group_id );
			if( !$g ){
				continue;
			}
		}
		//end this could be removed
		
		if( !isset($notifications[ $n->notif_type ]) ){
			$notifications[ $n->notif_type ] = array();
		}
	
		$ndx = ( $n->notif_object_id > 0 )? $n->notif_object_type.'_'.$n->notif_object_id : 'none_0';

		if( !isset($notifications[ $n->notif_type ][ $ndx ]) ){
			$notifications[ $n->notif_type ][ $ndx ] = array();
		}
		$notifications[ $n->notif_type ][$ndx][$n->from_user_id.'-'.$n->in_group_id] = array( 'gid' => $n->in_group_id, 'from_uid' => $n->from_user_id, 'date' => $n->date, 'nobj' => array( 'type' => $n->notif_object_type, 'id' => $n->notif_object_id ));
	}
	
	$D->posts_html = '';
	ob_start();
	
	$D->hide_notification = FALSE;
	$D->ntf_group = '';
	
	
	
	if( count($notifications) > 0 ){ 
	
		foreach( $notifications as $notif_type => $notif_array ){ 
			
			$notification_group_count = count($notif_array); 
			$D->ntf_group = $notif_type;
			echo '<div id="dv_'.$D->ntf_group.'">';
			if($notification_group_count>1){
				echo '<hr class="hidden_ntf_group_hr" id="hr_'.$D->ntf_group.'" >';
			}	
			foreach( $notif_array as $nobj_name => $notif_objects ){ 
				$numb = count($notif_objects); 
				$notif_objects = reset($notif_objects);
				$date = $notif_objects['date'];
				$u = $this->network->get_user_by_id( $notif_objects['from_uid'] );
				if( !$u ){
					continue;
				}
				$is_in_group = $notif_objects['gid'];
				
				//the info for the langiage file
				$D->usr_avatar = '<img src="'.$C->IMG_URL.'avatars/thumbs3/'.$u->avatar.'" alt="'.htmlspecialchars($u->fullname).'" title="'.htmlspecialchars($u->fullname).'" />';
				$usr = '<a href="'.$C->SITE_URL.$u->username.'">'.$u->username.'</a>';
				$a1 = '<a href="javascript: void(0);" onClick="show_notif_users(\''.trim($notif_type).'\' ,\''.trim($nobj_name).'\', \''.$is_in_group.'\' );" >';
				$a2 = '</a>';
				$a3 = '';
				$notifobj = '';
				$error_continue = FALSE;
				//end

				$about = explode('_', $nobj_name); 
				if( $about[0] != '0' || $about[1] != '0' ){
					switch( $about[0] ){
						case 'post': 
										$a3 = '<a href="'.$C->SITE_URL.'view/post:'.intval($about[1]).'">';
										break;
						case 'network': 
										$notifobj = '<a href="'.$C->SITE_URL.'">'.mb_substr($C->SITE_TITLE, 0, 20).'</a>';
										break;
						case 'group': 
										$g1 = $this->network->get_group_by_id( $about[1] ); 
										$g1 = $g1? $g1->groupname : '';  
										$notifobj = '<a href="'.$C->SITE_URL.$g1.'">'.$g1.'</a>'; 
										if( empty($g1) ){
											$error_continue = TRUE;
										}
										break;
						case 'user': 
										if( !$is_in_group ){
											$u1 = $this->network->get_user_by_id( $about[1] );
											$u1 = $u1? $u1->username : ''; 
											$notifobj = '<a href="'.$C->SITE_URL.$u1.'">'.$u1.'</a>';
											
											if( empty($u1) ){
												$error_continue = TRUE;
											}
										}else{
											$g1 = $this->network->get_group_by_id( $is_in_group );
											$g1 = $g1? $g1->groupname : '';  
											$notifobj = '<a href="'.$C->SITE_URL.$g1.'">'.$g1.'</a>';
											
											if( empty($g1) ){
												$error_continue = TRUE;
											}
										}
										break;
					}
				}
				
				if( $error_continue ){
					continue;
				}
				
				$D->error = FALSE;
				$D->post_show_slow = FALSE;
				$D->post_date = post::parse_date($date);
				$D->notif_text = '';
				
				if( $numb == 1 ){
					$D->notif_text = $this->lang('single_msg_'.$notif_type, array('#USER#'=>$usr, '#A3#'=>$a3, '#A2#'=>$a2, '#NOTIFOBJ#'=>$notifobj));
				}elseif( $numb > 1 ){ 
					$D->notif_text = $this->lang('grouped_msg_'.$notif_type, array('#USER#'=>$usr, '#NUM#'=>($numb-1), '#A1#'=>$a1, '#A2#'=>$a2, '#A3#'=>$a3, '#NOTIFOBJ#'=>$notifobj));
				}
				
				$this->load_template('single_notification.php');
				if( $notification_group_count != 1 ){
					$D->hide_notification = TRUE;	
				}
			}
			echo '</div>';
			
			if($notification_group_count>1){
				echo '<a id="a_'.$D->ntf_group.'_show" href="javascript: void(0);" onClick="show_notification_group(\''.$D->ntf_group.'\');" class="hidden_ntf_group_link" style="display: block;"> '.( $notification_group_count-1).' '.$this->lang('dbrd_more_notifications_link').' </a>';
				echo '<hr class="hidden_ntf_group_hr" id="hr_'.$D->ntf_group.'" >';
			}	
		
			$D->hide_notification = FALSE;
			
		}
	}else{
		$D->noposts_box_title	= $this->lang('noposts_dtb_notifications_ttl');
		$D->noposts_box_text	= $this->lang('noposts_dtb_notifications_txt');
		$this->load_template('noposts_box.php');
	}
	$D->posts_html	= ob_get_contents();
	ob_end_clean();
		
	$D->tabs_state	= $this->network->get_dashboard_tabstate($this->user->id, array('all','@me','private','commented','feeds', 'tweets', 'notifications'), $D->tab);
	if( isset($D->tabs_state[$D->tab]) ) {
		$D->tabs_state[$D->tab]	= 0;
	}
	
	$D->menu_groups	= $this->user->get_top_groups(5);
	
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
	
	$D->post_tags	= array();
	$D->post_tags	= $this->network->get_recent_posttags();
	
	$D->check_new_posts = $this->request[0].'user_private_'.$D->tab;
	$D->tab = 'notifications';
	$D->mobi_site_url = str_cut(preg_replace('/^http\:\/\//iu', '', $C->SITE_URL).'m', 23);
	
	$this->load_template('notifications.php');
	
?>