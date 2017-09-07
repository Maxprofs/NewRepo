<?php
	
	if( !$this->network->id ) {
		$this->redirect('home');
	}
	if( !$this->user->is_logged ) {
		$this->redirect('signin');
	}
	
	$this->load_langfile('inside/global.php');
	$this->load_langfile('inside/settings.php');
	
	require_once( $C->INCPATH.'helpers/func_languages.php' );
	
	$D->page_title	= $this->lang('settings_system_pagetitle', array('#SITE_TITLE#'=>$C->SITE_TITLE));
	
	$D->menu_timezones	= array();
	if( floatval(substr(phpversion(),0,3)) >= 5.2 ) {
		$tmp	= array();
		foreach(DateTimeZone::listIdentifiers() as $v) {
			if( substr($v, 0, 4) == 'Etc/' ) { continue; }
			if( FALSE === strpos($v, '/') ) { continue; }
			$sdf	= new DateTimeZone($v);
			if( ! $sdf ) { continue; }
			$tmp[$v]	= $sdf->getOffset( new DateTime("now", $sdf) );
		}
		asort($tmp);
		foreach($tmp as $k=>$v) {
			$D->menu_timezones[$k]	= str_replace(array('/','_'), array(' / ',' '), $k);
		}
		asort($D->menu_timezones);
	}
	
	$D->language		= $C->LANGUAGE;
	$D->menu_languages	= array();
	foreach(get_available_languages(FALSE) as $k=>$v) {
		$D->menu_languages[$k]	= $v->name;
	}
	
	$D->submit	= FALSE;
	$D->error	= FALSE;
	$D->errmsg	= '';
	
	$D->timezone	= $C->DEF_TIMEZONE;
	if( ! empty($this->user->info->timezone) ) {
		$D->timezone	= $this->user->info->timezone;
	}
	
	if( isset($_POST['sbm']) ) {
		$D->submit	= TRUE;
		if( isset($_POST['timezone']) && isset($D->menu_timezones[$_POST['timezone']]) ) {
			$D->timezone	= $_POST['timezone'];
		}
		$D->language	= $C->LANGUAGE;
		if( isset($_POST['language']) && isset($D->menu_languages[$_POST['language']]) ) {
			$D->language	= $_POST['language'];
		}
		$db2->query('UPDATE users SET language="'.$db2->e($D->language).'", timezone="'.$db2->e($D->timezone).'" WHERE id="'.$this->user->id.'" LIMIT 1');
		
		$this->user->sess['LOGGED_USER']	= $this->network->get_user_by_id($this->user->id, TRUE);
		$this->user->info	= & $this->user->sess['LOGGED_USER'];
		date_default_timezone_set($D->timezone);
		
		$this->redirect('settings/system');
	}
	
	$this->load_template('settings_system.php');
	
?>