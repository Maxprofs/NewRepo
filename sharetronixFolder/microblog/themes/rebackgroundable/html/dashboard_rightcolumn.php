						<?php if( $C->MOBI_DISABLED==0 ) { ?>
						<div id="mobiad">
							<strong><?= $this->lang('dbrd_right_mobi_title', array('#SITE_TITLE#' => $C->OUTSIDE_SITE_TITLE) ) ?></strong>
							<?= $this->lang('dbrd_right_mobi_text2') ?> <b title='<?= $C->SITE_URL.'m'; ?>'><a href="<?= $C->SITE_URL.'m'; ?>"><?= $D->mobi_site_url; ?></a></b>
						</div>
						<?php } ?>
						
						<div style="background: url('<?= $C->IMG_URL ?>custom/sharetronix-download_l.png') no-repeat; display: none; width: 195px; height: 56px; margin: 0 0 10px 0;">
							<p style="margin: 20px 0 0 50px; padding: 0; font-weight: bold;">
								<a href="http://sharetronix.com/sharetronix/download" target="_blank" style="font-size: 10px;">
									Download Sharetronix
								</a>	
							</p>
						</div>
						
						<div style="background: url('<?= $C->IMG_URL ?>custom/sharetronix-addons_l.png') no-repeat; display: none; width: 195px; height: 56px; margin: 0 0 10px 0;">
							<p style="margin: 20px 0 0 50px; padding: 0; font-weight: bold;">
								<a href="http://sharetronix.com/sharetronix/addons" target="_blank" style="font-size: 10px;">
									Sharetronix Add-ons
								</a>	
							</p>
						</div>
						
						<?php if($D->whattodo_active) { ?>
						<a href="javascript:;" id="closedgtd" style="display:<?= $D->whattodo_minimized?'block':'none' ?>;" onclick="dbrd_whattodo_show();" onfocus="this.blur();"><b><?= $this->lang('dbrd_whattodo_title_mnm') ?></b></a>
						<div id="greentodo" style="display:<?= $D->whattodo_minimized?'none':'block' ?>;">
							<div id="greentodo2">
								<div id="gtdttl">
									<b><?= $this->lang('dbrd_whattodo_title') ?></b>
									<a href="javascript:;" title="<?= $this->lang('dbrd_whattodo_closebtn') ?>" onclick="dbrd_whattodo_hide();" onfocus="this.blur();"></a>
								</div>
								<div id="gtdlist">
									<?php foreach($D->whattodo_lines as $k=>$line) { ?>
									<a href="<?= $line[0] ?>" class="<?= $k==0 ? 'frst' : ($k==count($D->whattodo_lines)-1 ? 'last' : '') ?>"><?= $this->lang($line[1]) ?></a>
									<?php } ?>
								</div>
							</div>
						</div>
						<?php } ?>
						
						<?php if( count($D->last_online) > 0 ) { ?>
						<div class="ttl" style="margin-top:0px; margin-bottom:8px;"><div class="ttl2"><h3><?= $this->lang('dbrd_right_lastonline') ?></h3></div></div>
						<div class="slimusergroup" style="margin-right:-10px; margin-bottom:5px;">
							<?php foreach($D->last_online as $u) { ?>
							<a href="<?= userlink($u['username']) ?>" class="slimuser" title="<?= htmlspecialchars($u['username']) ?>"><img src="<?= $C->IMG_URL ?>avatars/thumbs1/<?= $u['avatar'] ?>" alt="" style="padding:3px;" /></a>
							<?php } ?>
						</div>
						<?php } ?>
						
						<?php if( count($D->post_tags) > 0 ) { ?>
						<div class="ttl" style="margin-top:0px; margin-bottom:8px;"><div class="ttl2"><h3><?= $this->lang('dbrd_right_posttags') ?></h3></div></div>
						<div class="taglist" style="margin-bottom:5px;">
							<?php foreach($D->post_tags as $tmp) { ?>
							<a href="<?= $C->SITE_URL ?>search/posttag:%23<?= $tmp ?>" title="#<?= htmlspecialchars($tmp) ?>"><small>#</small><?= htmlspecialchars(str_cut($tmp,25)) ?></a>
							<?php } ?>
						</div>
						<?php } ?>