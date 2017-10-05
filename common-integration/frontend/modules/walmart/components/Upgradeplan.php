<?php
namespace frontend\modules\walmart\components;
use Yii;
use yii\base\Component;
use frontend\modules\walmart\components\Data;

class Upgradeplan extends Component
{
    public static function remainingDays($merchant_id=null)
    {
        date_default_timezone_set('Asia/Kolkata');

        $optionName = "";
        $sql = "SELECT `expire_date`,`status` FROM `walmart_extension_detail` WHERE merchant_id='".$merchant_id."' and ((`status`='Not Purchase') || (`status`='Purchased') ) ";
        $expireDate = Data::sqlRecords($sql,'one','select');

        $diff=date_diff(date_create(date("Y-m-d H:i:s")),date_create($expireDate['expire_date']));
        $page_url = Yii::$app->urlManager->baseUrl.'/site/paymentplan';
        if (($expireDate['status']=='Not Purchase' ) && (($diff->d)<3) && (($diff->m) == 0) && (($diff->y) == 0) && (($diff->d)>=0 ))
        {
            $optionName = 'Choose Plan';
            $msg_name = "To avoid deactivation please purchase before the above time expires, Hurry!";
        }elseif (($expireDate['status']=='Purchased') && (($diff->d)<3) && (($diff->m) == 0) && (($diff->y) == 0) && (($diff->d)>=0) ) {
            $optionName = 'Upgrade Plan';
            $msg_name = "To avoid deactivation renew before the above time expires, Hurry!";
        }else{
            echo "<div class='no-trial-wrapper'></div>";
            return;
        }

        //$newExpire = date_format(date_create($expireDate['expire_date']),"Y-m-d");
        $newExpire = strtotime($expireDate['expire_date']);
        //$newDate = strtotime(date("Y:m:d H:i:s"));

        $html = "";

        $html.= "<div class='' id='counter-wrapper'><div class='trial-inner-wrapper'><div id='testing-trial' class='counter'><ul><li><span class='head-time'> Day </span><span class='digit-cls'>$diff->d</span></li><li><span class='head-time'>Hour</span><span class='digit-cls'>$diff->h</span></li><li> <span class='head-time'> Min </span><span class='digit-cls'>$diff->i</span></li><li><span class='head-time'> Sec </span><span class='digit-cls'>$diff->s</span></li></ul><i class='fa fa-chevron-down' aria-hidden='true'></i></div></div></div>  <div style='clear:both;'></div> ";
        echo $html;
        ?>
        <script type="text/javascript">
            // $('#counter-wrapper').parent('div').addClass('active-counter');
            function secondPassed()
            {
                var time = getRemaining("<?= $newExpire ?>");
//                console.log(time);
                document.getElementById('testing-trial').innerHTML="<ul><li><span class='head-time'> Day </span><span class='digit-cls'>"+time.days+"</span></li><li><span class='head-time'>Hour</span><span class='digit-cls'>"+time.hours+"</span></li><li> <span class='head-time'> Min </span><span class='digit-cls'>"+time.minutes+"</span></li><li><span class='head-time'> Sec </span><span class='digit-cls'>"+time.seconds+"</span></li></ul><i class='fa fa-chevron-down' aria-hidden='true'></i>";
                if(time.total<=0){
                    clearInterval(countdownTimer);
                }else{
                    (time.total)--;
                }
            }

            var countdownTimer = setInterval('secondPassed()', 1000);

            function getRemaining(endtime)
            {

                //var time = Date.parse(endtime) - Date.parse(new Date());
                date = Math.round(new Date().getTime()/1000);
                var time = endtime - date;
//                var seconds = Math.floor((time/1000)%60);
                var seconds = time % 60;
//                var minutes = Math.floor((time/1000/60)%60);
                var minutes = Math.floor((time % 3600)/60);
//                var hours = Math.floor((time/(1000*60*60))%24);
                var hours = Math.floor((time % 86400)/3600);
//                var days = Math.floor(time/(1000*60*60*24));
                var days = Math.floor(time / 86400);
                return {
                    'total': time,
                    'days': days,
                    'hours': hours,
                    'minutes': minutes,
                    'seconds': seconds
                };
            }
        </script>
        <?php
    }
}