<?php
    $this->load_template('home_header.php');
?>
</div>
 <div style ="margin-left: auto; margin-right: auto; margin-top: 20px;  width: 980px;">
     <div style="float: left; color: #0055a4; width: 200px;">
         <h1 style="margin-top: 0px;">Register to <?= $C->SITE_TITLE?></h1>
     </div>
     <div  style="float: left; width: 770px;">
          <form method="post" action="<?= $C->SITE_URL ?>signup<?= ((isset($D->reg_id,$D->reg_key))?"/regid:". $D->reg_id . "/regkey:" . $D->reg_key:'') ?>">
              <div id="signup1">
                    <div class="reg_field">
                        <input autocomplete="off" id="fullname" placeholder="<?= $this->lang('signup_step2_form_fullname') ?>" name="fullname" value="<?= htmlspecialchars($D->fullname) ?>" type="text" onblur="return check_username(this.name);" />
                    </div>                    
                    <div class="reg_field_notice" id="Info_fullname"><?= ($D->fullname_status)?$D->fullname_status:$this->lang('signup_err_fullname') ?></div>
                    <div class="reg_field">
                        <input autocomplete="off" id="email" placeholder="<?= $this->lang('signup_step2_form_email') ?>" name="email" value="<?= htmlspecialchars($D->email) ?>" <?= (isset($D->reg_id,$D->reg_key))?'readonly="readonly"':""; ?> type="text" onblur="return check_username(this.name);" />
                    </div>                    
                    <div class="reg_field_notice" id="Info_email"><?= ($D->email_status)?$D->email_status:$this->lang('signup_register_email') ?></div>
                    <div class="reg_field">
                        <input autocomplete="off" id="password" placeholder="<?= $this->lang('signup_step2_form_password') ?>" name="password" value="<?= htmlspecialchars($D->password) ?>" type="password" onblur="return check_username(this.name);" />
                    </div>
                    <div class="reg_field_notice" id="Info_password"><?= ($D->password)?"":$this->lang('signup_err_password') ?></div>
                    <div class="reg_field">
                        <input autocomplete="off" id="password2" placeholder="<?= $this->lang('signup_step2_form_password2') ?>" name="password2" value="<?= htmlspecialchars($D->password2) ?>" type="password" onblur="return check_username(this.name);"  />
                    </div>
                    <div class="reg_field_notice" id="Info_password2"><?= ($D->password)?$D->password_status:$this->lang('signup_register_retype_pass') ?></div>
                    <div class="reg_field">
                        <input autocomplete="off" id="username" placeholder="<?= $this->lang('signup_step2_form_username') ?>" name="username" value="<?= htmlspecialchars($D->username) ?>" type="text" onblur="return check_username(this.name);" />
                    </div>
                    <div class="reg_field_notice" id="Info_username"><?= ($D->username_status)?$D->username_status:$this->lang('signup_err_username') ?></div>
                    <div class="klear"></div>
                    <div id="both_captcha" align="left" style="margin-left: 64px;">
                        <?php if( !isset($C->GOOGLE_CAPTCHA_PRIVATE_KEY, $C->GOOGLE_CAPTCHA_PUBLIC_KEY) || $C->GOOGLE_CAPTCHA_PRIVATE_KEY == '' || $C->GOOGLE_CAPTCHA_PUBLIC_KEY == '' ){ ?>
                            <div style="float: left;  padding-top: 3px;">
                                <input type="hidden" name="captcha_key" value="<?= $D->captcha_key ?>" />
                                <?= $D->captcha_html ?>
                            </div>
                            <div style="float: left; padding-top: 3px;">
                                <input type="text" maxlength="20" name="captcha_word" value="" autocomplete="off" class="reginp" style="width:156px;margin-top: 3px;" />
                            </div>
                            <div class="reg_field_notice" id="Info_captcha_word"><?= ($D->captcha_status)?$D->captcha_status:$this->lang('signup_register_imagecode') ?></div>
                            <div class="klear"></div>
                        <?php }else{ ?>
                            <div style="float: left;  padding-top: 3px;">
                                <?= $D->captcha_html ?>
                             </div>
                            <div style="margin-left: 60px" class="reg_field_notice" id="Info_captcha_word"><?= ($D->captcha_status)?$D->captcha_status:$this->lang('signup_register_imagecode') ?></div>
                            <div class="klear"></div>
                        <?php } ?> 
                    </div>                    
              </div><br>
              <?php if(isset($C->TERMSPAGE_ENABLED) && $C->TERMSPAGE_ENABLED) { ?>
			  <div style="width: 274px; margin-bottom: 5px; color: #777777; font-weight: bold;"><?= $this->lang('terms_and_conditions') ?></div>
              <div style=" width: 360px; margin-left: 60px; border-bottom: 1px solid #dedede;" class="round_corners">
                    <div class="line_1"></div>
                    <div class="line_2" style="background-color: #f4f4f4;"></div>
                    <div class="line_3" style="background-color: #f4f4f4;"></div>
                    <div class="line_4" style="background-color: #f4f4f4;"></div>
                    <div class="line_5" style="background-color: #f4f4f4;"></div>
                    <div class="content" style="background-color: #f4f4f4;">
                        <div id="block">
                            <?= $C->TERMSPAGE_CONTENT ?>
                        </div>
                    </div>
              </div>
              <div class="round_corners" style=" width: 360px; margin-left: 60px; border-top: 1px solid #ffffff">
                    <div class="content" style="background-color: #f4f4f4;">
						<div style="color: #777777; font-size: 10px; margin-bottom: 4px;  font-weight: bold;"><?= $this->lang('terms_agree') ?></div>
                        <input class="homepage_signup" type="submit" value="Create my account">
                    </div>
                    <div class="line_5" style="background-color: #f4f4f4;"></div>
                    <div class="line_4" style="background-color: #f4f4f4;"></div>
                    <div class="line_3" style="background-color: #f4f4f4;"></div>
                    <div class="line_2" style="background-color: #f4f4f4;"></div>
                    <div class="line_1"></div>
            </div>
            <?php } else { ?>            
                <input class="homepage_signup" style="margin-left: 68px;" type="submit" value="Create my account">            
            <?php } ?>
                <input type="hidden" name="accept_terms" value="1">
        </form>
        <script>
            $("#block").click(function(){
            if(($("#block").height())<500){
                      $("#block").animate({
                            height: "500px"
                      }, 1500 );
                    }
                    $("#block").css(overflow,scroll);
            });
        </script>
     </div>
 </div>
<?php
	$this->load_template('home_footer.php');
?>