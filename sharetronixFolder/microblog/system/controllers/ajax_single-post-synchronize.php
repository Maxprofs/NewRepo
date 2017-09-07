<?php
	if( !isset($_POST['post_id'])){
		echo 'ERROR';
		exit;
	}
	
	$p = explode('_', $_POST['post_id']);
	$pid = intval($p[1]);
	if( $pid < 1 ){
		echo 'ERROR';
		exit;
	}
	
	$ptype = trim($p[0]);
	if( $ptype != 'public' && $ptype != 'private' ){
		echo 'ERROR';
		exit;
	}
	
	$this->load_langfile('inside/global.php');
	$this->load_langfile('inside/dashboard.php');
	
	$not_in_groups	= '';
	$without_users	= '';
	if( $ptype == 'public' && (!$this->user->is_logged || !$this->user->info->is_network_admin ) ) {
		$not_in_groups	= array();
		$not_in_groups 	= array_diff( $this->network->get_private_groups_ids(), $this->user->get_my_private_groups_ids() ); 
		$not_in_groups	= (count($not_in_groups)>0) ? ('AND group_id NOT IN('.implode(', ', $not_in_groups).')') : '';
		
		$without_users = array();
		$without_users = array_diff( $this->network->get_post_protected_user_ids(), $this->user->get_my_post_protected_follower_ids() ); 
		$without_users = count($without_users)>0 ? (' AND (group_id>0 OR user_id NOT IN('.implode(', ', $without_users).'))') : '';	
	}
	
	if($ptype == 'public'){
		$db2->query('SELECT * FROM posts WHERE id="'.$pid.'" '.$not_in_groups.$without_users.' LIMIT 1');
	}else{
		$db2->query('SELECT * FROM posts_pr WHERE id="'.$pid.'" AND (user_id="'.$this->db2->e($this->user->id).'" OR to_user="'.$this->db2->e($this->user->id).'") LIMIT 1');
	}
	
	$post = $db2->fetch_object();
	if( !$post ){
		echo 'ERROR';
		exit;
	}
	
	$buff = new post($ptype, FALSE, $post);
	if( $buff->error ) {
		echo 'ERROR';
		exit;
	}
	
	$D->i_follow	= array_fill_keys(array_keys($this->network->get_user_follows($this->user->id, FALSE, 'hefollows')->follow_users), 1); 
	$D->i_follow[$this->user->id] 	= 1; 
	
	$D->if_follow_me = array();
	$db2->query('SELECT who FROM users_followed WHERE who="'.$post->user_id.'" AND whom="'.$this->user->id.'"');
	$res = $db2->fetch_object();
	if( $res ){
		$D->if_follow_me[$res->who] = 1;
	}
	
	ob_start();
	
	$D->p	= $buff;
	$D->post_show_slow	= FALSE;

	$D->parsedpost_attlink_maxlen	= 52;
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
	if($this->param('tab') == 'reshares'){
		$D->show_reshared_design = TRUE;
	}elseif( count( array_intersect(array_keys($D->p->post_reshares) , array_keys($D->i_follow)) )>0 ){
		$D->show_reshared_design = TRUE;
	}
	
	$D->display_comments = ( isset($_POST['display_comments']) && $_POST['display_comments'] == 1 );
	
	$this->load_template('single_post.php');
	
	$posts_html	= ob_get_contents();
	ob_end_clean();
	
	echo 'OK: '.$posts_html;
	exit;
?>