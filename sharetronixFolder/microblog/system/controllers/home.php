<?php
	
	if( $this->user->is_logged ) {
		$this->redirect('dashboard');
	}
	
	if( isset($_SESSION['TWITTER_CONNECTED']) && $_SESSION['TWITTER_CONNECTED'] && $_SESSION['TWITTER_CONNECTED']->id ) {
		$uid	= intval($_SESSION['TWITTER_CONNECTED']->id);
		$db2->query('SELECT email, password FROM users WHERE twitter_uid<>"" AND twitter_uid="'.$uid.'" LIMIT 1');
		if($tmp = $db2->fetch_object()) {
			if( $this->user->login(stripslashes($tmp->email), stripslashes($tmp->password)) ) {
				$this->redirect($C->SITE_URL.'dashboard');
			}
		}
	}
	
		$this->load_langfile('inside/global.php');
	$this->load_langfile('outside/home.php');

	$D->page_title	= $this->lang('os_home_page_title', array('#SITE_TITLE#'=>$C->SITE_TITLE));
	$D->intro_ttl	= $this->lang('os_welcome_ttl', array('#SITE_TITLE#'=>$C->SITE_TITLE));
	$D->intro_txt	= $this->lang('os_welcome_txt', array('#SITE_TITLE#'=>$C->SITE_TITLE));
	if( isset($C->HOME_INTRO_TTL) && !empty($C->HOME_INTRO_TTL) ) {
		$D->page_title	= strip_tags($C->SITE_TITLE.' - '.$C->HOME_INTRO_TTL);
		$D->intro_ttl	= $C->HOME_INTRO_TTL;
	}
	if( isset($C->HOME_INTRO_TXT) && !empty($C->HOME_INTRO_TXT) ) {
		$D->intro_txt	= $C->HOME_INTRO_TXT;
	}
        if( isset($C->FACEBOOK_API_ID, $C->FACEBOOK_API_SECRET) && !empty($C->FACEBOOK_API_ID) && !empty($C->FACEBOOK_API_SECRET) && function_exists('curl_init') && function_exists('json_decode') ){
		require_once( $C->INCPATH.'classes/class_facebook.php');
		$facebook = new Facebook(array(
			'appId'  => $C->FACEBOOK_API_ID,
			'secret' => $C->FACEBOOK_API_SECRET,
		));
		$D->fb_login_url = $facebook->getLoginUrl();

	}else{
		$D->fb_login_url = FALSE;
	}
	$this->load_template('home.php');

?>