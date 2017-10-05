<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
/* @var $this yii\web\View */
/* @var $model app\models\JetProduct */
$this->params['breadcrumbs'][] = "Jet Api Status";
$html='';
$html.='<table class="table table-striped table-bordered"><th>Api Name</th><th>Api Status</th><th>Api Response</th>';
if(is_array($response) && count($response)>0){
	foreach ($response as $key=>$value) {

		$value_res=json_decode($value,true);
		$html.='<tr><td>'.$key.'</td><td>'.$value_res['http_code'].'</td>';
		//if($value_res['http_code']!=200){
			$html.='<td>'.$value.'</td>';
		//}
		$html.='</tr>';
	}
}
$html.='</table>';
echo $html;
?>