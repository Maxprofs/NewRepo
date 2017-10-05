<?php
namespace frontend\modules\jet\controllers;

use Yii;
use yii\web\Response;
use yii\web\UploadedFile;
// use yii\base\Controller;

use common\models\User;
use frontend\modules\jet\components\Data;

class JetadmincategoryController extends JetmainController
{
	protected $jetHelper;
    public function actionIndex()
    {    	
    	if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }else
    		return $this->render('index');
    }
    /* public function actionSave()
    {	    	
    	die("gfd");
    	if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        
        $mimes = array('text/comma-separated-values','application/vnd.ms-excel','text/plain','text/csv','text/tsv');
    	if($_FILES)
    	{
			if(in_array($_FILES['csv_file']['type'],$mimes))
			{
              	$newname = $_FILES['csv_file']['name']; 
				$target = 'upload/'.$newname;
				$row = $row1 = $level_0 = $level_1 = $level_2 = 0;
				$flag=true;
				
				if(!file_exists($target))
				{
					move_uploaded_file($_FILES['csv_file']['tmp_name'], $target);
				}
				$categoryData = [];
				if (($handle = fopen($target, "r")) !== FALSE) 
				{
					$sql='INSERT INTO `jet_category`(`category_id`,`title`,`parent_id`,`parent_title`,`root_id`,`root_title`,`level`) VALUES';
													
				    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
				    {						    	
				    	if($row==0 && ($data[0]!='level-0-id' || strtolower($data[1])!='level-0-name')){
				    		$flag=false;
				    		break;
				    	}
				    	$row++;
				    	$num = count($data);
				    	if($row==1)
				    		continue;
				    	
				    	if ( $data[0] ) 
				    	{
				    		$res = self::checkAlreadyExist(trim($data[0]));
				    		if($res=='not')
				    		{
				    			$sql.= '("'.trim($data[0]).'","'.addslashes(trim($data[1])).'","","","","",0),';
				    		}
				    	}
				    	if ( $data[2] ) 
				    	{
				    		$res = self::checkAlreadyExist(trim($data[2]));
				    		if($res=='not')
				    		{
				    			$level_1++;
				    			$sql.= '("'.trim($data[2]).'","'.addslashes(trim($data[3])).'","'.trim($data[0]).'","'.addslashes(trim($data[1])).'","","",1),';
		      				}
				    	}
				    	if ($data[4]) 
				    	{
				    		$level_2++;
				    		$res = self::checkAlreadyExist(trim($data[4]));
				    		if($res=='not')
				    		{
				    			$sql.= '("'.trim($data[4]).'","'.addslashes(trim($data[5])).'","'.trim($data[2]).'","'.addslashes(trim($data[3])).'","'.trim($data[0]).'","'.addslashes(trim($data[1])).'",2),';		      								
				    		}
				    	}
			        	// if($row==6000)break;
					}
					$sql = rtrim($sql,',');
					echo $sql;
					die("<hr>");
					Data::sqlRecords($sql,null,'insert');
					if($flag==false){
						Yii::$app->session->setFlash('error', "Please upload category csv file..");
		  				return $this->redirect(['index']);
					}
				}
				else
				{
					Yii::$app->session->setFlash('error', "Csv file is not readable.Kindly check the permission");
		  			return $this->redirect(['index']);
				}
				Yii::$app->session->setFlash('success',$level_0.'=>'.$level_1.'=>'.$level_2. " => Category saved successfully");
				return $this->redirect(['index']);
				//print_r($category);		
			} 
			else 
			{
				Yii::$app->session->setFlash('error', "csv file is not allowed");
		  		return $this->redirect(['index']);
			}
		}
		else
		{
			Yii::$app->session->setFlash('error', "upload csv file to create category");
			return $this->redirect(['index']);
		}
    } */

    public function actionAjaxCsvImport()
    {
    	if (Yii::$app->user->isGuest) {
    		return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
    	}
    	$count = [];
    	
    	$mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv','text/comma-separated-values');
    	
    	if (!in_array($_FILES['csv_file']['type'], $mimes))
    	{
    		Yii::$app->session->setFlash('error', "Only CSV file allowed");
    		return $this->redirect(['index']);
    	}
    	if ($_FILES['csv_file']['name'])
    	{
    		$newname = $_FILES['csv_file']['name'];
    		$fileInfo=pathinfo($newname);
    		$log = \Yii::getAlias('@webroot').'/var/jet/jet_taxonomy/category/import';
    		if (!file_exists($log))
    		{
    			mkdir($log, 0775, true);
    		}
    
    		$log_file=$fileInfo['filename'].'.log';
    		$file = fopen($log.'/'.$log_file,"w");
    		fwrite($file, "product update start".PHP_EOL);
    		$target = $log.'/'.$newname;
    		
    		$imageModel=UploadedFile::getInstanceByName('csv_file');
    		
    		$imageModel->saveAs($target);
    		$fp = file($target);
    		$countLines = count($fp);
    		if(count($countLines)>0)
    		{
    			$pages=ceil($countLines/100);
    			return $this->render('ajaxbatchcsv', [
    					'totalcount' => $countLines,
    					'pages'=>$pages,
    					'target' => $target,
    					'log_file'=>$log.'/'.$log_file,
    			]);
    		}
    	}
    
    }
    public function actionMassUpdateCsv()
    {
    	$postData= Yii::$app->request->post();
    
    	$session = Yii::$app->session;
    	$flag=false;
    	if($postData && isset($postData['index']))
    	{
    		$response=[];
    		$count=0;    		
    		if (($handle = fopen($postData['target'], "r")))
    		{
    			if($postData['index']>1)
    				fseek($handle, $postData['current_pos']);
    				$row = 0;    				    			
    				while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
    				{    					
    					
    					if ($postData['index']==1 && $row == 0)
    					{
    						if( (trim($data[0])!='level-0-id' || trim($data[1])!='level-0-name' ) )
    						{
    							$flag=true;
    							break;
    						}    						
    
    						$row ++;
    						continue;
    					} 
    					
    					$updateFlag=false;
    					$sql='INSERT INTO `jet_category`(`category_id`,`title`,`parent_id`,`parent_title`,`root_id`,`root_title`,`level`) VALUES';
    					if (trim($data[0]))
    					{
    						$res = "";
    						$res = self::checkAlreadyExist(trim($data[0]));
    						if($res=='not')
    						{
    							$updateFlag = true;
    							$sql.= '("'.trim($data[0]).'","'.addslashes(trim($data[1])).'","","","","",0),';
    						}
    					}
    					if (trim($data[2]))
    					{
    						$res = "";
    						$res = self::checkAlreadyExist(trim($data[2]));
    						if($res=='not')
    						{
    							$updateFlag = true;
    							$sql.= '("'.trim($data[2]).'","'.addslashes(trim($data[3])).'","'.trim($data[0]).'","'.addslashes(trim($data[1])).'","","",1),';
    						}
    					}
    					if (trim($data[4]))
    					{
    						$res = "";
    						$res = self::checkAlreadyExist(trim($data[4]));
    						if($res=='not')
    						{
    							$updateFlag = true;
    							$sql.= '("'.trim($data[4]).'","'.addslashes(trim($data[5])).'","'.trim($data[2]).'","'.addslashes(trim($data[3])).'","'.trim($data[0]).'","'.addslashes(trim($data[1])).'",2),';
    						}
    					}
    					if($updateFlag)
    					{
    						$count++;
    						Data::sqlRecords(rtrim($sql,','),null,'insert');
    					}
    					
    					if($row==100){
    						$cur_pos = ftell($handle);
    						$last_sku=$data[0];
    						break;
    					}
    					$row++;    					    						
    				}
    				
    				if(!$flag && isset($cur_pos))
    				{
    					$response['success']['cur_pos']=$cur_pos;
    					$response['success']['last_sku']=$last_sku;
    				}
    				elseif(!$flag)
    				{
    					unset($session['titleIndex'],$session['priceIndex'],$session['barcodeIndex'],$session['asinIndex'],$session['mpnIndex'],$session['descIndex']);
    					$response['success']['end']="end";
    				}
    				else
    					$response['error']="Please upload correct csv file...";
    				$response['success']['update_count']=$count;
    		}
    		return json_encode($response);
    	}
    }
    
    
    public function checkAlreadyExist($id,$merchant_id=MERCHANT_ID)
    {
    	$res = [];
    	$query = "SELECT `id` FROM `jet_category` WHERE `category_id`={$id} ";

    	$res = Data::sqlRecords($query,'one','select');

    	if (empty($res)) {
    		return "not";
    	}/* else 
    	{
    		$response = $this->jetHelper->CGetRequest('/taxonomy/nodes/'.$id,$merchant_id);
    		$catRes = json_decode($response,true);
    		if (isset($catRes['active']) && $catRes['active']!=1)
    			Data::sqlRecords("UPDATE `jet_category` SET `is_active` = '0' WHERE `category_id` =".$id,null,'update');
    		
    	} */
    		return "exist";
    }
}