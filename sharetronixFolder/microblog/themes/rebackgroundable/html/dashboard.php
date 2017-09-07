<?php
	
	$this->load_template('header.php');
	
?>
					<?php $this->load_template('postform.php');	?>
					
					<div id="home_left">
						<?php $this->load_template('dashboard_leftmenu.php');	?>
					</div>
					<div id="home_content">
						<?php if( $D->tab == 'group' ) { ?>
						<div class="ttl" style="margin-bottom:8px;">
							<div class="ttl2">
								<h3><a href="<?= $C->SITE_URL ?><?= $D->onlygroup->groupname ?>" title="<?= htmlspecialchars($D->onlygroup->title) ?>"><?= htmlspecialchars(str_cut($D->onlygroup->title, 50)) ?></a></h3>
							</div>
						</div>
						<?php }elseif( $D->tab == 'reshares' ) { ?>
						<div class="htabs" style="margin-bottom:6px; margin-top:0px;">
							<strong><?= $this->lang('dbrd_reshtab_title') ?></strong>
							<a href="<?= $C->SITE_URL ?>dashboard/tab:reshares/reshtab:byme" class="<?= $D->reshtab=='byme'?'onhtab':'' ?>"><b><?= $this->lang('dbrd_reshtab_subtab_byme') ?></b></a>
							<a href="<?= $C->SITE_URL ?>dashboard/tab:reshares/reshtab:byother" class="<?= $D->reshtab=='byother'?'onhtab':'' ?>"><b><?= $this->lang('dbrd_reshtab_subtab_byother') ?></b></a>
						</div>
						<?php } elseif( $D->tab != 'private' ) { ?>
						<div class="ttl" style="margin-bottom:8px;">
							<div class="ttl2">
								<h3><?= $this->lang('dbrd_poststitle_'.$D->tab, array('#USERNAME#'=>$this->user->info->username, '#COMPANY#'=>htmlspecialchars($C->COMPANY))) ?></h3>
							</div>
						</div>
						<?php } else { ?>
						<div class="htabs" style="margin-bottom:6px; margin-top:0px;">
							<strong><?= $this->lang('dbrd_privtab_title') ?></strong>
							<a href="<?= $C->SITE_URL ?>dashboard/tab:private/privtab:all" class="<?= $D->privtab=='all'?'onhtab':'' ?>"><b><?= $this->lang('dbrd_privtab_subtab_all') ?></b></a>
							<a href="<?= $C->SITE_URL ?>dashboard/tab:private/privtab:inbox" class="<?= $D->privtab=='inbox'?'onhtab':'' ?>"><b><?= $this->lang('dbrd_privtab_subtab_inbox') ?></b></a>
							<a href="<?= $C->SITE_URL ?>dashboard/tab:private/privtab:sent" class="<?= $D->privtab=='sent'?'onhtab':'' ?>"><b><?= $this->lang('dbrd_privtab_subtab_sent') ?></b></a>
							<a href="<?= $C->SITE_URL ?>dashboard/tab:private/privtab:usr" class="<?= $D->privtab=='usr'?'onhtab':'' ?>"><b><?= $this->lang('dbrd_privtab_subtab_usr') ?></b></a>
						</div>
						<div id="pmfilter" style="display:<?= $D->privtab=='usr'&&!$D->privusr?'block':'none' ?>;">
							<form name="privform" method="POST" action="javascript:;" onsubmit="privmsg_usrfilter_setusr(this.privusr_inp.value,true); return false;">
							<?= $this->lang('dbrd_privtab_usrtb_txt') ?>: <input type="text" name="privusr_inp" value="" rel="autocomplete" autocompletecallback="privmsg_usrfilter_setusr(word);" style="width:178px;" />
							</form>
						</div>
						<div id="pmfilterok" style="display:<?= $D->privtab=='usr'&&$D->privusr?'block':'none' ?>;">
							<strong><?= $this->lang('dbrd_privtab_usrtb_txt') ?>&nbsp;</strong> <b><?= $D->privusr?$D->privusr->username:'' ?></b>
							<a href="javascript:;" onclick="privmsg_usrfilter_reset();" onfocus="this.blur();"><small><?= $this->lang('dbrd_privtab_usrtb_txt_x') ?></small></a>
						</div>
						<script type="text/javascript">
							var tmpfnc	= function() { try { document.privform.privusr_inp.focus(); } catch(e) {} };
							if( d.addEventListener ) {
								d.addEventListener("load", tmpfnc, false);
								w.addEventListener("load", tmpfnc, false);
							}
							else if( d.attachEvent ) {
								d.attachEvent("onload", tmpfnc);
								w.attachEvent("onload", tmpfnc);
							}
						</script>
						<?php } ?>
						<?php if($this->param('msg')=='deletedpost') { ?>
						<?= okbox($this->lang('msg_post_deleted_ttl'), $this->lang('msg_post_deleted_txt'), TRUE, 'margin-bottom:5px;') ?>
						<?php } ?>
						<script type="text/javascript">
							var last_post_id = <?= $D->lats_post_id ?>;
							var about_user_id = <?= $this->user->id ?>; 
							var about_tab = "<?= $D->check_new_posts ?>"; 
							var group_id = <?= isset($D->onlygroup->id)? $D->onlygroup->id : 0 ?>;
							
							if( d.addEventListener ) {
								d.addEventListener("load", new_activity_check, false);
								w.addEventListener("load", new_activity_check, false);
							}
							else if( d.attachEvent ) {
								d.attachEvent("onload", new_activity_check);
								w.attachEvent("onload", new_activity_check);
							}
						</script>
						
						<div id="newactivity">
							<a id="loadnewactivity" href="javascript:;" onclick="new_activity_show();"></a>
							<img id="loading_posts" src="<?= $C->SITE_URL.'themes/'.$C->THEME ?>/imgs/loading_posts.gif" />
						</div>
						<!-- new -->
						
						<div id="posts_html">
							<div id="insertAfter"></div>
							<?= $D->posts_html ?>
						</div>
						
						<!-- new -->
						<?php if( $D->num_results == $C->PAGING_NUM_POSTS ) { ?>
						
						<div id="loadmore">	
							<a href="javascript: void(0);" id="loadmorelink" onclick="load_more_results('posts_html', <?= $D->start_from ?>);">Show more</a> 
							<img id="loadmore-img" src="<?= $C->SITE_URL.'themes/'.$C->THEME ?>/imgs/loading_posts.gif" />
							<a href="javascript: void(0);" id="loadmoretotop" onClick="scroll_to_top();">Top</a>			
						</div>
						
						<?php } ?>
					</div>
					<div id="home_right">
						<?php $this->load_template('dashboard_rightcolumn.php');	?>
					</div>
					<div class="klear"></div>
<?php
	
	$this->load_template('footer.php');
	
?>