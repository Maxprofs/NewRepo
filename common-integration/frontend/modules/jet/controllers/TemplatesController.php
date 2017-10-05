<?php
namespace frontend\modules\jet\controllers;

use Yii;
use yii\web\Controller;

use frontend\modules\jet\components\Data;

class TemplatesController extends Controller
{
    public function actionTest()
    {
      $files = Yii::getAlias('@webroot').'/jet/frontend/views/templates/email';
      $query="SELECT * FROM `jet_email_template`";
      $email = Data::sqlRecords($query,"all");
      if (is_dir($files)){
        if ($dh = opendir($files)){
          while (($file = readdir($dh)) !== false){
             if (strpos($file, '.html')){
             $results_array[] = $file;
           }
          }
          closedir($dh);
        }
      }
      if(empty($email)){
          foreach($results_array as $value)
          {

              $title =str_replace('.html', '',$value);
            
                 $model = Data::sqlRecords("INSERT INTO `jet_email_template` (`template_title`,`template_path`,`custom_title`) VALUES ('".$title."','email/".$value."','".$title."')", 'all','insert');
              
             
          }
      }
      else{
        
         
         foreach($results_array as $value)
          {
            foreach ($email as $key => $emailvalue) {
              $title =str_replace('.html', '',$value);
              if(trim($emailvalue['template_title'])==trim($title)){
                $emailConfiguration[$title] =0;
                                break;  
            }
            else{
              $emailConfiguration[$title] =1;
            }
          }
        }
          
        if(!empty($emailConfiguration)){
            foreach ($emailConfiguration as $key => $value) 
                {
                   
                    if($value=='1'){
                  
                      $model = Data::sqlRecords("INSERT INTO `jet_email_template` (`template_title`,`template_path`,`custom_title`) VALUES ('".$key."','email/".$key.".html','".$key."')", 'all','insert');
                      
                   
                    }
                }
                
        }
          
      }


    
    
        }

}