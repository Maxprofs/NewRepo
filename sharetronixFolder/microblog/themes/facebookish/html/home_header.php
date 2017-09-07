<?php
	
	$this->load_langfile('inside/header.php');
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
	<head>
		<title><?= htmlspecialchars($D->page_title) ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<link href="<?= $C->SITE_URL ?>themes/default/css/inside.css" type="text/css" rel="stylesheet" />
		<script type="text/javascript" src="<?= $C->SITE_URL ?>themes/default/js/swfobject.js"></script>
		<base href="<?= $C->SITE_URL ?>" />
		<script type="text/javascript"> var siteurl = "<?= $C->SITE_URL ?>"; </script>
		
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
		<?php if( $this->lang('global_html_direction') == 'rtl' ) { ?>
		<style type="text/css"> #site { direction:rtl; } </style>
		<?php } ?>
			<script type="text/javascript">
				function ajax_init(is_xml)
				{
					var w = window;
					var req = false;
					if (w.XMLHttpRequest) {
						req = new XMLHttpRequest();
						if (req.overrideMimeType) {
							if( is_xml ) { req.overrideMimeType("application/xml"); }
							else { req.overrideMimeType("text/plain"); }
						}
					} else if (w.ActiveXObject) {
						try { req = new w.ActiveXObject("MSXML3.XMLHTTP"); } catch(exptn) {
						try { req = new w.ActiveXObject("MSXML2.XMLHTTP.3.0"); } catch(exptn) {
						try { req = new w.ActiveXObject("Msxml2.XMLHTTP"); } catch(exptn) {
						try { req = new w.ActiveXObject("Microsoft.XMLHTTP"); } catch(exptn) {
						}}}}
					}
					return req;
				}
			</script>
                <script src="<?= $C->SITE_URL ?>i/js/jquery.min.js"></script>
                <script src="<?= $C->SITE_URL ?>themes/default/js/jquery.placeholder.js"></script>
				<link href="<?= $C->SITE_URL ?>themes/default/css/jquery-ui-1.8.17.custom.css?v=<?= $C->VERSION ?>" type="text/css" rel="stylesheet" />
                <script type="text/javascript" src="<?= $C->IMG_URL ?>js/jquery-ui.min.js?v=<?= $C->VERSION ?>"></script>
                <script>
                    $(function(){
                        $('input[placeholder], textarea[placeholder]').placeholder();
                   });
                </script>
	</head>
	<body>
		<div id="home-header">            
            <div style ="margin-left: auto; margin-right: auto;  height: 70px;">
                <div align="left" style="margin-left: auto; margin-right: auto; width: 980px;">
                    <?php if( $C->HDR_SHOW_LOGO==2 && !empty($C->HDR_CUSTOM_LOGO) ) { ?>
                    <?php //Place your custom logo here  ?>
                    <?php } else { ?>
                    <a href="<?= $C->SITE_URL ?>dashboard"  title="<?= htmlspecialchars($C->SITE_TITLE) ?>">
                        <img src="<?= $C->SITE_URL ?>themes/default/imgs/new_logo.png" alt="<?= htmlspecialchars($C->SITE_TITLE) ?>" style="float: left; border: 0px;"  />
                    </a>
                    <?php } ?>
                    <div id="header_login" style="float: right; width: 350px; ">
                       <form method="post" action="<?= $C->SITE_URL ?>signin">
                           <input autocomplete="off" class="text_field" id="user_name" placeholder="<?= $this->lang('os_login_unm') ?>" name="email" size="20" tabindex="1" type="text" />
                           <input autocomplete="off" class="text_field" id="user_name" placeholder="<?= $this->lang('os_login_pwd') ?>" name="password" size="20" tabindex="2" type="password" />
                           <input type="submit" class="button_signin" style="width: 70px; height: 26px; border: 0px; font-weight:bold; padding-top: 3px; margin-right:0" value="<?= $this->lang('os_login_btn') ?>" tabindex="3" />
                            <label style="width: 140px; color: #ffffff; text-align: left; float:left">
                                    <input type="checkbox" name="rememberme" value="1" tabindex="4" style="margin-top: 3px; width: 11px; height: 11px; padding: 0px;" />
                                    <span style="font-size: 10px;"><?= $this->lang('os_login_rem') ?></span>
                            </label>
                             <div style="float: left; margin-top: 4px;">
                                    <a style="font-size: 10px; color: #ffffff" href="<?= $C->SITE_URL ?>signin/forgotten"><?= $this->lang('os_login_frg') ?></a>
                            </div>
                           <div class="klear"></div>
                        </form>
                    </div>
                </div>
               <div class="klear"></div>
            </div>
			<?php if( isset($_GET['installed']) && $_GET['installed']=='ok' ) { ?>
                <style>
                    .mydialog{
                      background-color:#1c93e7 !important;
                      color: #ffffff;
                      font-weight: bold;
                    }
                    .ui-dialog-titlebar { background-color: #0a346e !important; }
                </style>
                <script language="javascript">
                    $("<div class='mydialog'><br><?= $this->lang('sharetronix_install_ok_txt',array('#VER#'=>$C->VERSION)) ?></div>").dialog({
                        modal: true,
                        height: 'auto',
                        title: 'Done'                       
                    });
                </script>
            <?php } ?>