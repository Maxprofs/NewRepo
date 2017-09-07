		<?php if( $D->error ) { ?>
		
		<?php } else { ?>
		
			<div class="mpost" style="display:<?= isset($D->hide_notification)&&$D->hide_notification?'none':'block' ?>;" ntf_group="<?= $D->ntf_group; ?>">
				<div class="mpost2">
					<div class="ntfimg"> 
						<?= $D->usr_avatar ?>
					</div>	
					<div class="ntftxt">
						<?= $D->notif_text ?>
						<br />
						<small><?= $D->post_date ?></small>
					</div>
				</div>
			</div>
			
		<?php } ?>