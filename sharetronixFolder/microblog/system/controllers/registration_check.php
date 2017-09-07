<?php
require_once( $C->INCPATH.'helpers/func_main.php' );
$this->load_langfile('outside/signup.php');

$fieldname 	= $_REQUEST['fieldname'];
$value = $_REQUEST['value'];

switch ($fieldname){
    case 'username':
        if(empty($value)){
             echo $this->lang('signup_err_username');
        } else {
            $D->error = FALSE;
            if( !$D->error && (strlen($value)<3 || strlen($value)>30) ) {
                    $D->error	= TRUE;
                    $D->errmsg	= 'signup_err_usernmlen';
            }
            if( !$D->error && preg_match('/[^a-z0-9-_]/i', $value) ) {
                    $D->error	= TRUE;
                    $D->errmsg	= 'signup_err_usernmlet';
            }
            if( !$D->error ) {
                    $db2->query('SELECT id, active FROM users WHERE username="'.$db2->e($value).'" LIMIT 1');
                    if($obj = $db2->fetch_object()) {
                            $D->error	= TRUE;
                            $D->errmsg	= $obj->active==1 ? 'signup_err_usernm_exists' : 'signup_err_usernm_disabled';
                    }
            }
            if( !$D->error ) {
                    $db2->query('SELECT id FROM groups WHERE groupname="'.$db2->e($value).'" LIMIT 1');
                    if($obj = $db2->fetch_object()) {
                            $D->error	= TRUE;
                            $D->errmsg	= 'signup_err_usernm_exists';
                    }
            }
            if( !$D->error && file_exists($C->INCPATH.'controllers/'.strtolower($value).'.php') ) {
                    $D->error	= TRUE;
                    $D->errmsg	= 'signup_err_usernm_existss';
            }
            if( !$D->error && file_exists($C->INCPATH.'controllers/mobile/'.strtolower($value).'.php') ) {
                    $D->error	= TRUE;
                    $D->errmsg	= 'signup_err_usernm_existss';
            }
            if( !$D->error && file_exists($C->INCPATH.'../'.strtolower($value)) ) {
                    $D->error	= TRUE;
                    $D->errmsg	= 'signup_err_usernm_existss';
            }
            echo($D->error)?'<div id="Error">' . $this->lang($D->errmsg) . '</div>':'<div id="Success">'. $this->lang('signup_register_username_ok') . '</div>';
        }
        break;
    case 'email':
        $email	= strtolower(trim($value));
        if(empty($email)){
             echo $this->lang('signup_register_email');
        } else {
            if(!is_valid_email($email)){
                    echo '<div id="Error">' . $this->lang('signup_err_email_invalid') . '</div>';
            } else {
                    $db2->query('SELECT email from users where email ="' . strtolower( $email) . '" LIMIT 1');
                    echo ($db2->fetch_object())?'<div id="Error">' . $this->lang('signup_err_email_exists') . '</div>':'<div id="Success">'. $this->lang('signup_register_email_ok') . '</div>';
            }
        }
        break;
    case 'fullname':
        if(strlen($value) >2){
            echo'<div id="Success">' . $this->lang('signup_register_fullname_ok') . '</div>';
        }else if(strlen($value) <=2 && strlen($value)>0){
            echo '<div id="Error">' . $this->lang('signup_register_fullname_notreal') . '</div>';
        }else{
            echo $this->lang('signup_err_fullname');
        }
        break;
}
?>