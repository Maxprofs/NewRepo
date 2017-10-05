<?php 
namespace frontend\modules\jet\components\Dashboard;

use frontend\modules\jet\components\Data;
use yii\base\Component;

class LatestUpdates extends Component
{   
    /**
     * To fetch the latest updates
     * @return []
     */
    public static function fetchLatestUpdates()
    {      
      $result = [];
      $query = "SELECT * FROM `latest_updates` WHERE `marketplace` IN ('jet','all') ORDER BY `latest_updates`.`updated_at` DESC LIMIT 10";
      $result = Data::sqlRecords($query, 'all','select');      
      return $result;        
    }    

    public static function timeDifference($time)
    {
        $date_create = date_create_from_format('Y-m-d H:i:s', $time);
        $timeStamp = $date_create->getTimestamp();

        $timeDiff = ['years'=>0,'months'=>0,'days'=>0,'hours'=>0,'minutes'=>0,'seconds'=>0];

        $diff = time() - $timeStamp;

        if($diff)
        {
          $years = floor($diff / (365*60*60*24));
          $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
          $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));

          $hours   = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24)/ (60*60));
          $minutes  = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60)/ 60);
          $seconds = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60 - $minutes*60)); 
          
          $timeDiff = ['years'=>$years,'months'=>$months,'days'=>$days,'hours'=>$hours,'minutes'=>$minutes,'seconds'=>$seconds];
        }

        $timeStr = '';
        $count = 0;
        
        if($count < 2 && $timeDiff['years']) {
          $timeStr .= $timeDiff['years'].' years ';
          $count++;
        }
        if($count < 2 && $timeDiff['months']) {
          $timeStr .= $timeDiff['months'].' months ';
          $count++;
        }
        if($count < 2 && $timeDiff['days']) {
          $timeStr .= $timeDiff['days'].' days ';
          $count++;
        }
        if($count < 2 && $timeDiff['hours']) {
          $timeStr .= $timeDiff['hours'].' hours ';
          $count++;
        }
        if($count < 2 && $timeDiff['minutes']) {
          $timeStr .= $timeDiff['minutes'].' minutes ';
          $count++;
        }
        if($count < 2 && $timeDiff['seconds']) {
          $timeStr .= $timeDiff['seconds'].' seconds ';
          $count++;
        }
        return $timeStr;
    }

    public static function getFormattedTime($time)
    {
        $date_create = date_create_from_format('Y-m-d H:i:s', $time);
        $timeStamp = $date_create->getTimestamp();

        return date('M j, Y',$timeStamp);
    }
}
?>
