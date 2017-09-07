<?php
	
	$this->load_template('home_header.php');
        $this->load_langfile('outside/signin.php');
        $this->load_langfile('outside/home.php');
        $this->load_langfile('outside/signup.php');
	
?>
<div class="homepage_maindiv">
    <div align="left" class="homepage_innerdiv">
        <div class="intro-text">
            <h1 style="text-shadow: white 0.1em 0.1em 0.3em; "><?= $D->intro_ttl ?></h1>
            <?= $D->intro_txt ?>
        </div>
        <div id="registration" style="">
            <form method="post" action="<?= $C->SITE_URL . 'signup' ?>">
                <div style="margin: 0px 0px 5px 5px;"><?= $this->lang('signin_reg_title') . " " .  $this->lang('os_welcome_btn') ?>!</div>
                <?php if(!$C->USERS_EMAIL_CONFIRMATION) { ?>
                <input autocomplete="off" id="fullname" placeholder="<?= $this->lang('signup_step2_form_fullname') ?>" name="fullname" size="20" tabindex="5" type="text" />
                <?php } ?>
                <input autocomplete="off" id="email" placeholder="<?= $this->lang('signup_step2_form_email') ?>" name="email" size="20" tabindex="6" type="text" />
                <?php if(!$C->USERS_EMAIL_CONFIRMATION) { ?>
                <input autocomplete="off" id="password" placeholder="<?= $this->lang('signup_step2_form_password') ?>" name="password" size="20" tabindex="7" type="password" />
                <?php } ?>
                <br><br><br>
                <input class="homepage_signup" style="border: 0px; width: 256px; height: 33px; border: 1px solid #FFAA22;" type="submit" value="Sign Up">
            </form>
            <?php if( isset($C->TWITTER_CONSUMER_KEY,$C->TWITTER_CONSUMER_SECRET) && !empty($C->TWITTER_CONSUMER_KEY) && !empty($C->TWITTER_CONSUMER_SECRET) ) { ?>
            <div class="loginfbtw">
                    <?php if( $D->fb_login_url ) { ?>
                            <div style="float:left; margin-right:5px;" title="Facebook Connect">
                                    <a id="facebookconnect" href="<?= $D->fb_login_url; ?>"></a>
                            </div>
                    <?php } ?>
                    <?php if( isset($C->TWITTER_CONSUMER_KEY,$C->TWITTER_CONSUMER_SECRET) && !empty($C->TWITTER_CONSUMER_KEY) && !empty($C->TWITTER_CONSUMER_SECRET) ) { ?>
                    <a id="twitterconnect" href="<?= $C->SITE_URL ?>twitter-connect?backto=<?= $C->SITE_URL ?>signin/get:twitter" title="Twitter Connect" style="margin-top:3px;"><b>Twitter</b></a>
                    <?php } ?>
            </div>
            <?php } ?>
        </div>
        <div class="klear"></div>
    </div>
</div>

</div>

<div class="golden_line"></div>
<?php
	
	$this->load_template('home_footer.php');
	
?>