<?php
namespace frontend\modules\jet\components;
use Yii;
use yii\base\Component;
use frontend\modules\jet\components\Data;

class Upgradeplan extends Component
{
	public static function remainingDays($merchant_id=null) 
	{
		date_default_timezone_set('Asia/Kolkata');
		$RevenueData = $expireDate = [];
		$RevenueData = Data::sqlRecords("SELECT `order_data` FROM `jet_order_detail` WHERE  `merchant_id`='".$merchant_id."' AND `status` = 'complete' ","all","select");
        $total=0.00;
        if(!empty($RevenueData) && count($RevenueData)>0)
        {
            foreach ($RevenueData as $val)
            {
                if($val['order_data'])
                {                   
                    $priceData = json_decode($val['order_data'],true);
                    $total +=  $priceData['order_totals']['item_price']['item_tax'] + $priceData['order_totals']['item_price']['item_shipping_cost']+ $priceData['order_totals']['item_price']['item_shipping_tax']+ $priceData['order_totals']['item_price']['base_price'];  

                }                  
            }
        }  
        $optionName = "";
		$sql = "SELECT `expired_on`,`purchase_status` FROM `jet_shop_details` WHERE merchant_id='".$merchant_id."' and ((`purchase_status`='Not Purchase') || (`purchase_status`='Purchased') ) ";
		$expireDate = Data::sqlRecords($sql,'one','select');
		
		$diff=date_diff(date_create(date("Y-m-d H:i:s")),date_create($expireDate['expired_on']));
		$expire_date = date("Y-m-d",strtotime($expireDate['expired_on']));
        $date7=date('Y-m-d',strtotime('+7 days', strtotime(date('Y-m-d'))));
		$page_url = Yii::getAlias('@webjeturl').'/site/paymentplan';
		if (($expireDate['purchase_status']=='Not Purchase' ) && (($diff->d)<7) && (($diff->d)>=0) && ($date7>=$expire_date) ) 
		{
			$optionName = 'Choose Plan';
			$msg_name = "To avoid deactivation please purchase before the above time expires, Hurry!";			
		}elseif (($expireDate['purchase_status']=='Purchased') && (($diff->d)<7) && ($total>200)  && (($diff->d)>=0) && ($date7>=$expire_date) ) {
			$optionName = 'Upgrade Plan';
			$msg_name = "To avoid deactivation renew before the above time expires, Hurry!";			
		}else{
			echo "<div class='no-trial-wrapper'></div>";
			return;
		}
		
		$newExpire = date_format(date_create($expireDate['expired_on']),"D M d Y H:i:s");
		
		$html = "";
		
		$html.= "<div class='' id='counter-wrapper'><div class='trial-inner-wrapper'><div id='testing-trial' class='counter'><ul><li><span class='head-time'> Day </span><span class='digit-cls'>$diff->d</span></li><li><span class='head-time'>Hour</span><span class='digit-cls'>$diff->h</span></li><li> <span class='head-time'> Min </span><span class='digit-cls'>$diff->i</span></li><li><span class='head-time'> Sec </span><span class='digit-cls'>$diff->s</span></li></ul><i class='fa fa-chevron-down' aria-hidden='true'></i></div><div id='show_plan' style='display:none;' ><span class='heading-start'>$msg_name</span><span class='btn-path choose-plan'> <a href=$page_url >$optionName</a></span></div></div></div>  <div style='clear:both;'></div> ";			
		echo $html;
		?>
		<script type="text/javascript">
            function secondPassed() 
            {    
            	var time = getRemaining("<?= $newExpire ?> GMT+0530 (IST)");
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
			  var time = Date.parse(endtime) - Date.parse(new Date());
			  var seconds = Math.floor((time/1000)%60);
			  var minutes = Math.floor((time/1000/60)%60);
			  var hours = Math.floor((time/(1000*60*60))%24);
			  var days = Math.floor(time/(1000*60*60*24));
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