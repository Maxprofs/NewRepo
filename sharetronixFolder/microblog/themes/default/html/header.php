<?php
	
	$this->user->write_pageview();
	
	$hdr_search	= ($this->request[0]=='members' ? 'users' : ($this->request[0]=='groups' ? 'groups' : ($this->request[0]=='search' ? $D->tab : 'posts') ) );
	
	$this->load_langfile('inside/header.php');
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
	<head>
		<title><?= htmlspecialchars($D->page_title) ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="keywords" content="microblogging, sharetronix, blogtronix, enterprise microblogging">
		<link href="<?= $C->SITE_URL ?>themes/<?= $C->THEME ?>/css/inside.css?v=<?= $C->VERSION ?>" type="text/css" rel="stylesheet" />
		<link href="<?= $C->SITE_URL ?>themes/<?= $C->THEME ?>/css/jquery-ui-1.8.17.custom.css?v=<?= $C->VERSION ?>" type="text/css" rel="stylesheet" />
		<script type="text/javascript" src="<?= $C->IMG_URL ?>js/jquery.min.js?v=<?= $C->VERSION ?>"></script>
		<script type="text/javascript" src="<?= $C->IMG_URL ?>js/jquery-ui.min.js?v=<?= $C->VERSION ?>"></script>
		<script type="text/javascript" src="<?= $C->SITE_URL ?>i/js/jquery.outside.js?v=<?= $C->VERSION ?>"></script>
		<script type="text/javascript" src="<?= $C->SITE_URL ?>i/js/functions.js?v=<?= $C->VERSION ?>"></script>
		<script type="text/javascript" src="<?= $C->SITE_URL ?>i/js/posts.js?v=<?= $C->VERSION ?>"></script>
		<script type="text/javascript" src="<?= $C->SITE_URL ?>i/js/dashboard.js?v=<?= $C->VERSION ?>"></script>
		<script type="text/javascript" src="<?= $C->SITE_URL ?>i/js/admin.js?v=<?= $C->VERSION ?>"></script>
		<script type="text/javascript" src="<?= $C->SITE_URL ?>i/js/inside_postform.js?v=<?= $C->VERSION ?>"></script>
		<script type="text/javascript" src="<?= $C->SITE_URL ?>i/js/inside_autocomplete.js?v=<?= $C->VERSION ?>"></script>
		<?php if($this->request[0]=='view'){ ?><script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script><?php } ?>
		<base href="<?= $C->SITE_URL ?>" />
		<script type="text/javascript"> 
			var siteurl = "<?= $C->SITE_URL ?>"; 
			var paging_num_posts = "<?= $C->PAGING_NUM_POSTS ?>"; 
		</script>
		
		<?php if( isset($D->page_favicon) ) { ?>
		<link href="<?= $D->page_favicon ?>" type="image/x-icon" rel="shortcut icon" />
		<?php } elseif( $C->HDR_SHOW_FAVICON == 1 ) { ?>
		<link href="<?= $C->SITE_URL.'themes/default/imgs/favicon.ico' ?>" type="image/x-icon" rel="shortcut icon" />
		<?php } elseif( $C->HDR_SHOW_FAVICON == 2 ) { ?>
		<link href="<?= $C->IMG_URL.'attachments/'.$this->network->id.'/'.$C->HDR_CUSTOM_FAVICON ?>" type="image/x-icon" rel="shortcut icon" />
		<?php } ?>
		<?php if(isset($D->rss_feeds)) { foreach($D->rss_feeds as &$f) { ?>
		<link rel="alternate" type="application/atom+xml" title="<?= $f[1] ?>" href="<?= $f[0] ?>" />
		<?php }} ?>
		<?php if( $C->SPAM_CONTROL ){ ?>
		<script type="text/javascript"> spam_control_check = true; spam_control_message = "<?= $this->lang('newpost_spam_filter_msg') ?>"; </script>
		<?php } ?>
		<?php if( $this->lang('global_html_direction') == 'rtl' ) { ?>
		<style type="text/css"> #site { direction:rtl; } </style>
		<?php } ?>
	</head>
	<body>
		<div id="site">
			<div id="wholesite">
				<div id="toprow" class="<?= in_array( $this->request[0], array('dashboard', 'privatemessages', 'notifications') )? 'specialhomelink' : '' ?>">
					<div id="toprow2">
						<?php if( $C->HDR_SHOW_LOGO==2 && !empty($C->HDR_CUSTOM_LOGO) ) { ?>
						<a href="<?= $C->SITE_URL ?>dashboard" id="logolink_custom" title="<?= htmlspecialchars($C->SITE_TITLE) ?>"><img src="<?= $C->IMG_URL.'attachments/'.$this->network->id.'/'.$C->HDR_CUSTOM_LOGO ?>" alt="<?= htmlspecialchars($C->SITE_TITLE) ?>" /></a>	
						<?php } else { ?>
						<a href="<?= $C->SITE_URL ?>dashboard" id="logolink" title="<?= htmlspecialchars($C->SITE_TITLE) ?>"><strong><?= htmlspecialchars($C->SITE_TITLE) ?></strong></a>
						<?php } ?>
						<div id="userstuff">
							<?php if( $this->user->is_logged ) { ?>
							<div id="avatar"><img src="<?= $C->IMG_URL ?>avatars/thumbs2/<?= $this->user->info->avatar ?>" alt="" /></div>
							<a href="<?= $C->SITE_URL ?><?= $this->user->info->username ?>" id="username"><span><?= $this->user->info->username ?></span></a>
							<div id="userlinks">
								<a href="<?= $C->SITE_URL ?>settings"><b><?= $this->lang('hdr_nav_settings') ?></b></a>
								<a href="<?= $C->SITE_URL ?>signout"><b><?= $this->lang('hdr_nav_signout') ?></b></a>
							</div>
							<?php } else { ?>
							<div id="userlinks">
								<a href="<?= $C->SITE_URL ?>signin"><b><?= $this->lang('hdr_nav_signin') ?></b></a>
								<a href="<?= $C->SITE_URL ?>signup"><b><?= $this->lang('hdr_nav_signup') ?></b></a>
							</div>
							<?php } ?>
							<div id="topsearch">
								<form name="search_form" method="post" action="<?= $C->SITE_URL ?>search">
									<input type="hidden" name="lookin" value="<?= $hdr_search ?>" />
									<div id="searchbtn"><input type="submit" value="<?= $this->lang('hdr_search_submit') ?>" /></div>
									<div class="searchselect">
										<a id="search_drop_lnk" href="javascript:;" onfocus="this.blur();" onclick="try{msgbox_close();}catch(e){}; dropdiv_open('search_drop_menu1');"><?= $this->lang('hdr_search_'.$hdr_search) ?></a>
										<div id="search_drop_menu1" class="searchselectmenu" style="display:none;">
											<a href="javascript:;" onclick="hdr_search_settype('posts',this.innerHTML);dropdiv_close('search_drop_menu1');" onfocus="this.blur();"><?= $this->lang('hdr_search_posts') ?></a>
											<a href="javascript:;" onclick="hdr_search_settype('users',this.innerHTML);dropdiv_close('search_drop_menu1');" onfocus="this.blur();"><?= $this->lang('hdr_search_users') ?></a>
											<a href="javascript:;" onclick="hdr_search_settype('groups',this.innerHTML);dropdiv_close('search_drop_menu1');" onfocus="this.blur();" style="border-bottom:0px;"><?= $this->lang('hdr_search_groups') ?></a>
										</div>
									</div>
									<div id="searchinput"><input type="text" name="lookfor" value="<?= isset($D->search_string)?htmlspecialchars($D->search_string):'' ?>" rel="autocomplete" autocompleteoffset="-6,4" /></div>
								</form>
							</div>
						</div>
					</div>
				</div>
				<div id="nethdr1">
					<div id="nethdr2">
						<div id="netnav" class="specialhomelink">
							<a href="<?= $C->SITE_URL ?>dashboard" class="<?= in_array( $this->request[0], array('dashboard', 'privatemessages', 'notifications') )?'onnettab ':'' ?>homelink"><span><b><?= $this->lang('hdr_nav_home') ?></b></span></a>
							<?php if(!$C->PROTECT_OUTSIDE_PAGES || $this->user->is_logged){ ?>
							<a href="<?= $C->SITE_URL ?>members" class="<?= $this->request[0]=='members'?'onnettab':'' ?>"><span><b><?= $this->lang('hdr_nav_users') ?></b></span></a>
							<a href="<?= $C->SITE_URL ?>groups" class="<?= $this->request[0]=='groups'?'onnettab':'' ?>"><span><b><?= $this->lang('hdr_nav_groups') ?></b></span></a>
							<?php } ?>
							<?php if($this->user->is_logged){?>
								<a href="<?= $C->SITE_URL ?>leaders" class="<?= $this->request[0]=='leaders'?'onnettab':'' ?>"><span><b><?= $this->lang('hdr_search_competitions') ?></b></span></a>
							<?php } ?>
							<?php if( $this->user->is_logged && $this->user->info->is_network_admin == 1 ) { ?>
							<a href="<?= $C->SITE_URL ?>admin" class="<?= $this->request[0]=='admin'?'onnettab':'' ?>"><span><b><?= $this->lang('hdr_nav_admin') ?></b></span></a>
							<?php } ?>
						</div>
					</div>
				</div>
				<div id="slim_msgbox" style="display:none;">
					<strong id="slim_msgbox_msg"></strong>
					<a href="javascript:;" onclick="msgbox_close('slim_msgbox'); this.blur();" onfocus="this.blur();"><b><?= $this->lang('pf_msg_okbutton') ?></b></a>
				</div>
				
				<div id="pagebody">