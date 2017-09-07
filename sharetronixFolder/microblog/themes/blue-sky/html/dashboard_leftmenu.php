						<?php if( $D->tab == 'group' ) { ?>
						<script type="text/javascript">
							pf_hotkeyopen_loadgroup	= "<?= $D->onlygroup->title ?>";
						</script>
						<a href="javascript:;" class="npbtn" id="postform_open_button" onclick="<?= ($C->SPAM_CONTROL)? 'spam_control':'postform_open' ?>(({groupname:'<?= $D->onlygroup->title ?>'}));" onfocus="this.blur();"><b><?= $this->lang('dbrd_left_newpost') ?></b></a>
						<?php } else { ?>
						<a href="javascript:;" class="npbtn" id="postform_open_button" onclick="<?= ($C->SPAM_CONTROL)? 'spam_control':'postform_open' ?>();" onfocus="this.blur();"><b><?= $this->lang('dbrd_left_newpost') ?></b></a>
						<?php } ?>
						
						<div id="homefltr">
							<a href="<?= $C->SITE_URL ?>dashboard" class="item mystr<?= $D->tab=='all'?' onitem':'' ?>"><b></b><strong><?= $this->lang('dbrd_leftmenu_all') ?></strong><span><small id="dbrd_tab_all" style="<?= $D->tabs_state['all']==0||$D->tab=='all'?'display:none;':'' ?>"><?= $D->tabs_state['all'] ?></small></span></a>
							<a href="<?= $C->SITE_URL ?>dashboard/tab:@me" class="item atme<?= $D->tab=='@me'?' onitem':'' ?>"><b></b><strong><?= $this->lang('dbrd_leftmenu_@me', array('#USERNAME#'=>$this->user->info->username)) ?></strong><span><small id="dbrd_tab_mention" style="<?= $D->tabs_state['@me']==0||$D->tab=='@me'?'display:none;':'' ?>"><?= $D->tabs_state['@me'] ?></small></span></a>
							<a href="<?= $C->SITE_URL ?>privatemessages" class="item prvt<?= $D->tab=='private'?' onitem':'' ?>"><b></b><strong><?= $this->lang('dbrd_leftmenu_private') ?></strong><span><small id="dbrd_tab_private" style="<?= $D->tabs_state['private']==0||$D->tab=='private'?'display:none;':'' ?>"><?= $D->tabs_state['private'] ?></small></span></a>
							<a href="<?= $C->SITE_URL ?>dashboard/tab:commented" class="item cmnt<?= $D->tab=='commented'?' onitem':'' ?>"><b></b><strong><?= $this->lang('dbrd_leftmenu_commented') ?></strong><span><small id="dbrd_tab_commented" style="<?= $D->tabs_state['commented']==0||$D->tab=='commented'?'display:none;':'' ?>"><?= $D->tabs_state['commented'] ?></small></span></a>
							<a href="<?= $C->SITE_URL ?>notifications" class="item notif<?= $D->tab=='notifications'?' onitem':'' ?>"><b></b><strong><?= $this->lang('dbrd_leftmenu_notifications') ?></strong><span><small id="dbrd_tab_notifications" style="<?= $D->tabs_state['notifications']==0 ? 'display:none;':'' ?>"><?= $D->tabs_state['notifications'] ?></small></span></a>
							<a href="<?= $C->SITE_URL ?>dashboard/tab:feeds" class="item xfed<?= $D->tab=='feeds'?' onitem':'' ?>"><b></b><strong><?= $this->lang('dbrd_leftmenu_feeds') ?></strong><span><small id="dbrd_tab_feeds" style="<?= $D->tabs_state['feeds']==0||$D->tab=='feeds'?'display:none;':'' ?>"><?= $D->tabs_state['feeds'] ?></small></span></a>
							<a href="<?= $C->SITE_URL ?>dashboard/tab:tweets" class="item xtwit<?= $D->tab=='tweets'?' onitem':'' ?>"><b></b><strong><?= $this->lang('dbrd_leftmenu_tweets') ?></strong><span><small id="dbrd_tab_feeds" style="<?= $D->tabs_state['tweets']==0||$D->tab=='tweets'?'display:none;':'' ?>"><?= $D->tabs_state['tweets'] ?></small></span></a>
							<a href="<?= $C->SITE_URL ?>dashboard/tab:bookmarks" class="item fvrt<?= $D->tab=='bookmarks'?' onitem':'' ?>"><b></b><strong><?= $this->lang('dbrd_leftmenu_bookmarks') ?></strong></a>
							<a href="<?= $C->SITE_URL ?>dashboard/tab:reshares" class="item resh<?= $D->tab=='reshares'?' onitem':'' ?>"><b></b><strong><?= $this->lang('dbrd_leftmenu_reshares') ?></strong></a>
							<a href="<?= $C->SITE_URL ?>dashboard/tab:everybody" class="item allp<?= $D->tab=='everybody'?' onitem':'' ?>"><b></b><strong><?= $this->lang('dbrd_leftmenu_everybody', array('#COMPANY#'=>$C->COMPANY)) ?></strong></a>
							<?php if( count($D->menu_groups) > 0 ) { ?>
								<a href="javascript:;" id="dbrd_menu_groupsbtn" class="dropio<?= $D->groupsmenu_active?' dropped':'' ?>" onclick="dbrd_groupmenu_toggle();" onfocus="this.blur();"><?= $this->lang('dbrd_leftmenu_groups') ?></a>
								<div id="dbrd_menu_groups" style="<?= $D->groupsmenu_active?'':'display:none;' ?>">
									<?php foreach($D->menu_groups as $g) { ?>
										<a href="<?= $C->SITE_URL ?>dashboard/tab:group/g:<?= $g->groupname ?>" class="item<?= $D->tab=='group'&&$D->onlygroup->id==$g->id?' onitem':'' ?>" title="<?= htmlspecialchars($g->title) ?>"><b style="background-image:url('<?= $C->IMG_URL.'avatars/thumbs2/'.$g->avatar ?>');"></b><strong><?= htmlspecialchars(str_cut($g->title,14)) ?></strong></a>
									<?php } ?>
								</div>
								<script type="text/javascript">
									dbrd_grpmenu_showst	= <?= $D->groupsmenu_active ? 1 : 0 ?>;
								</script>
							<?php } ?>
						</div>