<?php use frontend\modules\jet\models\JetAttributes;
		$id=Yii::$app->getRequest()->getQueryParam('id');
    	$product_type=Yii::$app->getRequest()->getQueryParam('product_type');
    	$product_id=Yii::$app->getRequest()->getQueryParam('product_id');
    	
    	$merchant_id= \Yii::$app->user->identity->id;
    	$html='';
    	$Attrmodel=$model;
        $connection = Yii::$app->getDb();
        $merchantCategory = $connection->createCommand("SELECT * FROM `jet_category` WHERE category_id='".$id."'");// AND merchant_id='".$merchant_id."'
        $result = $merchantCategory->queryOne();
		if($result['jet_attributes']){
			$attributes=explode(',',$result['jet_attributes']);
?>
<table class="table table-striped table-bordered">
<?php	$v=0;
		foreach ($attributes as $value)
		{
				$result="";
				$result=$Attrmodel->find()->where(['id' => $value])->one();
				if($result=="")
					continue;
				$attrName=$result->display_name;
				$attrDes=$result->description;
				$resultAttr = $connection->createCommand("SELECT * FROM `jet_attribute_value` WHERE attribute_id='".$value."'")->queryOne();
				$attrvalues=explode(',',$resultAttr['value']);
				?>
				<tr>
				<?php 
				if($result->free_text==0 && $result->display=='TRUE'){
					if($product_type=='simple')
					{?>
						<td style="width:50%"><?php echo $attrDes;?></td><td  style="width:50%">
					<?php	if(count($attrvalues)>0 && $attrvalues[0]!=''){?>
							<select class="form-control" name="jet_attributes1[<?php echo $value;?>][]">	
								<option value="">Please Select Jet Attribute</option>
							<?php foreach ($attrvalues as $val) {?>
								<option value="<?=$val?>"><?=$val?></option>
							<?php }?>
							</select>
						<?php }else{?>
							<input type="text" class="form-control" value="" placeholder="Attribute Value" name="jet_attributes1[<?=$value ?>][]"/>
						<?php }?>
						</td>
					<?php }?>
					
				<?php }
				elseif($result->free_text==1 && $result->display=='TRUE')
				{
					if($product_type=='simple')
					{?>
						
						<td style="width:50%"><?=$attrDes ?></td><td style="width:50%"><input type="text" value="" class="form-control" placeholder="Attribute Value" name="jet_attributes1[<?=$value ?>][]"/></td>
					<?php }
					
				}
				elseif($result->free_text==2 && $result->display=='TRUE')
				{
					$unitArray=explode(',',$resultAttr['units']);
					if($product_type=='simple')
					{?>
						<td style="width:50%"><?=$attrDes ?><span class="text-validator">Enter Attribute Value with Unit field.</span></td><td style="width:50%"><input type="text" value="" class="form-control" placeholder="Attribute Value" name="jet_attributes1[<?=$value ?>][]"/>
						<select class="form-control" name="jet_attributes1[<?=$value ?>][]">
						<option value="">Please Select Attribute Unit</option>
						<?php foreach ($unitArray as $val) {?>
							<option value="<?=$val ?>"><?=$val ?></option>
						<?php }?>
						</select></td>
					<?php }
					
				}?>
				</tr>
			<?php }?>
			</table>
			<?php 
			
		}else{
			echo "No attributes for the selected Category";
			$html_val='<script type="text/javascript">';
			$html_val.='j$("#savenuploadbutton").prop("disabled", false);';
			$html_val.="</script>";
			echo $html_val;
		}