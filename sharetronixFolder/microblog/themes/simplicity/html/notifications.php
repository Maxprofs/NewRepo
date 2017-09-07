<?php
	
	$this->load_template('header.php');
	
?>
					<?php $this->load_template('postform.php');	?>
					
					<div id="home_left">
						<?php $this->load_template('dashboard_leftmenu.php');	?>
					</div>
					<div id="home_content">
						<div class="ttl" style="margin-bottom:8px;">
							<div class="ttl2">
								<h3><?= $this->lang('dbrd_leftmenu_notifications') ?></h3>	
							</div>
						</div>
						<div id="posts_html">
							<?= $D->posts_html ?>
						</div>
					</div>
					<div id="home_right">
						<?php $this->load_template('dashboard_rightcolumn.php');	?>
					</div>
					<div class="klear"></div>
<?php
	
	$this->load_template('footer.php');
	
?>