<?php
namespace frontend\modules\jet\controllers;
use common\models\User;
use frontend\modules\jet\components\Jetnotificationscom;
use frontend\modules\jet\components\Jetproductinfo;
use frontend\modules\jet\models\JetAttributes;
use frontend\modules\jet\models\JetAttributeValue;
use frontend\modules\jet\models\JetProduct;
use Yii;

class JetcatattributeController extends \yii\web\Controller
{
    protected $connection;
    public function actionIndex()
    {
    	if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        else
        return $this->render('index');
    }
    public function actionSave()
    {
    	if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        else
        {
        	$Attrmodel=new JetAttributes();
        	$merchant_id= \Yii::$app->user->identity->id;
        	$connection = Yii::$app->getDb();
        	if(file_exists(\Yii::getAlias('@webroot').'/upload/Jet_Taxonomy_mapping.csv') && file_exists(\Yii::getAlias('@webroot').'/upload/Jet_Taxonomy_attribute.csv') && file_exists(\Yii::getAlias('@webroot').'/upload/Jet_Taxonomy_attribute_value.csv'))
        	{
        	    /*
        		$target=\Yii::getAlias('@webroot').'/upload/Jet_Taxonomy_mapping.csv';
        		$row=0;
				$flag=true;
				$catattrData=array();
				if (($handle = fopen($target, "r")) !== FALSE) 
				{
				    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
				    {
				    	$row++;
				    	$num = count($data);
				    	if($row==1)continue;
			        	$catattrData[$data[1]][]=number_format($data[0], 0, ".", "");
			        	//$catattrData[$data[1]]['active']=$data[0];
			        	//$catattrData[$data[1]]['id']=$data[1];
					}
				}
				//print_r($catattrData);die;
				$merchantCategory = $connection->createCommand("SELECT * FROM `jet_category`");
	            $result = $merchantCategory->queryAll();
	            //var_dump($result);die;
	            $count=0;
	            if($result)
	            {
	            	foreach ($result as $value) 
	            	{
	            		if(array_key_exists($value['category_id'], $catattrData)){
	            			$count++;
	            			//echo $catattrData[$value['category_id']]."<br>";
	            			$attribute_ids=implode(',',$catattrData[$value['category_id']]);
	            			
	        				$merchantCategory = $connection->createCommand("SELECT `id` FROM `jet_category` WHERE category_id='".$value['category_id']."' AND merchant_id='".$merchant_id."'");
	        				$result = $merchantCategory->queryOne();
	        				if($result && $result['jet_attributes']==''){
	        					$sql="UPDATE `jet_category` SET `jet_attributes`='".addslashes($attribute_ids)."' WHERE category_id='".$value['category_id']."' AND merchant_id='".$merchant_id."'";
	                            $model = $connection->createCommand($sql)->execute();
	        				}
	            		}
	            	}
	            }
	            
	            //jet_attributes
	            $target=\Yii::getAlias('@webroot').'/upload/Jet_Taxonomy_attribute.csv';
        		$row=0;
				$flag=0;
				$cat=array();
				if (($handle = fopen($target, "r")) !== FALSE) 
				{
				    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
				    {
				    	$row++;
				    	$num = count($data);
				    	if($row==1)continue;
				    	$flag++;
				    	$result=$Attrmodel->find()->where(['id' => $data[0]])->one();
				    	if(!$result){
					    	$Attrmodel=new JetAttributes();
				        	$Attrmodel->id=number_format($data[0], 0, ".", "");
				        	$Attrmodel->display_name=$data[2];
				        	$Attrmodel->description=$data[1];
				        	$Attrmodel->free_text=$data[6];
				        	
				        	if($data[7]==0)
				        		$Attrmodel->display="FALSE";
				        	elseif($data[7]==1)
				        		$Attrmodel->display="TRUE";
				        	//elseif($data[4]=="TRUE" || $data[4]=="FALSE")
				        		//$Attrmodel->display=$data[4];
				        	
				        	if($data[8]==0)
				        		$Attrmodel->facet_filter="FALSE";
				        	elseif($data[8]==1)
				        		$Attrmodel->facet_filter="TRUE";
				        	//elseif($data[5]=="TRUE" || $data[5]=="FALSE")
				        		//$Attrmodel->facet_filter=$data[5];
				        	//$Attrmodel->facet_filter=$data[5];
				        	
				        	if($data[9]==0)
				        		$Attrmodel->variant="FALSE";
				        	elseif($data[9]==1)
				        		$Attrmodel->variant="TRUE";
				        	//elseif($data[6]=="TRUE" || $data[6]=="FALSE")
				        		//$Attrmodel->variant=$data[6];
				        	//$Attrmodel->variant=$data[6];
				        	
				        	if($data[12]==0)
				        		$Attrmodel->variant_pair="FALSE";
				        	elseif($data[12]==1)
				        		$Attrmodel->variant_pair="TRUE";
				        	//elseif($data[7]=="TRUE" || $data[7]=="FALSE")
				        		//$Attrmodel->variant_pair=$data[7];
				        	//$Attrmodel->variant_pair=$data[7];
				    		$Attrmodel->save(false);
						}
					}
					
				} */
				//jet_attributes values
			 	$target=\Yii::getAlias('@webroot').'/upload/Jet_Taxonomy_attribute_value.csv';
				$row=0;
				$flag=0;
				$cat=array();
				$arr=array();
				if (($handle = fopen($target, "r")) !== FALSE)
				{
					while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
					{
						$row++;
						$num = count($data);
						if($row==1)continue;
						$flag++;
						$i=0;
						$arr1=array();
						$arr2=array();
						//	$arr3=array();
						//$connection = Yii::$app->getDb();
						if(array_key_exists($data[0],$arr))
						{
							if(isset($arr[$data[0]]['value']))
							{
								$arr1[0]=$arr[$data[0]]['value'];
								$arr1[1]=$data[1];
								$string=implode(',',$arr1);
								$arr[$data[0]]['value']=$string;
									
							}
							if(isset($arr[$data[0]]['units']) && ($data[2]!="NULL"))
							{
								$arr2[0]=$arr[$data[0]]['units'];
								$arr2[1]=$data[2];
								$string=implode(',',$arr2);
								$arr[$data[0]]['units']=$string;
							}
						
						}
						else
						{
								
							$arr[$data[0]]['value']=$data[1];
							if($data[2]!="NULL"){
								$arr[$data[0]]['units']=$data[2];
							}
							if($data[3]==0)
								$arr[$data[0]]['retired']="FALSE";
							elseif($data[3]==1)
								$arr[$data[0]]['retired']="TRUE";
							elseif($data[3]=="TRUE" || $data[3]=="FALSE")
								$arr[$data[0]]['retired']=$data[3];
							
							$arr[$data[0]]['id']=$data[0];
							
						}
				
					}
					foreach($arr as $value)
					{
						$resultAttr = $connection->createCommand("SELECT * FROM `jet_attribute_value` WHERE attribute_id='".number_format($value['id'], 0, ".", "")."'")->queryOne();
						if(empty($resultAttr))
						{	
							if(isset($value['units']))
							{
							    $attr_unit="";$attr_value="";
							    $attr_unit=implode(',',(array_unique(explode(',',$value['units']))));
							    $attr_value=implode(',',(array_unique(explode(',',$value['value']))));
								$connection->createCommand()
								->insert('jet_attribute_value', [
										'attribute_id' =>number_format($value['id'], 0, ".", ""),
										'value' => addslashes($attr_value),
										'units' => addslashes($attr_unit),
										'retired'=> addslashes($value['retired']),
											
								])->execute();
							}
							else
							{
							    $attr_value="";
							    $attr_value=implode(',',(array_unique(explode(',',$value['value']))));
								$connection->createCommand()
								->insert('jet_attribute_value', [
										'attribute_id' => number_format($value['id'], 0, ".", ""),
										'value' => addslashes($attr_value),
										'retired'=> addslashes($value['retired']),
							   	
								])
								->execute();
							} 
						}	
					}
				} 
				Yii::$app->session->setFlash('success', "Jet Attributes created Successfully");
				return $this->redirect(['index']);
        	}
        	 else
        	{
        		Yii::$app->session->setFlash('error', "Jet_Taxonomy_mapping.csv & Jet_Taxonomy_attribute.csv & Jet_Taxonomy_attribute_value.csv files do not exist.Kindly upload file at location:".\Yii::getAlias('@webroot')."/upload/");
                return $this->redirect(['index']); 
        	} 
    	}
    }
    public function actionSaveattrvalue(){
        $modelattr="";
        $modelattr=JetAttributeValue::find()->all();
        $count=0;
        $notId=array();
        foreach($modelattr as $value){
            $attrExist="";
            $attrExist=JetAttributes::find()->where(['id'=>$value->attribute_id])->one();
            if($attrExist){
                $count++;
                $attrExist->attribute_values=$value->value;
                $attrExist->attribute_unit=$value->units;
                $attrExist->save(false);
            }else{
                $notId[]=$value->attribute_id;
            }
        }
        echo $count;
        echo "<br>";
        var_dump($notId);die;
    }
    public function actionGetattribute()
    {
    	$this->layout="main2";
    	$product_type=Yii::$app->getRequest()->getQueryParam('product_type');
    	$Attrmodel=new JetAttributes();
    	if($product_type=='variants')
		{
            if(Yii::$app->user->identity->id==14){
                $html=$this->render('varients1',array('model'=>$Attrmodel),true);
                return $html;
            }else{
                $html=$this->render('varients',array('model'=>$Attrmodel),true);
                return $html;
            }
			
		}else{
			$html=$this->render('simple',array('model'=>$Attrmodel),true);
    		return $html;
		}
    }
    public function actionCheckupc(){
    	$type="";
    	$id="";
    	$msg['success']=false;
    	$product_upc="";
        $barcode_type="";
        $connection=Yii::$app->getDb();
    	$product_upc=Yii::$app->getRequest()->getQueryParam('product_upc');
    	$product_upc=trim($product_upc);
    	$product_id=Yii::$app->getRequest()->getQueryParam('product_id');
    	$type=Yii::$app->getRequest()->getQueryParam('type');
        //$barcode_type=Yii::$app->getRequest()->getQueryParam('barcode_type');
    	if($type=='variant'){
            $variant_id=Yii::$app->getRequest()->getQueryParam('variant_id');
            $variant_as_parent=Yii::$app->getRequest()->getQueryParam('variant_as_parent');
            if(Jetproductinfo::checkUpcVariants($product_upc,$product_id,$variant_id,$variant_as_parent,$connection))
            {
                $msg['success']=true;
                return json_encode($msg);
            }
    	}
        elseif($type=='variant-simple'){
            $product_sku="";
            $product_sku=Yii::$app->getRequest()->getQueryParam('product_sku');
            if(Jetproductinfo::checkUpcVariantSimple($product_upc,$product_id,$product_sku,$connection)){
                $msg['success']=true;
                return json_encode($msg);
            }
        }
        elseif($type=='simple'){
            if(Jetproductinfo::checkUpcSimple($product_upc,$product_id,$connection)){
                $msg['success']=true;
                return json_encode($msg);
            }
    	}
    	return json_encode($msg);

    }
    public function actionCheckasin(){
        $type="";
        $id="";
        $msg['success']=false;
        $product_asin="";
        $product_asin=Yii::$app->getRequest()->getQueryParam('product_asin');
        $product_asin=trim($product_asin);
        $product_id=Yii::$app->getRequest()->getQueryParam('product_id');
        $type=Yii::$app->getRequest()->getQueryParam('type');
        if($type=='variant'){
            $variant_id=Yii::$app->getRequest()->getQueryParam('variant_id');
            $variant_as_parent=Yii::$app->getRequest()->getQueryParam('variant_as_parent');
            if(Jetproductinfo::checkAsinVariants($product_asin,$product_id,$variant_id,$variant_as_parent,$connection))
            {
                $msg['success']=true;
                return json_encode($msg);
            }

        }
        elseif($type=='variant-simple'){
            $product_sku="";
            $product_sku=Yii::$app->getRequest()->getQueryParam('product_sku');
            if(Jetproductinfo::checkAsinVariantSimple($product_asin,$product_id,$product_sku,$connection)){
                $msg['success']=true;
                return json_encode($msg);
            }
        }
        elseif($type=='simple'){
            if(Jetproductinfo::checkAsinSimple($product_asin,$product_id,$connection)){
                $msg['success']=true;
                return json_encode($msg);
            }
        }
        return json_encode($msg);

    }
    public function actionNotificationredirect()
    {
    		$merchant_id="";
			$merchant_id=Yii::$app->user->identity->id;
    		if(isset($_GET['code'])){
    				$url="";
    				if(trim($_GET['code'])=="order-ready"){
    					Jetnotificationscom::resetCount($merchant_id,trim($_GET['code']));
    					$arrayParams =array();
           				$params = array();
            			$url="";
            			$arrayParams = ['sort'=>'-status'];
            			$params = array_merge(["jetorderdetail/index"], $arrayParams);
            			$url=Yii::$app->urlManager->createUrl($params);
    				}elseif(trim($_GET['code'])=="order-ack"){
    					Jetnotificationscom::resetCount($merchant_id,trim($_GET['code']));
    					$arrayParams =array();
           				$params = array();
            			$url="";
            			$arrayParams = ['sort'=>'status'];
            			$params = array_merge(["jetorderdetail/index"], $arrayParams);
            			$url=Yii::$app->urlManager->createUrl($params);
    				}elseif(trim($_GET['code'])=="product-upload"){
    					Jetnotificationscom::resetCount($merchant_id,trim($_GET['code']));
    					$arrayParams =array();
           				$params = array();
            			$url="";
            			$arrayParams = ['code' => trim($_GET['code'])];
            			$params = array_merge(["jetproduct/index"], $arrayParams);
            			$url=Yii::$app->urlManager->createUrl($params);
    				}elseif(trim($_GET['code'])=="rejected-files"){
    					Jetnotificationscom::resetCount($merchant_id,trim($_GET['code']));
    					$arrayParams =array();
           				$params = array();
            			$url="";
            			$arrayParams = ['code' => trim($_GET['code'])];
            			$params = array_merge(["jetrejectfiles/index"], $arrayParams);
            			$url=Yii::$app->urlManager->createUrl($params);
    				}elseif(trim($_GET['code'])=="order-error"){
    					Jetnotificationscom::resetCount($merchant_id,trim($_GET['code']));
    					$arrayParams =array();
           				$params = array();
            			$url="";
            			$arrayParams = ['code' => trim($_GET['code'])];
            			$params = array_merge(["jetorderimporterror/index"], $arrayParams);
            			$url=Yii::$app->urlManager->createUrl($params);
    				}
    				elseif(trim($_GET['code'])=="product-under-jet-review"){
    					Jetnotificationscom::resetCount($merchant_id,trim($_GET['code']));
    					$arrayParams =array();
           				$params = array();
            			$url="";
            			$arr=array();
            			$arr['JetProductSearch']=array('status'=>'Under Jet Review');
            			$params = array_merge(["jetproduct/index"], $arr);
            			$url=Yii::$app->urlManager->createUrl($params);
    				}
    				elseif(trim($_GET['code'])=="product-missing-listing-data"){
    					Jetnotificationscom::resetCount($merchant_id,trim($_GET['code']));
    					$arrayParams =array();
           				$params = array();
            			$url="";
            			$arr=array();
            			$arr['JetProductSearch']=array('status'=>'Missing Listing Data');
            			$params = array_merge(["jetproduct/index"], $arr);
            			$url=Yii::$app->urlManager->createUrl($params);
    				}
    				elseif(trim($_GET['code'])=="available-for-purchase"){
    					Jetnotificationscom::resetCount($merchant_id,trim($_GET['code']));
    					$arrayParams =array();
           				$params = array();
            			$url="";
            			$arr=array();
            			$arr['JetProductSearch']=array('status'=>'Available for Purchase');
            			$params = array_merge(["jetproduct/index"], $arr);
            			$url=Yii::$app->urlManager->createUrl($params);
    				}
    				elseif(trim($_GET['code'])=="product-unauthorized"){
    					Jetnotificationscom::resetCount($merchant_id,trim($_GET['code']));
    					$arrayParams =array();
           				$params = array();
            			$url="";
            			$arr=array();
            			//$arr['JetProductSearch']=array('status'=>'Available for Purchase');
            			$params = array_merge(["jetproduct/index"], $arr);
            			$url=Yii::$app->urlManager->createUrl($params);
    				}
    				elseif(trim($_GET['code'])=="product-excluded"){
    					Jetnotificationscom::resetCount($merchant_id,trim($_GET['code']));
    					$arrayParams =array();
           				$params = array();
            			$url="";
            			$arr=array();
            			//$arr['JetProductSearch']=array('status'=>'Available for Purchase');
            			$params = array_merge(["jetproduct/index"], $arr);
            			$url=Yii::$app->urlManager->createUrl($params);
    				}
    				if($url !=""){
    						return $this->redirect($url); 
    				}
    				return $this->redirect(Yii::$app->request->referrer);
    		}
    }
 }
   

